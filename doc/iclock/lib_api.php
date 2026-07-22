<?php
// lib_api.php
define('API_BASE', 'https://erp.chisarecubrimientos.com.mx/api/reloj/');
define('API_TOKEN', '4f016b4371933cf76e0fc0c311b1885273452d49ee89211b1218641d380086cb'); 

function hacer_peticion($endpoint, $payload = null, $metodo = 'POST') {
    $resultado = peticion_erp($endpoint, $payload, $metodo);
    return $resultado['body'];
}

function peticion_erp($endpoint, $payload = null, $metodo = 'POST') {
    $ch = curl_init(API_BASE . $endpoint);
    $headers = ['X-API-Key: ' . API_TOKEN, 'Content-Type: application/json'];

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    if ($metodo === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    } else {
        curl_setopt($ch, CURLOPT_HTTPGET, true);
    }

    $body = curl_exec($ch);
    $resultado = [
        'ok' => !curl_errno($ch),
        'http' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body,
        'error' => curl_error($ch),
    ];
    curl_close($ch);
    return $resultado;
}

/** POST sin X-API-Key — solo para sync_asistencias_debug en pruebas */
function peticion_erp_debug($endpoint, $payload) {
    $ch = curl_init(API_BASE . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

    $body = curl_exec($ch);
    $resultado = [
        'ok' => !curl_errno($ch),
        'http' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body' => $body,
        'error' => curl_error($ch),
    ];
    curl_close($ch);
    return $resultado;
}