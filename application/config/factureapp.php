<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Facture App / Mi Contador — ambiente OAuth y API
|--------------------------------------------------------------------------
| Cambiar solo $config['factureapp_ambiente'] entre 'sandbox' y 'produccion'.
| Tras cambiar ambiente: volver a OAuth (facturacion/Facturas/conectar).
|
| Renovación de token: refresh_token se guarda en api_tokens; no hay refresh
| automático en código. Reautorizar con conectar o verificar con:
|   php index.php facturacion/Facturas/cli_probe
*/
$config['factureapp_ambiente'] = 'sandbox';

$config['factureapp_ambientes'] = [
    'sandbox' => [
        'api_base'      => 'https://app.facture.com.mx',
        'client_id'     => 'UrBzgu6LQzOEsX0ddS1r',
        'client_secret' => 'VBW726kPiPx4TsEGeYJ4SCFsSVQfwtlK',
    ],
    'produccion' => [
        'api_base'      => 'https://app.micontador.mx',
        'client_id'     => 'mv6uSwgKrt4h7M4c7l0B',
        'client_secret' => 'u6pHF5ftuOVIQzCh309fdVD6Vn2xpNv4',
    ],
];
