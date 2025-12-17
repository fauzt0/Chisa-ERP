<?php
defined('BASEPATH') OR exit('No direct script access allowed');


if (!function_exists('get_status_code_by_result')) {
  /**
   * Devuelve el código HTTP según el tipo de resultado.
   * Tipos: success, created, emptyresult, badrequest, unauthorized, forbidden, notfound, error
   *
   * @param string $type
   * @param int $default
   * @return int
   */
  function get_status_code_by_result($type, $default = 200)
  {
    $t = strtolower(trim($type));
    $map = [
      'success'     => 200,
      'created'     => 201,
      'emptyresult' => 204,
      'badrequest'  => 400,
      'unauthorized'=> 401,
      'forbidden'   => 403,
      'notfound'    => 404,
      'error'       => 500,
    ];

    return isset($map[$t]) ? $map[$t] : (int)$default;
  }

  /**
   * Establece la respuesta de la vista como sin contenido
   * @param string $message
   */
  function setViewNoContent(string $message = 'No se ha encontrado contenido'): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = true;
    $CI->viewData['statusCode'] = get_status_code_by_result('emptyresult');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = '';//no tiene mensaje de error
  }

  /**
   * Establece la respuesta de la vista como exitosa
   * @param string $message
   */
  function setViewSuccess(string $message = 'Operación realizada con éxito'): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = true;
    $CI->viewData['statusCode'] = get_status_code_by_result('success');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = '';//no tiene mensaje de error
  }

  /**
   * Establece la respuesta de la vista como error
   * @param string $message
   * @param string $errorDetail
   */
  function setViewError(string $message = 'Ha ocurrido un error en la operación', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = false;
    $CI->viewData['statusCode'] = get_status_code_by_result('error');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = $errorDetail;//detalle del error
  }

  /**
   * Establece la respuesta de la vista como solicitud incorrecta
   * @param string $message
   * @param string $errorDetail
   */
  function setViewBadRequest(string $message = 'La solicitud es incorrecta', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = false;
    $CI->viewData['statusCode'] = get_status_code_by_result('badrequest');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = $errorDetail;//detalle del error
  }

  /**
   * Establece la respuesta de la vista como recurso no encontrado
   * @param string $message
   * @param string $errorDetail
   */
  function setViewNotFound(string $message = 'El recurso solicitado no fue encontrado', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = false;
    $CI->viewData['statusCode'] = get_status_code_by_result('notfound');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = $errorDetail;//detalle del error
  }

  /**
   * Establece la respuesta de la vista como no autorizado
   * @param string $message
   * @param string $errorDetail
   */
  function setViewUnauthorized(string $message = 'No autorizado para acceder al recurso', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = false;
    $CI->viewData['statusCode'] = get_status_code_by_result('unauthorized');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = $errorDetail;//detalle del error
  }

  /**
   * Establece la respuesta de la vista como prohibido el acceso
   * @param string $message
   * @param string $errorDetail
   */
  function setViewForbidden(string $message = 'Prohibido el acceso al recurso', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = false;
    $CI->viewData['statusCode'] = get_status_code_by_result('forbidden');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = $errorDetail;//detalle del error
  }

  /**
   * Establece la respuesta de la vista como recurso creado
   * @param string $message
   */
  function setViewCreated(string $message = 'Recurso creado con éxito'): void
  {
    $CI = &get_instance();
    $CI->viewData['success'] = true;
    $CI->viewData['statusCode'] = get_status_code_by_result('created');
    $CI->viewData['message'] = $message;
    $CI->viewData['error'] = '';//no tiene mensaje de error
  }

  /**
   * Peticiones AJAX o del API 
  */

  /**
   * Establece la respuesta de la API o peticiones AJAX como sin contenido
   * @param string $message
   */  
  function setOutputNoContet(string $message = 'Respuesta sin contenido'): void
  {
    $CI = &get_instance();
    $CI->outputData['success'] = true;
    $CI->outputData['statusCode'] = get_status_code_by_result('emptyresult');
    $CI->outputData['message'] = $message;
    $CI->outputData['error'] = '';//no tiene mensaje de error    
  }

  /**
   * Establece la respuesta de la API o peticiones AJAX como exitosa
   * @param string $message
   */
  function setOutputSuccess(string $message = 'Operación realizada con éxito'): void
  {
    $CI = &get_instance();
    $CI->outputData['success'] = true;
    $CI->outputData['statusCode'] = get_status_code_by_result('success');
    $CI->outputData['message'] = $message;
    $CI->outputData['error'] = '';//no tiene mensaje de error    
  }

  /**
   * Establece la respuesta de la API o peticiones AJAX como error
   * @param string $message
   * @param string $errorDetail
   */
  function setOutputError(string $message = 'Ha ocurrido un error en la operación', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->outputData['success'] = false;
    $CI->outputData['statusCode'] = get_status_code_by_result('error');
    $CI->outputData['message'] = $message;
    $CI->outputData['error'] = $errorDetail;//detalle del error    
  }
  
  /**
   * Establece la respuesta de la API o peticiones AJAX como solicitud incorrecta
   * @param string $message
   * @param string $errorDetail
   */
  function setOutputBadRequest(string $message = 'La solicitud es incorrecta', string $errorDetail = ''): void
  {
    $CI = &get_instance();
    $CI->outputData['success'] = false;
    $CI->outputData['statusCode'] = get_status_code_by_result('badrequest');
    $CI->outputData['message'] = $message;
    $CI->outputData['error'] = $errorDetail;//detalle del error    
  }

}