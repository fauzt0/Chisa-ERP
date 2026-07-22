Ya hemos generado el Cliente ID y el Secret ID para la aplicación.
ESTA ES UNA COPIO DE LA DOCUMENTACIÓN DE LA API EN LA URL:  https://api.facture.com.mx/#7d3e9796-642c-45d6-a7d1-bcc8b3e259e7

Sigue:
#Autorización
Descripción: Punto de enlace para intecambiar un grant de OAuth2 por un access token.

Tipos de grant soportados.

Autorizathion code.- intercabia un authorization code por un access token.
Refresh token.- intercambia un refresh token por un access token.
Password.- intercambia un usuario y contraseña válidos por un access token.


Recuerda que un usuario puede generar hasta 3 token de acceso por aplicación, después de este número la API no podrá generar nuevos. El usuario puede gestionar sus permisos a través del módulo Permiso aplicación en la plataforma de Facture App.

-------------------

POST  Authorize
https://app.facture.com.mx/api/authorize?grant_type=authorization_code&redirect_uri=http://localhost&code=82cbc9c8948dec172248551e47f774c1&scope=facturacion,concepto

Example Request:
curl --location 'https://app.facture.com.mx/api/authorize?grant_type=authorization_code&redirect_uri=http%3A%2F%2Flocalhost&code=82cbc9c8948dec172248551e47f774c0' \
--header 'Content-Type: application/x-www-form-urlencoded' \
--data-urlencode 'client_id=ON6RO3erE60I1WAOPmk9g1QQsjTJ81ZE' \
--data-urlencode 'client_secret=r1Gd3FEuuNavx0DF8MHwkthwKJq8lFHk'

Descripción: Método para intercambiar un grant de tipo request code por un access token.

Para obtener un request code la aplicación cliente deberá deberá llevar a un usuario registrado en Facture App a la URL de Login. El desarrollador será el responsable de obtener el request code e intercambiarlo por un access token con este método.

Flujo desarrollador:
1.- Formar una URL válida con los siguientes datos:
 a)redirect_uri.- URL donde será redirigido el usuario al completar el formulario.
 b)response_type.- Usar el valor: code.
 c)client_id.- Id de cliente proporcionado por el staff de Facture App para la aplicación cliente.
 d)scope.- Una lista de scopes válidos separados por comas. Vea Scopes.
2.- Redirigir al usuario a la URL (ejemplo)
a)https://app.facture.com.mx/ws/login.jsp?redirect_uri=http://localhost&response_type=code&client_id=Ixp3Qc8AvASmkH2u&scope=facturacion,concepto
3.-Esperar por el request code en el redirect URI o bien obtenerlo del parametro request_code de la URL al finalizar el proceso de login.



Flujo usuario:
1.- El usuario va a la URL:Login.
2.- Completa el formulario de login
3.- Acepta brindar acceso a los scopes definidos en la URL por el desarrollador
4.- Si el proceso fue satisfactorio, la aplicación cliente podrá hacer peticiones con el access token intercambiado.


HEADERS
-Content-Type: application/x-www-form-urlencoded
Header requerido para identificar el tipo de petición
Accept: application/json
Header requerido para aceptar JSON como respuesta

PARAMS:
-grant_type:authorization_code
redirect_uri: http://localhost
code: 82cbc9c8948dec172248551e47f774c1
scope: facturacion,concepto

Body (urlencoded):
- client_id: ON6RO3erE60I1WAOPmk9g1QQsjTJ81ZE
Valor requerido para enviar el ID de aplicación cliente
-client_secret:r1Gd3FEuuNavx0DF8MHwkthwKJq8lFHk
Valor requerido para enviar la contraseña de la aplicación cliente

-------------------
POST Password
https://app.facture.com.mx/api/authorize?grant_type=password


Descripción: Método para intercambiar un Grant de tipo Password por un access token.

HEADERS
-Content-Type: application/x-www-form-urlencoded
Header requerido para identificar el tipo de petición

PARAMS
grant_type: password

Body (urlencode d):
-username: usuario@webservice.com
Valor requerido para enviar el correo de usuario en Facture App
-password: 12345678a
Valor requerido para enviar la contraseña de usuario en Facture App
-scope: timbrado sucursal facturacion cancelacion
Valor requerido para enviar los scopes a brindar en el access token. Vea Scopes
-client_id: ON6RO3erE60I1WAOPmk9g1QQsjTJ81ZE
Valor requerido para enviar el ID de aplicación cliente
-client_secret: r1Gd3FEuuNavx0DF8MHwkthwKJq8lFHk
Valor requerido para enviar la contraseña de la aplicación cliente


-------------------

POST Refresh Token
https://app.facture.com.mx/api/authorize?grant_type=refresh_token
Descripción: Método para intercambiar un refresh token por un nuevo access token.

Los access token obtenidos tienen una vigencia de 365 días, si el access token expira habrá que generar uno nuevo y para ello se usa este método que cambia un refresh token por un nuevo access token.

HEADERS
-Content-Type: application/x-www-form-urlencoded
Header requerido para identificar el tipo de petición

PARAMS
grant_type: refresh_token

Body (urlencoded):
-client_id: ON6RO3erE60I1WAOPmk9g1QQsjTJ81ZE
Valor requerido para enviar el ID de aplicación cliente
-client_secret: r1Gd3FEuuNavx0DF8MHwkthwKJq8lFHk
Valor requerido para enviar la contraseña de la aplicación cliente
-refresh_token:c4b9b20553078b87f59fcdd0be4c658a
Valor requerido para enviar el refresh token para obtener un nuevo access token


---
Sucursal
Punto de enlace para realizar acciones con sucursal de los emisores vinculados a la cuenta del usuario en la plataforma.

Métodos expuestos
find.- Obtiene una lista de resultados con sucursales vinculadas a la cuenta del usuario en la plataforma.
Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope sucursal a la aplicación cliente.

GET
Find
https://app.facture.com.mx/api/sucursal/find?offset=0&size=10
Descripción
Método para obtener el catálogo de Sucursales disponibles.

Búsqueda
Puede recuperar una(s) sucursal(es) de un emisor en especifico filtrando por el RFC del mismo, por ejemplo agregando los parametros de búsqueda en la URL:


&filter=empresa.rfc:eq!LAN7008173R5
AUTHORIZATION
OAuth 2.0
Access Token
<access-token>

HEADERS
Authorization
Bearer

Header requerido para autenticar la peticion.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

size
10

Example Request
Find
View More
curl
curl --location 'https://app.facture.com.mx/api/sucursal/find?offset=0&size=10' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "pagination": {
    "items": [
      {
        "id": 17694,
        "nombre": "PRUEBAS",
        "direccion": {
          "id": 655518,
          "catCodigoPostal": null,
          "calle": "FOO",
          "numerointerior": null,
          "numeroexterior": "123",
          "codigopostal": "58000",
          "colonia": "Morelia Centro",
          "municipio": "Morelia",
          "ciudad": "Morelia",
          "estado": "Michoacán de Ocampo",
          "pais": "México",
          "referencia": null
        }
      },
      {
        "id": 17699,
        "nombre": "SECUNDARIA",
        "direccion": {
          "id": 656263,
          "catCodigoPostal": null,
          "calle": "foo",
          "numerointerior": null,
          "numeroexterior": "123",
          "codigopostal": "50000",
          "colonia": "Centro",
          "municipio": "Toluca",
          "ciudad": "Toluca de Lerdo",
          "estado": "Hidalgo",
          "pais": "México",
          "referencia": null
        }
      },
      {
        "id": 17700,
        "nombre": "Matriz",
        "direccion": {
          "id": 656264,
          "catCodigoPostal": null,
          "calle": "foo",
          "numerointerior": null,
          "numeroexterior": "123",
          "codigopostal": "58000",
          "colonia": "Morelia Centro",
          "municipio": "Morelia",
          "ciudad": "Morelia",
          "estado": "Michoacán de Ocampo",
          "pais": "México",
          "referencia": null
        }
      },
      {
        "id": 17701,
        "nombre": "SELVA",
        "direccion": {
          "id": 656265,
          "catCodigoPostal": null,
          "calle": "foo",
          "numerointerior": null,
          "numeroexterior": "123",
          "codigopostal": "58000",
          "colonia": "Morelia Centro",
          "municipio": "Morelia",
          "ciudad": "Morelia",
          "estado": "Michoacán de Ocampo",
          "pais": "México",
          "referencia": null
        }
      }
    ],
    "count": 4,
    "offset": 0
  },
  "message": "Petición satisfactoria."
}
Facturación
Punto de enlace para realizar acciones con comprobates emitidos dentro de la plataforma.

Métodos expuestos

find.- Obtiene una lista de resultados con comprobantes emitidos en la plataforma.

get.- Obtiene un resultado con un comprobante emitido en la plataforma.

envío.- Permite enviar por correo electrónico una lista de comprobantes a diferentes destinatarios.

recuperar.- Permite recuperar los archivos XML y PDF de un listado de comproabantes emitidos en la plataforma.

Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope facturacion a la aplicación cliente.

GET
Find
https://app.facture.com.mx/api/facturacion/find
Descripción
Método que permite obtener una lista de resultados con comprobantes emitidos desde la cuenta de un usuario registrado en Facture App.

AUTHORIZATION
OAuth 2.0
Access Token
<access-token>

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

Parametro requerido para definir el inicio de la lista de resultados.

size
10

Parametro requerido para definir el tamaño de la lista de resultados (máximo 100).

orderby
orderby?fecha:lt

Parametro opcional para definir un ordenamiento de la lista de resultados. Vea Ordenamiento

filter
cancelada:eq!true

Parametro opcional para agregar un filtrado a la lista de resultados. Vea Filtrado

type
movil

Parametro opcional para definir el tipo de lista de resultados. Vea Tipos de resultados

Example Request
Find
View More
curl
curl --location 'https://app.facture.com.mx/api/facturacion/find?offset=0&size=10&orderby=fecha%3Aasc&filter=cancelada%3Aeq!true&type=movil' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json'
200 OK
Example Response
Body
Headers (1)
View More
json
{
  "succeed": true,
  "code": 2000,
  "pagination": {
    "items": [
      {
        "sucursal": "PRUEBAS",
        "serie": "A[185]",
        "total": 906.13,
        "rfcReceptor": "LIO110531DE6",
        "nombreEmisor": "LAN AMERICAS SA",
        "fecha": 1509669778000,
        "folio": 200,
        "nombreReceptor": "LIONDEV SA DE CV",
        "cancelada": true,
        "rfcEmisor": "LAN7008173R5",
        "uuid": "adf69747-700d-4916-8d9f-9f7d83dbb16a",
        "correoReceptor": "ceo.lion.dev@gmail.com"
      },
      {
        "sucursal": "PRUEBAS",
        "serie": "A[185]",
        "total": 906.13,
        "rfcReceptor": "LIO110531DE6",
        "nombreEmisor": "LAN AMERICAS SA",
        "fecha": 1509669915000,
        "folio": 202,
        "nombreReceptor": "LIONDEV SA DE CV",
        "cancelada": true,
        "rfcEmisor": "LAN7008173R5",
        "uuid": "3d52726b-ec3d-4580-a0ea-dc9461462b3f",
        "correoReceptor": "ceo.lion.dev@gmail.com"
      }
    ],
    "count": 2,
    "offset": 0
  },
  "message": "Petición satisfactoria."
}
GET
Get
https://app.facture.com.mx/api/facturacion?id=e31e2908-de97-4c6a-b85b-dcfd51e8e44b
Descripción
Método que permite obtener los detalles de un comprobante previamente timbrado.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
id
e31e2908-de97-4c6a-b85b-dcfd51e8e44b

Example Request
Get
View More
curl
curl --location 'https://app.facture.com.mx/api/facturacion?id=e31e2908-de97-4c6a-b85b-dcfd51e8e44b' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "entity": {
    "data": {
      "id": 81785,
      "fechatimbrado": 1510203717000,
      "tipocomprobante": "INGRESO",
      "mifolio": 206,
      "miserie": "A[1]",
      "uuid": "e31e2908-de97-4c6a-b85b-dcfd51e8e44b",
      "moneda": "MXN",
      "cbb": "https://verificacfdi.facturaelectronica.sat.gob.mx/default.aspx?id=E31E2908-DE97-4C6A-B85B-DCFD51E8E44B&re=LAN7008173R5&rr=XAXX010101000&tt=1.16&fe=i7wPUg==",
      "decimales": 2,
      "mensaje": null,
      "cancelada": false,
      "recibo": false,
      "fecha": 1510203709000,
      "subtotal": 1,
      "total": 1.16,
      "descuento": 0,
      "totalimpuestosretenidos": 0,
      "totalimpuestostrasladados": 0.16,
      "tercero": {
        "id": 615941,
        "nombre": "PUBLICO EN GENERAL",
        "rfc": "XAXX010101000",
        "email": null,
        "email2": null,
        "email3": null,
        "direccion": null
      },
      "emisor": {
        "id": 17300,
        "nombre": "LAN AMERICAS SA",
        "rfc": "LAN7008173R5",
        "tipo": null,
        "regimenes": null,
        "sucursales": null
      },
      "sucursal": {
        "id": 17694,
        "nombre": "PRUEBAS",
        "direccion": null
      },
      "serie": {
        "id": 24332,
        "serie": "A",
        "folioinicial": 0,
        "foliofinal": 0,
        "folioactual": 208,
        "tipo": null
      },
      "comprobantes": null
    }
  },
  "message": "Petición satisfactoria."
}
POST
Envío
https://app.facture.com.mx/api/facturacion/enviar
Descripción
Método que permite enviar una lista de comprobantes a sus respectivos destinararios.

En el listado de comprobantes deberá envíar los UUID que desea envíar además de una lista de Destinatarios a los cuales desea se envíen cada comprobante.

AUTHORIZATION
OAuth 2.0
Access Token
<access-token>

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header requerido para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"uuid" : "123e4567-e89b-12d3-a456-426655440000",
					"recipients" : [
						"info@liondev.com.mx",
						"dpatino@facture.com.mx"
					]
				},
				{
					"uuid" : "fde657cf-5fe0-4e7b-bc1f-ef8a5c470df5",
					"recipients" : [
						"info@liondev.com.mx",
						"dpatino@facture.com.mx"
					]
				}
			]
		}
	}
}
Example Request
Envío
View More
curl
curl --location 'https://app.facture.com.mx/api/facturacion/enviar' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw '{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"uuid" : "123e4567-e89b-12d3-a456-426655440000",
					"recipients" : [
						"info@liondev.com.mx",
						"dpatino@facture.com.mx"
					]
				},
				{
					"uuid" : "fde657cf-5fe0-4e7b-bc1f-ef8a5c470df5",
					"recipients" : [
						"info@liondev.com.mx",
						"dpatino@facture.com.mx"
					]
				}
			]
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "recipients": [
          "info@liondev.com.mx",
          "dpatino@facture.com.mx"
        ],
        "succeed": false,
        "uuid": "123e4567-e89b-12d3-a456-426655440000",
        "message": "Comprobante no enviado.",
        "error": "Item '123e4567-e89b-12d3-a456-426655440000' no existe o no pertenece al usuario."
      },
      {
        "recipients": [
          "info@liondev.com.mx",
          "dpatino@facture.com.mx"
        ],
        "succeed": true,
        "uuid": "fde657cf-5fe0-4e7b-bc1f-ef8a5c470df5",
        "message": "Comprobante enviado correctamente."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
POST
Recuperar
https://app.facture.com.mx/api/facturacion/recuperar
Descripción
Método que permite recuperar los archivos XML y PDF de una lista de comprobantes.

Petición
Deberá envíar una lista de objetos comprobante con sus respectivos atributos, ejemplo:


{
    "requestUuid" : "123e4567-e89b-12d3-a456-426655440001"
}
Donde:
requestUuid.- UUID para identificar el comprobante en la petición, es el folio fiscal que asigna el PAC al timbrarlo.
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
  "entity": {
    "data":{
      "comprobantes":[
        {
          "requestUuid" : "585d5509-222d-4484-947f-b6a0b8762aac"
        },
        {
          "requestUuid" : "3b503cfc-a5e1-42c7-9957-d280f3a29f23"
        },
        {
          "requestUuid" : "3b503cfc-0000-0000-0000-d280f3a29f23"
        }
      ]
    }
  }
}
Example Request
Recuperar
View More
curl
curl --location 'https://app.facture.com.mx/api/facturacion/recuperar' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "entity": {
    "data":{
      "comprobantes":[
        {
          "requestUuid" : "585d5509-222d-4484-947f-b6a0b8762aac"
        },
        {
          "requestUuid" : "3b503cfc-a5e1-42c7-9957-d280f3a29f23"
        },
        {
          "requestUuid" : "3b503cfc-0000-0000-0000-d280f3a29f23"
        }
      ]
    }
  }
}'
200 OK
Example Response
Body
Headers (5)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "requestUuid": "585d5509-222d-4484-947f-b6a0b8762aac",
        "succeed": true,
        "xml": "77u/PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pgo8Y2ZkaTpDb21wcm9iYW50ZSBMdWdhckV4cGVkaWNpb249IjU4MTk5IiBNZXRvZG9QYWdvPSJQVUUiIFRpcG9EZUNvbXByb2JhbnRlPSJJIiBUb3RhbD0iMjY4Ni45MSIgTW9uZWRhPSJNWE4iIFN1YlRvdGFsPSIyMzE2LjMwIiBDZXJ0aWZpY2Fkbz0iTUlJRjBUQ0NBN21nQXdJQkFnSVVNakF3TURFd01EQXdNREF6TURBd01qSTRNVFl3RFFZSktvWklodmNOQVFFTEJRQXdnZ0ZtTVNBd0hnWURWUVFEREJkQkxrTXVJRElnWkdVZ2NISjFaV0poY3lnME1EazJLVEV2TUMwR0ExVUVDZ3dtVTJWeWRtbGphVzhnWkdVZ1FXUnRhVzVwYzNSeVlXTnB3N051SUZSeWFXSjFkR0Z5YVdFeE9EQTJCZ05WQkFzTUwwRmtiV2x1YVhOMGNtRmphY096YmlCa1pTQlRaV2QxY21sa1lXUWdaR1VnYkdFZ1NXNW1iM0p0WVdOcHc3TnVNU2t3SndZSktvWklodmNOQVFrQkZocGhjMmx6Ym1WMFFIQnlkV1ZpWVhNdWMyRjBMbWR2WWk1dGVERW1NQ1FHQTFVRUNRd2RRWFl1SUVocFpHRnNaMjhnTnpjc0lFTnZiQzRnUjNWbGNuSmxjbTh4RGpBTUJnTlZCQkVNQlRBMk16QXdNUXN3Q1FZRFZRUUdFd0pOV0RFWk1CY0dBMVVFQ0F3UVJHbHpkSEpwZEc4Z1JtVmtaWEpoYkRFU01CQUdBMVVFQnd3SlEyOTViMkZqdzZGdU1SVXdFd1lEVlFRdEV3eFRRVlE1TnpBM01ERk9Uak14SVRBZkJna3Foa2lHOXcwQkNRSU1FbEpsYzNCdmJuTmhZbXhsT2lCQlEwUk5RVEFlRncweE5qRXdNalV5TVRVME1UbGFGdzB5TURFd01qVXlNVFUwTVRsYU1JRzlNUjR3SEFZRFZRUURFeFZOUWlCSlJFVkJVeUJFU1VkSlZFRk1SVk1nVTBNeEhqQWNCZ05WQkNrVEZVMUNJRWxFUlVGVElFUkpSMGxVUVV4RlV5QlRRekVlTUJ3R0ExVUVDaE1WVFVJZ1NVUkZRVk1nUkVsSFNWUkJURVZUSUZORE1TVXdJd1lEVlFRdEV4eE1RVTQ0TlRBM01qWTRTVUVnTHlCR1ZVRkNOemN3TVRFM1FsaEJNUjR3SEFZRFZRUUZFeFVnTHlCR1ZVRkNOemN3TVRFM1RVUkdVazVPTURreEZEQVNCZ05WQkFzVUMxQnlkV1ZpWVY5RFJrUkpNSUlCSWpBTkJna3Foa2lHOXcwQkFRRUZBQU9DQVE4QU1JSUJDZ0tDQVFFQWpIcjRLZW9FeDNCZGtRUDkzQXVONGZLbzByQ1pRc2Q5UkpHQnpRRnZobVBKakdhVlA4MU9VT1JNK2xDUmxseFp4QVRaQ0FJRlBPVDNqbDV3WWd0b2xHWVdXcnQxSG9BaXVqYTFMS0RHS3JZZ3BoMHFXWUtZZXVldzEwZlR5VitBZVNieDFqVEt6MVBBQWFrMDZoeDRNMHJ2bWRpR08vS2cwMC8wd0t6NS9MM1pJTVhFaitIZ3IwSUdoL3lVSXk4bTVhS2YrOWp3dU50dG0veERvZVczQThweHVpZFBVMVoxdmxpYVpzNzVuODloQzlMTndzaGhvYUYzQXZYSXNnTERldWg5V29NR1NtMEhyaWxQOXVtRm5tM25HVUVTaUphMTVFcDdMYkc0Q0loWnJya25TbTRmeXJQazlLQWlncUxZTUpoUnNSd2ZwMnFuY0FuQUErRnVTUUlEQVFBQm94MHdHekFNQmdOVkhSTUJBZjhFQWpBQU1Bc0dBMVVkRHdRRUF3SUd3REFOQmdrcWhraUc5dzBCQVFzRkFBT0NBZ0VBZDd0NDh0Z2F3QzlhY3pyR1l0KzRHRlJjamoxTFZLVjNORWxHK1ZIMnM1MUtQa0tQTGoyU3c2T2lFT0dkKzQ5c3B4SGoxVlI1TUZ2Sm8vcEVKTFkzRXVMVGlmQzlZWlpZQzhwSE5EaUEvZVN2S3FXNUpOenA1L3JnczNxQUcxR3JmZE5HdVNEM0ZrcWhEZEI2dEpZcXpUYzEySUM3eEVBaEtYcldaWUNxYSt6YjlvZ3R6clVWTDN2UlJMTXBuR0VISzJ5eDhkaHZHMzVxakhFZlh5dW9Cc1dJTHJWbW5QcERDRk8vQ0NMUUIxT3VNdGkxbWxpcjZ2b0JOMEwxRWJGSzMwdzJiRXVWaWhBZVZMWDh2VmZNcTRaUEk3VVRMbmJsR25OMTFDQ3FpWmtXaGhlaFlyTWRDamI1dGhNa0VBK0NNbElhRkpZcDdwTmtMeFFkNFk1K3I4cFRyZHh4eXZwQTUxRElXZG94dndhT2l6MWJ6Wms2RWxWWTJyZnh3eVphSjE3Y0oxam1TNFliNVA0aDgrNXprbVpuUG1ScWZtYVZPM25zQXBMV1A2QTM4WkJyd3dzczQyOVBKTVNwZmVYS0d5c1BzcXdGMHlQM2Jsc003Q3c1MzM5M0xTSEdLTm0yR2dHMGtjckhuYmJrdTZ6NmZqQmRYTVFRNXZqUHVNTnl3L3BlM1B6UUxWb05PckQ1QU9vWm1TRzJUSTNEdFk0ZWRMZGlHbU5Ram8zTW1BTU1xNHM3bHI0QUVMUFdBWlJibk9sRDFuRVdHTGRScDFtVml0ZUR2WHdCTDlFOThFQjRLOXhLMjFEdmdKNnJ6dy9EOXJYNmVwZUFOZm9YYXpXQzBpQ1ljQk5YaVBpa0FwY1c3M2EvSmwvV2prRXdFZGtML2pMajBLQ2VwNTg9IiBOb0NlcnRpZmljYWRvPSIyMDAwMTAwMDAwMDMwMDAyMjgxNiIgRm9ybWFQYWdvPSIwMSIgU2VsbG89Ik5nY2x2SjRIdEpEK2ozVkloZ09wbFF5eUhiVVpJbitoNG5Kd2NiS0dEUVJDeU1aSlNXTEIySFl1ZlZhN0NENUhCMVExM0VBazFxNFdFM0tXWk5wREhaclExbk9XWU8xd2ZCbWRRZGY3YTZ1Z2FtYkxKVlBud1JkTkRrWmtJNXhSVjFienhlL1kzYUJiZVBzaGIzTHFkbmRXdWtqRnJENDhod3IwQ2M5eUtiZDJNZW9kUHV6c1NESDQvb2ZVd3UwOHNGMVBtL08xWWdUenZPREsyMUwrMWxWN0JqaWZpNXNWN2RVYU5rVkFodE9Eekc3d21TMjJEdkFNNU5zME1HQmthRW5Ta2xQK3JmanYzeitnaEJqWEY5cFdEZWRoQUNlUVpTZVZ5R254Z2swWWhObDBDS1Ezd2JncnVqZ0huSVczcmQxY0tTVnVNcXdwOUFWTHA5bmxKUT09IiBGZWNoYT0iMjAxOC0wMi0xMVQyMTowMToxMyIgRm9saW89IjkzIiBTZXJpZT0iQSIgVmVyc2lvbj0iMy4zIiB4c2k6c2NoZW1hTG9jYXRpb249Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9jZmQvMyBodHRwOi8vd3d3LnNhdC5nb2IubXgvc2l0aW9faW50ZXJuZXQvY2ZkLzMvY2ZkdjMzLnhzZCIgeG1sbnM6Y2ZkaT0iaHR0cDovL3d3dy5zYXQuZ29iLm14L2NmZC8zIiB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIj4KICAgIDxjZmRpOkVtaXNvciBSZWdpbWVuRmlzY2FsPSI2MDEiIE5vbWJyZT0iRW1pc29yIGRlIHBydWViYXMgSEgiIFJmYz0iTEFOODUwNzI2OElBIi8+CiAgICA8Y2ZkaTpSZWNlcHRvciBVc29DRkRJPSJHMDEiIE5vbWJyZT0iUEnDkUEsIHNveSB1biBwZW5kZWpvIiBSZmM9IkFBQTAxMDEwMUFBQSIvPgogICAgPGNmZGk6Q29uY2VwdG9zPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjQ5My4xNiIgVmFsb3JVbml0YXJpbz0iMjQ2LjU4IiBEZXNjcmlwY2lvbj0iRUxFQ1RSw5MiIFVuaWRhZD0iUGllemEiIENsYXZlVW5pZGFkPSJIODciIENhbnRpZGFkPSIyLjAwIiBOb0lkZW50aWZpY2FjaW9uPSI2ODQ2ODE2ODEiIENsYXZlUHJvZFNlcnY9IjQyMjk1MTAwIj4KICAgICAgICAgICAgPGNmZGk6SW1wdWVzdG9zPgogICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9Ijc4LjkxIiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSI0OTMuMTYiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjExLjY2IiBWYWxvclVuaXRhcmlvPSIxMS42NiIgRGVzY3JpcGNpb249IkFMQ09IT0wiIFVuaWRhZD0iS2lsb2dyYW1vIiBDbGF2ZVVuaWRhZD0iS0dNIiBDYW50aWRhZD0iMS4wMCIgTm9JZGVudGlmaWNhY2lvbj0iNTE2NTE2NSIgQ2xhdmVQcm9kU2Vydj0iNTAxNTE1MTMiPgogICAgICAgICAgICA8Y2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iMS44NyIgVGFzYU9DdW90YT0iMC4xNiIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMiIgQmFzZT0iMTEuNjYiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjU1OS4wMiIgVmFsb3JVbml0YXJpbz0iNTU5LjAyIiBEZXNjcmlwY2lvbj0iQUNJRE8gUE9MSUdMSUNPTElDTyIgVW5pZGFkPSJLaWxvZ3JhbW8iIENsYXZlVW5pZGFkPSJLR00iIENhbnRpZGFkPSIxLjAwIiBOb0lkZW50aWZpY2FjaW9uPSI2NDY4NDY4NCIgQ2xhdmVQcm9kU2Vydj0iNTAxNTE2MDUiPgogICAgICAgICAgICA8Y2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iODkuNDQiIFRhc2FPQ3VvdGE9IjAuMTYiIFRpcG9GYWN0b3I9IlRhc2EiIEltcHVlc3RvPSIwMDIiIEJhc2U9IjU1OS4wMiIvPgogICAgICAgICAgICAgICAgPC9jZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgPC9jZmRpOkltcHVlc3Rvcz4KICAgICAgICA8L2NmZGk6Q29uY2VwdG8+CiAgICAgICAgPGNmZGk6Q29uY2VwdG8gSW1wb3J0ZT0iODEuNjYiIFZhbG9yVW5pdGFyaW89IjgxLjY2IiBEZXNjcmlwY2lvbj0iQUJBVEVMRU5HVUFTIiBVbmlkYWQ9IlBpZXphIiBDbGF2ZVVuaWRhZD0iSDg3IiBDYW50aWRhZD0iMS4wMCIgTm9JZGVudGlmaWNhY2lvbj0iNjg0NjgxIiBDbGF2ZVByb2RTZXJ2PSI0MzIyMTYwMCI+CiAgICAgICAgICAgIDxjZmRpOkltcHVlc3Rvcz4KICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkbyBJbXBvcnRlPSIxMy4wNyIgVGFzYU9DdW90YT0iMC4xNiIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMiIgQmFzZT0iODEuNjYiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjI1OC42MyIgVmFsb3JVbml0YXJpbz0iODYuMjEiIERlc2NyaXBjaW9uPSJDYXRlZ29yaWEgY3JlYWRhIGFsIHZ1ZWxvIiBVbmlkYWQ9IktpbG9ncmFtbyIgQ2xhdmVVbmlkYWQ9IktHTSIgQ2FudGlkYWQ9IjMuMDAiIE5vSWRlbnRpZmljYWNpb249IjEyMzQ1Njc4OTAxMjM0NTAiIENsYXZlUHJvZFNlcnY9IjgyMTUxNzA2Ij4KICAgICAgICAgICAgPGNmZGk6SW1wdWVzdG9zPgogICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9IjQxLjM4IiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSIyNTguNjMiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjczOS43NyIgVmFsb3JVbml0YXJpbz0iMjQ2LjU5IiBEZXNjcmlwY2lvbj0ic295IHVuYSBwdXRpdGEgY29uIGFjZW50w7PCqCoiIFVuaWRhZD0iTWV0cm8gY3VhZHJhZG8gaG9yYSAiIENsYXZlVW5pZGFkPSJMMTQiIENhbnRpZGFkPSIzLjAwIiBOb0lkZW50aWZpY2FjaW9uPSIzOTQ5NThZMzkiIENsYXZlUHJvZFNlcnY9IjQyMjk1MTAwIj4KICAgICAgICAgICAgPGNmZGk6SW1wdWVzdG9zPgogICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9IjExOC4zNiIgVGFzYU9DdW90YT0iMC4xNiIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMiIgQmFzZT0iNzM5Ljc3Ii8+CiAgICAgICAgICAgICAgICA8L2NmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICA8L2NmZGk6SW1wdWVzdG9zPgogICAgICAgIDwvY2ZkaTpDb25jZXB0bz4KICAgICAgICA8Y2ZkaTpDb25jZXB0byBJbXBvcnRlPSIxNzIuNDAiIFZhbG9yVW5pdGFyaW89IjM0LjQ4IiBEZXNjcmlwY2lvbj0iTWFuemFuaXRhIiBVbmlkYWQ9IkxpdHJvIiBDbGF2ZVVuaWRhZD0iTFRSIiBDYW50aWRhZD0iNS4wMCIgTm9JZGVudGlmaWNhY2lvbj0iNTQ5ODg5OTgiIENsYXZlUHJvZFNlcnY9IjUwMTkzMDAyIj4KICAgICAgICAgICAgPGNmZGk6SW1wdWVzdG9zPgogICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9IjI3LjU4IiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSIxNzIuNDAiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgPC9jZmRpOkNvbmNlcHRvcz4KICAgIDxjZmRpOkltcHVlc3RvcyBUb3RhbEltcHVlc3Rvc1RyYXNsYWRhZG9zPSIzNzAuNjEiPgogICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iMzcwLjYxIiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIi8+CiAgICAgICAgPC9jZmRpOlRyYXNsYWRvcz4KICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICA8Y2ZkaTpDb21wbGVtZW50bz4KICAgICAgICA8dGZkOlRpbWJyZUZpc2NhbERpZ2l0YWwgRmVjaGFUaW1icmFkbz0iMjAxOC0wMi0xMVQyMTowMToxNyIgTm9DZXJ0aWZpY2Fkb1NBVD0iMjAwMDEwMDAwMDAzMDAwMjIzMjMiIFJmY1Byb3ZDZXJ0aWY9IkFBQTAxMDEwMUFBQSIgU2VsbG9DRkQ9Ik5nY2x2SjRIdEpEK2ozVkloZ09wbFF5eUhiVVpJbitoNG5Kd2NiS0dEUVJDeU1aSlNXTEIySFl1ZlZhN0NENUhCMVExM0VBazFxNFdFM0tXWk5wREhaclExbk9XWU8xd2ZCbWRRZGY3YTZ1Z2FtYkxKVlBud1JkTkRrWmtJNXhSVjFienhlL1kzYUJiZVBzaGIzTHFkbmRXdWtqRnJENDhod3IwQ2M5eUtiZDJNZW9kUHV6c1NESDQvb2ZVd3UwOHNGMVBtL08xWWdUenZPREsyMUwrMWxWN0JqaWZpNXNWN2RVYU5rVkFodE9Eekc3d21TMjJEdkFNNU5zME1HQmthRW5Ta2xQK3JmanYzeitnaEJqWEY5cFdEZWRoQUNlUVpTZVZ5R254Z2swWWhObDBDS1Ezd2JncnVqZ0huSVczcmQxY0tTVnVNcXdwOUFWTHA5bmxKUT09IiBTZWxsb1NBVD0ibDRYdEhGb1cwLzVQNUJYZFd2M2llYTdZU2llVWRhSGRULzhHdmVjaCtBa3ppM0NTbG9hdXJXZFNtNUZBbjZXK2tZTjBheUorT1ZtK3haQjExT2FtMGxJbXZ2THgrZTUwQjA5Z1lvS3BSWC9nM3IwMW9qWmhyZ1RBd3RjWmtYazRGMDlmMFk0UGh1NnhYTFFuMGtEbjhqc0g3aWJsUEpWQnZkSi9iVE51Y050OHNJdHhVMm9meWJFZHMzSkZTa2k4M2U2VldlV0tTZ3FSVkF1OVQ3Z2R5MWJ1NkdKMUpPaE9jSkhlL1dsMjBzNHlleHBKU05OYjZSNWpHR1VkQVlGZnVEUDB6Y0VKNkxkMkxsQ3VBN1UxdVJZYWdtdHh1emdLL1A2bjc3T21ocGNEODUwaFpBOTBrNDM1QzFxME9XVHlyYVc0djFqUEJJY0psRmtDRGs1d0xnPT0iIFVVSUQ9IjU4NWQ1NTA5LTIyMmQtNDQ4NC05NDdmLWI2YTBiODc2MmFhYyIgVmVyc2lvbj0iMS4xIiB4c2k6c2NoZW1hTG9jYXRpb249Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9UaW1icmVGaXNjYWxEaWdpdGFsIGh0dHA6Ly93d3cuc2F0LmdvYi5teC9zaXRpb19pbnRlcm5ldC9jZmQvVGltYnJlRmlzY2FsRGlnaXRhbC9UaW1icmVGaXNjYWxEaWdpdGFsdjExLnhzZCIgeG1sbnM6dGZkPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvVGltYnJlRmlzY2FsRGlnaXRhbCIvPgogICAgPC9jZmRpOkNvbXBsZW1lbnRvPgo8L2NmZGk6Q29tcHJvYmFudGU+Cg==",
        "pdf": "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MDIgMF0KZW5kb2JqCjIgMCBvYmoKPDwvQmFzZUZvbnQvSGVsdmV0aWNhL1R5cGUvRm9udC9FbmNvZGluZy9XaW5BbnNpRW5jb2RpbmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iagozIDAgb2JqCjw8L0Jhc2VGb250L0hlbHZldGljYS1Cb2xkL1R5cGUvRm9udC9FbmNvZGluZy9XaW5BbnNpRW5jb2RpbmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iago1IDAgb2JqCjw8L0Jhc2VGb250L0hlbHZldGljYS1PYmxpcXVlL1R5cGUvRm9udC9FbmNvZGluZy9XaW5BbnNpRW5jb2RpbmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iago3IDAgb2JqCjw8L0lUWFQoMi4xLjcpL1R5cGUvUGFnZXMvQ291bnQgMS9LaWRzWzEgMCBSXT4+CmVuZG9iago5IDAgb2JqCjw8L05hbWVzWyhKUl9QQUdFX0FOQ0hPUl8wXzEpIDggMCBSXT4+CmVuZG9iagoxMCAwIG9iago8PC9EZXN0cyA5IDAgUj4+CmVuZG9iagoxMSAwIG9iago8PC9OYW1lcyAxMCAwIFIvVHlwZS9DYXRhbG9nL1ZpZXdlclByZWZlcmVuY2VzPDwvUHJpbnRTY2FsaW5nL0FwcERlZmF1bHQ+Pi9QYWdlcyA3IDAgUj4+CmVuZG9iagoxMiAwIG9iago8PC9DcmVhdG9yKEphc3BlclJlcG9ydHMgTGlicmFyeSB2ZXJzaW9uIDYuNC4xKS9Qcm9kdWNlcihpVGV4dCAyLjEuNyBieSAxVDNYVCkvTW9kRGF0ZShEOjIwMTgwMjEyMTM0MDUxLTA2JzAwJykvQ3JlYXRpb25EYXRlKEQ6MjAxODAyMTIxMzQwNTEtMDYnMDAnKT4+CmVuZG9iagp4cmVmCjAgMTMKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDIwMzk2IDAwMDAwIG4gCjAwMDAwMjA3MTMgMDAwMDAgbiAKMDAwMDAyMDgwMSAwMDAwMCBuIAowMDAwMDAwMDE1IDAwMDAwIG4gCjAwMDAwMjA4OTQgMDAwMDAgbiAKMDAwMDAxNzI3OSAwMDAwMCBuIAowMDAwMDIwOTkwIDAwMDAwIG4gCjAwMDAwMjA2NzggMDAwMDAgbiAKMDAwMDAyMTA1MyAwMDAwMCBuIAowMDAwMDIxMTA3IDAwMDAwIG4gCjAwMDAwMjExNDAgMDAwMDAgbiAKMDAwMDAyMTI0NSAwMDAwMCBuIAp0cmFpbGVyCjw8L1Jvb3QgMTEgMCBSL0lEIFs8YjY1MzRiNTJmMzgwYjk4YWI1NDBiN2M5Y2VlNzY3NmI+PDA0MDc0NDJjZjkzZjRjN2ViZmYwZjZhY2ZlMDZlOTk3Pl0vSW5mbyAxMiAwIFIvU2l6ZSAxMz4+CnN0YXJ0eHJlZgoyMTQxMwolJUVPRgo="
      },
      {
        "requestUuid": "3b503cfc-a5e1-42c7-9957-d280f3a29f23",
        "succeed": true,
        "xml": "77u/PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pgo8Y2ZkaTpDb21wcm9iYW50ZSBMdWdhckV4cGVkaWNpb249IjU4MTk5IiBNZXRvZG9QYWdvPSJQVUUiIFRpcG9EZUNvbXByb2JhbnRlPSJJIiBUb3RhbD0iMTA2MS42MyIgTW9uZWRhPSJNWE4iIFN1YlRvdGFsPSI5MjcuNDMiIENlcnRpZmljYWRvPSJNSUlGMFRDQ0E3bWdBd0lCQWdJVU1qQXdNREV3TURBd01EQXpNREF3TWpJNE1UWXdEUVlKS29aSWh2Y05BUUVMQlFBd2dnRm1NU0F3SGdZRFZRUUREQmRCTGtNdUlESWdaR1VnY0hKMVpXSmhjeWcwTURrMktURXZNQzBHQTFVRUNnd21VMlZ5ZG1samFXOGdaR1VnUVdSdGFXNXBjM1J5WVdOcHc3TnVJRlJ5YVdKMWRHRnlhV0V4T0RBMkJnTlZCQXNNTDBGa2JXbHVhWE4wY21GamFjT3piaUJrWlNCVFpXZDFjbWxrWVdRZ1pHVWdiR0VnU1c1bWIzSnRZV05wdzdOdU1Ta3dKd1lKS29aSWh2Y05BUWtCRmhwaGMybHpibVYwUUhCeWRXVmlZWE11YzJGMExtZHZZaTV0ZURFbU1DUUdBMVVFQ1F3ZFFYWXVJRWhwWkdGc1oyOGdOemNzSUVOdmJDNGdSM1ZsY25KbGNtOHhEakFNQmdOVkJCRU1CVEEyTXpBd01Rc3dDUVlEVlFRR0V3Sk5XREVaTUJjR0ExVUVDQXdRUkdsemRISnBkRzhnUm1Wa1pYSmhiREVTTUJBR0ExVUVCd3dKUTI5NWIyRmp3NkZ1TVJVd0V3WURWUVF0RXd4VFFWUTVOekEzTURGT1RqTXhJVEFmQmdrcWhraUc5dzBCQ1FJTUVsSmxjM0J2Ym5OaFlteGxPaUJCUTBSTlFUQWVGdzB4TmpFd01qVXlNVFUwTVRsYUZ3MHlNREV3TWpVeU1UVTBNVGxhTUlHOU1SNHdIQVlEVlFRREV4Vk5RaUJKUkVWQlV5QkVTVWRKVkVGTVJWTWdVME14SGpBY0JnTlZCQ2tURlUxQ0lFbEVSVUZUSUVSSlIwbFVRVXhGVXlCVFF6RWVNQndHQTFVRUNoTVZUVUlnU1VSRlFWTWdSRWxIU1ZSQlRFVlRJRk5ETVNVd0l3WURWUVF0RXh4TVFVNDROVEEzTWpZNFNVRWdMeUJHVlVGQ056Y3dNVEUzUWxoQk1SNHdIQVlEVlFRRkV4VWdMeUJHVlVGQ056Y3dNVEUzVFVSR1VrNU9NRGt4RkRBU0JnTlZCQXNVQzFCeWRXVmlZVjlEUmtSSk1JSUJJakFOQmdrcWhraUc5dzBCQVFFRkFBT0NBUThBTUlJQkNnS0NBUUVBakhyNEtlb0V4M0Jka1FQOTNBdU40ZktvMHJDWlFzZDlSSkdCelFGdmhtUEpqR2FWUDgxT1VPUk0rbENSbGx4WnhBVFpDQUlGUE9UM2psNXdZZ3RvbEdZV1dydDFIb0FpdWphMUxLREdLcllncGgwcVdZS1lldWV3MTBmVHlWK0FlU2J4MWpUS3oxUEFBYWswNmh4NE0wcnZtZGlHTy9LZzAwLzB3S3o1L0wzWklNWEVqK0hncjBJR2gveVVJeThtNWFLZis5and1TnR0bS94RG9lVzNBOHB4dWlkUFUxWjF2bGlhWnM3NW44OWhDOUxOd3NoaG9hRjNBdlhJc2dMRGV1aDlXb01HU20wSHJpbFA5dW1Gbm0zbkdVRVNpSmExNUVwN0xiRzRDSWhacnJrblNtNGZ5clBrOUtBaWdxTFlNSmhSc1J3ZnAycW5jQW5BQStGdVNRSURBUUFCb3gwd0d6QU1CZ05WSFJNQkFmOEVBakFBTUFzR0ExVWREd1FFQXdJR3dEQU5CZ2txaGtpRzl3MEJBUXNGQUFPQ0FnRUFkN3Q0OHRnYXdDOWFjenJHWXQrNEdGUmNqajFMVktWM05FbEcrVkgyczUxS1BrS1BMajJTdzZPaUVPR2QrNDlzcHhIajFWUjVNRnZKby9wRUpMWTNFdUxUaWZDOVlaWllDOHBITkRpQS9lU3ZLcVc1Sk56cDUvcmdzM3FBRzFHcmZkTkd1U0QzRmtxaERkQjZ0SllxelRjMTJJQzd4RUFoS1hyV1pZQ3FhK3piOW9ndHpyVVZMM3ZSUkxNcG5HRUhLMnl4OGRodkczNXFqSEVmWHl1b0JzV0lMclZtblBwRENGTy9DQ0xRQjFPdU10aTFtbGlyNnZvQk4wTDFFYkZLMzB3MmJFdVZpaEFlVkxYOHZWZk1xNFpQSTdVVExuYmxHbk4xMUNDcWlaa1doaGVoWXJNZENqYjV0aE1rRUErQ01sSWFGSllwN3BOa0x4UWQ0WTUrcjhwVHJkeHh5dnBBNTFESVdkb3h2d2FPaXoxYnpaazZFbFZZMnJmeHd5WmFKMTdjSjFqbVM0WWI1UDRoOCs1emttWm5QbVJxZm1hVk8zbnNBcExXUDZBMzhaQnJ3d3NzNDI5UEpNU3BmZVhLR3lzUHNxd0YweVAzYmxzTTdDdzUzMzkzTFNIR0tObTJHZ0cwa2NySG5iYmt1Nno2ZmpCZFhNUVE1dmpQdU1OeXcvcGUzUHpRTFZvTk9yRDVBT29abVNHMlRJM0R0WTRlZExkaUdtTlFqbzNNbUFNTXE0czdscjRBRUxQV0FaUmJuT2xEMW5FV0dMZFJwMW1WaXRlRHZYd0JMOUU5OEVCNEs5eEsyMUR2Z0o2cnp3L0Q5clg2ZXBlQU5mb1hheldDMGlDWWNCTlhpUGlrQXBjVzczYS9KbC9XamtFd0Vka0wvakxqMEtDZXA1OD0iIE5vQ2VydGlmaWNhZG89IjIwMDAxMDAwMDAwMzAwMDIyODE2IiBGb3JtYVBhZ289IjAxIiBTZWxsbz0iRU03UCtMRC9zY3FWVUhZeE9FckFySGlndmdSS2ZjRWFmaUg4QW9FaFlWMVNOSTh3SnBoYzQ1NldGbjAxVUN5bnkwdDJ5NXhNemVjenEyMFRoeFg0Q1lidGFIcTFndnZySnFHdHpTUXZGUHdnSDNyS1N3MGNZSVVVSitYcVJYdFRsV1ZWdzZ1Z2dqWkZRZ2xPRm5tdmtkYWZjdGdwNEFPT0NpSCtOVzBDWVhEZlh0L1pTOVd2YW1SbWJROWFGQkpCMENlSGhVRXVXaVV5ckZBYUhOYk5HaDFQS1pkTldXSjhwSVh6cDQ1aGJqNVYxYVE2TWZha1BDVHEvdHN1L1ZDdkswdTNHL1NlOEY2V0dGUWNxUzg1WHFuNGYyb0F4all0RWVqdm44empqNkJtdkhnTDk2MVRtZndhOVpLbS9KQzdEVWVJVERjd1JwQjVSeDVpb3BVL3R3PT0iIEZlY2hhPSIyMDE4LTAyLTExVDIxOjAwOjUwIiBGb2xpbz0iOTIiIFNlcmllPSJBIiBWZXJzaW9uPSIzLjMiIHhzaTpzY2hlbWFMb2NhdGlvbj0iaHR0cDovL3d3dy5zYXQuZ29iLm14L2NmZC8zIGh0dHA6Ly93d3cuc2F0LmdvYi5teC9zaXRpb19pbnRlcm5ldC9jZmQvMy9jZmR2MzMueHNkIiB4bWxuczpjZmRpPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvY2ZkLzMiIHhtbG5zOnhzaT0iaHR0cDovL3d3dy53My5vcmcvMjAwMS9YTUxTY2hlbWEtaW5zdGFuY2UiPgogICAgPGNmZGk6RW1pc29yIFJlZ2ltZW5GaXNjYWw9IjYwMSIgTm9tYnJlPSJFbWlzb3IgZGUgcHJ1ZWJhcyBISCIgUmZjPSJMQU44NTA3MjY4SUEiLz4KICAgIDxjZmRpOlJlY2VwdG9yIFVzb0NGREk9IkcwMSIgTm9tYnJlPSJQScORQSwgc295IHVuIHBlbmRlam8iIFJmYz0iQUFBMDEwMTAxQUFBIi8+CiAgICA8Y2ZkaTpDb25jZXB0b3M+CiAgICAgICAgPGNmZGk6Q29uY2VwdG8gSW1wb3J0ZT0iMTgzLjI2IiBWYWxvclVuaXRhcmlvPSIxODMuMjYiIERlc2NyaXBjaW9uPSJFUVVJUE8gREUgQ0lSVUdJQSIgVW5pZGFkPSJDYWphIiBDbGF2ZVVuaWRhZD0iWEJYIiBDYW50aWRhZD0iMS4wMCIgTm9JZGVudGlmaWNhY2lvbj0iNjUxMzUxMzEzNSIgQ2xhdmVQcm9kU2Vydj0iMzIxMDE1MjIiPgogICAgICAgICAgICA8Y2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iMjkuMzIiIFRhc2FPQ3VvdGE9IjAuMTYiIFRpcG9GYWN0b3I9IlRhc2EiIEltcHVlc3RvPSIwMDIiIEJhc2U9IjE4My4yNiIvPgogICAgICAgICAgICAgICAgPC9jZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgPC9jZmRpOkltcHVlc3Rvcz4KICAgICAgICA8L2NmZGk6Q29uY2VwdG8+CiAgICAgICAgPGNmZGk6Q29uY2VwdG8gSW1wb3J0ZT0iODguNzEiIFZhbG9yVW5pdGFyaW89IjI5LjU3IiBEZXNjcmlwY2lvbj0iQ09NUExFSk8gQiIgVW5pZGFkPSJLaWxvZ3JhbW8iIENsYXZlVW5pZGFkPSJLR00iIENhbnRpZGFkPSIzLjAwIiBOb0lkZW50aWZpY2FjaW9uPSI0Njg0Njg0ODQiIENsYXZlUHJvZFNlcnY9IjUwMTYxOTAwIi8+CiAgICAgICAgPGNmZGk6Q29uY2VwdG8gSW1wb3J0ZT0iMTEuNjYiIFZhbG9yVW5pdGFyaW89IjExLjY2IiBEZXNjcmlwY2lvbj0iQUxDT0hPTCIgVW5pZGFkPSJLaWxvZ3JhbW8iIENsYXZlVW5pZGFkPSJLR00iIENhbnRpZGFkPSIxLjAwIiBOb0lkZW50aWZpY2FjaW9uPSI1MTY1MTY1IiBDbGF2ZVByb2RTZXJ2PSI1MDE1MTUxMyI+CiAgICAgICAgICAgIDxjZmRpOkltcHVlc3Rvcz4KICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkbyBJbXBvcnRlPSIxLjg3IiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSIxMS42NiIvPgogICAgICAgICAgICAgICAgPC9jZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgPC9jZmRpOkltcHVlc3Rvcz4KICAgICAgICA8L2NmZGk6Q29uY2VwdG8+CiAgICAgICAgPGNmZGk6Q29uY2VwdG8gSW1wb3J0ZT0iODEuNjYiIFZhbG9yVW5pdGFyaW89IjgxLjY2IiBEZXNjcmlwY2lvbj0iQUJBVEVMRU5HVUFTIiBVbmlkYWQ9IlBpZXphIiBDbGF2ZVVuaWRhZD0iSDg3IiBDYW50aWRhZD0iMS4wMCIgTm9JZGVudGlmaWNhY2lvbj0iNjg0NjgxIiBDbGF2ZVByb2RTZXJ2PSI0MzIyMTYwMCI+CiAgICAgICAgICAgIDxjZmRpOkltcHVlc3Rvcz4KICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkbyBJbXBvcnRlPSIxMy4wNyIgVGFzYU9DdW90YT0iMC4xNiIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMiIgQmFzZT0iODEuNjYiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjQ5My4xOCIgVmFsb3JVbml0YXJpbz0iMjQ2LjU5IiBEZXNjcmlwY2lvbj0ic295IHVuYSBwdXRpdGEgY29uIGFjZW50w7PCqCoiIFVuaWRhZD0iTWV0cm8gY3VhZHJhZG8gaG9yYSAiIENsYXZlVW5pZGFkPSJMMTQiIENhbnRpZGFkPSIyLjAwIiBOb0lkZW50aWZpY2FjaW9uPSIzOTQ5NThZMzkiIENsYXZlUHJvZFNlcnY9IjQyMjk1MTAwIj4KICAgICAgICAgICAgPGNmZGk6SW1wdWVzdG9zPgogICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9Ijc4LjkxIiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSI0OTMuMTgiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjY4Ljk2IiBWYWxvclVuaXRhcmlvPSIzNC40OCIgRGVzY3JpcGNpb249Ik1hbnphbml0YSIgVW5pZGFkPSJMaXRybyIgQ2xhdmVVbmlkYWQ9IkxUUiIgQ2FudGlkYWQ9IjIuMDAiIE5vSWRlbnRpZmljYWNpb249IjU0OTg4OTk4IiBDbGF2ZVByb2RTZXJ2PSI1MDE5MzAwMiI+CiAgICAgICAgICAgIDxjZmRpOkltcHVlc3Rvcz4KICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkbyBJbXBvcnRlPSIxMS4wMyIgVGFzYU9DdW90YT0iMC4xNiIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMiIgQmFzZT0iNjguOTYiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgPC9jZmRpOkNvbmNlcHRvcz4KICAgIDxjZmRpOkltcHVlc3RvcyBUb3RhbEltcHVlc3Rvc1RyYXNsYWRhZG9zPSIxMzQuMjAiPgogICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iMTM0LjIwIiBUYXNhT0N1b3RhPSIwLjE2IiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIi8+CiAgICAgICAgPC9jZmRpOlRyYXNsYWRvcz4KICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICA8Y2ZkaTpDb21wbGVtZW50bz4KICAgICAgICA8dGZkOlRpbWJyZUZpc2NhbERpZ2l0YWwgRmVjaGFUaW1icmFkbz0iMjAxOC0wMi0xMVQyMTowMDo1NCIgTm9DZXJ0aWZpY2Fkb1NBVD0iMjAwMDEwMDAwMDAzMDAwMjIzMjMiIFJmY1Byb3ZDZXJ0aWY9IkFBQTAxMDEwMUFBQSIgU2VsbG9DRkQ9IkVNN1ArTEQvc2NxVlVIWXhPRXJBckhpZ3ZnUktmY0VhZmlIOEFvRWhZVjFTTkk4d0pwaGM0NTZXRm4wMVVDeW55MHQyeTV4TXplY3pxMjBUaHhYNENZYnRhSHExZ3Z2ckpxR3R6U1F2RlB3Z0gzcktTdzBjWUlVVUorWHFSWHRUbFdWVnc2dWdnalpGUWdsT0ZubXZrZGFmY3RncDRBT09DaUgrTlcwQ1lYRGZYdC9aUzlXdmFtUm1iUTlhRkJKQjBDZUhoVUV1V2lVeXJGQWFITmJOR2gxUEtaZE5XV0o4cElYenA0NWhiajVWMWFRNk1mYWtQQ1RxL3RzdS9WQ3ZLMHUzRy9TZThGNldHRlFjcVM4NVhxbjRmMm9BeGpZdEVlanZuOHpqajZCbXZIZ0w5NjFUbWZ3YTlaS20vSkM3RFVlSVREY3dScEI1Ung1aW9wVS90dz09IiBTZWxsb1NBVD0iRWJ0VzdKTmNmcEdtWGk3aGI2RnVRWG4yQkdONGlBL2ZhSW9oZHVncHR0NGZDcDVWYmVrMlR5R2wzcnVkeHJHQjAvYnQrT0FlM2JSaUFhU09id0VRYUwwS1lPczRRREFBTThwK2lEdnI1TzZsaEpyaEJ6UG44Yzk5T0hZQ0JMclFYckFoZllVam1wV1dpN0lSU0ltYUpFQnQ4YXk5eWh0RzdXTzRzTnZoL0M3Y2lGYUh5b2VUYVpHWnJWbVJ0YVVVM2JyT0t6UHhLK2xocjYvOVZ6R2tHUTNGbUFRLzhxWXhZUkUwRFpZVHdaMmpadXROSlBIUGNldm5icnNmYm1EZ1RRR3dWTkNrbUxnbzJXNWthNUNLUVZ0d1VRZGxZZDFWOFE0OFdpVmNvNlZScmFTNGNvVENFLys2ZWlqc0tlMmJkTUVLTmltN0JaY2FUcnlHVEgrSGVRPT0iIFVVSUQ9IjNiNTAzY2ZjLWE1ZTEtNDJjNy05OTU3LWQyODBmM2EyOWYyMyIgVmVyc2lvbj0iMS4xIiB4c2k6c2NoZW1hTG9jYXRpb249Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9UaW1icmVGaXNjYWxEaWdpdGFsIGh0dHA6Ly93d3cuc2F0LmdvYi5teC9zaXRpb19pbnRlcm5ldC9jZmQvVGltYnJlRmlzY2FsRGlnaXRhbC9UaW1icmVGaXNjYWxEaWdpdGFsdjExLnhzZCIgeG1sbnM6dGZkPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvVGltYnJlRmlzY2FsRGlnaXRhbCIvPgogICAgPC9jZmRpOkNvbXBsZW1lbnRvPgo8L2NmZGk6Q29tcHJvYmFudGU+Cg==",
        "pdf": "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bmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iagozIDAgb2JqCjw8L0Jhc2VGb250L0hlbHZldGljYS1Cb2xkL1R5cGUvRm9udC9FbmNvZGluZy9XaW5BbnNpRW5jb2RpbmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iago1IDAgb2JqCjw8L0Jhc2VGb250L0hlbHZldGljYS1PYmxpcXVlL1R5cGUvRm9udC9FbmNvZGluZy9XaW5BbnNpRW5jb2RpbmcvU3VidHlwZS9UeXBlMT4+CmVuZG9iago3IDAgb2JqCjw8L0lUWFQoMi4xLjcpL1R5cGUvUGFnZXMvQ291bnQgMS9LaWRzWzEgMCBSXT4+CmVuZG9iago5IDAgb2JqCjw8L05hbWVzWyhKUl9QQUdFX0FOQ0hPUl8wXzEpIDggMCBSXT4+CmVuZG9iagoxMCAwIG9iago8PC9EZXN0cyA5IDAgUj4+CmVuZG9iagoxMSAwIG9iago8PC9OYW1lcyAxMCAwIFIvVHlwZS9DYXRhbG9nL1ZpZXdlclByZWZlcmVuY2VzPDwvUHJpbnRTY2FsaW5nL0FwcERlZmF1bHQ+Pi9QYWdlcyA3IDAgUj4+CmVuZG9iagoxMiAwIG9iago8PC9DcmVhdG9yKEphc3BlclJlcG9ydHMgTGlicmFyeSB2ZXJzaW9uIDYuNC4xKS9Qcm9kdWNlcihpVGV4dCAyLjEuNyBieSAxVDNYVCkvTW9kRGF0ZShEOjIwMTgwMjEyMTM0MDUyLTA2JzAwJykvQ3JlYXRpb25EYXRlKEQ6MjAxODAyMTIxMzQwNTItMDYnMDAnKT4+CmVuZG9iagp4cmVmCjAgMTMKMDAwMDAwMDAwMCA2NTUzNSBmIAowMDAwMDIwMzIxIDAwMDAwIG4gCjAwMDAwMjA2MzggMDAwMDAgbiAKMDAwMDAyMDcyNiAwMDAwMCBuIAowMDAwMDAwMDE1IDAwMDAwIG4gCjAwMDAwMjA4MTkgMDAwMDAgbiAKMDAwMDAxNzI3OSAwMDAwMCBuIAowMDAwMDIwOTE1IDAwMDAwIG4gCjAwMDAwMjA2MDMgMDAwMDAgbiAKMDAwMDAyMDk3OCAwMDAwMCBuIAowMDAwMDIxMDMyIDAwMDAwIG4gCjAwMDAwMjEwNjUgMDAwMDAgbiAKMDAwMDAyMTE3MCAwMDAwMCBuIAp0cmFpbGVyCjw8L1Jvb3QgMTEgMCBSL0lEIFs8NDI3NDUwNWQxMmZhYjQ2MTQxNjkwNjEyYTM1MWNhZDc+PGFjMmUwNDBiYWY3NDI4Y2QzNjhjYzcyMmYyMjQ0MjJmPl0vSW5mbyAxMiAwIFIvU2l6ZSAxMz4+CnN0YXJ0eHJlZgoyMTMzOAolJUVPRgo="
      },
      {
        "requestUuid": "3b503cfc-0000-0000-0000-d280f3a29f23",
        "succeed": false,
        "message": "Comprobante no existe.",
        "error": "Item '3b503cfc-0000-0000-0000-d280f3a29f23' no existe o no pertenece al usuario."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
Timbrado
Descripción

Punto de enlace para timbrar comprobantes.
Importante: para realizar pruebas deberá usar un emisor de pruebas, vaya a Ambiente de pruebas para más información.

Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope timbrado a la aplicación cliente.

Video tutorial sobre el uso de la API y timbrado JSON


POST
Timbrado
https://app.facture.com.mx/api/timbrado
Descripción
Método que permite timbrar una lista de al menos uno y máximo 25 comprobantes XML codificados en Base64.

Puede descargar un ejemplo de la petición XML a codificar en Base64 en los siguientes enlaces:

[Comprobante de Ingreso]

[Comprobante de Pago 2.0]

Petición

Deberá envíar una lista de objetos comprobante con sus respectivos atributos, ejemplo:

json
{
  "requestUuid" : "123e4567-e89b-12d3-a456-426655440001",
  "encode": "PD9...==",
  "itemUsuario" : {
    "id" : 56423
  }
}
Donde:

requestUuid.- UUID para identificar el comprobante en la petición.

encode.- XML válido codificado en Base64.

itemUsuario.- Item comprado en la tienda para una plantilla o tratamiento especial del comprobante.

Los atributos en negrita siempre deberán estar presentes, mientras que los en italica pueden existir o no.

Deberá incluir también un objeto sucursal donde deberá poner el ID de la sucursal que desea usar para la firma del documento.

json
"sucursal": {
  "id" : 17694
}
Resultado

Obtendrá la misma lista que envió pero con atributos que contendrán los resultados del timbrado y folio fiscal asignado en caso de ser satisfactorio.

json
{
  "requestUuid": "123e4567-e89b-12d3-a456-426655440000",
  "encode": "PD94...T4K",
  "succeed": true,
  "uuid": "6ddd0a38-6783-494c-bd81-1ed3b5b4ba45",
  "message": "Comprobante procesado correctamente."
}
Donde:

requestUuid.- UUID que usó en la petición para identificar el comprobante.

encode.- XML timbrado codificado en Base64 (El comprobante ya contiene el nodo TimbreFiscal).

succeed.- Indicador de si el timbrado fue correctamente timbrado.

uuid.- El folio fiscal resultado del timbre.

message.- Texto que indica el mensaje resultado de la petición.

error.- Texto que indica el error en caso de existir al momento de haber intentado timbrar el comprobante.

Los atributos en negrita siempre estarán presentes, mientras que los en italica pueden existir condicionalmente del resultado.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440000",
					"encode": "PD9...=="
				}, 
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440001",
					"encode": "PD9...=="
				}, 
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440001",
					"encode": "PD9...==",
					"itemUsuario" : {
						"id" : 56423,
						"template" : "reports/platilla_constructoras.jasper"
					}
				},
				...
			],
			"sucursal": {
				"id" : 17694
			}
		}
	}
}
Example Request
Timbrado
View More
curl
curl --location 'https://app.facture.com.mx/api/timbrado' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440000",
					"encode": "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pg0KPGNmZGk6Q29tcHJvYmFudGUgTHVnYXJFeHBlZGljaW9uPSI1ODAwMCIgTWV0b2RvUGFnbz0iUFVFIiBUaXBvRGVDb21wcm9iYW50ZT0iTiIgVG90YWw9Ijk5NTAuMDAiIE1vbmVkYT0iTVhOIiBEZXNjdWVudG89IjYwMC4wMCIgU3ViVG90YWw9IjEwNTUwLjAwIiBGb3JtYVBhZ289Ijk5IiBGZWNoYT0iMjAxNy0xMC0yNlQyMDoyMzo0NyIgRm9saW89IjE0IiBTZXJpZT0iTiIgVmVyc2lvbj0iMy4zIiB4c2k6c2NoZW1hTG9jYXRpb249Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9jZmQvMyBodHRwOi8vd3d3LnNhdC5nb2IubXgvc2l0aW9faW50ZXJuZXQvY2ZkLzMvY2ZkdjMzLnhzZCIgeG1sbnM6Y2ZkaT0iaHR0cDovL3d3dy5zYXQuZ29iLm14L2NmZC8zIiB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIj4NCiAgICA8Y2ZkaTpFbWlzb3IgUmVnaW1lbkZpc2NhbD0iNjAxIiBOb21icmU9IkxBTiBBTUVSSUNBUyBTQSIgUmZjPSJMQU43MDA4MTczUjUiLz4NCiAgICA8Y2ZkaTpSZWNlcHRvciBVc29DRkRJPSJQMDEiIE5vbWJyZT0iREFOSUVMIE1BVVJJQ0lPIFBBVElOTyBMRU9OIiBSZmM9IlBBTEQ4NDAxMjg4UzMiLz4NCiAgICA8Y2ZkaTpDb25jZXB0b3M+DQogICAgICAgIDxjZmRpOkNvbmNlcHRvIERlc2N1ZW50bz0iNjAwLjAwIiBJbXBvcnRlPSIxMDU1MC4wMCIgVmFsb3JVbml0YXJpbz0iMTA1NTAuMDAiIERlc2NyaXBjaW9uPSJQYWdvIGRlIG7Ds21pbmEiIENsYXZlVW5pZGFkPSJBQ1QiIENhbnRpZGFkPSIxIiBDbGF2ZVByb2RTZXJ2PSI4NDExMTUwNSIvPg0KICAgIDwvY2ZkaTpDb25jZXB0b3M+DQogICAgPGNmZGk6Q29tcGxlbWVudG8+DQogICAgICAgIDxub21pbmExMjpOb21pbmEgRmVjaGFGaW5hbFBhZ289IjIwMTctMTAtMjYiIEZlY2hhSW5pY2lhbFBhZ289IjIwMTctMTAtMTEiIEZlY2hhUGFnbz0iMjAxNy0xMC0yNiIgTnVtRGlhc1BhZ2Fkb3M9IjE1IiBUaXBvTm9taW5hPSJPIiBUb3RhbERlZHVjY2lvbmVzPSI2MDAuMDAiIFRvdGFsT3Ryb3NQYWdvcz0iMjUwLjAwIiBUb3RhbFBlcmNlcGNpb25lcz0iMTAzMDAuMDAiIFZlcnNpb249IjEuMiIgeHNpOnNjaGVtYUxvY2F0aW9uPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvbm9taW5hMTIgaHR0cDovL3d3dy5zYXQuZ29iLm14L3NpdGlvX2ludGVybmV0L2NmZC9ub21pbmEvbm9taW5hMTIueHNkIiB4bWxuczpub21pbmExMj0iaHR0cDovL3d3dy5zYXQuZ29iLm14L25vbWluYTEyIj4NCiAgICAgICAgICAgIDxub21pbmExMjpFbWlzb3IgUmVnaXN0cm9QYXRyb25hbD0iQzg5NTYyMyIvPg0KICAgICAgICAgICAgPG5vbWluYTEyOlJlY2VwdG9yIEFudGlnw7xlZGFkPSJQOEQiIEJhbmNvPSIxMzgiIENsYXZlRW50RmVkPSJNSUMiIEN1ZW50YUJhbmNhcmlhPSI1NTc5MDc4MDAzNDU0NjcxIiBDdXJwPSJQQUxEODQwMTI4SE1OVE5OMDUiIERlcGFydGFtZW50bz0iSVQiIEZlY2hhSW5pY2lvUmVsTGFib3JhbD0iMjAxNy0xMC0xOSIgTnVtRW1wbGVhZG89IjAwMSIgTnVtU2VndXJpZGFkU29jaWFsPSIxMTIzMjMxMTMxMiIgUGVyaW9kaWNpZGFkUGFnbz0iMDQiIFB1ZXN0bz0iU0VOSU9SIiBSaWVzZ29QdWVzdG89IjEiIFNhbGFyaW9CYXNlQ290QXBvcj0iMTAwMC4wMCIgU2FsYXJpb0RpYXJpb0ludGVncmFkbz0iNTAwMC4wMCIgVGlwb0NvbnRyYXRvPSIwMSIgVGlwb0pvcm5hZGE9IjAxIiBUaXBvUmVnaW1lbj0iMDIiLz4NCiAgICAgICAgICAgIDxub21pbmExMjpQZXJjZXBjaW9uZXMgVG90YWxFeGVudG89IjMwMC4wMCIgVG90YWxHcmF2YWRvPSIxMDAwMC4wMCIgVG90YWxTdWVsZG9zPSIxMDMwMC4wMCI+DQogICAgICAgICAgICAgICAgPG5vbWluYTEyOlBlcmNlcGNpb24gQ2xhdmU9IlAwMDEiIENvbmNlcHRvPSJTdWVsZG9zLCBzYWxhcmlvcyAgcmF5YXMgeSBqb3JuYWxlcyIgSW1wb3J0ZUV4ZW50bz0iMC4wMCIgSW1wb3J0ZUdyYXZhZG89IjEwMDAwLjAwIiBUaXBvUGVyY2VwY2lvbj0iMDAxIi8+DQogICAgICAgICAgICAgICAgPG5vbWluYTEyOlBlcmNlcGNpb24gQ2xhdmU9IlAwMjkiIENvbmNlcHRvPSJWYWxlcyBkZSBkZXNwZW5zYSIgSW1wb3J0ZUV4ZW50bz0iMzAwLjAwIiBJbXBvcnRlR3JhdmFkbz0iMC4wMCIgVGlwb1BlcmNlcGNpb249IjAyOSIvPg0KICAgICAgICAgICAgPC9ub21pbmExMjpQZXJjZXBjaW9uZXM+DQogICAgICAgICAgICA8bm9taW5hMTI6RGVkdWNjaW9uZXMgVG90YWxJbXB1ZXN0b3NSZXRlbmlkb3M9IjQwMC4wMCIgVG90YWxPdHJhc0RlZHVjY2lvbmVzPSIyMDAuMDAiPg0KICAgICAgICAgICAgICAgIDxub21pbmExMjpEZWR1Y2Npb24gQ2xhdmU9IkQwMDIiIENvbmNlcHRvPSJJU1IiIEltcG9ydGU9IjQwMC4wMCIgVGlwb0RlZHVjY2lvbj0iMDAyIi8+DQogICAgICAgICAgICAgICAgPG5vbWluYTEyOkRlZHVjY2lvbiBDbGF2ZT0iRDAwMSIgQ29uY2VwdG89IlNlZ3VyaWRhZCBzb2NpYWwiIEltcG9ydGU9IjIwMC4wMCIgVGlwb0RlZHVjY2lvbj0iMDAxIi8+DQogICAgICAgICAgICA8L25vbWluYTEyOkRlZHVjY2lvbmVzPg0KICAgICAgICAgICAgPG5vbWluYTEyOk90cm9zUGFnb3M+DQogICAgICAgICAgICAgICAgPG5vbWluYTEyOk90cm9QYWdvIENsYXZlPSJPMDAyIiBDb25jZXB0bz0iU3Vic2lkaW8gcGFyYSBlbCBlbXBsZW8gZWZlY3RpdmFtZW50ZSBlbnRyZWdhZG8gYWwgdHJhYmFqYWRvciIgSW1wb3J0ZT0iMjUwLjAwIiBUaXBvT3Ryb1BhZ289IjAwMiI+DQogICAgICAgICAgICAgICAgICAgIDxub21pbmExMjpTdWJzaWRpb0FsRW1wbGVvIFN1YnNpZGlvQ2F1c2Fkbz0iMjUwLjAiLz4NCiAgICAgICAgICAgICAgICA8L25vbWluYTEyOk90cm9QYWdvPg0KICAgICAgICAgICAgPC9ub21pbmExMjpPdHJvc1BhZ29zPg0KICAgICAgICA8L25vbWluYTEyOk5vbWluYT4NCiAgICAgICAgDQogICAgPC9jZmRpOkNvbXBsZW1lbnRvPg0KPC9jZmRpOkNvbXByb2JhbnRlPg0K"
				}, 
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440001",
					"encode": "PD9...=="
				}, 
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440001",
					"encode": "PD9...==",
					"itemUsuario" : {
						"id" : 56423,
						"template" : "reports/platilla_constructoras.jasper"
					}
				}
			],
			"sucursal": {
				"id" : 17694
			}
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "requestUuid": "123e4567-e89b-12d3-a456-426655440000",
        "encode": "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pgo8Y2ZkaTpDb21wcm9iYW50ZSBMdWdhckV4cGVkaWNpb249IjU4MDAwIiBNZXRvZG9QYWdvPSJQVUUiIFRpcG9EZUNvbXByb2JhbnRlPSJOIiBUb3RhbD0iOTk1MC4wMCIgTW9uZWRhPSJNWE4iIERlc2N1ZW50bz0iNjAwLjAwIiBTdWJUb3RhbD0iMTA1NTAuMDAiIENlcnRpZmljYWRvPSJNSUlGeFRDQ0E2MmdBd0lCQWdJVU1qQXdNREV3TURBd01EQXpNREF3TWpJNE1UVXdEUVlKS29aSWh2Y05BUUVMQlFBd2dnRm1NU0F3SGdZRFZRUUREQmRCTGtNdUlESWdaR1VnY0hKMVpXSmhjeWcwTURrMktURXZNQzBHQTFVRUNnd21VMlZ5ZG1samFXOGdaR1VnUVdSdGFXNXBjM1J5WVdOcHc3TnVJRlJ5YVdKMWRHRnlhV0V4T0RBMkJnTlZCQXNNTDBGa2JXbHVhWE4wY21GamFjT3piaUJrWlNCVFpXZDFjbWxrWVdRZ1pHVWdiR0VnU1c1bWIzSnRZV05wdzdOdU1Ta3dKd1lKS29aSWh2Y05BUWtCRmhwaGMybHpibVYwUUhCeWRXVmlZWE11YzJGMExtZHZZaTV0ZURFbU1DUUdBMVVFQ1F3ZFFYWXVJRWhwWkdGc1oyOGdOemNzSUVOdmJDNGdSM1ZsY25KbGNtOHhEakFNQmdOVkJCRU1CVEEyTXpBd01Rc3dDUVlEVlFRR0V3Sk5XREVaTUJjR0ExVUVDQXdRUkdsemRISnBkRzhnUm1Wa1pYSmhiREVTTUJBR0ExVUVCd3dKUTI5NWIyRmp3NkZ1TVJVd0V3WURWUVF0RXd4VFFWUTVOekEzTURGT1RqTXhJVEFmQmdrcWhraUc5dzBCQ1FJTUVsSmxjM0J2Ym5OaFlteGxPaUJCUTBSTlFUQWVGdzB4TmpFd01qVXlNVFV5TVRGYUZ3MHlNREV3TWpVeU1UVXlNVEZhTUlHeE1Sb3dHQVlEVlFRREV4RkRTVTVFUlUxRldDQlRRU0JFUlNCRFZqRWFNQmdHQTFVRUtSTVJRMGxPUkVWTlJWZ2dVMEVnUkVVZ1ExWXhHakFZQmdOVkJBb1RFVU5KVGtSRlRVVllJRk5CSUVSRklFTldNU1V3SXdZRFZRUXRFeHhNUVU0M01EQTRNVGN6VWpVZ0x5QkdWVUZDTnpjd01URTNRbGhCTVI0d0hBWURWUVFGRXhVZ0x5QkdWVUZDTnpjd01URTNUVVJHVWs1T01Ea3hGREFTQmdOVkJBc1VDMUJ5ZFdWaVlWOURSa1JKTUlJQklqQU5CZ2txaGtpRzl3MEJBUUVGQUFPQ0FROEFNSUlCQ2dLQ0FRRUFndnZDaUNGREZWYVlYN3hkVlJocC8zOFVMV3RvL0xLRFNaeTF5clhLcGFxRlhxRVJKV0Y3OFlIS2YzTjVHQm9YZ3p3RlB1RFgrNWt2WTV3dFlOeHgvT3d1MnNoTlpxRkZoNkVLc3lzUU1lUDVyejZrRTFnRlllbmFQRVVQOXpqK2gwYkwzeFI1YXFvVHNxR0YyNG1LQkxvaWFLNDRwWEJ6R3pnc3haaXNoVkpWTTZYYnpOSlZvbkVVTmJJMjVEaGdXQWQ4NmYyYVUzQm1PSDJLMVJaeDQxZHRUVDU2VXNzekpsczR0UEZPRHIvY2FXdVpFdVV2THAxTTNuajdEeXU4OG1oRDJmKzFmQS9nN2t6Y1UvMXRjcEZYRi9ySXk5M0FQdmtVNzJqd3Zrcm5wcnpzK1NuRzgxKy9GMTZhaHVHc2IyRVo4OGRLSHdxeEVrd3poTXlUYlFJREFRQUJveDB3R3pBTUJnTlZIUk1CQWY4RUFqQUFNQXNHQTFVZER3UUVBd0lHd0RBTkJna3Foa2lHOXcwQkFRc0ZBQU9DQWdFQUoveGtMOEkrZnBpbFpQKzlhTzhuOTMrMjBYeFZvbUxKamVTTCtOZzJFckwyR2dhdHBMdU41SmtuRkJrWkFoeFZJZ01hVFMyM3p6azFSTHRSYVl2SDgzbEJINUUrTStrRWpGR3AxNEZuZTFpVjJQbTN2TDRqZUxtekhnWTFLZjVIbWVWcnJwNFBVN1dRZzE2VnB5SGFKL2VvblBOaUVCVWpjeVExaUZma3pKbW5TSnZER3RmUUsyVGlFb2xESkFwWXYwT1dkbTRpczlCc2ZpOWo2bEk5L1Q2TU5aKy9MTTJML3Q3MlZhdTRyN205NEpERXphTzNBMHdIQXRROTdmakJmQmlPNU04QUVJU0FWN2VaaWRJbDNpYUpKSGtRYkJZaWlXMmdpa3JlVVpLUFVYMEhtbG5JcXFRY0JKaFdLUnU2TnFrNmFaQlRFVExMcEdydkY5T0FyVjFKU3NiZHcvWkgrUDg4UkF0NWVtNS9nand3dEZsTkh5aUtHNXcrVUZwYVpPSzNnWlAwc3Uwc2E2ZGxQZVE5RUw0SmxGa0dxUUNnU1ErTk9zWHFhT2F2Z29QNVZMeWtMd3VHbndJVW51aEJUVmVEYnpwZ3JnOUx1RjVkWXAvenMrWTlTY0pxZTVWTUFhZ0xTWVRTaE50TjhsdVY3THZ4RjlwZ1d3WmRjTTdsVXdxSm1VZGRDaVpxZG5nZzN2elRhY3RNVG9HMTZnWkE0Q1duTWdiVTRFK3I1NDErRk5NcGdBWk52czJDaVcvZUFwZmFhUW9qc1pFQUhEc0R2NEw1bjNNMUNDN2ZZakUvZDYxYVNuZzFMYU82VDFtaCtkRWZQdkx6cDd6eXp6K1VnV01oaTVDczRwY1h4MWVpYzVyN3V4UG9Cd2NDVHQzWUkxaktWVm5WNy93PSIgTm9DZXJ0aWZpY2Fkbz0iMjAwMDEwMDAwMDAzMDAwMjI4MTUiIEZvcm1hUGFnbz0iOTkiIFNlbGxvPSJJeW9tbTk4bzUraWM0b3BrcW9LSXgvc29saEs0NWErNlU4S3QrZVArTUd2K3k5QTRQWmVUaFRVN09uODl4V0lPVVFTclh2MGFYUitXWlA1TEVRS0E4YWs1SEl5TGgzL0VDZjAxRUgzdFJsUWo0cWJ4WE9aL1pad2J2cGgzTk9ualRiYU1lKzBwcm9hMU1rdXdoM0RiZitGakFuRnp4bnhIWmxLMlJzbytYRy8rb2NJVGFCV2FqeEZlVkhXM0NjMjB5V2dkNy82UWliWHdjMFNYcVNTS0RjelVmV2loMlFTN09hS2lrVHpySHVBM25QbWhLNklGVmNCMTR5M3lBR25saVVTTFBHNmd3ZTZBZVNEaWUxcXc1ODhNRjJkSFFqN2EwNzhwcnE0Uk1ubW9JbVJReTJJd3ppdlZhKzRYazdyaysxRUgrWGtGYWthNUROQld1K0hJN1E9PSIgRmVjaGE9IjIwMTctMTEtMDZUMTk6Mjk6NDYiIEZvbGlvPSIxNCIgU2VyaWU9Ik4iIFZlcnNpb249IjMuMyIgeHNpOnNjaGVtYUxvY2F0aW9uPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvY2ZkLzMgaHR0cDovL3d3dy5zYXQuZ29iLm14L3NpdGlvX2ludGVybmV0L2NmZC8zL2NmZHYzMy54c2QiIHhtbG5zOmNmZGk9Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9jZmQvMyIgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSI+CiAgICA8Y2ZkaTpFbWlzb3IgUmVnaW1lbkZpc2NhbD0iNjAxIiBOb21icmU9IkxBTiBBTUVSSUNBUyBTQSIgUmZjPSJMQU43MDA4MTczUjUiLz4KICAgIDxjZmRpOlJlY2VwdG9yIFVzb0NGREk9IlAwMSIgTm9tYnJlPSJEQU5JRUwgTUFVUklDSU8gUEFUSU5PIExFT04iIFJmYz0iUEFMRDg0MDEyODhTMyIvPgogICAgPGNmZGk6Q29uY2VwdG9zPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIERlc2N1ZW50bz0iNjAwLjAwIiBJbXBvcnRlPSIxMDU1MC4wMCIgVmFsb3JVbml0YXJpbz0iMTA1NTAuMDAiIERlc2NyaXBjaW9uPSJQYWdvIGRlIG7Ds21pbmEiIENsYXZlVW5pZGFkPSJBQ1QiIENhbnRpZGFkPSIxIiBDbGF2ZVByb2RTZXJ2PSI4NDExMTUwNSIvPgogICAgPC9jZmRpOkNvbmNlcHRvcz4KICAgIDxjZmRpOkNvbXBsZW1lbnRvPgogICAgICAgIDxub21pbmExMjpOb21pbmEgRmVjaGFGaW5hbFBhZ289IjIwMTctMTAtMjYiIEZlY2hhSW5pY2lhbFBhZ289IjIwMTctMTAtMTEiIEZlY2hhUGFnbz0iMjAxNy0xMC0yNiIgTnVtRGlhc1BhZ2Fkb3M9IjE1IiBUaXBvTm9taW5hPSJPIiBUb3RhbERlZHVjY2lvbmVzPSI2MDAuMDAiIFRvdGFsT3Ryb3NQYWdvcz0iMjUwLjAwIiBUb3RhbFBlcmNlcGNpb25lcz0iMTAzMDAuMDAiIFZlcnNpb249IjEuMiIgeHNpOnNjaGVtYUxvY2F0aW9uPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvbm9taW5hMTIgaHR0cDovL3d3dy5zYXQuZ29iLm14L3NpdGlvX2ludGVybmV0L2NmZC9ub21pbmEvbm9taW5hMTIueHNkIiB4bWxuczpub21pbmExMj0iaHR0cDovL3d3dy5zYXQuZ29iLm14L25vbWluYTEyIj4KICAgICAgICAgICAgPG5vbWluYTEyOkVtaXNvciBSZWdpc3Ryb1BhdHJvbmFsPSJDODk1NjIzIi8+CiAgICAgICAgICAgIDxub21pbmExMjpSZWNlcHRvciBBbnRpZ8O8ZWRhZD0iUDhEIiBCYW5jbz0iMTM4IiBDbGF2ZUVudEZlZD0iTUlDIiBDdWVudGFCYW5jYXJpYT0iNTU3OTA3ODAwMzQ1NDY3MSIgQ3VycD0iUEFMRDg0MDEyOEhNTlROTjA1IiBEZXBhcnRhbWVudG89IklUIiBGZWNoYUluaWNpb1JlbExhYm9yYWw9IjIwMTctMTAtMTkiIE51bUVtcGxlYWRvPSIwMDEiIE51bVNlZ3VyaWRhZFNvY2lhbD0iMTEyMzIzMTEzMTIiIFBlcmlvZGljaWRhZFBhZ289IjA0IiBQdWVzdG89IlNFTklPUiIgUmllc2dvUHVlc3RvPSIxIiBTYWxhcmlvQmFzZUNvdEFwb3I9IjEwMDAuMDAiIFNhbGFyaW9EaWFyaW9JbnRlZ3JhZG89IjUwMDAuMDAiIFRpcG9Db250cmF0bz0iMDEiIFRpcG9Kb3JuYWRhPSIwMSIgVGlwb1JlZ2ltZW49IjAyIi8+CiAgICAgICAgICAgIDxub21pbmExMjpQZXJjZXBjaW9uZXMgVG90YWxFeGVudG89IjMwMC4wMCIgVG90YWxHcmF2YWRvPSIxMDAwMC4wMCIgVG90YWxTdWVsZG9zPSIxMDMwMC4wMCI+CiAgICAgICAgICAgICAgICA8bm9taW5hMTI6UGVyY2VwY2lvbiBDbGF2ZT0iUDAwMSIgQ29uY2VwdG89IlN1ZWxkb3MsIHNhbGFyaW9zICByYXlhcyB5IGpvcm5hbGVzIiBJbXBvcnRlRXhlbnRvPSIwLjAwIiBJbXBvcnRlR3JhdmFkbz0iMTAwMDAuMDAiIFRpcG9QZXJjZXBjaW9uPSIwMDEiLz4KICAgICAgICAgICAgICAgIDxub21pbmExMjpQZXJjZXBjaW9uIENsYXZlPSJQMDI5IiBDb25jZXB0bz0iVmFsZXMgZGUgZGVzcGVuc2EiIEltcG9ydGVFeGVudG89IjMwMC4wMCIgSW1wb3J0ZUdyYXZhZG89IjAuMDAiIFRpcG9QZXJjZXBjaW9uPSIwMjkiLz4KICAgICAgICAgICAgPC9ub21pbmExMjpQZXJjZXBjaW9uZXM+CiAgICAgICAgICAgIDxub21pbmExMjpEZWR1Y2Npb25lcyBUb3RhbEltcHVlc3Rvc1JldGVuaWRvcz0iNDAwLjAwIiBUb3RhbE90cmFzRGVkdWNjaW9uZXM9IjIwMC4wMCI+CiAgICAgICAgICAgICAgICA8bm9taW5hMTI6RGVkdWNjaW9uIENsYXZlPSJEMDAyIiBDb25jZXB0bz0iSVNSIiBJbXBvcnRlPSI0MDAuMDAiIFRpcG9EZWR1Y2Npb249IjAwMiIvPgogICAgICAgICAgICAgICAgPG5vbWluYTEyOkRlZHVjY2lvbiBDbGF2ZT0iRDAwMSIgQ29uY2VwdG89IlNlZ3VyaWRhZCBzb2NpYWwiIEltcG9ydGU9IjIwMC4wMCIgVGlwb0RlZHVjY2lvbj0iMDAxIi8+CiAgICAgICAgICAgIDwvbm9taW5hMTI6RGVkdWNjaW9uZXM+CiAgICAgICAgICAgIDxub21pbmExMjpPdHJvc1BhZ29zPgogICAgICAgICAgICAgICAgPG5vbWluYTEyOk90cm9QYWdvIENsYXZlPSJPMDAyIiBDb25jZXB0bz0iU3Vic2lkaW8gcGFyYSBlbCBlbXBsZW8gZWZlY3RpdmFtZW50ZSBlbnRyZWdhZG8gYWwgdHJhYmFqYWRvciIgSW1wb3J0ZT0iMjUwLjAwIiBUaXBvT3Ryb1BhZ289IjAwMiI+CiAgICAgICAgICAgICAgICAgICAgPG5vbWluYTEyOlN1YnNpZGlvQWxFbXBsZW8gU3Vic2lkaW9DYXVzYWRvPSIyNTAuMCIvPgogICAgICAgICAgICAgICAgPC9ub21pbmExMjpPdHJvUGFnbz4KICAgICAgICAgICAgPC9ub21pbmExMjpPdHJvc1BhZ29zPgogICAgICAgIDwvbm9taW5hMTI6Tm9taW5hPgogICAgICAgIDx0ZmQ6VGltYnJlRmlzY2FsRGlnaXRhbCBGZWNoYVRpbWJyYWRvPSIyMDE3LTExLTA2VDE5OjI5OjQ5IiBOb0NlcnRpZmljYWRvU0FUPSIyMDAwMTAwMDAwMDMwMDAyMjMyMyIgUmZjUHJvdkNlcnRpZj0iQUFBMDEwMTAxQUFBIiBTZWxsb0NGRD0iSXlvbW05OG81K2ljNG9wa3FvS0l4L3NvbGhLNDVhKzZVOEt0K2VQK01Hdit5OUE0UFplVGhUVTdPbjg5eFdJT1VRU3JYdjBhWFIrV1pQNUxFUUtBOGFrNUhJeUxoMy9FQ2YwMUVIM3RSbFFqNHFieFhPWi9aWndidnBoM05PbmpUYmFNZSswcHJvYTFNa3V3aDNEYmYrRmpBbkZ6eG54SFpsSzJSc28rWEcvK29jSVRhQldhanhGZVZIVzNDYzIweVdnZDcvNlFpYlh3YzBTWHFTU0tEY3pVZldpaDJRUzdPYUtpa1R6ckh1QTNuUG1oSzZJRlZjQjE0eTN5QUdubGlVU0xQRzZnd2U2QWVTRGllMXF3NTg4TUYyZEhRajdhMDc4cHJxNFJNbm1vSW1SUXkySXd6aXZWYSs0WGs3cmsrMUVIK1hrRmFrYTVETkJXdStISTdRPT0iIFNlbGxvU0FUPSJKOG14S3hKejhWL1c4NG9MeWMxWlhxbHdoRlpGNzFuNlhIVzdsNVpyS2xiOGpzbTdwSWNLMmE2T1VNTUlCRmJTZ0g4aVNEcEJXY0U4Z1RuQzBsMTRjeERvcGViL05Vc25jQmNTQ0xaemI2KzZtam1qM2E3S21RTXNnNWJraXJZTFNjb2NGdmp2WE5nWVdMbjZBT0t5RkdiaXRjenVvdzVWeHc3dGFzVmNyOUpjMWdVWUpIT1dJM2VEWlE0b0ZyZFdVb25YcXBFdW1pU3ZuaFJjSjFwRmxwUTQ5enU2VnRhSE5IN3BDTzN2U3lpaDZCYitKQ2VmRDNLV2FUeWg0aWo2eTFTZk5pakJBS3RXTzRDUkJqUUZ5RlBoODJTeGxXYTBzTkRSRjlEdGlsWE5zU0d4ZzdDcFlhQi9sSkE3cHhWTFA4YUtNNlZZQkJDSFhzVm4razAwemc9PSIgVVVJRD0iNmRkZDBhMzgtNjc4My00OTRjLWJkODEtMWVkM2I1YjRiYTQ1IiBWZXJzaW9uPSIxLjEiIHhzaTpzY2hlbWFMb2NhdGlvbj0iaHR0cDovL3d3dy5zYXQuZ29iLm14L1RpbWJyZUZpc2NhbERpZ2l0YWwgaHR0cDovL3d3dy5zYXQuZ29iLm14L3NpdGlvX2ludGVybmV0L2NmZC9UaW1icmVGaXNjYWxEaWdpdGFsL1RpbWJyZUZpc2NhbERpZ2l0YWx2MTEueHNkIiB4bWxuczp0ZmQ9Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9UaW1icmVGaXNjYWxEaWdpdGFsIi8+CiAgICA8L2NmZGk6Q29tcGxlbWVudG8+CjwvY2ZkaTpDb21wcm9iYW50ZT4K",
        "succeed": true,
        "uuid": "6ddd0a38-6783-494c-bd81-1ed3b5b4ba45",
        "message": "Comprobante procesado correctamente."
      },
      {
        "requestUuid": "123e4567-e89b-12d3-a456-426655440001",
        "succeed": false,
        "message": "Comprobante no procesado.",
        "error": "Petición errónea, el atributo 'encode' no es válido."
      },
      {
        "requestUuid": "123e4567-e89b-12d3-a456-426655440001",
        "itemUsuario": {
          "id": 56423,
          "template": "reports/platilla_constructoras.jasper",
          "nombre": null
        },
        "succeed": false,
        "message": "Comprobante no procesado.",
        "error": "Petición errónea, el parametro '123e4567-e89b-12d3-a456-426655440001' está repetido en la petición."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
POST
Timbrado JSON
https://app.facture.com.mx/api/timbrado/json
Descripción
Método que permite timbrar una lista de al menos uno y máximo 25 documentos JSON codificados en Base64.

Puede descargar un ejemplo para cada tipo de comprobante que serán codificados en Base64 en los siguientes enlances:

Comprobante de ingreso

Comprobante de Pago 2.0

Comprobante Carta Porte Ingreso

Comprobante Carta Porte Traslado

Comprobante Carta Porte 3.0 Ingreso

Petición

Deberá envíar una lista de objetos comprobante con sus respectivos atributos, ejemplo:

Plain Text
{
  "requestUuid" : "123e4567-e89b-12d3-a456-426655440001",
  "encode": "PD9...==",
  "itemUsuario" : {
    "id" : 56423,
    "template" : "reports/plantilla_constructoras.jasper"
  }
}
Donde:

requestUuid.- UUID para identificar el comprobante en la petición.

encode.- JSON válido codificado en Base64.

itemUsuario.- Item comprado en la tienda para una plantilla o tratamiento especial del comprobante.

Los atributos en negrita siempre deberán estar presentes, mientras que los en italica pueden existir o no.

Deberá incluir también un objeto sucursal donde deberá poner el ID de la sucursal que desea usar para la firma del documento.

Plain Text
"sucursal": {
  "id" : 17694
}
Resultado

Obtendrá la misma lista que envió pero con atributos que contendrán los resultados del timbrado y folio fiscal asignado en caso de ser satisfactorio.

Plain Text
{
  "requestUuid": "123e4567-e89b-12d3-a456-426655440000",
  "encode": "PD94...T4K",
  "succeed": true,
  "uuid": "6ddd0a38-6783-494c-bd81-1ed3b5b4ba45",
  "message": "Comprobante procesado correctamente."
}
Donde:

requestUuid.- UUID que usó en la petición para identificar el comprobante.

encode.- XML timbrado codificado en Base64 (El comprobante ya contiene el nodo TimbreFiscal).

succeed.- Indicador de si el timbrado fue correctamente timbrado.

uuid.- El folio fiscal resultado del timbre.

message.- Texto que indica el mensaje resultado de la petición.

error.- Texto que indica el error en caso de existir al momento de haber intentado timbrar el comprobante.

Los atributos en negrita siempre estarán presentes, mientras que los en italica pueden existir condicionalmente del resultado.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440000",
					"encode": "ew0K...=="
				}
			],
			"sucursal": {
				"id" : 17694
			}
		}
	}
}
Example Request
Timbrado JSON
View More
curl
curl --location 'https://app.facture.com.mx/api/timbrado/json' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"requestUuid" : "123e4567-e89b-12d3-a456-426655440000",
					"encode": "ew0...=="
				}
			],
			"sucursal": {
				"id" : 17694
			}
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "requestUuid": "123e4567-e89b-12d3-a456-426655440000",
        "encode": "PD94...=",
        "succeed": true,
        "uuid": "72610bc0-5437-49f8-b1f2-5dd855c00228",
        "message": "Comprobante procesado correctamente."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
Cancelación
Descripción
Punto de enlace para cancelar y recuperar acuses de cancelación.
Importante: para realizar pruebas deberá usar un emisor de pruebas, vaya a Ambiente de pruebas para más información.

Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope cancelacion a la aplicación cliente.

POST
Cancelar
https://app.facture.com.mx/api/cancelacion
Método para cancelar una lista de comprobantes emitidos

En la lista de comprobantes deberá envíar los UUID de los comprobantes a cancelar, además de motivo de cancelación y UUID relacionado en caso de aplicar.

Cancelación 2022
A partir del 2022 será necesario señalar el motivo de la cancelación de los comprobantes y al seleccionar como motivo de cancelación la clave 01 “Comprobante emitido con errores con relación” deberá relacionarse el folio fiscal del comprobante que sustituye al cancelado.

01	Comprobante emitido con errores con relación
02	Comprobante emitido con errores sin relación
03	No se llevó a cabo la operación
04	Operación nominativa relacionada en la factura global
Actualización
Debido a los cambios en el proceso de cancelación dictados por el SAT, fue necesario realizar cambios en el End Point cancelacion del api de FactureApp.


La respuesta que puede regresar el servicio depende de en cual de los siguientes estados de cancelación se encuentra el comprobante.

Comprobante cancelado pero aún no cambia su estatus en el SAT
Comprobante cancelable con autorización del receptor
Comprobante en proceso de cancelación
Comprobante cancelado previamente
Comprobante no cancelable
Comprobante no encontrado
A continuación se describen las respuestas que puede regresar el servicio de cancelación.

Comprobante cancelado pero aún no cambia su estatus en el SAT
Son todos aquellos comprobantes que se pueden cancelar de manera directa, es decir no requieren la autorización del receptor para poder ser cancelados.

La respuesta que regresa el servicio es la siguiente:

View More
Plain Text
{
            "succeed": true,
            "code": 2000,
            "result": {
                "items": [
                    {
                        "succeed": true,
                        "uuid": "a1d96013-c942-49ed-8128-74935b584717",
                        "error": "El comprobante con UUID a1d96013-c942-49ed-8128-74935b584717 fue cancelado, pero su estado no ha cambiado en el sistema del SAT. Se le enviará un correo electrónico cuando el estado del comprobante cambie. Le pedimos que revise su bandeja de correo constantemente."
                    }
                ]
            },
            "message": "Petición satisfactoria."
        }
Comprobante cancelable con autorización del receptor
Son todos aquellos comprobantes de tipo ingreso, con un monto total mayor a $5,000.00 pesos y mayores a 72 horas de la fecha de expedición del comprobante.

La respuesta que regresa el servicio es la siguiente:

View More
Plain Text
{
        "succeed": true,
        "code": 2000,
        "result": {
            "items": [
                {
                    "succeed": true,
                    "uuid": "f6cedfe4-4557-4fbf-a49d-c881452cde57",
                    "message": "Envie el siguiente enlace al receptor nombre_receptor para que pueda autorizar la cancelación del comprobante https://app.facture.com.mx/cancelacion/index.html?data=eyJ1dWlkIjoiZjZjZWRmZTQtNDU1Ny00ZmJmLWE0OWQtYzg4MTQ1MmNkZTU3IiwicmZjUmVjZXB0b3IiOiJMQU44NTA3MjY4SUEiLCJpZEVtaXNvciI6MTczMDIsInJmY0VtaXNvciI6IlVSVTA3MDEyMlMyOCIsInRvdGFsRmFjdHVyYSI6MTM5MjAuMDAwMDAwfQ==",
                    "error": "El comprobante debe ser autorizado por el receptor para poder ser cancelado."
                }
            ]
        },
        "message": "Petición satisfactoria."

    }
Donde el enlace para que el receptor pueda aceptar o rechazar la solicitud de cancelación, se envía también mediante correo electrónico al cliente del web service.

Comprobante en proceso de cancelación
Son todos aquellos comprobantes cancelables con aceptación de los que ya se ha enviado la solicitud de cancelación anteriormente y en este momento se encuentra en proceso de cancelación, y/o los comprobantes cancelables sin aceptación que ya fueron enviados a cancelar pero el SAT aún no cambia su estatus a cancelado.


La respuesta que regresa el servicio es la siguiente:

View More
Plain Text
{
            "succeed": true,
            "code": 2000,
            "result": {
                "items": [
                    {
                        "succeed": false,
                        "uuid": "f6cedfe4-4557-4fbf-a49d-c881452cde57",
                        "error": "El comprobante con UUID f6cedfe4-4557-4fbf-a49d-c881452cde57 ya se encuetra en proceso de cancelación"
                    }
                ]
            },
            "message": "Petición satisfactoria."
        }
Comprobante cancelado previamente
Son todos aquellos comprobantes que ya fueron cancelados con anterioridad.


La respuesta que regresa el servicio es la siguiente:

View More
Plain Text
{
            "succeed": true,
            "code": 2000,
            "result": {
                "items": [
                    {
                        "succeed": true,
                        "uuid": "a1d96013-c942-49ed-8128-74935b584717",
                        "error": "El comprobante con UUID a1d96013-c942-49ed-8128-74935b584717 ya se encuentra previamente cancelado"
                    }
                ]
            },
            "message": "Petición satisfactoria."
        }
Comprobante no cancelable
Son todos aquellos comprobantes que no pueden ser cancelados, debido a que tienen otros comprobantes relacionados.


La respuesta que regresa el servicio es la siguiente:

View More
Plain Text
{
            "succeed": true,
            "code": 2000,
            "result": {
                "items": [
                    {
                        "succeed": false,
                        "uuid": "c1e45fdf-6290-4f11-bd00-4595175a05ed",
                        "message": "Primero debes cancelar sus comprobantes de relación tipo padre.",
                        "error": "El comprobante con UUID c1e45fdf-6290-4f11-bd00-4595175a05ed no puede ser cancelado debido a que tiene relación con otros comprobantes."
                    }
                ]
            },
            "message": "Petición satisfactoria."
        }
Comprobante no encontrado
Son todos aquellos comprobantes que no se encontraron en los registros de la base de datos del SAT.


La respuesta que regresa el servicio es la siguiente:

View More
Plain Text
{
            "succeed": true,
            "code": 2000,
            "result": {
                "items": [
                    {
                        "succeed": false,
                        "uuid": "ea107b21-4bad-4396-b528-eb43e55f3feb",
                        "error": "El comprobante con UUID ea107b21-4bad-4396-b528-eb43e55f3feb no puede ser cancelado debido a que no se encontró en la base de datos del SAT"
                    }
                ]
            },
            "message": "Petición satisfactoria."
        }
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header requerido para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"uuid" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f30",
                    "motivo" : "01",
                    "uuidRelacionado" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f20"
				},
				{
					"uuid" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f10",
                    "motivo" : "02"
				}
			]
		}
	}
}
Example Request
Cancelar
View More
curl
curl --location 'https://app.facture.com.mx/api/cancelacion' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"uuid" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f30",
                    "motivo" : "01",
                    "uuidRelacionado" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f20"
				},
				{
					"uuid" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f10",
                    "motivo" : "02"
				}
			]
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "succeed": false,
        "uuid": "99659d5d-e1a4-47ec-8aaa-cd9d48628f30",
        "error": "CA205 - El UUID no existe / Error al cancelar el UUID de la factura"
      },
      {
        "succeed": false,
        "uuid": "99659d5d-e1a4-47ec-8aaa-cd9d48628f30",
        "message": "Comprobante no procesado.",
        "error": "Petición errónea, el parametro '99659d5d-e1a4-47ec-8aaa-cd9d48628f30' está repetido en la petición."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
POST
Acuse
https://app.facture.com.mx/api/cancelacion/acuse
Descripción
Método para obtener el acuse de cancelación de un listado de comprobantes cancelados.

En el listado de comprobantes deberá envíar los UUID de los comprobantes que desea obtener el acuse de cancelación.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header requerido para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"uuid" : "adf69747-700d-4916-8d9f-9f7d83dbb16a"
				},
				{
					"uuid" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f30"
				}
			]
		}
	}
}
Example Request
Acuse
View More
curl
curl --location 'https://app.facture.com.mx/api/cancelacion/acuse' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity": {
		"data":{
			"comprobantes":[
				{
					"uuid" : "adf69747-700d-4916-8d9f-9f7d83dbb16a"
				},
				{
					"uuid" : "99659d5d-e1a4-47ec-8aaa-cd9d48628f30"
				}
			]
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "encode": "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiPz48QWN1c2UgeG1sbnM6eHNkPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYSIgeG1sbnM6eHNpPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxL1hNTFNjaGVtYS1pbnN0YW5jZSIgRmVjaGE9IjIwMTctMTEtMDJUMTg6NDQ6NTAuODEwNTQiIFJmY0VtaXNvcj0iTEFONzAwODE3M1I1Ij4NCiAgPEZvbGlvcyB4bWxucz0iaHR0cDovL2NhbmNlbGFjZmQuc2F0LmdvYi5teCI+DQogICAgPFVVSUQ+QURGNjk3NDctNzAwRC00OTE2LThEOUYtOUY3RDgzREJCMTZBPC9VVUlEPg0KICAgIDxFc3RhdHVzVVVJRD4yMDI8L0VzdGF0dXNVVUlEPg0KICA8L0ZvbGlvcz4NCiAgPFNpZ25hdHVyZSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC8wOS94bWxkc2lnIyIgSWQ9IlNlbGxvU0FUIj4NCiAgICA8U2lnbmVkSW5mbz4NCiAgICAgIDxDYW5vbmljYWxpemF0aW9uTWV0aG9kIEFsZ29yaXRobT0iaHR0cDovL3d3dy53My5vcmcvVFIvMjAwMS9SRUMteG1sLWMxNG4tMjAwMTAzMTUiLz4NCiAgICAgIDxTaWduYXR1cmVNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGRzaWctbW9yZSNobWFjLXNoYTUxMiIvPg0KICAgICAgPFJlZmVyZW5jZSBVUkk9IiI+DQogICAgICAgIDxUcmFuc2Zvcm1zPg0KICAgICAgICAgIDxUcmFuc2Zvcm0gQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy9UUi8xOTk5L1JFQy14cGF0aC0xOTk5MTExNiI+DQogICAgICAgICAgICA8WFBhdGg+bm90KGFuY2VzdG9yLW9yLXNlbGY6OipbbG9jYWwtbmFtZSgpPSdTaWduYXR1cmUnXSk8L1hQYXRoPg0KICAgICAgICAgIDwvVHJhbnNmb3JtPg0KICAgICAgICA8L1RyYW5zZm9ybXM+DQogICAgICAgIDxEaWdlc3RNZXRob2QgQWxnb3JpdGhtPSJodHRwOi8vd3d3LnczLm9yZy8yMDAxLzA0L3htbGVuYyNzaGE1MTIiLz4NCiAgICAgICAgPERpZ2VzdFZhbHVlPjlrZnY4SGRIOTY5aFVXRlVvMFJaa3dGOENTZXpiekhITmU5L28rQWU3QjhjUllQdE5pb2tuUkhxS2huVHlmaFVrQlRzekVRemU0dDJDc3JlTnQrMHZBPT08L0RpZ2VzdFZhbHVlPg0KICAgICAgPC9SZWZlcmVuY2U+DQogICAgPC9TaWduZWRJbmZvPg0KICAgIDxTaWduYXR1cmVWYWx1ZT5yY1BLYW9NM2t0d04yVW1pZWV5bEJWWlFSQWcwY09DNG9vRDNpMU9RcGZjK2VNT0N2V25kZy9acHFVSk9oR2lqRjN1NGluYjB3R01sdm9tbDB3bk9VQT09PC9TaWduYXR1cmVWYWx1ZT4NCiAgICA8S2V5SW5mbz4NCiAgICAgIDxLZXlOYW1lPjAwMDAxMDg4ODg4ODEwMDAwMDAxPC9LZXlOYW1lPg0KICAgICAgPEtleVZhbHVlPg0KICAgICAgICA8UlNBS2V5VmFsdWU+DQogICAgICAgICAgPE1vZHVsdXM+dkFyNlFMbWN2VzZhdVRnN2ErT2dtMHZlTnZxSjMwckQzajBpU0FIeEd6R1ZyZzFkMHhsMEZqNWwrSlg5RWl2RCtxaGtTWTdwZkxuSm9PYkxwUTNHR1paT09paEpWUzJ0YkpEbW5uOVRXOGZLVU9WZytqR2hjbnBDSGFVUHEvUG9qOEkyT1ZiM2c3aGlhUkVPUm02dEx0ek9JamtPdjlJTlh4SXBSTXg1NGN3NDZENUYxKzBNN0VDRVZPOEpnKzN5b0k2T3ZETkJIK2pBQnNqN1N1dG1TbkwxVG92L29tSWxTV2F1c2RiWHF5a2NsMTBCTHUyWGlRQWM2S0xubDArTnR6eG94aytkUFVTZFJ5UjdmM1ZsczZ5VWxLLytDLzRGYWNiUitmc3pUMFhJYUpOV2tIYVRPb3F6NzZBeDlYZ1R2OVV1VDY3ajdyZFRWelR2QU4zNjN3PT08L01vZHVsdXM+DQogICAgICAgICAgPEV4cG9uZW50PkFRQUI8L0V4cG9uZW50Pg0KICAgICAgICA8L1JTQUtleVZhbHVlPg0KICAgICAgPC9LZXlWYWx1ZT4NCiAgICA8L0tleUluZm8+DQogIDwvU2lnbmF0dXJlPg0KPC9BY3VzZT4NCg==",
        "succeed": true,
        "uuid": "adf69747-700d-4916-8d9f-9f7d83dbb16a",
        "message": "Acuse disponible."
      },
      {
        "succeed": false,
        "uuid": "99659d5d-e1a4-47ec-8aaa-cd9d48628f30",
        "error": "Error al obtener acuse, el comprobante no esta cancelado."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
Autofactura
Descripción
Punto de enlace para crear autofacturas.
Importante: Este punto de enlace trabaja en conjunto con el módulo de Autofactura, es necesario haberlo comprado y habilitado antes de empezar a consumirlo.

El proceso de autenticación y de concesión de permisos es diferente a los demás puntos de enlace, al momento de comprar el módulo de Autofactura se crea una clave de acceso y al momento de completar el proceso de configuración del ítem, se concesionan los permisos, el Header de autorización podrá localizarlo en el apartado de configuración del Ítem dentro del sistema de Facture App.

Podrá visualizar la aplicación y los permisos creados dentro de su panel de Administración en la opción Configuración tal como si fuera una aplicación habitual.


La justificación de este proceso se debe a que la página web generada hará uso de los Servicios Web de Facture App para recuperar las autofacturas y realizar el proceso de timbrado, deberá acceder por el mecanismo de autorización por lo cual se tendrá que manejar un Access Token del lado del cliente, por lo tanto para no exponer los scopes previos que posea la aplicación y comprometer la seguridad tanto de su aplicación como de Facture App, se crea una nueva aplicación solamente con los permisos necesarios para crear autofacturas y timbrarlas.

Además, se realiza validaciones de seguridad en todo el proceso de autofacturación como, por ejemplo:

Durante la creación de autofacturas se valida la clave de acceso (este campo solamente es visible en el sistema de Facture App y puede ser modificado por el usuario).
Durante la fase de timbrado se valida que la petición enviada se haya guardado antes como una autofactura sino concuerda es rechazada la petición
Scope
La aplicación resultante vendra con los scopes autofactura , facturacion y sucursal.

Importante: para realizar pruebas deberá contar con un emisor de pruebas, vaya a Ambiente de pruebas para más información.

POST
Crear
https://app.facture.com.mx/api/autofactura
Descripción
Método que permite crear autofacturas.

El método fue desarrollado para utilizar la cantidad mínima para realizar un comprobante, en el caso de los impuestos solamente es necesario las tasas debido a que el total de impuestos es calculado en la página usado por el cliente final, si se desea guardar una partida con impuesto exento solamente debe omitirlo, si desea guardarlo como tasa 0% se debe agregar el campo con valor cero.

Nota : Ahora autofactura trabaja con impuestos retenidos, en esta nueva actualización ya se podrá realizar la creación de facturas con impuestos, como: IVA, ISR e IEPS desde la plataforma y a través del WebService.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

Parametro requerido para definir el inicio de la lista de resultados.

size
10

Parametro requerido para definir el tamaño de la lista de resultados (máximo 100).

orderby
orderby?fecha:lt

Parametro opcional para definir un ordenamiento de la lista de resultados. Vea Ordenamiento

filter
cancelada:eq!true

Parametro opcional para agregar un filtrado a la lista de resultados. Vea Filtrado

type
movil

Parametro opcional para definir el tipo de lista de resultados. Vea Tipos de resultados

Body
raw
View More
{
   "entity":{
      "data":{
         "claveAcceso":"GjScQ7sfSfxwitl98XvE",
         "numTicket":"1099238492",
         "moneda":"MXN",
         "formaDePago":"01",
         "sucursal":{
            "id": 1
         },
         "partidas":[
            {
               "descripcion":"Concepto IVA exento y IEPS exento con descuento",
               "valorUnitario":1000,
               "claveUnidad":"mtr",
               "claveProdServ":"10101501",
               "cantidad":10.00,
               "descuento": 500.00,
               "objetoImp": "01"
            },
            {
               "descripcion":"Concepto ISR 4% con descuento",
               "valorUnitario":1000,
               "claveUnidad":"mtr",
               "claveProdServ":"10101501",
               "cantidad":10.00,
               "descuento": 500.00,
               "objetoImp": "02",
		  "retenciones":[
                    {
                       "impuesto": "ISR",
                       "tipoFactor": "Tasa",
                       "tasaOCuota": 0.04
                   }
               ]
            },
            {
               "descripcion":"Concepto IVA 16%",
               "valorUnitario":1000,
               "claveUnidad":"mtr",
               "claveProdServ":"10101501",
               "cantidad":10.00,
               "objetoImp": "02",
		  "retenciones":[
                    {
                       "impuesto": "IVA",
                       "tipoFactor": "Tasa",
                       "tasaOCuota": 0.16
                   }
               ]
            }
         ]
      }
   }
}
Example Request
Crear
View More
curl
curl --location 'https://app.facture.com.mx/api/autofactura' \
--data '{
   "entity":{
      "data":{
         "claveAcceso":"GjScQ7sfSfxwitl98XvE",
         "numTicket":"1099238492",
         "moneda":"MXN",
         "formaDePago":"01",
         "sucursal":{
            "id": 1
         },
         "partidas":[
            {
               "descripcion":"Concepto IVA exento y IEPS exento con descuento",
               "valorUnitario":1000,
               "claveUnidad":"mtr",
               "claveProdServ":"10101501",
               "cantidad":10.00,
               "descuento": 500.00,
               "objetoImp": "01"
            },
            {
               "descripcion":"Concepto ISR 4% con descuento",
               "valorUnitario":1000,
               "claveUnidad":"mtr",
               "claveProdServ":"10101501",
               "cantidad":10.00,
               "descuento": 500.00,
               "objetoImp": "02",
		  "retenciones":[
                    {
                       "impuesto": "ISR",
                       "tipoFactor": "Tasa",
                       "tasaOCuota": 0.04
                   }
               ]
            },
            {
               "descripcion":"Concepto IVA 16%",
               "valorUnitario":1000,
               "claveUnidad":"mtr",
               "claveProdServ":"10101501",
               "cantidad":10.00,
               "objetoImp": "02",
		  "retenciones":[
                    {
                       "impuesto": "IVA",
                       "tipoFactor": "Tasa",
                       "tasaOCuota": 0.16
                   }
               ]
            }
         ]
      }
   }
}'
200 OK
Example Response
Body
Headers (1)
json
{
  "succeed": true,
  "code": 2001,
  "serverId": 150488,
  "message": "Item creado correctamente."
}
GET
Get
https://app.facture.com.mx/api/autofactura?id=123
HEADERS
Authorization
Bearer

Header requerido para autenticar la peticion.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

claveAcceso
1cR2seeGzcimqVsXZY7e

PARAMS
id
123

Example Request
Get
View More
curl
curl --location 'https://app.facture.com.mx/api/autofactura?id=150528' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'claveAcceso: MM7wKeXEQCxAhqKV91ux'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "entity": {
    "data": {
      "id": 150528,
      "numTicket": "00000000010",
      "state": "COMPLETO",
      "folio": 0,
      "partidas": [
        {
          "id": 150528,
          "numTicket": "00000000010",
          "state": "COMPLETO",
          "folio": 0,
          "subtotal": 10000,
          "total": 10000,
          "emisor": {
            "id": 17315,
            "nombre": null,
            "rfc": "LAN8507268IA",
            "tipo": null,
            "regimenes": null,
            "sucursales": null
          },
          "sucursal": {
            "id": 17712,
            "nombre": null,
            "direccion": null
          },
          "formaDePago": "_02",
          "moneda": "MXN",
          "tipoDeCambio": 0
        }
      ],
      "subtotal": 10000,
      "total": 10000,
      "emisor": {
        "id": 17315,
        "nombre": null,
        "rfc": "LAN8507268IA",
        "tipo": null,
        "regimenes": null,
        "sucursales": null
      },
      "sucursal": {
        "id": 17712,
        "nombre": null,
        "direccion": null
      },
      "formaDePago": "_02",
      "moneda": "MXN",
      "tipoDeCambio": 0
    }
  },
  "message": "Petición satisfactoria."
}
GET
Find
https://app.facture.com.mx/api/autofactura/find?offset=0&size=10
HEADERS
Authorization
Bearer

Header requerido para autenticar la peticion.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

claveAcceso
1cR2seeGzcimqVsXZY7q

PARAMS
offset
0

size
10

Example Request
Find
View More
curl
curl --location 'https://app.facture.com.mx/api/autofactura/find?offset=0&size=10' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'claveAcceso: MM7wKeXEQCxAhqKV91ux'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "pagination": {
    "items": [
      {
        "id": 150524,
        "numTicket": "0000000006",
        "state": "PENDIENTE",
        "folio": 0,
        "subtotal": 246.2,
        "total": 287.14204,
        "emisor": {
          "id": 17315,
          "nombre": null,
          "rfc": "LAN8507268IA",
          "tipo": null,
          "regimenes": null,
          "sucursales": null
        },
        "sucursal": {
          "id": 17712,
          "nombre": null,
          "direccion": null
        },
        "formaDePago": "_01",
        "moneda": "MXN",
        "tipoDeCambio": 0
      },
      {
        "id": 150528,
        "numTicket": "00000000010",
        "state": "COMPLETO",
        "folio": 0,
        "subtotal": 10000,
        "total": 10000,
        "emisor": {
          "id": 17315,
          "nombre": null,
          "rfc": "LAN8507268IA",
          "tipo": null,
          "regimenes": null,
          "sucursales": null
        },
        "sucursal": {
          "id": 17712,
          "nombre": null,
          "direccion": null
        },
        "formaDePago": "_02",
        "moneda": "MXN",
        "tipoDeCambio": 0
      }
    ],
    "count": 2,
    "offset": 0
  },
  "message": "Petición satisfactoria."
}
DELETE
Delete
https://app.facture.com.mx/api/autofactura
HEADERS
Authorization
Bearer

Header requerido para autenticar la peticion.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

claveAcceso
4YOgJ7bU3m7VripTL4Yw

Content-Type
application/json

Body
raw
{
   "entity":{
     
      "data":{
    	"id":150566
      
      }
      
	}
}
Example Request
Delete
View More
curl
curl --location --request DELETE 'https://app.facture.com.mx/api/autofactura' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'claveAcceso: 4YOgJ7bU3m7VripTL4Yw' \
--header 'Content-Type: application/json' \
--data '{
   "entity":{
     
      "data":{
    	"id":150566
      
      }
      
	}
}'
200 OK
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
Cliente
Punto de enlace para realizar acciones con los clientes (Receptores dentro del marco del proceso de facturación) vinculados a la cuenta del usuario en la plataforma.

Métodos expuestos
Crear.- Crea un y vincula un cliente a la cuenta del usuario en la plataforma.
find.- Obtiene una lista de resultados con los clientes vinculados a la cuenta del usuario en la plataforma.
Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope cliente a la aplicación cliente.

POST
Crear
https://app.facture.com.mx/api/cliente
Descripción
Método que permite crear un cliente (Receptor en el contexto de proceso de facturación)

Nota: Para la nueva versión del CFDI v4.0 son requeridos nuevos atributos para el cliente, ahora se debe agregar la dirección fiscal y el régimen fiscal.

Atributos requeridos
nombre
rfc
email
regimen
direccion
codigopostal
Claves SAT
Por convención utilizamos las claves del SAT en los atributos usoCFDI y formaPago, vease guía técnica para más información.

Clave usoCFDI que corresponda al uso que le dará al comprobante fiscal el receptor.

G01 - Adquisición de mercancias
G02 - Devoluciones, descuentos o bonificaciones
G03 - Gastos en general
I01 - Construcciones
I02 - Mobilario y equipo de oficina por inversiones
I03 - Equipo de transporte
I04 - Equipo de computo y accesorios
I05 - Dados, troqueles, moldes, matrices y herramental
I06 - Comunicaciones telefónicas
I07 - Comunicaciones satelitales
I08 - Otra maquinaria y equipo
D01 - Honorarios médicos, dentales y gastos hospitalarios
D02 - Gastos médicos por incapacidad o discapacidad
D03 - Gastos funerales
D04 - Donativos
D05 - Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)
D06 - Aportaciones voluntarias al SAR
D07 - Primas por seguros de gastos médicos
D08 - Gastos de transportación escolar obligatoria
D09 - Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones
D10 - Pagos por servicios educativos (colegiaturas)
P01 - Por definir
Clave formaPago de la forma de pago de los bienes o servicios amparados, usado para el llenado del comprobante fiscal digital

01 - Efectivo
02 - Cheque nominativo
03 - Transferencia electrónica de fondos
04 - Construcciones
05 - Monedero electrónico
06 - Dinero electrónico
08 - Vales de despensa
12 - Dación en pago
13 - Pago por subrogación
14 - Pago por consignación
15 - Condonación
17 - Compensación
23 - Novación
24 - Confusión
25 - Remisión de deuda
26 - Prescripción o caducidad
27 - A satisfacción del acreedor
28 - Tarjeta de débito
29 - Tarjeta de servicios
30 - Aplicación de anticipos
31 - Intermediario pagos
99 - Por definir
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
    "entity": {
        "data":{
            "nombre": "SENTIEN SA DE CV",
            "rfc": "XAXX010101000",
            "telefono": 4431234567,
            "email": "email@gmail.com",
            "email2": "email2@gmail.com",
            "email3": "email3@gmail.com",
            "notas": "cualquier tipo de nota",
            "usoCFDI": "G01",
            "formaPago": "03",
            "regimen": "601",
            "direccion": {
                "calle": "Patzcuaro",
                "numerointerior": "7",
                "numeroexterior": "E",
                "codigopostal": "58000",
                "colonia": "Morelia Centro",
                "municipio": "Morelia",
                "ciudad": "Morelia",
                "estado": "Michoacán de Ocampo",
                "pais": "México",
                "referencia": "Cerca del Hospital"
            }
        }
    }
}
Example Request
Crear
View More
curl
curl --location 'https://app.facture.com.mx/api/cliente' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw '{
    "entity": {
        "data":{
            "nombre": "SENTIEN SA DE CV",
            "rfc": "XAXX010101000",
            "telefono": 4431234567,
            "email": "email@gmail.com",
            "email2": "email2@gmail.com",
            "email3": "email3@gmail.com",
            "notas": "cualquier tipo de nota",
            "usoCFDI": "G01",
            "formaPago": "03",
            "regimen": "601",
            "direccion": {
                "calle": "Patzcuaro",
                "numerointerior": "7",
                "numeroexterior": "E",
                "codigopostal": "58000",
                "colonia": "Morelia Centro",
                "municipio": "Morelia",
                "ciudad": "Morelia",
                "estado": "Michoacán de Ocampo",
                "pais": "México",
                "referencia": "Cerca del Hospital"
            }
        }
    }
}
'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
GET
Find
https://app.facture.com.mx/api/cliente/find?offset=0&size=10
Descripción
Método para obtener el catálogo de clientes vinculados al cuenta del usuario de la plataforma

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

size
10

Example Request
Find
View More
curl
curl --location 'https://app.facture.com.mx/api/cliente/find?offset=0&size=10' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
DELETE
Eliminar
https://app.facture.com.mx/api/cliente?serverId=5114453
Punto de enlace para eliminar un cliente, proveyendo el id del cliente.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
serverId
5114453

Example Request
Eliminar
View More
curl
curl --location --request DELETE 'https://app.facture.com.mx/api/cliente?serverId=5114453' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
PUT
Actualizar
https://app.facture.com.mx/api/cliente
Punto de enlace para actualizar un cliente, proveyendo un modelo que contenga su id y los datos a actualizar, atributo requerido rfc.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
 
 {
    "entity": {
        "data":{
            "id": 620860,
            "nombre": "SENTIEN SA DE CV",
            "rfc": "ULC051129GC0",
            "email": "jortiz@facture.com.mx",
            "email2": "stormvanx@hotmail.com",
            "regimen": "603"
        }
    }
}
 
Example Request
Actualizar
View More
curl
curl --location --request PUT 'https://app.facture.com.mx/api/cliente' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw ' 
 {
    "entity": {
        "data":{
            "id": 620860,
            "nombre": "SENTIEN SA DE CV",
            "rfc": "ULC051129GC0",
            "email": "jortiz@facture.com.mx",
            "email2": "stormvanx@hotmail.com",
            "regimen": "603"
        }
    }
}
 '
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
Cotización
Punto de enlace para realizar acciones con el módulo de cotizaciones (módulo que puede adquirir desde de la tienda de FactureApp).

Metodos expuestos

Crear.- Crea una nueva cotización perteneciente a la sucursal brindada.
Crear con impuestos.- Crea una nueva cotización la cúal incluye impuestos perteneciente a la sucursal brindada.
Editar.- Edite los conceptos de la cotización brindado el uuid de la cotización.
Eliminar.- Elimine una cotización brindado el uuid de la cotización.
Cambiar.- Cambie el estatus de una cotización, siempre y cuando ésta no sea timbrada.
Find.- Obtiene una lista de sus cotizaciones.
Enviar.- Envíe por correo electrónico una cotización a uno o varios receptores.
Get.- Obtiene una cotización en especifíco.
Timbrar.- Convierta una cotización aceptada en un CFDI.
Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope cotizacion a la aplicación cliente.

POST
Crear
https://app.facture.com.mx/api/cotizacion
Método que permite crear una nueva cotización.

Atributos requeridos en data

receptor
sucursal
fechaVencimiento
conceptos
Atributos Opcionales en data

direccion
mensajeFinal
consideracionesFinales
detalles
direccion
Si no se especifican los atributos opcionales, estos se tomaran de la configuración del módulo.

Receptor
Atributos requeridos en receptor

rfc
Atributos requerido al crear receptor

nombre
Si el rfc del receptor no existe en el catálogo, sera creado. Para poder crear al receptor será necesario especificar el atributo nombre.

sucursal
Atributos requeridos en sucursal

id
conceptos
Es la lista de conceptos que aparecerán en la cotización.

Atributos requeridos por concepto

id
cantidad
Atributos requeridos al crear concepto

claveProdServ
precio
descripcion
claveUnidad
Atributos opcionales para impuestos por concepto

ivaTrasladado
iepsTrasladado
ivaRetenido
iepsRetenido
impuestosLocalesTrasladados
impuestosLocalesRetenidos
Si es especificado el atributo id, la información del concepto es tomada del catalogo. Si id no es especificado, se creara el concepto utilizando los atributos requeridos al crear.

Para una descripción mas detallada sobre como especificar impuestos vea la sección correspondiente.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity":{
	    "data":{
	        "receptor":{
	        	"nombre": "nombre apellido",
		        "rfc" : "GGVY910321HY1"
	    	 },
			"sucursal":{
				"id":17814
			},
			"direccion": "Streety No. 23 Col. El Pipila INFONAVIT Morelia, Michoacán de Ocampo. México.",
            "mensajeFinal": " Para cualquier duda, no dude en contactarnos.",
			"fechaVencimiento": "2019-10-11 11:11:11",
			"conceptos": [
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 25,
				  "descripcion": "descripcion concepto",
				  "claveUnidad": "KGM",
				  "ivaTrasladado":{
				  	"factor":"TASA",
				  	"tasa":0.16
				  }
				},
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 20.50,
				  "descripcion": "descripcion concepto 2",
				  "claveUnidad": "KGM",
				  "ivaTrasladado":{
				  	"factor":"TASA",
				  	"tasa":0.16
				  }
				}
			]
		}
	}
}
Example Request
Crear
View More
curl
curl --location 'https://app.facture.com.mx/api/cotizacion' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity":{
	    "data":{
	        "receptor":{
	        	"nombre": "nombre apellido",
		        "rfc" : "GGVY910321HY1"
	    	 },
			"sucursal":{
				"id":17814
			},
			"direccion": "Streety No. 23 Col. El Pipila INFONAVIT Morelia, Michoacán de Ocampo. México.",
            "mensajeFinal": " Para cualquier duda, no dude en contactarnos.",
			"fechaVencimiento": "2019-10-11 11:11:11",
			"conceptos": [
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 25,
				  "descripcion": "descripcion concepto",
				  "claveUnidad": "KGM",
				  "ivaTrasladado":{
				  	"factor":"TASA",
				  	"tasa":0.16
				  }
				},
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 20.50,
				  "descripcion": "descripcion concepto 2",
				  "claveUnidad": "KGM",
				  "ivaTrasladado":{
				  	"factor":"TASA",
				  	"tasa":0.16
				  }
				}
			]
		}
	}
}'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
POST
Crear con impuestos
https://app.facture.com.mx/api/cotizacion
Método que permite crear una nueva cotización.

Se muestra un ejemplo para crear una cotización con impuestos.

Atributos opcionales para impuestos por concepto

ivaTrasladado
iepsTrasladado
ivaRetenido
iepsRetenido
impuestosLocalesTrasladados
impuestosLocalesRetenidos
IVA
Atributos requeridos por impuesto de tipo iva

tasa
factor
Para impuestos de tipo iva, el atributo factor debe ser TASA.

IEPS
Atributos por impuesto de tipo ieps

tasa
monto
factor
Para impuestos de tipo ieps:

Si el factor es TASA, el tributo tasa debera especificarse.
Si el factor es CUOTA, el tributo monto debera especificarse.
Impuestos locales
Atributos por impuesto local

descripcion
tasa
Claves SAT
Por convención utilizamos el catálogo del SAT en el atributo factor.

clave factor del impuesto aplicado al concepto

TASA - Tasa
CUOTA - Cuota
EXENTO -Exento
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity":{
	    "data":{
	        "receptor":{
	        	"nombre": "nombre apellido",
		        "rfc" : "GGVY910321HY1"
	    	 },
			"sucursal":{
				"id":17814
			},
			"fechaVencimiento": "2019-10-11 11:11:11",
			"conceptos": [
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 20.50,
				  "descripcion": "descripcion concepto 2",
				  "claveUnidad": "KGM",
				  "impuestosLocalesTrasladados": [
				  	{
				  		"descripcion":"impuesto local",
				  		"tasa": 0.16
				  	}
				  	]
				}
			]
		}
	}
}
Example Request
Crear con impuestos
View More
curl
curl --location 'https://app.facture.com.mx/api/cotizacion' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity":{
	    "data":{
	        "receptor":{
	        	"nombre": "nombre apellido",
		        "rfc" : "GGVY910321HY1"
	    	 },
			"sucursal":{
				"id":17814
			},
			"fechaVencimiento": "2019-10-11 11:11:11",
			"conceptos": [
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 20.50,
				  "descripcion": "descripcion concepto 2",
				  "claveUnidad": "KGM",
				  "impuestosLocalesTrasladados": [
				  	{
				  		"descripcion":"impuesto local",
				  		"tasa": 0.16
				  	}
				  	]
				}
			]
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
json
{
  "succeed": true,
  "code": 2001,
  "message": "Item creado correctamente.",
  "serverUuid": "66044643-1ca5-402a-bc38-8c6cf71b8464"
}
PUT
Editar
http://app.facture.com.mx/api/cotizacion
Método que permite editar una nueva cotización.

Atributos requeridos en data

conceptos
Atributos Opcionales en data

fechaVencimiento
direccion
mensajeFinal
consideracionesFinales
detalles
direccion
Es necesario volver a proporcionar los conceptos para crear que la cotización. Los conceptos anteriores serán sobrescritos. Si no se especifican los atributos opcionales, estos se tomaran de la configuración del módulo.

conceptos
Es la lista de conceptos que aparecerán en la cotización.

Atributos requeridos por concepto

id
cantidad
Atributos requeridos al crear concepto

claveProdServ
precio
descripcion
claveUnidad
Atributos opcionales para impuestos por concepto

ivaTrasladado
iepsTrasladado
ivaRetenido
iepsRetenido
impuestosLocalesTrasladados
impuestosLocalesRetenidos
Si es especificado el atributo 'id', la información del concepto es tomada del catalogo. Si 'id' no es especificado, se creara el concepto utilizando los atributos requeridos al crear.

Para una descripción mas detallada sobre como especificar impuestos vea la sección correspondiente.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity":{
	    "data":{
	    	"uuid":"18d9c5df-c3f7-4d68-a410-6092ee3560a7",
			"fechaVencimiento": "2019-10-11 11:11:11",
			"conceptos": [
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 20.50,
				  "descripcion": "descripcion concepto 2",
				  "claveUnidad": "KGM",
				  "impuestosLocalesTrasladados": [
				  	{
				  		"descripcion":"impuesto local",
				  		"tasa": 0.16
				  	},
				  	{
				  		"descripcion":"impuesto local 2",
				  		"tasa": 0.06
				  	}
				  	]
				}
			]
		}
	}
}
Example Request
Editar
View More
curl
curl --location --request PUT 'https://app.facture.com.mx/api/cotizacion' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity":{
	    "data":{
	    	"uuid":"18d9c5df-c3f7-4d68-a410-6092ee3560a7",
			"fechaVencimiento": "2019-10-11 11:11:11",
			"conceptos": [
				{
				  "claveProdServ": 10101500,
				  "cantidad":1,
				  "precio": 20.50,
				  "descripcion": "descripcion concepto 2",
				  "claveUnidad": "KGM",
				  "impuestosLocalesTrasladados": [
				  	{
				  		"descripcion":"impuesto local",
				  		"tasa": 0.16
				  	},
				  	{
				  		"descripcion":"impuesto local 2",
				  		"tasa": 0.06
				  	}
				  	]
				}
			]
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
json
{
  "succeed": true,
  "code": 3000,
  "message": "Item actualizado correctamente."
}
DELETE
Eliminar
http://app.facture.com.mx/api/cotizacion
Método que permite eliminar una cotización.

Atributos requeridos en data

uuid
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
{
  "entity":{
    "data":{
    	"uuid": "52743cbc-1ca4-4982-a3ba-1bbb8a9fe548"
    }
  }
}
Example Request
Eliminar
View More
curl
curl --location --request DELETE 'https://app.facture.com.mx/api/cotizacion' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "entity":{
    "data":{
    	"uuid": "52743cbc-1ca4-4982-a3ba-1bbb8a9fe548"
    }
  }
}'
200 OK
Example Response
Body
Headers (3)
json
{
  "succeed": true,
  "code": 2002,
  "message": "Item eliminado correctamente."
}
PUT
Cambiar
http://app.facture.com.mx/api/cotizacion/cambiar
Método que permite cambiar el estado de una cotización.

Atributos requeridos en data

uuid
estatusCotizacion
Los valores aceptados por estatusCotizacion son ACEPTADA,RECHAZADA,PENDIENTE.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
{
    "entity":{
        "data":{
            "uuid":  "44a47932-7d10-4734-91d6-d64b03b78a8d",
            "estatusCotizacion":"ACEPTADA"
        }
    }
}
Example Request
Cambiar
View More
curl
curl --location --request PUT 'https://app.facture.com.mx/api/cotizacion/cambiar' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
    "entity":{
        "data":{
            "uuid":  "44a47932-7d10-4734-91d6-d64b03b78a8d",
            "estatusCotizacion":"ACEPTADA"
        }
    }
}'
200 OK
Example Response
Body
Headers (3)
json
{
  "succeed": true,
  "code": 3000,
  "message": "Item actualizado correctamente."
}
GET
Find
http://app.facture.com.mx/api/cotizacion/find?offset=0&size=10
Método que permite obtener una lista de cotizaciones.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

size
10

Example Request
Find
View More
curl
curl --location 'https://app.facture.com.mx/api/cotizacion/find?offset=0&size=10' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "pagination": {
    "items": [
      {
        "uuid": "8d700f5b-938e-4cff-847b-2628c2961b4b",
        "sucursal": {
          "id": 17814,
          "nombre": "MI SUCURSAL",
          "direccion": null
        },
        "direccion": "Streety No. 23 Col. El Pipila INFONAVIT Morelia, Michoacán de Ocampo. México.",
        "mensajeFinal": " Para cualquier duda, no dude en contactarnos.",
        "detalles": "detalles de la cotizacion&nbsp;",
        "consideracionesFinales": null,
        "estatusCotizacion": "PENDIENTE",
        "fechaVencimiento": "2019-10-11 11:11:11",
        "fechaCreacion": "2019-08-16 17:55:27",
        "serie": "COT",
        "folio": 121,
        "receptor": {
          "id": 616722,
          "nombre": "nombre apellido",
          "rfc": "GGVY910321HY1",
          "telefono": null,
          "email": null,
          "email2": null,
          "email3": null,
          "notas": null,
          "usoCFDI": null,
          "formaPago": null,
          "direccion": null
        },
        "moneda": "MXN",
        "timbrada": false,
        "subtotal": 45.5,
        "descuentos": 0,
        "impuestosTrasladados": 7.28,
        "impuestosRetenidos": 0,
        "total": 52.78
      },
      {
        "uuid": "90607dc1-fcab-4238-b806-ee9c7ec6753b",
        "sucursal": {
          "id": 17814,
          "nombre": "MI SUCURSAL",
          "direccion": null
        },
        "direccion": "Streety No. 23 Col. El Pipila INFONAVIT Morelia, Michoacán de Ocampo. México.",
        "mensajeFinal": " Para cualquier duda, no dude en contactarnos.",
        "detalles": "detalles de la cotizacion&nbsp;",
        "consideracionesFinales": null,
        "estatusCotizacion": "ACEPTADA",
        "fechaVencimiento": "2019-10-11 11:11:11",
        "fechaCreacion": "2019-08-16 17:57:14",
        "serie": "COT",
        "folio": 122,
        "receptor": {
          "id": 616722,
          "nombre": "nombre apellido",
          "rfc": "GGVY910321HY1",
          "telefono": null,
          "email": null,
          "email2": null,
          "email3": null,
          "notas": null,
          "usoCFDI": null,
          "formaPago": null,
          "direccion": null
        },
        "moneda": "MXN",
        "timbrada": false,
        "subtotal": 45.5,
        "descuentos": 0,
        "impuestosTrasladados": 7.28,
        "impuestosRetenidos": 0,
        "total": 52.78
      }
    ],
    "count": 2,
    "offset": 0
  },
  "message": "Petición satisfactoria."
}
POST
Enviar
http://app.facture.com.mx/api/cotizacion/enviar
Método que permite enviar por correo una o varias cotizaciones.

Atributos requeridos en data

cotizaciones
receptor
Atributos opcionales en data

asunto
cc
Si se cuenta con el artículo de correo personalizado, se utilizara la configuración del articulo al enviar el correo.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
	"entity":{
	    "data":{
	    	"asunto":"Cotizaciones",
			"cotizaciones":[
				"8d700f5b-938e-4cff-847b-2628c2961b4b","90607dc1-fcab-4238-b806-ee9c7ec6753b"
			],
			"receptor":"correo@facture.com.mx",
			"cc":[
				"correoCopia@facture.com.mx"
			]
		}
	}
}
Example Request
Enviar
View More
curl
curl --location 'https://app.facture.com.mx/api/cotizacion/enviar' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data-raw '{
	"entity":{
	    "data":{
	    	"asunto":"Cotizaciones",
			"cotizaciones":[
				"8d700f5b-938e-4cff-847b-2628c2961b4b","90607dc1-fcab-4238-b806-ee9c7ec6753b"
			],
			"receptor":"correo@facture.com.mx",
			"cc":[
				"correoCopia@facture.com.mx"
			]
		}
	}
}'
200 OK
Example Response
Body
Headers (3)
json
{
  "succeed": false,
  "code": 9002,
  "message": "Correo enviado correctamente."
}
GET
Get
http://app.facture.com.mx/api/cotizacion?id=db810d2d-6fbd-415f-8e29-d936c9ad982d
Método para obtener la información de una cotización.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
id
db810d2d-6fbd-415f-8e29-d936c9ad982d

Example Request
Get
View More
curl
curl --location 'https://app.facture.com.mx/api/cotizacion?id=90607dc1-fcab-4238-b806-ee9c7ec6753b%0A' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "entity": {
    "data": {
      "uuid": "90607dc1-fcab-4238-b806-ee9c7ec6753b",
      "sucursal": {
        "id": 17814,
        "nombre": "MI SUCURSAL",
        "direccion": null
      },
      "direccion": "Streety No. 23 Col. El Pipila INFONAVIT Morelia, Michoacán de Ocampo. México.",
      "mensajeFinal": " Para cualquier duda, no dude en contactarnos.",
      "detalles": "detalles de la cotizacion&nbsp;",
      "consideracionesFinales": null,
      "estatusCotizacion": "ACEPTADA",
      "fechaVencimiento": "2019-10-11 11:11:11",
      "fechaCreacion": "2019-08-16 17:57:14",
      "serie": "COT",
      "folio": 122,
      "receptor": {
        "id": 616722,
        "nombre": "nombre apellido",
        "rfc": "GGVY910321HY1",
        "telefono": null,
        "email": null,
        "email2": null,
        "email3": null,
        "notas": null,
        "usoCFDI": null,
        "formaPago": null,
        "direccion": null
      },
      "moneda": "MXN",
      "timbrada": false,
      "subtotal": 45.5,
      "descuentos": 0,
      "impuestosTrasladados": 7.28,
      "impuestosRetenidos": 0,
      "total": 52.78
    }
  },
  "message": "Petición satisfactoria."
}
POST
Timbrar
http://app.facture.com.mx/api/cotizacion/timbrar
Método para generar una factura a partir de la cotización especificada.

Atributos requeridos en data - uuid - usoCfdi - formaPago - metodoPago Claves SAT
Por convención utilizamos las claves del SAT en los atributos usoCFDI, formaPago y metodoPago, véase guía técnica para más información.

Clave usoCFDI que corresponda al uso que le dará al comprobante fiscal el receptor.

G01 - Adquisición de mercancías
G02 - Devoluciones, descuentos o bonificaciones
G03 - Gastos en general
I01 - Construcciones
I02 - Mobilario y equipo de oficina por inversiones
I03 - Equipo de transporte
I04 - Equipo de computo y accesorios
I05 - Dados, troqueles, moldes, matrices y herramental
I06 - Comunicaciones telefónicas
I07 - Comunicaciones satelitales
I08 - Otra maquinaria y equipo
D01 - Honorarios médicos, dentales y gastos hospitalarios
D02 - Gastos médicos por incapacidad o discapacidad
D03 - Gastos funerales
D04 - Donativos
D05 - Intereses reales efectivamente pagados por créditos hipotecarios (casa habitación)
D06 - Aportaciones voluntarias al SAR
D07 - Primas por seguros de gastos médicos
D08 - Gastos de transportación escolar obligatoria
D09 - Depósitos en cuentas para el ahorro, primas que tengan como base planes de pensiones
D10 - Pagos por servicios educativos (colegiaturas)
P01 - Por definir
Clave formaPago de la forma de pago de los bienes o servicios amparados, usado para el llenado del comprobante fiscal digital

01 - Efectivo
02 - Cheque nominativo
03 - Transferencia electrónica de fondos
04 - Construcciones
05 - Monedero electrónico
06 - Dinero electrónico
08 - Vales de despensa
12 - Dación en pago
13 - Pago por subrogación
14 - Pago por consignación
15 - Condonación
17 - Compensación
23 - Novación
24 - Confusión
25 - Remisión de deuda
26 - Prescripción o caducidad
27 - A satisfacción del acreedor
28 - Tarjeta de débito
29 - Tarjeta de servicios
30 - Aplicación de anticipos
31 - Intermediario pagos
99 - Por definir
Clave metodoPagp del método de pago para el comprobante fiscal digital.

PUE - Pago en una sola exhibición
PPD - Pago en parcialidades o diferido
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
{
	"entity":{
	    "data":{
            "uuid":"60d42486-849a-4015-8807-7b8006aca47c",
	    	"usoCfdi": "P01",
	    	"metodoPago":"PUE",
	    	"formaPago": "01"
	    }
	}
}
Example Request
Timbrar
View More
curl
curl --location 'https://app.facture.com.mx/api/cotizacion/timbrar' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
	"entity":{
	    "data":{
            "uuid":"59c37d26-66bf-49e6-b714-6e7630f09c45",
	    	"usoCfdi": "P01",
	    	"metodoPago":"PUE",
	    	"formaPago": "01"
	    }
	}
}'
200 OK
Example Response
Body
Headers (3)
View More
json
{
  "succeed": true,
  "code": 2000,
  "result": {
    "items": [
      {
        "requestUuid": "59c37d26-66bf-49e6-b714-6e7630f09c45",
        "encode": "PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9InllcyI/Pgo8Y2ZkaTpDb21wcm9iYW50ZSBMdWdhckV4cGVkaWNpb249IjU4MTYwIiBNZXRvZG9QYWdvPSJQVUUiIFRpcG9EZUNvbXByb2JhbnRlPSJJIiBUb3RhbD0iNzY3OTIuMDAiIE1vbmVkYT0iTVhOIiBTdWJUb3RhbD0iNTEyMDAuMDAiIENlcnRpZmljYWRvPSJNSUlGN1RDQ0E5V2dBd0lCQWdJVU1qQXdNREV3TURBd01EQXpNREF3TWpJM056SXdEUVlKS29aSWh2Y05BUUVMQlFBd2dnRm1NU0F3SGdZRFZRUUREQmRCTGtNdUlESWdaR1VnY0hKMVpXSmhjeWcwTURrMktURXZNQzBHQTFVRUNnd21VMlZ5ZG1samFXOGdaR1VnUVdSdGFXNXBjM1J5WVdOcHc3TnVJRlJ5YVdKMWRHRnlhV0V4T0RBMkJnTlZCQXNNTDBGa2JXbHVhWE4wY21GamFjT3piaUJrWlNCVFpXZDFjbWxrWVdRZ1pHVWdiR0VnU1c1bWIzSnRZV05wdzdOdU1Ta3dKd1lKS29aSWh2Y05BUWtCRmhwaGMybHpibVYwUUhCeWRXVmlZWE11YzJGMExtZHZZaTV0ZURFbU1DUUdBMVVFQ1F3ZFFYWXVJRWhwWkdGc1oyOGdOemNzSUVOdmJDNGdSM1ZsY25KbGNtOHhEakFNQmdOVkJCRU1CVEEyTXpBd01Rc3dDUVlEVlFRR0V3Sk5XREVaTUJjR0ExVUVDQXdRUkdsemRISnBkRzhnUm1Wa1pYSmhiREVTTUJBR0ExVUVCd3dKUTI5NWIyRmp3NkZ1TVJVd0V3WURWUVF0RXd4VFFWUTVOekEzTURGT1RqTXhJVEFmQmdrcWhraUc5dzBCQ1FJTUVsSmxjM0J2Ym5OaFlteGxPaUJCUTBSTlFUQWVGdzB4TmpFd01qRXlNVE0yTVRGYUZ3MHlNREV3TWpFeU1UTTJNVEZhTUlIWk1TY3dKUVlEVlFRREV4NU5TVTVCVXlCVFFVNURTRVZhSUV4QldrTkJUazhnVTBFZ1JFVWdRMVl4SnpBbEJnTlZCQ2tUSGsxSlRrRlRJRk5CVGtOSVJWb2dURUZhUTBGT1R5QlRRU0JFUlNCRFZqRW5NQ1VHQTFVRUNoTWVUVWxPUVZNZ1UwRk9RMGhGV2lCTVFWcERRVTVQSUZOQklFUkZJRU5XTVNVd0l3WURWUVF0RXh4VlVsVXdOekF4TWpKVE1qZ2dMeUJJUlVkVU56WXhNREF6TkZNeU1SNHdIQVlEVlFRRkV4VWdMeUJJUlVkVU56WXhNREF6VFVSR1VrNU9NRGt4RlRBVEJnTlZCQXNVREZCeWRXVmlZWE5mUTBaRVNUQ0NBU0l3RFFZSktvWklodmNOQVFFQkJRQURnZ0VQQURDQ0FRb0NnZ0VCQUloVnRPdnBwc1B0ZGpLbGNCd2lpWERHMSs1a3M0a0hDVUVTakF0Z0s3YjRyOHhNRTdNU2lhUzZtbThtSzBKRGFHZUJ6T3c2UnkyamJaWkYza0UyUWpYMU9iUU1hS0MyS0ZPOVBxc2NadmVBcjlHNzR3clF2TXoxWlpCdlBialFKV3VKVk9wa3N6NmpFTkc0SHBJMnV2U2l3cWFHbXNZOCsxTGkxQjRaZjBDZG03SnJyNm1SN3phMnlQQkl5R0VQWW1weEJQWVhBcGVyVUYxa0dkWlNaajU3SVZSZysyVk5XUTBwK1N1UklYZDByeUVOVnZqYjFOTGJpK1psak8zVDFLc3NMNDdDNldJMjdOM3pUa0hvMkdhenZGZnY1QWtjUGNRZGR6RWRpR2FyN2ZxTDdyd1diWXAxcEZyOExZWittclZmU0VpTURqZzRWN2dWaGh3MnpQVUNBd0VBQWFNZE1Cc3dEQVlEVlIwVEFRSC9CQUl3QURBTEJnTlZIUThFQkFNQ0JzQXdEUVlKS29aSWh2Y05BUUVMQlFBRGdnSUJBR3RZb2kzWEpDeG5FRHpFci9xSzBWV1dBSVJsS0docE9ja0lSQ3NjUWZDZE45WEJxeHRvWE9Fb05sM3RxWTJhemV4OXVyeExQNDFpN2xRd0xEQ2tPWXk2VW45RGVjTWdHaC9CK1Ric1JnRG1hMm0vMXU0Nlhnb0FjZk9rcE9BQ3I3K04rUlJzVTBEazVhM0J1VDE5d25WeXQyYTlkcjM1UjQweno0TWcxK2N4T3BmVFZHS1NwTVpLL0UrTC9mZ0VGaWNXRVUrM2NoU1dUWCtCclcyQXFMa0pBajVJSGlEdlUzejZxcWw5VmdJaXI3UE4yVHdBS0ZjbW1rbkhESlRKcmdNaXJPcHYxVG9kYUt4VEFLUUt5SGV2OHdkaEFvejZld0VWaWtteFVXT2kvLzNzcGZ5OXlVYTB3dDFIZlBQMWd3YVN6ZkJTUFhjdXhReWhacWNISGRGT0d2VW92QkdiNUVmZTFvaGRlL1lUR2RzTHNxZElKVkUxZUNJQjdQNEhscU1qUGtCTWVXb1lmclpPL2o2VDVTS0FHR0N5emJ1aXNKR3pGcmNxU1dTWFp6RE1BQ2JrMS9LeElQT29RclRNSGNJK0JER0dseDQ5OFBrcFp2N256SFhQVmFFc1FnS1VsdEt6MFNTc3lWVksrTTJ2aTNkMUVMZkJwZE8wNkROZm5oUWwzZjV1OXFkWWFhaUR1bTJneUNZQ251dnMvSk9uZ2lJWGc3TzBRbTM2cHhCaFBQVlo0NzYrYjM5UjFoNkI3NWxwbGx6eitFQTV2TFE4aGp2S1M4VVV2eHNKR3l0OUhGTkE4Vk9zSHRlRUhWZlFXTTRsdjE3azBvNW1jUHMwTjdYZE44Nmo3ZU1BQncydy9SdWIwa0MzeVI1VGhvdGV4eFo5SWhJcFY5dDYiIE5vQ2VydGlmaWNhZG89IjIwMDAxMDAwMDAwMzAwMDIyNzcyIiBGb3JtYVBhZ289IjAxIiBTZWxsbz0iWExOYkxsZ2tvSEZiWGx4QmM1T3lnMjlKSStUd1hyVlNnZVFNTmpmb1RZZjFNN3kySUlCWFU1NC8zL3p2ZFVRUDRDYjQ5cU9xRlk0eTRMblNySFZDdTkzSHdCcWJzM1dxM1M3SUNJaU9NeUtDOTF4ZDVCRVhVdStqdElFbDNYZHVWRXhlYXUreUNjY2N0b1E5S3F2d28ybkIzQjNTdkxKUC92cVhxbkxENnhEWXBjZGhFdHd0bzBod0llbnFCd09vZ0JGZlBhTDBGdkNvZUdGelVVRGY1UXIySHpDSjNxbVNxOXRsRE5qNC8wQXNmMFMwYXp2VzFCbUMvMXFhWXpaZ3NGQkJRN01rTVdaT0FGblQ5SGh5bElSR2o4RzJ4RHZ1SmdlRFJCMzNSeENnamFLZUVLM0pXNE0yUUpxamswSFY4dGRhVE1FaUF0WjJyTm9wY3JoR0h3PT0iIEZlY2hhPSIyMDE5LTA4LTE2VDEzOjIyOjA5IiBGb2xpbz0iNTQ0IiBTZXJpZT0iQSIgVmVyc2lvbj0iMy4zIiB4c2k6c2NoZW1hTG9jYXRpb249Imh0dHA6Ly93d3cuc2F0LmdvYi5teC9jZmQvMyBodHRwOi8vd3d3LnNhdC5nb2IubXgvc2l0aW9faW50ZXJuZXQvY2ZkLzMvY2ZkdjMzLnhzZCIgeG1sbnM6Y2ZkaT0iaHR0cDovL3d3dy5zYXQuZ29iLm14L2NmZC8zIiB4bWxuczp4c2k9Imh0dHA6Ly93d3cudzMub3JnLzIwMDEvWE1MU2NoZW1hLWluc3RhbmNlIj4KICAgIDxjZmRpOkVtaXNvciBSZWdpbWVuRmlzY2FsPSI2MDEiIE5vbWJyZT0iUEVSUk8gUkVBTCIgUmZjPSJVUlUwNzAxMjJTMjgiLz4KICAgIDxjZmRpOlJlY2VwdG9yIFVzb0NGREk9IlAwMSIgTm9tYnJlPSJZYWlyIEfDs21leiBWYWxlbmNpYSIgUmZjPSJMQU44NTA3MjY4SUEiLz4KICAgIDxjZmRpOkNvbmNlcHRvcz4KICAgICAgICA8Y2ZkaTpDb25jZXB0byBJbXBvcnRlPSI1MDAwMC4wMCIgVmFsb3JVbml0YXJpbz0iNTAwMDAuMDAiIERlc2NyaXBjaW9uPSJUZWxlZm9ub3MgZGUgbWFyY2EgTW90b3JvbGEsIFNhbXN1bmcsIE5va2lhIHkgSXBob25lIiBVbmlkYWQ9IlBpZXphICIgQ2xhdmVVbmlkYWQ9IkVBIiBDYW50aWRhZD0iMSIgTm9JZGVudGlmaWNhY2lvbj0iNTE0MiIgQ2xhdmVQcm9kU2Vydj0iNDMxOTE1MDEiPgogICAgICAgICAgICA8Y2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iMTA0MDAuMDAiIFRhc2FPQ3VvdGE9IjAuMTYwMDAwIiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSI2NTAwMC4wMCIvPgogICAgICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9IjE1MDAwLjAwIiBUYXNhT0N1b3RhPSIwLjMwMDAwMCIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMyIgQmFzZT0iNTAwMDAuMDAiLz4KICAgICAgICAgICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICAgICAgICAgIDwvY2ZkaTpJbXB1ZXN0b3M+CiAgICAgICAgPC9jZmRpOkNvbmNlcHRvPgogICAgICAgIDxjZmRpOkNvbmNlcHRvIEltcG9ydGU9IjEyMDAuMDAiIFZhbG9yVW5pdGFyaW89IjEyMDAuMDAiIERlc2NyaXBjaW9uPSJQYW50YWxvbmVzIGVudHViYWRvcywgbWV6Y2xpbGxhIiBVbmlkYWQ9Ik1ldHJvIiBDbGF2ZVVuaWRhZD0iTVRSIiBDYW50aWRhZD0iMSIgTm9JZGVudGlmaWNhY2lvbj0iNDU4IiBDbGF2ZVByb2RTZXJ2PSI1MzEwMTUwMiI+CiAgICAgICAgICAgIDxjZmRpOkltcHVlc3Rvcz4KICAgICAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgICAgICAgICA8Y2ZkaTpUcmFzbGFkbyBJbXBvcnRlPSIxOTIuMDAiIFRhc2FPQ3VvdGE9IjAuMTYwMDAwIiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIiBCYXNlPSIxMjAwLjAwIi8+CiAgICAgICAgICAgICAgICA8L2NmZGk6VHJhc2xhZG9zPgogICAgICAgICAgICA8L2NmZGk6SW1wdWVzdG9zPgogICAgICAgIDwvY2ZkaTpDb25jZXB0bz4KICAgIDwvY2ZkaTpDb25jZXB0b3M+CiAgICA8Y2ZkaTpJbXB1ZXN0b3MgVG90YWxJbXB1ZXN0b3NUcmFzbGFkYWRvcz0iMjU1OTIuMDAiPgogICAgICAgIDxjZmRpOlRyYXNsYWRvcz4KICAgICAgICAgICAgPGNmZGk6VHJhc2xhZG8gSW1wb3J0ZT0iMTA1OTIuMDAiIFRhc2FPQ3VvdGE9IjAuMTYwMDAwIiBUaXBvRmFjdG9yPSJUYXNhIiBJbXB1ZXN0bz0iMDAyIi8+CiAgICAgICAgICAgIDxjZmRpOlRyYXNsYWRvIEltcG9ydGU9IjE1MDAwLjAwIiBUYXNhT0N1b3RhPSIwLjMwMDAwMCIgVGlwb0ZhY3Rvcj0iVGFzYSIgSW1wdWVzdG89IjAwMyIvPgogICAgICAgIDwvY2ZkaTpUcmFzbGFkb3M+CiAgICA8L2NmZGk6SW1wdWVzdG9zPgogICAgPGNmZGk6Q29tcGxlbWVudG8+CiAgICAgICAgPHRmZDpUaW1icmVGaXNjYWxEaWdpdGFsIEZlY2hhVGltYnJhZG89IjIwMTktMDgtMTZUMTM6MjI6MTgiIE5vQ2VydGlmaWNhZG9TQVQ9IjIwMDAxMDAwMDAwMzAwMDIyMzIzIiBSZmNQcm92Q2VydGlmPSJBQUEwMTAxMDFBQUEiIFNlbGxvQ0ZEPSJYTE5iTGxna29IRmJYbHhCYzVPeWcyOUpJK1R3WHJWU2dlUU1OamZvVFlmMU03eTJJSUJYVTU0LzMvenZkVVFQNENiNDlxT3FGWTR5NExuU3JIVkN1OTNId0JxYnMzV3EzUzdJQ0lpT015S0M5MXhkNUJFWFV1K2p0SUVsM1hkdVZFeGVhdSt5Q2NjY3RvUTlLcXZ3bzJuQjNCM1N2TEpQL3ZxWHFuTEQ2eERZcGNkaEV0d3RvMGh3SWVucUJ3T29nQkZmUGFMMEZ2Q29lR0Z6VVVEZjVRcjJIekNKM3FtU3E5dGxETmo0LzBBc2YwUzBhenZXMUJtQy8xcWFZelpnc0ZCQlE3TWtNV1pPQUZuVDlIaHlsSVJHajhHMnhEdnVKZ2VEUkIzM1J4Q2dqYUtlRUszSlc0TTJRSnFqazBIVjh0ZGFUTUVpQXRaMnJOb3BjcmhHSHc9PSIgU2VsbG9TQVQ9IlRtSnNOaittbnFBSHgrV3pSekFCc09zcC9wbko0NFhDVnd6cUYyeVVkYW9tWHArVFQ0TjlDcHpHTW90TGh6Z1JFK2RmUXE3YzJxUnBSRllwRFgrYWdPOUdyYWRMTE1URVR6TTEraWkwU3RsNUNhRUQybHZ4KzVSeEZpRWxFbjByL3JGdExTenk1VEEyOXEwQXlPSXRCc09HZWpJdkZJMngyRXVYTzJRWjM1S2MvdHV4ZHBzNVIvQ25nVWdJeGRhZHRCQ0gvNXlZdlZUN3d5MGczRCt1bW9makJBcURWZGFkTkhMYmtacWs0RnFwTlBVNnVGMnNGYVBFQzhER0hUMXpJcjRQT1VUa0RrdmlVUGZjZG5oZUVCYTZqTS9QMWdDUjFiaGxiOHZGbFhmaEVRQ3pWWXBpRHpTcjRud1dxZVd6V2lMRVV5dG1XSVRYblk4dmxyclVJUT09IiBVVUlEPSIyMDM2OWE2Ni04MjFhLTQyNGMtOWE2Yy02MjAxMTJkZGM4MmUiIFZlcnNpb249IjEuMSIgeHNpOnNjaGVtYUxvY2F0aW9uPSJodHRwOi8vd3d3LnNhdC5nb2IubXgvVGltYnJlRmlzY2FsRGlnaXRhbCBodHRwOi8vd3d3LnNhdC5nb2IubXgvc2l0aW9faW50ZXJuZXQvY2ZkL1RpbWJyZUZpc2NhbERpZ2l0YWwvVGltYnJlRmlzY2FsRGlnaXRhbHYxMS54c2QiIHhtbG5zOnRmZD0iaHR0cDovL3d3dy5zYXQuZ29iLm14L1RpbWJyZUZpc2NhbERpZ2l0YWwiLz4KICAgIDwvY2ZkaTpDb21wbGVtZW50bz4KPC9jZmRpOkNvbXByb2JhbnRlPgo=",
        "succeed": true,
        "uuid": "20369a66-821a-424c-9a6c-620112ddc82e",
        "message": "Comprobante procesado correctamente."
      }
    ]
  },
  "message": "Petición satisfactoria."
}
Concepto
GET
Find
https://app.facture.com.mx/api/concepto/find?offset=0&size=10
Descripción
Método para obtener el catálogo de conceptos vinculados a la cuenta del usuario de la plataforma.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

size
10

Example Request
Example Response
View More
curl
curl --location 'https://app.facture.com.mx/api/concepto/find?offset=0&size=10' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
200 OK
Example Response
Body
Headers (6)
View More
json
{
  "succeed": true,
  "code": 2000,
  "pagination": {
    "items": [
      {
        "id": 2270243,
        "impuesto": {
          "id": 51330,
          "tasa": 0,
          "nombre": "exento prueba",
          "tipo": "EXENTO",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": null,
        "categoria": {
          "id": null,
          "nombre": "Cómputo",
          "descripcion": null
        },
        "codigo": "SKU-76891",
        "descripcion": "Exento",
        "precio": 10,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Caja",
        "claveUnidad": "XBX",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "30162402",
        "descripcionProdServ": "Pantalla o biombo o cubículo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2613998,
        "impuesto": {
          "id": 51343,
          "tasa": 0,
          "nombre": "alma iva",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": {
          "id": 40637,
          "tasa": 0.01,
          "nombre": "0%",
          "tipo": null,
          "tipoIeps": "CUOTA",
          "monto": 0,
          "unidad": 0
        },
        "categoria": null,
        "codigo": "NA",
        "descripcion": "Fotocopiadora",
        "precio": 4599,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Aquipar",
        "claveUnidad": "11",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "44101501",
        "descripcionProdServ": "Fotocopiadoras",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": true,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614000,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": null,
        "categoria": null,
        "codigo": "na",
        "descripcion": "servicio de limpieza",
        "precio": 1,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Unidad de servicio",
        "claveUnidad": "E48",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": true,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614001,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": null,
        "categoria": null,
        "codigo": "CLAVE",
        "descripcion": "CONCEPTO CLAVE",
        "precio": 10,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Pieza",
        "claveUnidad": "EA",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614002,
        "impuesto": null,
        "ieps": null,
        "categoria": null,
        "codigo": "So01",
        "descripcion": "Sorgo rojo nacional",
        "precio": 2550.8,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Kilogramo",
        "claveUnidad": "KGM",
        "catMoneda": "USD",
        "tipoCambio": 19.5,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614003,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": null,
        "categoria": null,
        "codigo": "S01",
        "descripcion": "RECIBI DE LA SECRETARIA DE FINANZAS A TRAVEZ DE LA TESORERIA MUNICIPAL DEL HAYUNTAMIENTO MUNICIPAL CONSTITUCIONAL DE SAN BLAS ATEMPA, OAXACA CON CARGO A LOS RECURSOS PROVENIENTES DEL PROGRAMA DE INFRAESTRUCTURA INDIGENA PROII Y DEL FONDO DE INFRAESTRUCTURA SOCIAL MUNICIPAL FSIM FIII, RAMO GENERAL 33 DEL EJERCICIO 2014, LA CANTIDAD DE 1,800,00.00 POR CONCEPTO DE PAGO DEL ANTICIPO DEL 30% RELATIVO A LA OBRA MODERNIZACION Y AMPLIACION DEL CAMIN SAN BLAS ATEMPA TIERRA BLANCA. TRAMO DEL KM 0+000 AL 9+300 SUBTRAMO A MODERNIZAR DEL KM 7+850 AL 9+300 EN EL MUNICIPIO DE SAN BLAS ATEMPA, OAX. SEGUN CONTRATO DE OBRA PUBLICA No. MSBAOP/124/INFRA/FISMOO1/2014 QUE INCLUYEN LAS 0ARTIDAS PRELIMINARES DE TERRACERIAS Y OBRAS COMPLEMENTARIAS, PAVIMENTOS Y SEÑANAMIENTOS  CON PERIODO DE EJECUCION DEL 13 DE MAYO DEL 2014 AL DIA 28 DE NOVIMEMBRE DEL 2014, DE FECHA DE 10 DE MAYO DE 2014 CON UN IMPORTE TOTAL DE 6,000,000.00 SEIS MILLONES DE PESOS 00/100 M.N. IVA INCLUIDO DE ACUERDO AL ACTA DE AUTORIZACION DE RECURSIS No. MSBA/FISM0165/2014 DE FECHA 14 DE MARZO DE 2014 CORRESPONDIENTE AL PROGRAMA UC CARRETERAS ALIMENTADORAS Y SUBPROGRAMA 02 MODERNIZACION Y AMPLIACION",
        "precio": 1551724.15,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Unidad de servicio",
        "claveUnidad": "E48",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614004,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": {
          "id": 5745,
          "tasa": 0.05,
          "nombre": "FOO",
          "tipo": null,
          "tipoIeps": "TRASLADO",
          "monto": 0,
          "unidad": 0
        },
        "categoria": null,
        "codigo": "SKU-46331",
        "descripcion": "prueba tasa 16 sin impuesto",
        "precio": 100,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Pieza",
        "claveUnidad": "EA",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614005,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": null,
        "categoria": null,
        "codigo": "007",
        "descripcion": "CONCEPTO DE PRUEBA",
        "precio": 58,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Unidad de servicio",
        "claveUnidad": "E48",
        "catMoneda": "MXN",
        "tipoCambio": 1,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614006,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": {
          "id": 23135,
          "tasa": 0.03,
          "nombre": "IEPS gasolin",
          "tipo": null,
          "tipoIeps": "CUOTA",
          "monto": 0,
          "unidad": 0
        },
        "categoria": null,
        "codigo": "SKU-101656",
        "descripcion": "valero",
        "precio": 1723,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Pieza",
        "claveUnidad": "EA",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      },
      {
        "id": 2614007,
        "impuesto": {
          "id": 1210,
          "tasa": 0.16,
          "nombre": "IVA",
          "tipo": "IVA",
          "tipoIeps": null,
          "monto": null,
          "unidad": 0
        },
        "ieps": {
          "id": 23135,
          "tasa": 0.03,
          "nombre": "IEPS gasolin",
          "tipo": null,
          "tipoIeps": "CUOTA",
          "monto": 0,
          "unidad": 0
        },
        "categoria": null,
        "codigo": "poesía",
        "descripcion": "Servicios porfesionales",
        "precio": 16783.22,
        "precio1": null,
        "precio2": null,
        "precio3": null,
        "precio4": null,
        "precio5": null,
        "decimales": 0,
        "unidad": "Unidad de servicio",
        "claveUnidad": "E48",
        "catMoneda": "MXN",
        "tipoCambio": 0,
        "claveProdServ": "01010101",
        "descripcionProdServ": "No existe en el catálogo",
        "informacionAduanera": null,
        "cantidad": 0,
        "concepto": null,
        "deducir": false,
        "version": "V3_3",
        "cuentaPredial": null
      }
    ],
    "count": 632,
    "offset": 0
  },
  "message": "Petición satisfactoria."
}
POST
Crear
https://app.facture.com.mx/api/concepto
Descripción
Método que permite crear un concepto.

Nota: Para la nueva versión del CFDI v4.0 se requiere un nuevo atributo para el concepto, ahora se debe agregar Objeto impuesto.

Atributos requeridos
precio
unidad
catMoneda
claveUnidad
claveProdServ
tipoCambio
objetoImp
HEADERS
Authorization
Bearer

Accept
application/json

Content-Type
application/json

Body
raw
View More
{
    "entity": {
        "data":{
            "impuesto": {
                "tasa": 0.16,
                "nombre": "IVA",
                "tipo": "IVA",
                "tipoIeps": null,
                "monto": null,
                "unidad": 0.0
            },
            "ieps": null,
            "categoria": {
                "id": null,
                "nombre": "Cómputo",
                "descripcion": null
            },
            "codigo": "TEST-76891",
            "descripcion": "TEST DE PRUEBA DESDE POSTMAN-API PARA ACTUALIZAR",
            "precio": 1234.000000,
            "precio1": null,
            "precio2": null,
            "precio3": null,
            "precio4": null,
            "precio5": null,
            "decimales": 0,
            "unidad": "Caja",
            "claveUnidad": "XBX",
            "catMoneda": "MXN",
            "tipoCambio": 0.000000,
            "claveProdServ": "43201902",
            "descripcionProdServ": "Prueba de concepto desde API",
            "informacionAduanera": null,
            "cantidad": 1.000000,
            "concepto": null,
            "deducir": false,
            "version": "V3_3",
            "cuentaPredial": null,
            "objetoImp": "01"
        }
    }
}
Example Request
Example Response
View More
curl
curl --location 'https://app.facture.com.mx/api/concepto' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "entity": {
    "data":{
            "impuesto": {
                "tasa": 0.16,
                "nombre": "IVA",
                "tipo": "IVA",
                "tipoIeps": null,
                "monto": null,
                "unidad": 0.0
            },
            "ieps": null,
            "categoria": {
                "id": null,
                "nombre": "Cómputo",
                "descripcion": null
            },
            "codigo": "TEST-76891",
            "descripcion": "TEST DE PRUEBA DESDE POSTMAN-API PARA ELIMINAR",
            "precio": 12345.000000,
            "precio1": null,
            "precio2": null,
            "precio3": null,
            "precio4": null,
            "precio5": null,
            "decimales": 0,
            "unidad": "Caja",
            "claveUnidad": "XBX",
            "catMoneda": "MXN",
            "tipoCambio": 0.000000,
            "claveProdServ": "43201902",
            "descripcionProdServ": "Prueba de concepto desde API",
            "informacionAduanera": null,
            "cantidad": 1.000000,
            "concepto": null,
            "deducir": false,
            "version": "V3_3",
            "cuentaPredial": null
    }
  }
}'
200 OK
Example Response
Body
Headers (4)
json
{
  "succeed": true,
  "code": 2001,
  "serverId": 5114453,
  "message": "Item creado correctamente."
}
DELETE
Eliminar
https://app.facture.com.mx/api/concepto?serverId=5114453
Punto de enlace para eliminar un concepto, proveyendo el id del concepto.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
serverId
5114453

Example Request
Example Response
View More
curl
curl --location --request DELETE 'https://app.facture.com.mx/api/concepto?serverId=5114453' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data ''
200 OK
Example Response
Body
Headers (6)
json
{
  "succeed": true,
  "code": 2002,
  "message": "Item eliminado correctamente."
}
PUT
Actualizar
https://app.facture.com.mx/api/concepto
Descripción
Método que permite actualizar un concepto.

HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional para definir el formato en que se envía la petición (application/xml, application/json).

Content-Type
application/json

Header requerido con el formato de la respuesta esperada (application/xml, application/json).

Body
raw
View More
{
    "entity": {
        "data": {
            "id": 1148729,
            "impuesto": {
                "tasa": 0.16,
                "nombre": "IVA",
                "tipo": "IVA",
                "tipoIeps": null,
                "monto": null,
                "unidad": 0.0
            },
            "ieps": null,
            "categoria": {
                "id": null,
                "nombre": "Cómputo",
                "descripcion": null
            },
            "codigo": "TEST-76891",
            "descripcion": "TEST DE PRUEBA DESDE POSTMAN-API (actualizado)",
            "precio": 1234.0,
            "precio1": null,
            "precio2": null,
            "precio3": null,
            "precio4": null,
            "precio5": null,
            "decimales": 0,
            "unidad": "Caja",
            "claveUnidad": "XBX",
            "catMoneda": "MXN",
            "tipoCambio": 0.0,
            "claveProdServ": "43201902",
            "descripcionProdServ": "Prueba de concepto desde API",
            "informacionAduanera": null,
            "cantidad": 1.0,
            "concepto": null,
            "deducir": false,
            "version": "V3_3",
            "cuentaPredial": null,
            "objetoImp": "03"
        }
    }
}
Example Request
Example Response
View More
curl
curl --location --request PUT 'https://app.facture.com.mx/api/concepto' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "entity": {
    "data": {
        "id": 5108199,
        "impuesto": {
        "tasa": 0.16,
        "nombre": "IVA",
        "tipo": "IVA",
        "tipoIeps": null,
        "monto": null,
        "unidad": 0.0
      },
      "ieps": null,
      "categoria": {
        "id": null,
        "nombre": "Cómputo",
        "descripcion": null
      },
      "codigo": "TEST-76891",
      "descripcion": "TEST DE PRUEBA DESDE POSTMAN-API (actualizado-enero2021)",
      "precio": 1234.0,
      "precio1": null,
      "precio2": null,
      "precio3": null,
      "precio4": null,
      "precio5": null,
      "decimales": 0,
      "unidad": "Caja",
      "claveUnidad": "XBX",
      "catMoneda": "MXN",
      "tipoCambio": 0.0,
      "claveProdServ": "43201902",
      "descripcionProdServ": "Prueba de concepto desde API",
      "informacionAduanera": null,
      "cantidad": 1.0,
      "concepto": null,
      "deducir": false,
      "version": "V3_3",
      "cuentaPredial": null
    }
  }
}'
200 OK
Example Response
Body
Headers (6)
json
{
  "succeed": true,
  "code": 3000,
  "message": "Item actualizado correctamente."
}
Emisor
GET
Find
https://app.facture.com.mx/api/emisor/find?offset=0&size=10
Método para obtener el catálogo de emisores disponibles.

HEADERS
Authorization
Bearer

Accept
application/json

Content-Type
application/json

PARAMS
offset
0

size
10

Example Request
Example Response
View More
curl
curl --location 'https://app.facture.com.mx/api/emisor/find?offset=0&size=10' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
200 OK
Example Response
Body
Headers (6)
View More
json
{
  "succeed": true,
  "code": 2000,
  "pagination": {
    "items": [
      {
        "id": 23231,
        "nombre": "SINDICATO DE CARGADORES Y ESTIBADORES LAZARO CARDENAS DE LOS REYES, MICH.",
        "rfc": "SCE660930PDA",
        "tipo": "MORAL",
        "regimenes": null,
        "sucursales": null,
        "curp": null,
        "regimenNomina": null,
        "sucursal": null,
        "useRegimenNomina": false
      },
      {
        "id": 29975,
        "nombre": "prueba emisor",
        "rfc": "IVD920810GU2",
        "tipo": "MORAL",
        "regimenes": null,
        "sucursales": null,
        "curp": null,
        "regimenNomina": null,
        "sucursal": null,
        "useRegimenNomina": false
      },
      {
        "id": 29977,
        "nombre": "prueba para csd",
        "rfc": "ZUÑ920208KL4",
        "tipo": "MORAL",
        "regimenes": null,
        "sucursales": null,
        "curp": null,
        "regimenNomina": null,
        "sucursal": null,
        "useRegimenNomina": false
      },
      {
        "id": 30197,
        "nombre": "PET & VET CARE",
        "rfc": "IVD920810GU1",
        "tipo": "MORAL",
        "regimenes": null,
        "sucursales": null,
        "curp": null,
        "regimenNomina": null,
        "sucursal": null,
        "useRegimenNomina": false
      },
      {
        "id": 30202,
        "nombre": "LUIS IAN NUZCO",
        "rfc": "IAÑL750210963",
        "tipo": "FISICA",
        "regimenes": null,
        "sucursales": null,
        "curp": null,
        "regimenNomina": null,
        "sucursal": null,
        "useRegimenNomina": false
      },
      {
        "id": 30660,
        "nombre": "XAIME WEIR ROJO",
        "rfc": "WERX631016S30",
        "tipo": "FISICA",
        "regimenes": null,
        "sucursales": null,
        "curp": null,
        "regimenNomina": null,
        "sucursal": null,
        "useRegimenNomina": false
      }
    ],
    "count": 6,
    "offset": 0
  },
  "message": "Petición satisfactoria."
}
POST
Crear
https://app.facture.com.mx/api/emisor
Descripción
Método que permite crear un emisor

Atributos requeridos
Nombre completo o razón social
RFC
CURP
Nombre sucursal
Código postal
Calle
Número exterior
Colonia
Localidad
Municipio
País
Estado
Contraseña CSD
Llave CSD (base 64)
Certificado CSD (base 64)
HEADERS
Authorization
Bearer

Accept
application/json

Content-Type
application/json

Body
raw
View More
{
  "entity": {
    "data": {
      "nombre": "ZAPATERIA URTADO ÑERI SA DE CV",
      "rfc": "ZUÑ920208KL4",
      "tipo": "MORAL",
      "curp": "",
      "regimenNomina": "true",
      "sucursal": {
        "nombre": "Mi primera sucursal",
        "cert": "MIIFuzCCA6OgAwIBAgIUMzAwMDEwMDAwMDA0MDAwMDI0NDIwDQYJKoZIhvcNAQELBQAwggErMQ8wDQYDVQQDDAZBQyBVQVQxLjAsBgNVBAoMJVNFUlZJQ0lPIERFIEFETUlOSVNUUkFDSU9OIFRSSUJVVEFSSUExGjAYBgNVBAsMEVNBVC1JRVMgQXV0aG9yaXR5MSgwJgYJKoZIhvcNAQkBFhlvc2Nhci5tYXJ0aW5lekBzYXQuZ29iLm14MR0wGwYDVQQJDBQzcmEgY2VycmFkYSBkZSBjYWRpejEOMAwGA1UEEQwFMDYzNzAxCzAJBgNVBAYTAk1YMRkwFwYDVQQIDBBDSVVEQUQgREUgTUVYSUNPMREwDwYDVQQHDAhDT1lPQUNBTjERMA8GA1UELRMIMi41LjQuNDUxJTAjBgkqhkiG9w0BCQITFnJlc3BvbnNhYmxlOiBBQ0RNQS1TQVQwHhcNMTkwNjE3MjAxODA2WhcNMjMwNjE3MjAxODA2WjCB4jEnMCUGA1UEAxQeWkFQQVRFUklBIFVSVEFETyDRRVJJIFNBIERFIENWMScwJQYDVQQpFB5aQVBBVEVSSUEgVVJUQURPINFFUkkgU0EgREUgQ1YxJzAlBgNVBAoUHlpBUEFURVJJQSBVUlRBRE8g0UVSSSBTQSBERSBDVjElMCMGA1UELRQcWlXROTIwMjA4S0w0IC8gS0FITzY0MTEwMUIzOTEeMBwGA1UEBRMVIC8gS0FITzY0MTEwMUhOVExLUzA2MR4wHAYDVQQLFBVaYXBhdGVy7WEgVXJ0YWRvINFlcmkwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCX4ICuLAA/GYfEdRUMBLolOHKOUTDyw//CQjcj6dmCUWP8Y2UIhbq1JJ5nClsmhg6WThXjYqzRiYSAYagzrtwWri8I7oURIoTDeuxU69UOg1shuCEo89prJfx8ZIGKwB+GedC6dFdKGZLKn4ksSuyfntXmW4rZLup2j24mQx+jpdmcQIGMe6A/2668ZhpYpCw/vwfH8edkS5X41yp/zj6Z9mIySRWaMHm5eT/X0D01l3gX2gJVXY5ebUsKIEmDCP5pmBmyFyJjqYUhYi3+nYARrVPdZ53RPigcoLspXyIBVf2CPansqOYiRIqxLjfsL6B1qGJmpFN1RHd+XF9GzkHzAgMBAAGjHTAbMAwGA1UdEwEB/wQCMAAwCwYDVR0PBAQDAgbAMA0GCSqGSIb3DQEBCwUAA4ICAQAw5nqLkVuaTWX/qnPqWpOlSfrfVF2plqAPu1sdgzU/vZGgZCLxq3cY3Dg02khdsM74A3fYFQGxFFo6zDt5Ru9VySBks2gmbebRuuYKAKHMoi0tNpvI2arOMaiJsq2yzGAox6e+MXhhQ1oV+28HaxjYIWuVSwWAzie+n6VloWXMDDxTg7t9URJ7d8E7ZrPZ2X0+7h6b8sfnOjTljUm+Mt6e6AIl1+lA7Ar4YeJgIOeU/RKSOcvrCGDNtz7kpn0XdsFC/m1xG1bIdhyv0zkRmKvtpVGROsSEHPLBwuTENIkm0Cw2/FQsPrG8S9Yer451Jv0H+heFfJXqVrHv8Azba5OkotK5IlPaHm9voxfhwI1aXHbUS+NwpTHHOeVsvzsC9LQDiLGO3MQLn7VFZebFa84CddwLWf/JTyPYIcg3I/BRDFqbrVNRRDJwVlxIgbU7ZhRIcjiRLFx6UbAd8B2fe+GHgxw/x/6Pop65ca32iGAJgojtXWdD5gZvh6PlhEhWMhIOCvAesE52L4wmIHofAIZjlPRXNaitZ74xDEIpQ9f5X0w/4ltB7OOBYYfu8z9lzCc4M2iSmtq1rvSYxnLI/nvt7D0uYrq82ZeG3kzungcS8qNjufGJeEHNeyDxMXF9rYU4KLxK8KLzA2Ldh7+3zywU/pwXhW/aTS42EViWPbffng==",
        "key": "MIIFDjBABgkqhkiG9w0BBQ0wMzAbBgkqhkiG9w0BBQwwDgQIAgEAAoIBAQACAggAMBQGCCqGSIb3DQMHBAgwggS8AgEAMASCBMh4EHl7aNSCaMDA1VlRoXCZ5UUmqErAbucRFLOMmsAaFOpXTvw9AqcPbs75oxETQg3qB56TG1Cf2xjcp73gVNksxtSUUSaKuq92Ag16W6bbqKeJKSVMNQGhWGX8gUVJxw+vHXqTGRxOIFDbzgaGGPkZVPbifp2fHbRcCmJg2Ugb8eNe1MQByYHxN0UTw4OBR1Hs3D9k4c9STHzZ9OGWLnu6kc/z+b3GqWEGFwfs6vScvO87lw446GU6qRsAgPcazWX+bUf22mPx4YgdUyc4a0ARgQskZKdGoM9mNmyXCAW636uL/uyuudHjdM8EzDZE4dGd3aXgI3vuGb7Fqu21oOtdPkBCw7HsfOAjz9OZmLMNXGo/eWlJrVLO48yCwOXuyHjaWmRUqXVsggRXnCezML7IkLHX0Qqx0zbVu2RGRtKVv9ThFQxTkGqSWC3yMirEujU743ZLjZtgTmhv2cJ9iZ564R+jjYGe2IdY5akmyggjYdDpRkxrgw9jzmO1DVW5/8p2k19EcKnISIEDb65hq0fBucIxpnJJwMyJG+1N0pX4hb+A4GSdReFuxukZCTGfuMzKHQghalDdvEYIO/d6Fpo0eRXI5FPOlb4LNxq3P7vc8dqINrTtLQNQ7UEILZn3byvx5scaFO+FgP+a4H4aIHLR7DkYHEg5iHb2ZIBNvl7WgVmkvrJWF3x8ng0dVdfRcTLOyvFIq45bGW8sHDWO81gJp0PukxkeBKSrwC6BVDtUlAod7C1zcNQBKH9IGjWNEueGNnFEkMlHpmmM0HwJIPrlWFeLyZBDpD9DcWYHfVuKQs3jUOcVKbmqfobfhWlFfzkD3a0IMWtcxtr1IPF3DV6r1cyGkn660swMyh5SLKRUMtHnjismq7pNHEcKWI08NGFkf+ZXiB/ty2xMcbWU5yjKmly7ReSivh/8c7SkX90JXJCHfR2ryd1ToNy9pBGPvBlYw3jjRl8ZzL72I9WPxh18sc59ML5+NdiNW+7CcvuBL4XVTO+31HURXPOtPViQi4D/UnH2rW8dhH7X1VfGSNM4rs2w47hdCC7FjcWhBpdf9TnpFQx5WpbkS6P3MwsbJJrT7JkB8B60gAKexVI1KBBPj35n5UHAKUi2PaAenUUX6qbQaNgxwB8vFOgm44J7xef69n6AcIcjn7boWigm4uvRWS/TEsjta/bTX7Gx4qjF/1Ketgip0zliu+rbOyAihYxJSNIZv7PB1Nnj/7i2/AaqnBJex+Du7aRvjkoXu+QSh0/Fc1p05RQZX1AZno1PCcxzWL/49zU1rPONmPhwpnMW8A1RXq6wPqjC4+QMOaqNSndJSjxEzHSzbZcm8vMrgvkOnlgA0Np9nT0xj6tlLuRmMSFGdKFFa8FFGOcxXXyLHktoIqDABy3zF+jEa8ZRiBkR1A+NIhPMkgxCi2LLtbAhoWXdnHbTsqhbwIJc7zp25wvNi5iaJUvwIIzvHY52qCtmVNu/lFjkNHKZIYQ/rg8M6gVbeTtJYZkWCXBQ6QoafwZomjDIcBqf8Dz8BvcAPTOyrMLvOWUNtiE1PKNUVaf85IC/zZh/sWvCtG2/oDAwPm1qS704io0NfDtO7qc29Tu0CAQ6ncvGHDBZJ5WqWtjGLMNdMMQxHQc=",
        "logo": "iVBORw0KGgoAAAANSUhEUgAAAI8AAAB4CAYAAADL9KEyAAEMqElEQVR4AQByyY02AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAUAAAAUAAAAIgAAAC0AAAA7AAAAUgAAAGkAAAB/AAAAmQAAAK8AAADCAAAA1gAAAOcAAADrAAAA9AAAAPQAAAD1AAAA7QAAAOgAAADZAAAAxQAAALIAAACdAAAAhAAAAGwAAABWAAAAPwAAAC4AAAAkAAAAFgAAAAcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAACwAAACsAAABMAAAAdAAAAJkAAAC8AAAA3wAAAPYAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPoAAADjAAAAwQAAAJ4AAAB7AAAAUgAAADEAAAARAAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAIAAAAKgAAAGMAAACZAAAAxQAAAN0AAAD1AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA+AAAAOEAAADKAAAAogAAAG0AAAA0AAAADAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIAAAAQAAAANQAAAH8AAADHAAAA8QAAAPoAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPsAAADzAAAA0wAAAIwAAABBAAAAEwAAAAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAPgAAAH4AAADJAAAA+wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD9AAAA1QAAAIoAAABIAAAAGAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJgAAAHQAAAC7AAAA8QAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD3AAAAxQAAAIIAAAAyAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAAADIAAACeAAAA5QAAAP0AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAADrAAAAsAAAAEIAAAADAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4AAABDAAAAqAAAAPQAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/gAAAPUAAAC8AAAAUQAAABUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASwAAAK8AAAD5AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA+wAAAMAAAABbAAAABwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAADMAAACuAAAA7wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD4AAAAugAAAEgAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAdAAAAhwAAAOoAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPAAAACeAAAAJwAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAFgAAADYAAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA7AAAAGsAAAAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAjAAAAoAAAAPYAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AQEB/wICAv8GBgb/Dw8P/xoaGv8iIiL/JiYm/yYmJv8mJib/ICAg/xgYGP8ODg7/BgYG/wICAv8BAQH/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPsAAAC3AAAANgAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA...",
        "password": "12345678a",
        "serie": {
          "serie": "A",
          "folioinicial": 1,
          "foliofinal": 1001,
          "folioactual": 1,
          "tipo": "FACTURA"
        },
        "direccion": {
          "codigopostal": "58000",
          "calle": "FOo",
          "numeroexterior": "12",
          "colonia": "Ventura Pte",
          "ciudad": "Morelia",
          "municipio": "Morelia",
          "pais": "México",
          "estado": "Michoacán"
        }
      },
      "regimenes": [
        {
          "catRegimenFiscal": "601"
        }
      ]
    }
  }
}
Example Request
Example Response
View More
curl
curl --location 'https://app.facture.com.mx/api/emisor' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "entity": {
    "data": {
      "nombre": "ZAPATERIA URTADO ÑERI SA DE CV",
      "rfc": "ZUÑ920208KL4",
      "tipo": "MORAL",
      "curp": "",
      "regimenNomina": "true",
      "sucursal": {
        "nombre": "Mi primera sucursal",
        "cert": "MIIFuzCCA6OgAwIBAgIUMzAwMDEwMDAwMDA0MDAwMDI0NDIwDQYJKoZIhvcNAQELBQAwggErMQ8wDQYDVQQDDAZBQyBVQVQxLjAsBgNVBAoMJVNFUlZJQ0lPIERFIEFETUlOSVNUUkFDSU9OIFRSSUJVVEFSSUExGjAYBgNVBAsMEVNBVC1JRVMgQXV0aG9yaXR5MSgwJgYJKoZIhvcNAQkBFhlvc2Nhci5tYXJ0aW5lekBzYXQuZ29iLm14MR0wGwYDVQQJDBQzcmEgY2VycmFkYSBkZSBjYWRpejEOMAwGA1UEEQwFMDYzNzAxCzAJBgNVBAYTAk1YMRkwFwYDVQQIDBBDSVVEQUQgREUgTUVYSUNPMREwDwYDVQQHDAhDT1lPQUNBTjERMA8GA1UELRMIMi41LjQuNDUxJTAjBgkqhkiG9w0BCQITFnJlc3BvbnNhYmxlOiBBQ0RNQS1TQVQwHhcNMTkwNjE3MjAxODA2WhcNMjMwNjE3MjAxODA2WjCB4jEnMCUGA1UEAxQeWkFQQVRFUklBIFVSVEFETyDRRVJJIFNBIERFIENWMScwJQYDVQQpFB5aQVBBVEVSSUEgVVJUQURPINFFUkkgU0EgREUgQ1YxJzAlBgNVBAoUHlpBUEFURVJJQSBVUlRBRE8g0UVSSSBTQSBERSBDVjElMCMGA1UELRQcWlXROTIwMjA4S0w0IC8gS0FITzY0MTEwMUIzOTEeMBwGA1UEBRMVIC8gS0FITzY0MTEwMUhOVExLUzA2MR4wHAYDVQQLFBVaYXBhdGVy7WEgVXJ0YWRvINFlcmkwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQCX4ICuLAA/GYfEdRUMBLolOHKOUTDyw//CQjcj6dmCUWP8Y2UIhbq1JJ5nClsmhg6WThXjYqzRiYSAYagzrtwWri8I7oURIoTDeuxU69UOg1shuCEo89prJfx8ZIGKwB+GedC6dFdKGZLKn4ksSuyfntXmW4rZLup2j24mQx+jpdmcQIGMe6A/2668ZhpYpCw/vwfH8edkS5X41yp/zj6Z9mIySRWaMHm5eT/X0D01l3gX2gJVXY5ebUsKIEmDCP5pmBmyFyJjqYUhYi3+nYARrVPdZ53RPigcoLspXyIBVf2CPansqOYiRIqxLjfsL6B1qGJmpFN1RHd+XF9GzkHzAgMBAAGjHTAbMAwGA1UdEwEB/wQCMAAwCwYDVR0PBAQDAgbAMA0GCSqGSIb3DQEBCwUAA4ICAQAw5nqLkVuaTWX/qnPqWpOlSfrfVF2plqAPu1sdgzU/vZGgZCLxq3cY3Dg02khdsM74A3fYFQGxFFo6zDt5Ru9VySBks2gmbebRuuYKAKHMoi0tNpvI2arOMaiJsq2yzGAox6e+MXhhQ1oV+28HaxjYIWuVSwWAzie+n6VloWXMDDxTg7t9URJ7d8E7ZrPZ2X0+7h6b8sfnOjTljUm+Mt6e6AIl1+lA7Ar4YeJgIOeU/RKSOcvrCGDNtz7kpn0XdsFC/m1xG1bIdhyv0zkRmKvtpVGROsSEHPLBwuTENIkm0Cw2/FQsPrG8S9Yer451Jv0H+heFfJXqVrHv8Azba5OkotK5IlPaHm9voxfhwI1aXHbUS+NwpTHHOeVsvzsC9LQDiLGO3MQLn7VFZebFa84CddwLWf/JTyPYIcg3I/BRDFqbrVNRRDJwVlxIgbU7ZhRIcjiRLFx6UbAd8B2fe+GHgxw/x/6Pop65ca32iGAJgojtXWdD5gZvh6PlhEhWMhIOCvAesE52L4wmIHofAIZjlPRXNaitZ74xDEIpQ9f5X0w/4ltB7OOBYYfu8z9lzCc4M2iSmtq1rvSYxnLI/nvt7D0uYrq82ZeG3kzungcS8qNjufGJeEHNeyDxMXF9rYU4KLxK8KLzA2Ldh7+3zywU/pwXhW/aTS42EViWPbffng==",
        "key": "MIIFDjBABgkqhkiG9w0BBQ0wMzAbBgkqhkiG9w0BBQwwDgQIAgEAAoIBAQACAggAMBQGCCqGSIb3DQMHBAgwggS8AgEAMASCBMh4EHl7aNSCaMDA1VlRoXCZ5UUmqErAbucRFLOMmsAaFOpXTvw9AqcPbs75oxETQg3qB56TG1Cf2xjcp73gVNksxtSUUSaKuq92Ag16W6bbqKeJKSVMNQGhWGX8gUVJxw+vHXqTGRxOIFDbzgaGGPkZVPbifp2fHbRcCmJg2Ugb8eNe1MQByYHxN0UTw4OBR1Hs3D9k4c9STHzZ9OGWLnu6kc/z+b3GqWEGFwfs6vScvO87lw446GU6qRsAgPcazWX+bUf22mPx4YgdUyc4a0ARgQskZKdGoM9mNmyXCAW636uL/uyuudHjdM8EzDZE4dGd3aXgI3vuGb7Fqu21oOtdPkBCw7HsfOAjz9OZmLMNXGo/eWlJrVLO48yCwOXuyHjaWmRUqXVsggRXnCezML7IkLHX0Qqx0zbVu2RGRtKVv9ThFQxTkGqSWC3yMirEujU743ZLjZtgTmhv2cJ9iZ564R+jjYGe2IdY5akmyggjYdDpRkxrgw9jzmO1DVW5/8p2k19EcKnISIEDb65hq0fBucIxpnJJwMyJG+1N0pX4hb+A4GSdReFuxukZCTGfuMzKHQghalDdvEYIO/d6Fpo0eRXI5FPOlb4LNxq3P7vc8dqINrTtLQNQ7UEILZn3byvx5scaFO+FgP+a4H4aIHLR7DkYHEg5iHb2ZIBNvl7WgVmkvrJWF3x8ng0dVdfRcTLOyvFIq45bGW8sHDWO81gJp0PukxkeBKSrwC6BVDtUlAod7C1zcNQBKH9IGjWNEueGNnFEkMlHpmmM0HwJIPrlWFeLyZBDpD9DcWYHfVuKQs3jUOcVKbmqfobfhWlFfzkD3a0IMWtcxtr1IPF3DV6r1cyGkn660swMyh5SLKRUMtHnjismq7pNHEcKWI08NGFkf+ZXiB/ty2xMcbWU5yjKmly7ReSivh/8c7SkX90JXJCHfR2ryd1ToNy9pBGPvBlYw3jjRl8ZzL72I9WPxh18sc59ML5+NdiNW+7CcvuBL4XVTO+31HURXPOtPViQi4D/UnH2rW8dhH7X1VfGSNM4rs2w47hdCC7FjcWhBpdf9TnpFQx5WpbkS6P3MwsbJJrT7JkB8B60gAKexVI1KBBPj35n5UHAKUi2PaAenUUX6qbQaNgxwB8vFOgm44J7xef69n6AcIcjn7boWigm4uvRWS/TEsjta/bTX7Gx4qjF/1Ketgip0zliu+rbOyAihYxJSNIZv7PB1Nnj/7i2/AaqnBJex+Du7aRvjkoXu+QSh0/Fc1p05RQZX1AZno1PCcxzWL/49zU1rPONmPhwpnMW8A1RXq6wPqjC4+QMOaqNSndJSjxEzHSzbZcm8vMrgvkOnlgA0Np9nT0xj6tlLuRmMSFGdKFFa8FFGOcxXXyLHktoIqDABy3zF+jEa8ZRiBkR1A+NIhPMkgxCi2LLtbAhoWXdnHbTsqhbwIJc7zp25wvNi5iaJUvwIIzvHY52qCtmVNu/lFjkNHKZIYQ/rg8M6gVbeTtJYZkWCXBQ6QoafwZomjDIcBqf8Dz8BvcAPTOyrMLvOWUNtiE1PKNUVaf85IC/zZh/sWvCtG2/oDAwPm1qS704io0NfDtO7qc29Tu0CAQ6ncvGHDBZJ5WqWtjGLMNdMMQxHQc=",
        "logo": "iVBORw0KGgoAAAANSUhEUgAAAI8AAAB4CAYAAADL9KEyAAEMqElEQVR4AQByyY02AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAUAAAAUAAAAIgAAAC0AAAA7AAAAUgAAAGkAAAB/AAAAmQAAAK8AAADCAAAA1gAAAOcAAADrAAAA9AAAAPQAAAD1AAAA7QAAAOgAAADZAAAAxQAAALIAAACdAAAAhAAAAGwAAABWAAAAPwAAAC4AAAAkAAAAFgAAAAcAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAACwAAACsAAABMAAAAdAAAAJkAAAC8AAAA3wAAAPYAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPoAAADjAAAAwQAAAJ4AAAB7AAAAUgAAADEAAAARAAAAAgAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMAAAAIAAAAKgAAAGMAAACZAAAAxQAAAN0AAAD1AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA+AAAAOEAAADKAAAAogAAAG0AAAA0AAAADAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIAAAAQAAAANQAAAH8AAADHAAAA8QAAAPoAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPsAAADzAAAA0wAAAIwAAABBAAAAEwAAAAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAPgAAAH4AAADJAAAA+wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD9AAAA1QAAAIoAAABIAAAAGAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAJgAAAHQAAAC7AAAA8QAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD3AAAAxQAAAIIAAAAyAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAAADIAAACeAAAA5QAAAP0AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAADrAAAAsAAAAEIAAAADAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4AAABDAAAAqAAAAPQAAAD+AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/gAAAPUAAAC8AAAAUQAAABUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAASwAAAK8AAAD5AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA+wAAAMAAAABbAAAABwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABAAAADMAAACuAAAA7wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD4AAAAugAAAEgAAAAHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAEAAAAdAAAAhwAAAOoAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPAAAACeAAAAJwAAAAIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAFgAAADYAAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA7AAAAGsAAAAMAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAjAAAAoAAAAPYAAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AQEB/wICAv8GBgb/Dw8P/xoaGv8iIiL/JiYm/yYmJv8mJib/ICAg/xgYGP8ODg7/BgYG/wICAv8BAQH/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAP8AAAD/AAAA/wAAAPsAAAC3AAAANgAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA...",
        "password": "12345678a",
        "serie": {
          "serie": "A",
          "folioinicial": 1,
          "foliofinal": 1001,
          "folioactual": 1,
          "tipo": "FACTURA"
        },
        "direccion": {
          "codigopostal": "58000",
          "calle": "FOo",
          "numeroexterior": "12",
          "colonia": "Ventura Pte",
          "ciudad": "Morelia",
          "municipio": "Morelia",
          "pais": "México",
          "estado": "Michoacán"
        }
      },
      "regimenes": [
        {
          "catRegimenFiscal": "601"
        }
      ]
    }
  }
}'
200 OK
Example Response
Body
Headers (4)
json
{
  "succeed": true,
  "code": 2001,
  "serverId": 30735,
  "message": "Item creado correctamente."
}
PUT
Actualizar
https://app.facture.com.mx/api/emisor
Punto de enlace para actualizar un emisor, proveyendo un modelo que contenga su id y los datos a actualizar

HEADERS
Authorization
Bearer

Accept
application/json

Content-Type
application/json

Body
raw
View More
{
  "entity": {
    "data": {
      "id": 30735,
      "nombre": "ZAPATERIA HURTADO ÑERI S.A DE C.V",
      "rfc": "ZUÑ920208KL4",
      "tipo": "MORAL",
      "curp": "",
      "regimenNomina": "true",
      "sucursal": {
        "nombre": "Mi primera sucursal API",
        "password": "12345678a",
        "direccion": {
          "codigopostal": "58000",
          "calle": "Rio Yaqui",
          "numeroexterior": "286",
          "colonia": "Ventura Puente",
          "ciudad": "Morelia",
          "municipio": "Morelia",
          "pais": "México",
          "estado": "Michoacán"
        }
      }
    }
  }
}
Example Request
Example response
View More
curl
curl --location --request PUT 'https://app.facture.com.mx/api/emisor' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json' \
--data '{
  "entity": {
    "data": {
      "id": 30735,
      "nombre": "ZAPATERIA HURTADO ÑERI S.A DE C.V",
      "rfc": "ZUÑ920208KL4",
      "tipo": "MORAL",
      "curp": "",
      "regimenNomina": "true",
      "sucursal": {
        "nombre": "Mi primera sucursal API",
        "password": "12345678a",
        "direccion": {
          "codigopostal": "58000",
          "calle": "Rio Yaqui",
          "numeroexterior": "286",
          "colonia": "Ventura Puente",
          "ciudad": "Morelia",
          "municipio": "Morelia",
          "pais": "México",
          "estado": "Michoacán"
        }
      }
    }
  }
}
'
200 OK
Example Response
Body
Headers (6)
json
{
  "succeed": true,
  "code": 3000,
  "message": "Item actualizado correctamente."
}
DELETE
Eliminar
https://app.facture.com.mx/api/emisor?serverId=30735
Punto de enlace para eliminar un emisor, proveyendo el id.

HEADERS
Authorization
Bearer

Accept
application/json

Content-Type
application/json

PARAMS
serverId
30735

Example Request
Example response
View More
curl
curl --location --request DELETE 'https://app.facture.com.mx/api/emisor?serverId=30735' \
--header 'Authorization: Bearer ' \
--header 'Accept: application/json' \
--header 'Content-Type: application/json'
200 OK
Example Response
Body
Headers (6)
json
{
  "succeed": true,
  "code": 2002,
  "message": "Item eliminado correctamente."
}
Descarga masiva
Descripción

Punto de enlace para descarga masiva de comprobantes desde el SAT.
Importante:

Para realizar pruebas deberá usar un emisor real ya que los CSD del

 Ambiente de pruebas

no tendrán registros dentro de los controles del SAT.

Para realizar pruebas el usuario que consume este punto de enlace deberá tener una suscrpción activa, vigente y a partir del plan 2 a Descarga Masiva o cualquier suscripción activa y vigente para Contabilidad electrónica.

Scope
El punto de enlace requiere que el usuario haya brindado permisos al scope descarga_masiva en la aplicación cliente.

Flujo
El proceso de descarga masiva requiere un flujo especial de peticiones:

Crear petición.- Se crea una petición de CFDI emitidos o recibidos. El SAT regresa un estatus de Aceptado o Rechazado.
Verificar petición.- Una vez creada y aceptada una petición se debe revisar el estatus de la misma: Lista para descarga o Pendiente de descarga. Cabe señalar que una petición Aceptada no precisamente esta lista para ser descargada.
Descargar petición.- Una vez que la petición fue aceptada para descarga se puede proceder a descargar los XML del SAT.
Procesar petición.- Una vez descargado el/los paquetes de la petición se procesan y se obtendrá como resultado el listado de comprobantes que se cumplieron el criterio inincial de crear una petición.
GET
Find
https://app.facture.com.mx/api/descargaMasiva/find?offset=0&size=10
HEADERS
Accept-Language
application/json

Authorization
Bearer

PARAMS
offset
0

size
10

Example Request
Find
View More
curl
curl --location 'https://app.facture.com.mx/api/descargaMasiva/find?offset=0&size=10' \
--header 'Accept-Language: application/json' \
--header 'Authorization: Bearer '
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
POST
Crear
https://app.facture.com.mx/api/descargaMasiva/create
Descripción
Método que permite crear una petición de descarga

Atributos requeridos
fechaInicio.- Fecha de inicio de la petición
fechaFin.- Fecha de fin de la petición.
tipoSolicitud.- Tipo de solicitud a crear. Valores permitidos: CFDI_RECIBIDOS y CFDI_EMITIDOS
empresa.- Objeto empresa con el ID del emisor que creará la petición. Use le método find de Emisor.
HEADERS
Accept-Language
application/json

Content-Type
application/json

Authorization
Bearer

Body
raw
View More
{
  "entity": {
    "data":{
            "fechaInicio" : "2021-10-01T00:00:00",
            "fechaFin" : "2021-10-15T23:59:59",
            "tipoSolicitud" : "CFDI_RECIBIDOS",
            "empresa" : {
                "id" : 1 
            }
    }
  }
}
Example Request
Crear
View More
curl
curl --location 'https://app.facture.com.mx/api/descargaMasiva/create' \
--header 'Accept-Language: application/json' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer ' \
--data '{
  "entity": {
    "data":{
            "fechaInicio" : "2021-10-01T00:00:00",
            "fechaFin" : "2021-10-15T23:59:59",
            "tipoSolicitud" : "CFDI_RECIBIDOS",
            "empresa" : {
                "id" : 1 
            }
    }
  }
}
'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
POST
Verificar
https://app.facture.com.mx/api/descargaMasiva/verify
Descripción
Método que permite verificar el status de petición de descarga

Atributos requeridos
entity.- Objeto entity con un objeto anidado data y cuyo atributo id deberá ir el de la solicitud que se desea verificar.
El id del objeto data podrá ser obtenido en la respuesta del método crear petición o bien desde find de Descarga masiva.

HEADERS
Accept-Language
application/json

Content-Type
application/json

Authorization
Bearer

Body
raw
{
  "entity": {
    "data":{
        "id" : 1
    }
  }
}
Example Request
Verificar
View More
curl
curl --location 'https://app.facture.com.mx/api/descargaMasiva/verify' \
--header 'Accept-Language: application/json' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer ' \
--data '{
  "entity": {
    "data":{
        "id" : 1
    }
  }
}
'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
POST
Descargar
https://app.facture.com.mx/api/descargaMasiva/download
Descripción
Método que permite realizar la descarga de una petición

Atributos requeridos
entity.- Objeto entity con un objeto anidado data y cuyo atributo id deberá ir el de la solicitud que se desea verificar.
El id del objeto data podrá ser obtenido en la respuesta del método crear petición o bien desde find de Descarga masiva.

HEADERS
Accept-Language
application/json

Content-Type
application/json

Authorization
Bearer

Body
raw (json)
json
{
  "entity": {
    "data":{
        "id" : 1
    }
  }
}
Example Request
Descargar
View More
curl
curl --location 'https://app.facture.com.mx/api/descargaMasiva/download' \
--header 'Accept-Language: application/json' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer ' \
--data '{
  "entity": {
    "data":{
        "id" : 1
    }
  }
}
'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
POST
Procesar
https://app.facture.com.mx/api/descargaMasiva/process
Descripción
Método que permite procesar una petición previamente descargada

Atributos requeridos
entity.- Objeto entity con un objeto anidado data y cuyo atributo id deberá ir el de la solicitud que se desea verificar.
El id del objeto data podrá ser obtenido en la respuesta del método crear petición o bien desde find de Descarga masiva.

HEADERS
Accept-Language
application/json

Content-Type
application/json

Authorization
Bearer

Body
raw
{
  "entity": {
    "data":{
        "id" :  1
    }
  }
}
Example Request
Procesar
View More
curl
curl --location 'https://app.facture.com.mx/api/descargaMasiva/process' \
--header 'Accept-Language: application/json' \
--header 'Content-Type: application/json' \
--header 'Authorization: Bearer ' \
--data '{
  "entity": {
    "data":{
        "id" :  1
    }
  }
}
'
Example Response
Body
Headers (0)
No response body
This request doesn't return any response body
CFDI Recibidos
Descripción
Punto de enlace para realizar acciones con comprobates emitidos dentro de la plataforma.

Scope
Obtiene una lista de resultados con comprobantes emitidos en la plataforma.

GET
GET
https://app.facture.com.mx/api/facturacion/find
Método que permite obtener una lista de resultados con comprobantes emitidos desde la cuenta de un usuario registrado en Facture App.

AUTHORIZATION
OAuth 2.0
HEADERS
Authorization
Bearer

Header requerido para enviar el access token y autenticar la petición.

Accept
application/json

Header opcional con el formato de la respuesta esperada (application/xml, application/json).

PARAMS
offset
0

Parametro requerido para definir el inicio de la lista de resultados.

size
10

Parametro requerido para definir el tamaño de la lista de resultados (máximo 100).

orderby
orderby?fecha:lt

Parametro opcional para definir un ordenamiento de la lista de resultados. Vea Ordenamiento

filter
cancelada:eq!true

Parametro opcional para agregar un filtrado a la lista de resultados. Vea Filtrado

type
movil

Parametro opcional para definir el tipo de lista de resultados. Vea Tipos de resultados