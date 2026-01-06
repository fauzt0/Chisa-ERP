<?php
/**
 * ============================================================================
 * COMPONENTE DE BREADCRUMB (MIGAS DE PAN)
 * ============================================================================
 * 
 * Convierte un string de breadcrumb en un componente visual con enlaces
 * navegables usando Bootstrap.
 * 
 * ----------------------------------------------------------------------------
 * INSTRUCCIONES DE USO:
 * ----------------------------------------------------------------------------
 * 
 * 1. EN EL CONTROLADOR:
 *    Define el breadcrumb como string separado por " > "
 * 
 *    Ejemplo:
 *    $this->viewData['breadcrumb'] = 'Inicio > Gestión de usuarios > Alta';
 * 
 * 2. EN LA VISTA:
 *    Carga el componente pasando la variable breadcrumb
 * 
 *    Ejemplo:
 *    <?php $this->load->view('components/breadcrumb', ['breadcrumb' => $breadcrumb]); ?>
 * 
 * 3. PERSONALIZAR URLS:
 *    Edita el array $breadcrumb_urls más abajo para agregar/modificar rutas
 * 
 *    Ejemplo:
 *    'Recursos Humanos' => base_url('rh/RecursosHumanos'),
 * 
 * ----------------------------------------------------------------------------
 * EJEMPLOS DE BREADCRUMBS:
 * ----------------------------------------------------------------------------
 * 
 * Simple (2 niveles):
 *   'Inicio > Dashboard'
 *   Resultado: Inicio (link) > Dashboard (activo)
 * 
 * Complejo (4 niveles):
 *   'Inicio > Usuarios > Gestión > Editar'
 *   Resultado: Inicio (link) > Usuarios (link) > Gestión (link) > Editar (activo)
 * 
 * ----------------------------------------------------------------------------
 * NOTAS:
 * ----------------------------------------------------------------------------
 * - El último elemento siempre se marca como "activo" (sin enlace)
 * - Los elementos intermedios son enlaces clicables
 * - Si un elemento no tiene URL definida, se usa "#" por defecto
 * - El separador debe ser " > " (espacio-mayor que-espacio)
 * 
 * @param string $breadcrumb String con las migas de pan separadas por ">"
 * @version 1.0
 * @author ERP Chisa Recubrimientos
 */

// Verificar que existe el breadcrumb
if (!isset($breadcrumb) || empty($breadcrumb)) {
    return;
}

// Parsear el breadcrumb
$items = array_map('trim', explode('>', $breadcrumb));

// Mapeo de nombres a URLs (puedes personalizar según tus rutas)
$breadcrumb_urls = [
    'Inicio' => base_url()."dashboard",
    'Gestion de usuarios' => base_url('usuarios/GestionUsuarios'),
    'Alta de usuarios' => base_url('usuarios/GestionUsuarios/alta'),
    'Editar usuario' => '#', // No tiene enlace, es la página actual
    'Producción' => base_url('produccion/Productos'),
    'Alta de productos' => base_url('produccion/Productos/alta'),
    'Editar producto' => '#', // No tiene enlace, es la página actual
    // Agrega más rutas según necesites
];
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <?php foreach ($items as $index => $item): ?>
            <?php 
                $is_last = ($index === count($items) - 1);
                $url = isset($breadcrumb_urls[$item]) ? $breadcrumb_urls[$item] : '#';
            ?>
            
            <?php if ($is_last): ?>
                <!-- Último elemento (página actual) -->
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($item); ?>
                </li>
            <?php else: ?>
                <!-- Elementos con enlace -->
                <li class="breadcrumb-item">
                    <a href="<?php echo $url; ?>">
                        <?php echo htmlspecialchars($item); ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ol>
</nav>
