1)	MÓDULO “Administración de Usuarios” Se desarrollará:
•	Este es el módulo inicial mediante el cual se permitirá agregar usuarios a la base de datos de manera manual, así como los permisos y roles de usuario (Ejemplo: administrador, ventas, contabilidad, etc.) por medio de un formulario o de manera masiva mediante un archivo de Excel. Se solicitará que se llenen los campos nombre, apellidos, fecha de nacimiento, CURP, teléfono, estado (activo, suspendido), observaciones, y datos de contacto como email, teléfono de casa y teléfono móvil. 
Nota1: Estos campos son personalizables y en caso de requerir datos adicionales, es necesario indicarlos en un listado de texto.
Nota2: Los roles o permisos de usuario tendrán acceso a secciones específicas del sistema ERP. Estos permisos únicamente los podrá definir un usuario con nivel de “administrador”
•	El proceso de almacenamiento asignará un identificador único del usuario, el cual será un proceso automático (no será necesario capturar el número de folio o ID) por lo que al momento de finalizar el registro el sistema nos devolverá el número asignado el cual estará ligado al nombre del usuario y a su correo electrónico.  En el caso de que el sistema detecte algún dato duplicado se notificará mediante una alerta al administrador y será necesario corroborarlo para finalizar el proceso de alta.
•	 Todos los registros y movimientos se almacenarán en la bitácora. En el caso de ingresar un dato erróneo o requerir la edición o eliminación de un usuario será necesaria la validación de un usuario con acceso de administrador.
•	Este módulo permitirá consultar de manera rápida y efectiva los usuarios previamente registrados con búsquedas por distintos parámetros como nombre, apellidos, domicilio, clave de usuario o campo de búsqueda libre, donde se podrá ingresar en este campo alguna palabra clave, el sistema realizará una búsqueda en la base de datos tratando de encontrar cualquier coincidencia, como podría ser un número telefónico, email, id de usuario o nombre.  Una vez encontrado el resultado particular, se mostrará el lis-tado de resultados con los detalles del usuario y las herramientas para editar sus datos, actualizar el tipo de usuario o eliminarlo del sistema.
•	Este módulo implementará un método de “Autentificación en 2 pasos”, al iniciar sesión desde un nuevo dispositivo, enviando un email al usuario con un código de acceso adicional a su contraseña el cual deberá ingresar en el formulario de inicio de sesión. La autenticación en 2, pasos previene el acceso no autoriza-do y aumenta la seguridad del sistema ERP.

2)	MÓDULO “Recursos Humanos” Se desarrollará:
•	Este módulo permitirá gestionar el área de recursos humanos de la empresa, por lo que tendrá una conexión directa con el módulo de “Administración de Usuarios”. Permitirá realizar búsquedas mediante filtros como nombre, correo, Id de usuario, puesto de trabajo, etc.
Nota1: Este módulo se alimentará de la información de los usuarios registrados, los cuales se tomarán en cuenta como trabajadores dentro de este módulo.
•	Una vez encontrado el resultado de la búsqueda, se mostrará el listado de trabajadores con sus datos de contacto, y la opción para editar los datos del trabajador como número de seguridad social, RFC, horarios, turnos, etc. Además, permitirá agregar, editar y consultar contratos o documentación relacionada con su registro de la empresa. 
•	Este módulo permitirá el alta y administración de plantillas o machotes de contratos para asignarlos a un usuario o trabajador determinado. El sistema conservará la versión de cada contrato, indicando quién lo creó y quién lo autorizó. Además, se mantendrá un histórico de todas las versiones de los contratos para cada trabajador, organizados en una estructura de árbol por fecha de creación o edición para facilitar su seguimiento.
Nota1: El contenido de los contratos, plantillas y machotes será proporcionado por el cliente y administrado por él.
•	El sistema almacenará los datos de los trabajadores junto con la documentación relacionada con contratos, renuncias, cartas de baja, finiquitos y liquidaciones los cuales se podrán agregar, consultar y editar en este módulo, facilitando su consulta y gestión.
Nota1: El sistema no realizará cálculos de finiquitos ni liquidaciones.
•	Este módulo contará con un formulario para cargar los reportes generados por el reloj checador biométrico, el cual permitirá registrar la entrada y salida de los trabajadores, así como horarios de comida.
Nota1: Para realizar el registro de asistencia de los usuarios, será necesario que el reloj checador biométrico se encuentre activo en las horas laborales.
•	Permitirá registrar y procesar incidencias de los trabajadores como faltas, horas extras, vacaciones, incapacidades, entre otros, que se reflejarán en el cálculo de la nómina y se almacenarán en el registro del usuario, por lo que tendrá una conexión directa con el módulo de “Contabilidad”.

3)	“Reloj Checador Remoto (Obras)” API de conexión con sistema Bixpe.
•	La API KONECT® es un software patentado por Especialistas Web®, que crea el puente entre la aplicación Bixpe y el sistema ERP. Bixpe es un software de control de horarios y gestión de tiempo, disponible en la nube y aplicaciones móviles, diseñado para empresas que buscan la gestión de horarios de sus trabajadores de manera remota.
•	¿Qué hace Bixpe? Registra las horas trabajadas mediante una app certificada por Google Store y Apple Store, esto mediante un sistema de geolocalización que permite registrar las horas trabajadas por sus empleados en cada proyecto, estén donde estén, ingresando a la aplicación Bixpe y registrando su hora de entrada y salida en cada ocasión en tiempo real conectada por medio de la API KONECT® al sistema ERP de Chisa Recubrimientos.
•	¿Qué hace API KONECT®? Es un puente entre la aplicación principal para este módulo (Bixpe) y el ERP de Chisa, con ella se podrá monitorear y operar en tiempo real los accesos de los trabajadores en las obras desde el ERP de Chisa. ¿Cómo funciona? Basta con que el usuario descargue la aplicación en su dispositivo móvil y lo primero que haremos es crear el usuario y contraseña que usará para firmar su entrada y salida mediante el lector biométrico de su dispositivo móvil o un PIN en su jornada laboral. 
•	Todos los movimientos, registros de acceso, geolocalización GPS, usuarios y reportes se enlazarán de manera automática por medio de la API KONECT® al sistema ERP Chisa Recubrimientos, para que en el repose toda la información y el administrador del ERP y propietarios la consulten cómodamente desde su usuario del sistema(ERP).
•	La API KONECT® se encargará de mostrar dentro del sistema ERP Chisa, la información con filtros de: 
o	Obra(proyecto).
o	Id de Usuario (nombre del trabajador, email, teléfono, No. Seguridad Social, fotografía del trabajador, etc.).
o	Rango de Fechas (duración de proyecto).
o	Número de trabajadores.
o	Geolocalización GPS por trabajador.
o	Asistencias e inasistencias.
o	Horas trabajadas.
o	Horas de descanso(comida).
o	Incidencias.
o	Vacaciones(opcional).
o	Reporte por trabajador y por obra.
Información Importante: 
•	La API KONECT® es un producto registrado, y requiere la renovación anual de la licencia de uso, así como ejecutarse dentro de nuestros servidores para un mejor seguimiento de Especialistas Web, desde luego la información reposará en el servidor de Chisa para su tranquilidad. No se proporcionarán en ningún caso accesos para el montaje y ejecución La API KONECT® en otros servidores y el mantenimiento de la herramienta solo podrá ser realizado por personal de especialistas web (podrá contratarla de forma mensual o anual). En caso de que el cliente cambie de proveedor de servidor VPS, no se podrá llevar la aplicación (API KONECT®).
•	Bixpe es una empresa internacional con sede en España, que cuenta con todas las regulaciones para brindar el servicio en diversos usos horarios y latitudes. En México empresas como CANON, La Salle, Orange, ALSA y Ferrovial trabajan utilizando sus servicios. 
•	Bixpe maneja paquetes desde 5 usuarios hasta los que sean necesarios, y su cobro puede ser mensual o anual y el pago es en Euros. En este caso el cliente “Chisa” podrá contratar directamente en cada ocasión el número de empleados que requiera monitorear, o bien solicitar a Especialistas Web® que realice la contratación y liquidarle el monto resultante.
•	Especialistas Web® brindará soporte al contratar API KONECT®, ya sea por anualidad o mensualidad, que consta de:
•	Alta y edición de usuarios.
•	Soporte y mantenimiento correctivo y preventivo a lo ya desarrollado y autorizado por el cliente.
•	Gestión de actualización entre Bixpe, la API KONECT® y el sistema ERP Chisa.
•	Alertas y notificaciones de actualizaciones disponibles en aplicación Android e IOS.
•	Soporte vía WhatsApp, telefónica y remota a los empleados sobre la aplicación de Bixpe en horarios de oficina de lunes a viernes de 10:00 am a 06:00 pm (no incluye visitas a las obras).
•	Cuando no haya señal de internet o no se pueda obtener la geolocalización GPS, el sistema almacenará localmente la firma de checado en el dispositivo móvil. Una vez que el dispositivo móvil se conecte a internet, el sistema actualizará la información de checado en el servidor de BIXPE y en el sistema ERP.

Nota Importante: Los trabajadores requieren tener un Smartphone o Tablet con sistema operativo Android (versión 14 en adelante) y la aplicación Bixpe Control Horario instalada con conexión vía Wifi o datos móviles. Especialistas Web® no será responsable en los casos de fallas por dispositivos móviles obsoletos, sin conexión de datos o mal uso del sistema de geolocalización GPS como el caso de móviles robados, perdidos o prestados a compañeros para realizar la firma.


4)	MÓDULO “Proveedores/Compras” Se desarrollará:
•	El módulo permitirá el alta y edición de proveedores. Una vez registrado los datos, se podrán consultar los datos de contacto, detalles y seguimientos mediante un buscador por filtros.
•	Este módulo llevará el control de las compras a los proveedores, por lo que, al realizar una compra o pedido, el usuario podrá realizar la captura de las órdenes y productos adquiridos, generando un identificador único de pedido, lo cual permitirá llevar un mejor control y seguimiento.
•	El sistema ERP podrá generar una pre-solicitud de cotización interna de los proveedores, con el fin de realizar y aprobar las compras necesarias. Cada pre-solicitud contará con un número de guía único para su seguimiento el cual podrá ser consultado o aceptado para generar una orden de compra correspondiente. Además, el sistema incluirá el cálculo del tipo de cambio actual para facilitar la evaluación de las cotizaciones recibidas de los proveedores.
•	Se podrán consultar las órdenes de compra mediante un buscador por filtros con valores como Id de compra, nombre del proveedor, fecha, etc. Una vez que se tengan los resultados, se mostrará el listado de ordenes con los detalles importantes como fecha de solicitud, nombre de proveedor, monto de la compra y el acceso directo a los detalles de la orden. 
•	Se tendrá un acceso directo al módulo de “Facturas” por lo que cada compra generada a los proveedores se podrá asociar a una factura o recibo de compra, los cuales se podrán descargar o enviar vía email.
•	Este módulo permitirá generar búsquedas de productos por proveedores, así como la consulta de compras por filtros de búsqueda con campos como tipo de producto, fecha de compra o rango de precios.



5)	MÓDULO “CRM de CLIENTES” Se desarrollará:
•	Este es el módulo central que funcionará a manera de cerebro, el cual será el encargado de unificar y enlazar la comunicación de los demás módulos. Concentrará las búsquedas internas y conexiones entre el módulo de ventas, registro de cálculo de materiales y producción. Este módulo se encargará del control de clientes y prospectos potenciales. Permitirá el registro de contactos mediante un formulario con los campos: nombre, apellidos, email, teléfono, dirección, estatus, tipo de contacto (cliente o prospecto) y comentarios. 
Nota1: Estos campos son personalizables y en caso de requerir datos adicionales, es necesario indicarlos en un listado de texto.
•	Una vez realizada el alta de un cliente o prospecto, el sistema le asignará un ID único de manera automática, el cual servirá para la consulta y seguimientos posteriores dentro de todo el sistema.
•	Se podrá consultar el listado de clientes o prospectos mediante un buscador avanzado con filtros con campos como Id de cliente, nombre, teléfono, email, etc. 
•	Una vez realizada la consulta, la tabla de resultados mostrará los detalles de contacto, así como el acceso a la edición de datos y actualización de estatus de cliente o prospecto. Se podrán agregar datos de contacto adicionales, y se tendrá una agenda de seguimiento avanzada en la cual se podrán registrar y consultar las interacciones y oportunidades de venta, registros de llamadas telefónicas y correos de seguimiento lo que permite un control preciso y personalizado.
•	Tendrá una conexión directa con el módulo “Ventas”, por lo que se podrá realizar una consulta rápida de las ventas realizadas o acceder de manera directa a la creación de nuevas órdenes de venta o cotizaciones.
•	Mediante este módulo los administradores podrán consultar el estatus de cada venta relacionadas a los clientes, desde el momento en que se genera la orden por un vendedor o en el punto de venta físico, hasta la entrega del producto o servicio. Además, contará con una conexión directa al módulo de facturación, el cual a través de la API KONECT®, se conectará con la plataforma “micontador.mx”, permitiendo generar la factura correspondiente a la orden venta de manera automática, la cual se podrá, descargar (en formato PDF y XML), imprimir o enviar al email del cliente.
•	El modulo permitirá agregar precios especiales, descuentos o cupones los cuales se podrán asociar por Id de cliente. Estos precios y descuentos se reflejarán de manera automática al crear una nueva “Venta” o “Cotización”.
•	Mediante este módulo se podrán consultar las órdenes de venta activas, así como el estatus de cada orden y el departamento en el cual se encuentra el proceso como “Producción”, “Registro de Cálculo de Materiales”, etc.

6)	MÓDULO “Ventas / Ordenes de Ventas(pedidos)” Se desarrollará:
•	Este módulo se encargará del control de las ventas o cotizaciones directas a los clientes por medio de órdenes, las cuales contarán con información actualizada del cliente y sus productos previamente adquiridos.  Estas podrán consultarse mediante un formulario de búsqueda por filtros como Id de cliente, fecha, etc, así como la posibilidad de reenviarse vía email.
•	Las órdenes de venta o cotizaciones se realizarán de manera manual mediante un formulario, permitiendo seleccionar un cliente o prospecto, así como los productos cotizados y precios especiales previamente cargados en el módulo de clientes además de conceptos adicionales de venta como concepto de aplicaciones e instalaciones. Una vez finalizada la orden de venta, se generará de manera automática un Id único de seguimiento.
•	Se integrará una interfaz en el módulo de ventas para medir y gestionar las ventas realizadas desde el punto de venta física. Los vendedores podrán realizar ventas a través de esta interfaz, ingresando los productos vendidos, cantidades y otros detalles relevantes. El punto de venta permitirá generar un recibo de la venta en formato PDF o para impresión
Nota1: La cotización no incluye equipo de cómputo, caja registradora ni la impresora para el punto de venta. Se puede utilizar cualquier impresora láser o de inyección de tinta para la impresión de los recibos.

•	Una vez generada una orden de Venta, se enviará una solicitud y notificación a los módulos de “Registro de Cálculo de Materiales”, “Producción” y “CRM de clientes”, la cual llevará un estatus para identificar en que área o proceso se encuentra.
•	Cada orden de venta contará con un enlace o acceso a una pasarela de pagos, para que los clientes puedan realizar los pagos correspondientes de las ventas, mediante tarjetas de crédito, débito o mediante transferencia bancaria. 
•	Las ventas o cotizaciones tendrán un estatus (pendiente, activa, finalizada) la cual podrá ser actualizada por los vendedores en tiempo real una vez que el pago sea recibido y comprobado. Es módulo tendrá una conexión directa al módulo de facturación, por lo que podrá adjuntarse la factura correspondiente mediante un formulario de carga manual o bien mediante una API de conexión a su sistema actual de facturación. Este registro se almacenará en la bitácora de movimientos.
Nota1: Una API es un programa que permite realizar conexiones entre sistemas de terceros, para realizar una comunicación directa, envío de información o descarga de archivos.
Nota2: Para que el módulo de ventas pueda consultar o descargar las facturas de manera automática, es necesario que su actual proveedor de facturación permita esta funcionalidad.

7)	MÓDULO “Registro de Cálculo de Materiales” Se desarrollará:
•	Una vez que se crea una orden de venta, este módulo permitirá al Arquitecto o Ingeniero encargado de los seguimientos de las obras, llevar un control y registro de la “Aplicación” o del “Proyecto Asociado”, por lo que podrá cargar o enlazar documentación para llevar registros relacionados como los archivos de los “Renders”, comentarios del proyecto, cálculo de materiales, materia prima requerida o cantidad de productos o cubetas a entregar.
Nota1: El sistema permitirá la carga de archivos en los siguientes formatos: BMP, TGA, TIF, PCX, JPG, PNG y DWG
•	Cada seguimiento de las órdenes de venta se asociará a un Id único, el cual permitirá llevar un registro de los proyectos y aplicaciones realizadas, las cuales se podrán consultar mediante un buscador por filtros con valores como id de orden, fecha de creación, estatus o usuario asignado, nombre de aplicación proyecto, etc.
•	Una vez que se muestre la tabla de resultados, se podrá consultar información relevante de la orden, como registros de quien calculo, cuanto calculo, comentarios, etc.
•	Este módulo también contará con una conexión directa al módulo de “Venta” y “CRM” por lo que la actualización del estatus se reflejará en tiempo real en todas las áreas.
•	Este módulo permitirá agregar información adicional relacionada con las obras o “Aplicaciones” como registro de viáticos, ubicación de las aplicaciones, personal o trabajadores asignados, etc. Por lo que tendrá una conexión directa al módulo de “Recursos Humanos”, en el cual se registrarán estos eventos.
•	El sistema proporcionará acceso directo al catálogo de colores preexistentes que han sido vendidos previamente a clientes. Cada cliente tendrá un histórico de los colores o materiales que ha contratado, y se podrá acceder a reportes relacionados con esta información.
•	El sistema realizará el cálculo de materiales basados en los colores disponibles en la página web.
 Nota1: El cálculo de los materiales restantes del catálogo, deberán ser cargados por el cliente.

8)	MÓDULO “Producción” Se desarrollará:
•	El módulo de producción permitirá gestionar y controlar el proceso de fabricación de recubrimientos, pinturas, selladores, pastas y preparadores de superficie, desde la planificación hasta la finalización de la producción. El modulo contará con formularios de carga para ingresar los datos de la materia prima, así como las cantidades a utilizar, y la cantidad de cubetas o lotes a producir según la necesidad de una venta o pedido previamente especificado en el módulo de “Registro de Cálculo de Materiales”. 
•	Permitirá asignar y gestionar lotes de producción, incluyendo la cantidad de cubetas o lotes a fabricar y los materiales necesarios, como pigmentos, resinas y otros componentes. Cada lote o cubeta contará con un ID único para su identificación y trazabilidad.
•	Permitirá realizar controles de calidad durante el proceso de producción y registrar la producción realizada, incluyendo la cantidad de cubetas fabricadas y los tiempos de producción, estatus de pedidos o lotes, así como un proceso de control de calidad relacionado con la viscosidad del producto. El resultado de este control será una bandera de estatus "aprobado" o "no aprobado", el cual podrá ser consultado por los vendedores y administradores del sistema.
•	Se implementará una “pantalla touch” en el área de producción para mostrar y actualizar el estatus de los pedidos en tiempo real. Esta pantalla también permitirá la comunicación con el sistema para indicar cuando se concluya la producción de un pedido, solicitar productos o materiales al módulo de compras y para visualizar el destino del producto en el sistema, ya sea en punto de venta o entregas directas a clientes.
Nota1: La “Pantalla Touchscreen” se cotizará por separado
•	Cuando un producto esté terminado en el área de producción y el estatus sea actualizado de manera manual, el sistema enviará una notificación o alerta a todas las áreas relevantes para informar sobre los productos terminados.
•	Cada cubeta o lote podrá ser marcado o etiquetado con un código de barras o QR, lo que permitirá su identificación y seguimiento en todo momento. Esto se integrará con el módulo de inventarios, permitiendo una gestión precisa y eficiente del stock y la trazabilidad de los productos.
Nota Importante: Será necesario incluir en este módulo y proceso la Etiquetadora térmica de códigos de barras /códigos QR y la Lectora láser de códigos de barras /códigos QR.

Este módulo estará conectado a los módulos de “Ventas”, “Registro de Cálculo de Materiales”, “Compras”, “Contabilidad”, “Almacén”, “Facturación” y “Reportes.

9)	MÓDULO “Almacén/Inventarios” Se desarrollará:
•	Este módulo mantendrá el control del stock y reportes de productos en el área del almacén y de la tienda física. De igual manera mantendrá un control constante de mínimos y máximos de productos los cuales se mostrarán en forma de alertas constantes a los usuarios. El stock se actualizará en tiempo real por lo que tiene comunicación directa con el módulo de ventas.
•	Mediante este módulo se pueden realizar reportes de productos, cargas y actualización de listas de precios y un desglose en general de los datos relacionados a los productos.
•	Este módulo mantendrá el control de productos mediante un id único, el cual contendrá los detalles y características del producto (tipo de producto, características, cubetas, etc.), el cual podrá ser consultado a partir de un código de barras o código QR.
Nota1: Para poder llevar el registro y control del stock, será necesario que los usuarios etiqueten de manera manual los productos de venta final con el código de barras o QR.
•	Mediante este módulo se realizará el check-out o salida de productos, lotes o cubetas vendidas, mediante el lector láser o lector de códigos QR el cual actualizará el stock en tiempo real. De igual manera se pueden generar reportes, consultar información de clientes, consulta del stock y mantener alertas sobre órdenes de venta pendientes.
•	Mediante este módulo, los vendedores y todos los departamentos relacionados, podrán verificar que los productos de una venta, sean entregados al cliente mediante un estatus de envío en tiempo real. Para lograr esto, se realizará la conexión con el servicio de paquetería y envíos “Tres Guerras” a través de la API KONECT®.
Nota1: Para poder llevar a cabo la conexión de la API KONECT® con el sistema de rastreo de “Tres Guerras”, será necesario que se tenga un convenio o plan tarifario activo.

10)	 MÓDULO “Facturación” Se desarrollará:
•	Este módulo tendrá una conexión directa al módulo de “Proveedores / Compras”, por lo que los usuarios podrán capturar de manera manual las facturas o recibos que sean proporcionados por los proveedores. 
•	Une vez capturados y asociados estos datos, se podrá realizar la consulta mediante un filtro de búsqueda con campos como Id de orden, nombre de proveedor, número de factura, número de recibo o rango de fecha. El resultado de la búsqueda mostrará los detalles de la orden, las facturas asociadas y permitirá editar, agregar o eliminar información adicional, así como el reenvío de la información vía Email.
•	Este módulo tendrá una conexión directa al módulo de “Ventas / Ordenes de Ventas”, por lo que las facturas o recibos que hayan sido generados en la venta correspondiente, podrán ser consultados mediante 
un filtro de búsqueda con campos como Id de orden, id de cliente, número de factura, número de recibo o rango de fecha. El resultado de la búsqueda mostrará los detalles de la orden de venta y las facturas asociadas, además, permitirá editar, agregar o eliminar información adicional, así como el reenvío de la información vía Email.
•	Esté módulo tendrá una conexión directa a la plataforma “MICONTADOR.MX” a través de la API KONECT®, por lo que los vendedores podrán generar, pre facturas y facturas de manera automática una vez generada una orden de venta o cotización y podrán ser descargadas para su impresión o reenvío al cliente mediante un email.
Nota1: Para poder llevar a cabo la conexión de la API KONECT® con el sistema de facturación MICONTADOR.MX, será necesario que el distribuidor autorice la conexión, de lo contrario esto no será posible y será necesario cambiar de proveedor del sistema de facturación.
•	Este módulo permitirá generar una liga por cada orden de venta, la cual le permitirá a los clientes generar su factura de manera automática en caso de requerirlo. Esta factura se almacenará en el sistema ERP y podrá ser consultada en los detalles de la orden de venta.  
•	Se tendrá una conexión directa con el módulo de reportes, por lo que se podrán consultar reportes de facturas en rangos de fechas diarias, semanales, mensuales o anuales. Estos reportes se podrán imprimir o enviar vía email.
•	De manera constante este módulo generará alertas a los usuarios en caso de encontrar facturas faltantes en órdenes de compra u órdenes de venta.

11)	 MÓDULO “Contabilidad” Se desarrollará:
•	El módulo de contabilidad permitirá registrar y gestionar todas las transacciones financieras de la empresa, incluyendo registros de ingresos y egresos, como pagos a proveedores, pagos de clientes, pagos de salarios y pagos de servicios recurrentes (internet, luz, agua, etc.).
•	Se contará con un calendario de pagos recurrentes, el cual permitirá la consulta de información por fechas e incluirá datos del servicio, fechas de pago, y montos a pagar. Se implementará un sistema de alertas o notificaciones, el cual enviará recordatorios de forma automática vía email a los usuarios previamente designados para recibir estas alertas en el módulo de “Administración”.
•	Se tendrá una conexión directa con el módulo de “Recursos Humanos”, por lo que permitirá el control o manejo de nóminas, automatizar el cálculo de salarios brutos, deducciones (seguridad social, impuestos, etc.), y salarios netos, incluyendo bonificaciones y otros conceptos. En caso de contar con alguna aplicación o sistema SAP, se desarrollará un API de conexión para alimentar esta sección de manera automática. 
•	Este módulo permitirá exportar datos relevantes en formato Excel los cuales servirán para almacenar o alimentar los sistemas de contabilidad (Aspel COI) y nómina (Aspel NOI) utilizados por la empresa. De esta manera, se facilita la integración de la información operativa del ERP con los procesos contables y de nómina gestionados en Aspel COI y NOI.

12)	 MÓDULO “Reportes” Se desarrollará:
El módulo de reportes tendrá una conexión con todos los módulos del sistema, ya que cada acción o movimiento de los usuarios se almacenará en una bitácora de seguimiento, lo que permitirá la creación de reportes detallados y personalizados sobre los datos y procesos de la empresa, basados en filtros de búsqueda avanzados, así como la posibilidad de descargarlos en formato Excel o PDF.  A continuación, se enlistan los reportes que se podrán generar en este módulo:
•	Reportes de Recursos Humanos 
o	Reportes de usuarios y trabajadores activos: Incluirá información de contacto y detalles del trabajador.
o	Reportes de faltas y asistencia.
o	Reportes de horas extras, permisos y viáticos.
o	Reportes de incapacidad y vacaciones.
o	Filtros: Los reportes pueden ser filtrados por fechas, ID de trabajador, nombre de trabajador y horarios.

•	Reportes de Proveedores/Compras
o	Reportes de proveedores: Se podrán generar reportes de proveedores, incluyendo información de contacto y detalles de compras.
o	Reportes de productos comprados: Se podrán generar reportes de productos comprados a proveedores, incluyendo cantidades y fechas.
o	Reportes de pagos realizados.
o	Reportes de productos más y menos comprados: Generar reportes de productos con más y menos demanda por proveedor
o	Reportes de órdenes de compra:  Incluirán compras realizadas a proveedores, incluyendo fechas y productos comprados.
o	Filtros: Los reportes pueden ser filtrados por fecha de registro, nombre, ID, etc.
•	Reportes del CRM de clientes
o	Reportes de clientes: Incluirá información de contacto y detalles de compras.
o	Reportes de clientes nuevos: Fechas de registro y detalles de contacto.
o	Reportes de ventas por clientes: Incluirá información de la orden, montos y fechas.
o	Reportes de cotizaciones y ventas: Incluirá detalles de productos y montos.
o	Filtros: Los reportes pueden ser filtrados por fecha de registro, tipo de cliente, ID, nombre, etc.
•	Reportes de Ventas
o	Reportes de ventas mensuales o por rangos de fecha: Montos y detalles de productos.
o	Reportes de ventas con detalles: Se incluirán detalles específicos de las órdenes de venta.
•	Reportes de Almacén
o	Reportes de inventario de materiales disponibles o materia prima en existencia.
•	Reportes de Facturación
o	Reportes de facturas de compras: Generará reportes de facturas de órdenes de compras a proveedores, incluyendo fechas y montos.
o	Reportes de facturas de ventas: Generará reportes de facturas de órdenes de ventas, incluyendo fechas y montos.
Nota1: Estos son los reportes que se incluirán de manera inicial en el sistema ERP, sin embargo, en caso de requerir algún reporte adicional, puede solicitarlo para incluirlo en el listado correspondiente.
