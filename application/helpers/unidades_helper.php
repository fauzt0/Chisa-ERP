<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Unidades Helper
 *
 * Conversión segura entre unidades de medida usadas en el sistema.
 *
 * CONTEXTO (Iteración 4 - Pre-órdenes Automáticas):
 * `detalle_formulacion.unidad` solo admite ('L','ml','Kg','g','Pza'), mientras
 * que `insumos.unidad_medida` admite 14 valores distintos ('Kg','g','mg','L',
 * 'mL','Pza','Cubeta','Tambo','Galón','m²','m³','Ton','Servicio','Otro').
 * Antes de comparar "cantidad requerida" (en la unidad de la fórmula) contra
 * "stock disponible" (en la unidad del insumo), hay que convertir de forma
 * segura o, si no es posible, señalarlo explícitamente en vez de asumir.
 *
 * Solo se convierte dentro de la MISMA familia física:
 *   - Masa:    Kg, g, mg, Ton      (base = gramos)
 *   - Volumen: L, mL/ml, m³        (base = mililitros)
 *   - Pieza:   Pza                 (sin conversión, solo igualdad exacta)
 *
 * Unidades NO convertibles automáticamente (requieren revisión manual):
 *   Cubeta, Tambo, Galón, m², Servicio, Otro
 *   - Galón se excluye a propósito por ser ambiguo (US=3.785L vs Imperial=4.546L)
 *     y no existe en el schema un dato que indique cuál usa Chisa.
 *   - m² es un área, no convertible a masa/volumen sin un dato de densidad o
 *     rendimiento que no existe en `insumos`.
 *   - Cubeta/Tambo son unidades de empaque sin tamaño estándar fijo.
 */

if (!function_exists('unidad_familia')) {
    /**
     * Determina la familia física de una unidad de medida.
     *
     * @param string $unidad
     * @return string 'masa' | 'volumen' | 'pieza' | 'no_convertible'
     */
    function unidad_familia($unidad) {
        $u = mb_strtolower(trim((string) $unidad), 'UTF-8');

        $masa = ['kg', 'g', 'mg', 'ton'];
        if (in_array($u, $masa, true)) return 'masa';

        $volumen = ['l', 'ml', 'm³', 'm3'];
        if (in_array($u, $volumen, true)) return 'volumen';

        if ($u === 'pza') return 'pieza';

        return 'no_convertible';
    }
}

if (!function_exists('unidad_factor_base')) {
    /**
     * Factor para convertir 1 unidad a la unidad base de su familia
     * (gramos para masa, mililitros para volumen). Retorna null si la
     * unidad no pertenece a una familia convertible.
     *
     * @param string $unidad
     * @return float|null
     */
    function unidad_factor_base($unidad) {
        $u = mb_strtolower(trim((string) $unidad), 'UTF-8');

        $factores = [
            // Masa → base: gramos
            'kg'  => 1000,
            'g'   => 1,
            'mg'  => 0.001,
            'ton' => 1000000,
            // Volumen → base: mililitros
            'l'   => 1000,
            'ml'  => 1,
            'm³'  => 1000000,
            'm3'  => 1000000,
            // Pieza → base: piezas
            'pza' => 1,
        ];

        return $factores[$u] ?? null;
    }
}

if (!function_exists('unidades_son_compatibles')) {
    /**
     * Indica si dos unidades pertenecen a la misma familia física
     * (y por lo tanto se pueden convertir de forma segura entre sí).
     *
     * @param string $unidad_a
     * @param string $unidad_b
     * @return bool
     */
    function unidades_son_compatibles($unidad_a, $unidad_b) {
        $fa = unidad_familia($unidad_a);
        $fb = unidad_familia($unidad_b);
        return $fa !== 'no_convertible' && $fa === $fb;
    }
}

if (!function_exists('convertir_unidad_insumo')) {
    /**
     * Convierte una cantidad de una unidad de origen a una unidad de destino,
     * SOLO si la conversión es segura (misma familia física). Nunca asume
     * factores para unidades ambiguas o no comparables.
     *
     * @param float  $cantidad        Cantidad en la unidad de origen
     * @param string $unidad_origen   Unidad de origen (ej. unidad de la fórmula)
     * @param string $unidad_destino  Unidad de destino (ej. unidad de stock del insumo)
     * @return array{
     *   success: bool,
     *   cantidad_convertida: float|null,
     *   factor: float|null,
     *   unidad_coincide: bool,
     *   familia: string,
     *   motivo: string|null
     * }
     */
    function convertir_unidad_insumo($cantidad, $unidad_origen, $unidad_destino) {
        $origen_norm  = mb_strtolower(trim((string) $unidad_origen), 'UTF-8');
        $destino_norm = mb_strtolower(trim((string) $unidad_destino), 'UTF-8');

        // Normaliza variantes equivalentes de escritura antes de comparar igualdad
        $normalizar_ml = function ($u) {
            return ($u === 'ml') ? 'ml' : $u; // 'mL' y 'ml' ya colapsan a 'ml' por strtolower
        };
        $origen_norm  = $normalizar_ml($origen_norm);
        $destino_norm = $normalizar_ml($destino_norm);

        // 1. Igualdad exacta (case-insensitive) → sin conversión necesaria
        if ($origen_norm === $destino_norm) {
            return [
                'success'             => true,
                'cantidad_convertida' => (float) $cantidad,
                'factor'              => 1.0,
                'unidad_coincide'     => true,
                'familia'             => unidad_familia($unidad_origen),
                'motivo'              => null,
            ];
        }

        $familia_origen  = unidad_familia($unidad_origen);
        $familia_destino = unidad_familia($unidad_destino);

        // 2. Piezas: solo se acepta igualdad exacta, nunca conversión numérica
        if ($familia_origen === 'pieza' || $familia_destino === 'pieza') {
            return [
                'success'             => false,
                'cantidad_convertida' => null,
                'factor'              => null,
                'unidad_coincide'     => false,
                'familia'             => 'pieza',
                'motivo'              => "\"$unidad_origen\" y \"$unidad_destino\" no son comparables: las piezas (Pza) solo se pueden comparar contra piezas.",
            ];
        }

        // 3. Misma familia física (masa↔masa o volumen↔volumen) → conversión segura
        if ($familia_origen !== 'no_convertible' && $familia_origen === $familia_destino) {
            $factor_origen  = unidad_factor_base($unidad_origen);
            $factor_destino = unidad_factor_base($unidad_destino);
            $factor = $factor_origen / $factor_destino;

            return [
                'success'             => true,
                'cantidad_convertida' => round((float) $cantidad * $factor, 6),
                'factor'              => $factor,
                'unidad_coincide'     => false,
                'familia'             => $familia_origen,
                'motivo'              => null,
            ];
        }

        // 4. Sin familia común y segura → NO se asume nada, requiere revisión manual
        return [
            'success'             => false,
            'cantidad_convertida' => null,
            'factor'              => null,
            'unidad_coincide'     => false,
            'familia'             => 'no_convertible',
            'motivo'              => "No hay una conversión segura definida entre \"$unidad_origen\" (fórmula) y \"$unidad_destino\" (insumo). Requiere revisión manual.",
        ];
    }
}
