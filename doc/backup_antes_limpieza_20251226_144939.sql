/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.24-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: st32477_chisa
-- ------------------------------------------------------
-- Server version	10.6.24-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administradores`
--

DROP TABLE IF EXISTS `administradores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `administradores` (
  `id` tinyint(4) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(200) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(255) NOT NULL,
  `privilegios` text NOT NULL,
  `departamento` varchar(40) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL,
  `estatus` tinyint(4) NOT NULL,
  `meta_nombre` varchar(50) NOT NULL,
  `fecha_alta` date NOT NULL,
  `fecha_baja` date DEFAULT NULL,
  `fecha_edicion` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administradores`
--

LOCK TABLES `administradores` WRITE;
/*!40000 ALTER TABLE `administradores` DISABLE KEYS */;
INSERT INTO `administradores` VALUES (1,'EHWEB','EHWEB','soporte2@especialistasweb.com.mx','$2y$10$E0cJjeFIhHc3WG2F/doUGOzBy.FyO4QBjSn.FmgkgRWhsJPsmNPgK','','IT','',1,'','2025-12-24',NULL,'2025-12-26');
/*!40000 ALTER TABLE `administradores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alertas_stock`
--

DROP TABLE IF EXISTS `alertas_stock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `alertas_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_alerta` enum('Producto','Insumo','Formulacion') NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `insumo_id` int(11) DEFAULT NULL,
  `formulacion_id` int(11) DEFAULT NULL COMMENT 'Si la alerta es por formulación',
  `nivel_alerta` enum('Critico','Bajo','Proximo') NOT NULL,
  `mensaje` text NOT NULL,
  `stock_actual` decimal(10,2) DEFAULT NULL,
  `stock_minimo` decimal(10,2) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `resuelta` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_lectura` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tipo` (`tipo_alerta`),
  KEY `idx_nivel` (`nivel_alerta`),
  KEY `idx_leida` (`leida`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_insumo` (`insumo_id`),
  KEY `fk_alert_formulacion` (`formulacion_id`),
  KEY `idx_alertas_no_resueltas` (`resuelta`,`nivel_alerta`),
  CONSTRAINT `fk_alert_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alert_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_alert_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alertas de stock bajo';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas_stock`
--

LOCK TABLES `alertas_stock` WRITE;
/*!40000 ALTER TABLE `alertas_stock` DISABLE KEYS */;
/*!40000 ALTER TABLE `alertas_stock` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bitacora`
--

DROP TABLE IF EXISTS `bitacora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mensaje` text NOT NULL,
  `usuario` varchar(250) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fecha_hora` (`fecha`,`hora`),
  KEY `idx_mensaje` (`mensaje`(768))
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bitacora`
--

LOCK TABLES `bitacora` WRITE;
/*!40000 ALTER TABLE `bitacora` DISABLE KEYS */;
INSERT INTO `bitacora` VALUES (1,'Se ha agregado al Administrador con el ID: 1','soporte2@chisarecubrimientos.com','Registro agregado','187.190.154.188','2025-12-24','14:19:16'),(2,'Ingreso al sistema','soporte2@especialistasweb.com.mx','Ingreso correcto','187.190.154.188','2025-12-26','07:05:50'),(3,'Intento de acceso erróneo','soporte2@especialistasweb.com.mx','Ingreso erroneo','187.190.154.188','2025-12-26','07:14:27'),(4,'Ingreso al sistema','soporte2@especialistasweb.com.mx','Ingreso correcto','187.190.154.188','2025-12-26','07:14:38'),(5,'Se ha actualizado al Administrador con el ID: 1','soporte2@chisarecubrimientos.com','Registro actualizado','187.190.154.188','2025-12-26','07:45:10'),(6,'Se ha actualizado al Administrador con el ID: 1','soporte2@chisarecubrimientos.com','Registro actualizado','187.190.154.188','2025-12-26','08:10:17'),(7,'Intento de acceso erróneo','soporte2@especialistasweb.com.mx','Ingreso erroneo','189.233.147.163','2025-12-26','08:51:49'),(8,'Ingreso al sistema','soporte2@especialistasweb.com.mx','Ingreso correcto','189.233.147.163','2025-12-26','08:52:06'),(9,'Ingreso al sistema','soporte2@especialistasweb.com.mx','Ingreso correcto','189.233.147.163','2025-12-26','09:20:32'),(10,'Intento de acceso erróneo','soporte2@especialistasweb.com.mx','Ingreso erroneo','187.251.241.163','2025-12-26','13:36:52'),(11,'Ingreso al sistema','soporte2@especialistasweb.com.mx','Ingreso correcto','187.251.241.163','2025-12-26','13:36:58');
/*!40000 ALTER TABLE `bitacora` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_insumos`
--

DROP TABLE IF EXISTS `categorias_insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_insumos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `categoria_padre_id` int(11) DEFAULT NULL COMMENT 'Para categorías jerárquicas',
  `tipo` enum('Materia Prima','Material','Servicio') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fa-box' COMMENT 'Clase FontAwesome',
  `orden` int(3) DEFAULT 0,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  KEY `idx_padre` (`categoria_padre_id`),
  KEY `idx_tipo` (`tipo`),
  CONSTRAINT `fk_categoria_insumo_padre` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias_insumos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de insumos jerárquicas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_insumos`
--

LOCK TABLES `categorias_insumos` WRITE;
/*!40000 ALTER TABLE `categorias_insumos` DISABLE KEYS */;
INSERT INTO `categorias_insumos` VALUES (1,'Materias Primas Químicas',NULL,'Materia Prima','Químicos y componentes para fabricación','fa-flask',1,'Activo'),(2,'Materiales y Herramientas',NULL,'Material','Materiales de trabajo y herramientas','fa-tools',2,'Activo'),(3,'Servicios',NULL,'Servicio','Servicios contratados','fa-handshake',3,'Activo'),(4,'Resinas',1,'Materia Prima','Resinas acrílicas, epóxicas, etc.','fa-vial',1,'Activo'),(5,'Solventes',1,'Materia Prima','Solventes y diluyentes','fa-flask-vial',2,'Activo'),(6,'Pigmentos',1,'Materia Prima','Pigmentos y colorantes','fa-palette',3,'Activo'),(7,'Aditivos',1,'Materia Prima','Aditivos y modificadores','fa-prescription-bottle',4,'Activo'),(8,'Cargas',1,'Materia Prima','Cargas minerales','fa-cubes',5,'Activo'),(9,'Envases',2,'Material','Cubetas, tambos, envases','fa-bucket',1,'Activo'),(10,'Herramientas',2,'Material','Espátulas, brochas, rodillos','fa-screwdriver-wrench',2,'Activo'),(11,'Empaques',2,'Material','Etiquetas, cajas, embalaje','fa-box',3,'Activo'),(12,'Equipo de Seguridad',2,'Material','EPP y equipo de protección','fa-hard-hat',4,'Activo'),(13,'Telecomunicaciones',3,'Servicio','Internet, telefonía','fa-wifi',1,'Activo'),(14,'Mantenimiento',3,'Servicio','Mantenimiento de equipos','fa-wrench',2,'Activo'),(15,'Transporte',3,'Servicio','Servicios de logística','fa-truck',3,'Activo'),(16,'Consultoría',3,'Servicio','Servicios profesionales','fa-user-tie',4,'Activo'),(17,'Materias Primas Químicas',NULL,'Materia Prima','Químicos y componentes para fabricación','fa-flask',1,'Activo'),(18,'Materiales y Herramientas',NULL,'Material','Materiales de trabajo y herramientas','fa-tools',2,'Activo'),(19,'Servicios',NULL,'Servicio','Servicios contratados','fa-handshake',3,'Activo'),(20,'Resinas',1,'Materia Prima','Resinas acrílicas, epóxicas, etc.','fa-vial',1,'Activo'),(21,'Solventes',1,'Materia Prima','Solventes y diluyentes','fa-flask-vial',2,'Activo'),(22,'Pigmentos',1,'Materia Prima','Pigmentos y colorantes','fa-palette',3,'Activo'),(23,'Aditivos',1,'Materia Prima','Aditivos y modificadores','fa-prescription-bottle',4,'Activo'),(24,'Cargas',1,'Materia Prima','Cargas minerales','fa-cubes',5,'Activo'),(25,'Envases',2,'Material','Cubetas, tambos, envases','fa-bucket',1,'Activo'),(26,'Herramientas',2,'Material','Espátulas, brochas, rodillos','fa-screwdriver-wrench',2,'Activo'),(27,'Empaques',2,'Material','Etiquetas, cajas, embalaje','fa-box',3,'Activo'),(28,'Equipo de Seguridad',2,'Material','EPP y equipo de protección','fa-hard-hat',4,'Activo'),(29,'Telecomunicaciones',3,'Servicio','Internet, telefonía','fa-wifi',1,'Activo'),(30,'Mantenimiento',3,'Servicio','Mantenimiento de equipos','fa-wrench',2,'Activo'),(31,'Transporte',3,'Servicio','Servicios de logística','fa-truck',3,'Activo'),(32,'Consultoría',3,'Servicio','Servicios profesionales','fa-user-tie',4,'Activo'),(34,'Materiales y Herramientas',NULL,'Material','Materiales de trabajo y herramientas','fa-tools',2,'Activo'),(35,'Servicios',NULL,'Servicio','Servicios contratados','fa-handshake',3,'Activo'),(36,'Resinas',1,'Materia Prima','Resinas acrílicas, epóxicas, etc.','fa-vial',1,'Activo'),(37,'Solventes',1,'Materia Prima','Solventes y diluyentes','fa-flask-vial',2,'Activo'),(38,'Pigmentos',1,'Materia Prima','Pigmentos y colorantes','fa-palette',3,'Activo'),(39,'Aditivos',1,'Materia Prima','Aditivos y modificadores','fa-prescription-bottle',4,'Activo'),(40,'Cargas',1,'Materia Prima','Cargas minerales','fa-cubes',5,'Activo'),(41,'Envases',2,'Material','Cubetas, tambos, envases','fa-bucket',1,'Activo'),(42,'Herramientas',2,'Material','Espátulas, brochas, rodillos','fa-screwdriver-wrench',2,'Activo'),(43,'Empaques',2,'Material','Etiquetas, cajas, embalaje','fa-box',3,'Activo'),(44,'Equipo de Seguridad',2,'Material','EPP y equipo de protección','fa-hard-hat',4,'Activo'),(45,'Telecomunicaciones',3,'Servicio','Internet, telefonía','fa-wifi',1,'Activo'),(46,'Mantenimiento',3,'Servicio','Mantenimiento de equipos','fa-wrench',2,'Activo'),(47,'Transporte',3,'Servicio','Servicios de logística','fa-truck',3,'Activo'),(48,'Consultoría',3,'Servicio','Servicios profesionales','fa-user-tie',4,'Activo'),(49,'Materias Primas Químicas',NULL,'Materia Prima','Químicos y componentes para fabricación','fa-flask',1,'Activo'),(50,'Materiales y Herramientas',NULL,'Material','Materiales de trabajo y herramientas','fa-tools',2,'Activo'),(51,'Servicios',NULL,'Servicio','Servicios contratados','fa-handshake',3,'Activo'),(52,'Resinas',1,'Materia Prima','Resinas acrílicas, epóxicas, etc.','fa-vial',1,'Activo'),(53,'Solventes',1,'Materia Prima','Solventes y diluyentes','fa-flask-vial',2,'Activo'),(54,'Pigmentos',1,'Materia Prima','Pigmentos y colorantes','fa-palette',3,'Activo'),(55,'Aditivos',1,'Materia Prima','Aditivos y modificadores','fa-prescription-bottle',4,'Activo'),(56,'Cargas',1,'Materia Prima','Cargas minerales','fa-cubes',5,'Activo'),(57,'Envases',2,'Material','Cubetas, tambos, envases','fa-bucket',1,'Activo'),(58,'Herramientas',2,'Material','Espátulas, brochas, rodillos','fa-screwdriver-wrench',2,'Activo'),(59,'Empaques',2,'Material','Etiquetas, cajas, embalaje','fa-box',3,'Activo'),(60,'Equipo de Seguridad',2,'Material','EPP y equipo de protección','fa-hard-hat',4,'Activo'),(61,'Telecomunicaciones',3,'Servicio','Internet, telefonía','fa-wifi',1,'Activo'),(62,'Mantenimiento',3,'Servicio','Mantenimiento de equipos','fa-wrench',2,'Activo'),(63,'Transporte',3,'Servicio','Servicios de logística','fa-truck',3,'Activo'),(64,'Consultoría',3,'Servicio','Servicios profesionales','fa-user-tie',4,'Activo'),(65,'Materias Primas Químicas',NULL,'Materia Prima','Químicos y componentes para fabricación','fa-flask',1,'Activo'),(66,'Materiales y Herramientas',NULL,'Material','Materiales de trabajo y herramientas','fa-tools',2,'Activo'),(67,'Servicios',NULL,'Servicio','Servicios contratados','fa-handshake',3,'Activo'),(68,'Resinas',1,'Materia Prima','Resinas acrílicas, epóxicas, etc.','fa-vial',1,'Activo'),(69,'Solventes Fuertes',1,'Materia Prima','Solventes y diluyentes','fa-flask-vial',2,'Activo'),(70,'Pigmentos',1,'Materia Prima','Pigmentos y colorantes','fa-palette',3,'Activo'),(71,'Aditivos',1,'Materia Prima','Aditivos y modificadores','fa-prescription-bottle',4,'Activo'),(72,'Cargas',1,'Materia Prima','Cargas minerales','fa-cubes',5,'Activo'),(73,'Envases',2,'Material','Cubetas, tambos, envases','fa-bucket',1,'Activo'),(74,'Herramientas',2,'Material','Espátulas, brochas, rodillos','fa-screwdriver-wrench',2,'Activo'),(75,'Empaques',2,'Material','Etiquetas, cajas, embalaje','fa-box',3,'Activo'),(76,'Equipo de Seguridad',2,'Material','EPP y equipo de protección','fa-hard-hat',4,'Activo'),(77,'Telecomunicaciones',3,'Servicio','Internet, telefonía','fa-wifi',1,'Activo'),(78,'Mantenimiento',3,'Servicio','Mantenimiento de equipos','fa-wrench',2,'Activo'),(79,'Transporte',3,'Servicio','Servicios de logística','fa-truck',3,'Activo'),(80,'Consultoría',3,'Servicio','Servicios profesionales','fa-user-tie',4,'Activo');
/*!40000 ALTER TABLE `categorias_insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categorias_productos`
--

DROP TABLE IF EXISTS `categorias_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_padre_id` int(11) DEFAULT NULL COMMENT 'Para jerarquía de categorías',
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_padre` (`categoria_padre_id`),
  CONSTRAINT `fk_cat_prod_padre` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias_productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de productos terminados';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categorias_productos`
--

LOCK TABLES `categorias_productos` WRITE;
/*!40000 ALTER TABLE `categorias_productos` DISABLE KEYS */;
INSERT INTO `categorias_productos` VALUES (1,'Pinturas','Pinturas y recubrimientos',NULL,'Activa','2025-12-25 08:35:06'),(2,'Recubrimientos','Recubrimientos especiales',NULL,'Activa','2025-12-25 08:35:06'),(3,'Preparadores de Superficies','Primers, selladores, fondos',NULL,'Activa','2025-12-25 08:35:06'),(4,'Pastas','Pastas y masillas',NULL,'Activa','2025-12-25 08:35:06'),(5,'Selladores','Selladores y tapaporos',NULL,'Activa','2025-12-25 08:35:06'),(6,'Reventa','Productos de reventa directa',NULL,'Activa','2025-12-25 08:35:06'),(7,'Vinílicas','Pinturas vinílicas',1,'Activa','2025-12-25 08:35:06'),(8,'Esmaltes','Esmaltes y acabados',1,'Activa','2025-12-25 08:35:06'),(9,'Impermeabilizantes','Impermeabilizantes',1,'Activa','2025-12-25 08:35:06'),(10,'Herramientas','Espátulas, rodillos, brochas',6,'Activa','2025-12-25 08:35:06'),(11,'Accesorios','Accesorios diversos',6,'Activa','2025-12-25 08:35:06');
/*!40000 ALTER TABLE `categorias_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL COMMENT 'CLI-00001',
  `razon_social` varchar(200) NOT NULL,
  `nombre_comercial` varchar(200) DEFAULT NULL,
  `rfc` varchar(13) NOT NULL,
  `regimen_fiscal` varchar(100) DEFAULT NULL,
  `uso_cfdi` varchar(5) DEFAULT NULL COMMENT 'Clave SAT: G03, G01, etc.',
  `contacto_nombre` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `email_facturacion` varchar(100) DEFAULT NULL,
  `calle` varchar(200) DEFAULT NULL,
  `numero_exterior` varchar(20) DEFAULT NULL,
  `numero_interior` varchar(20) DEFAULT NULL,
  `colonia` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `limite_credito` decimal(10,2) DEFAULT 0.00,
  `dias_credito` int(11) DEFAULT 0,
  `saldo_pendiente` decimal(10,2) DEFAULT 0.00,
  `tipo_cliente` enum('Empresa','Persona Física','Mostrador') DEFAULT 'Empresa',
  `estatus` enum('Activo','Inactivo','Suspendido') DEFAULT 'Activo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_razon_social` (`razon_social`),
  KEY `idx_tipo_cliente` (`tipo_cliente`),
  KEY `idx_clientes_rfc` (`rfc`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clientes del sistema';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'CLI-00000','CLIENTE MOSTRADOR',NULL,'XAXX010101000',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0,1740.00,'Mostrador','Activo','2025-12-25 09:59:10','2025-12-25 11:01:45'),(2,'CLI-00001','Soluciones Tecnológicas del Bajío S.A. de C.V.','IT bajio','STB150820GH1','',NULL,'Ing. Roberto Mendiola','226620851501','cliente1@mail.com',NULL,'Av. Vallarta','2440','4B','Arcos Vallarta','Guadalajara','Jalisto','44130',500000.00,15,0.00,'Empresa','Activo','2025-12-25 10:12:32',NULL);
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contactos_proveedor`
--

DROP TABLE IF EXISTS `contactos_proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contactos_proveedor` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `puesto` varchar(100) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_principal` (`es_principal`),
  CONSTRAINT `fk_contacto_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Contactos adicionales de proveedores';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contactos_proveedor`
--

LOCK TABLES `contactos_proveedor` WRITE;
/*!40000 ALTER TABLE `contactos_proveedor` DISABLE KEYS */;
/*!40000 ALTER TABLE `contactos_proveedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contratos_empleados`
--

DROP TABLE IF EXISTS `contratos_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `contratos_empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `version` int(11) NOT NULL DEFAULT 1,
  `tipo_contrato` enum('Inicial','Renovación','Modificación Salarial','Cambio de Puesto','Cambio de Departamento') NOT NULL,
  `vigente` tinyint(1) DEFAULT 1,
  `puesto` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `tipo_trabajador` varchar(50) NOT NULL,
  `salario_base_mensual` decimal(15,2) NOT NULL,
  `salario_base_diario` decimal(15,2) NOT NULL,
  `tipo_nomina` varchar(50) NOT NULL,
  `jornada_laboral` varchar(100) DEFAULT 'Tiempo Completo',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL,
  `motivo_cambio` text DEFAULT NULL,
  `creado_por` tinyint(4) DEFAULT NULL,
  `contrato_texto` text DEFAULT NULL COMMENT 'Texto completo del contrato generado',
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_vigente` (`vigente`),
  KEY `fk_contrato_admin` (`creado_por`),
  CONSTRAINT `fk_contrato_admin` FOREIGN KEY (`creado_por`) REFERENCES `administradores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_contrato_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contratos_empleados`
--

LOCK TABLES `contratos_empleados` WRITE;
/*!40000 ALTER TABLE `contratos_empleados` DISABLE KEYS */;
INSERT INTO `contratos_empleados` VALUES (1,1,1,'Modificación Salarial',1,'Auxiliar Contable','Administración','Planta',9500.00,316.67,'Quincenal','Tiempo Completo','2025-12-24',NULL,'2025-12-24 17:20:04','Cambio de salario de $8,500.00 a $9,500.00',NULL,'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Modificación Salarial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: EHWEB EHWEB EHWEB\nRFC: PERJ850510HT5\nCURP: PERJ850510HDFRRN01\nNSS: 12345678901\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Auxiliar Contable\nSEGUNDA.- DEPARTAMENTO: Administración\nTERCERA.- TIPO DE TRABAJADOR: Planta\nCUARTA.- SALARIO BASE MENSUAL: $9,500.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $316.67 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2025-12-24\nMOTIVO: Cambio de salario de $8,500.00 a $9,500.00\n\nFecha de generación: 24/12/2025 17:20:04'),(2,2,1,'Inicial',1,'Auxiliar Contable','Contabilidad','Planta',7850.00,261.67,'Quincenal','Tiempo Completo','2025-12-24',NULL,'2025-12-24 17:37:38','Contrato inicial de trabajo',NULL,'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Inicial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: Pedro Lopez Morales\nRFC: DFVJ850510HT5\nCURP: QPRM541210HOCAOS82\nNSS: 151845111\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Auxiliar Contable\nSEGUNDA.- DEPARTAMENTO: Contabilidad\nTERCERA.- TIPO DE TRABAJADOR: Planta\nCUARTA.- SALARIO BASE MENSUAL: $7,850.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $261.67 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2025-12-24\nMOTIVO: Contrato inicial de trabajo\n\nFecha de generación: 24/12/2025 17:37:38'),(3,3,1,'Inicial',1,'Auxiliar de Ventas','Ventas','Por Proyecto',3500.00,116.67,'Quincenal','Tiempo Completo','2024-12-24',NULL,'2025-12-24 17:43:35','Contrato inicial de trabajo',NULL,'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Inicial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: Ana Karina Roman Martinez\nRFC: GUMA900515H2A\nCURP: GUMA900515MDFTRN05\nNSS: 656514451\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Auxiliar de Ventas\nSEGUNDA.- DEPARTAMENTO: Ventas\nTERCERA.- TIPO DE TRABAJADOR: Por Proyecto\nCUARTA.- SALARIO BASE MENSUAL: $3,500.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $116.67 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2024-12-24\nMOTIVO: Contrato inicial de trabajo\n\nFecha de generación: 24/12/2025 17:43:35'),(4,4,1,'Inicial',1,'Compradora','Compras','Planta',4500.00,150.00,'Quincenal','Tiempo Completo','2022-12-24',NULL,'2025-12-24 18:02:32','Contrato inicial de trabajo',NULL,'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Inicial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: Maria Pilar Gomez\nRFC: GOLM8803257K9\nCURP: GOLM880325MJCMRR04\nNSS: 12459785123\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Compradora\nSEGUNDA.- DEPARTAMENTO: Compras\nTERCERA.- TIPO DE TRABAJADOR: Planta\nCUARTA.- SALARIO BASE MENSUAL: $4,500.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $150.00 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2022-12-24\nMOTIVO: Contrato inicial de trabajo\n\nFecha de generación: 24/12/2025 18:02:32');
/*!40000 ALTER TABLE `contratos_empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_bancarias`
--

DROP TABLE IF EXISTS `cuentas_bancarias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_bancarias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `banco` varchar(100) NOT NULL COMMENT 'Nombre del banco',
  `numero_cuenta` varchar(50) NOT NULL COMMENT 'Número de cuenta',
  `clabe` varchar(18) DEFAULT NULL COMMENT 'CLABE interbancaria',
  `tipo_cuenta` enum('Cheques','Inversión','Nómina','Ahorro') DEFAULT 'Cheques',
  `moneda` varchar(10) DEFAULT 'MXN' COMMENT 'Moneda de la cuenta',
  `saldo_inicial` decimal(15,2) DEFAULT 0.00 COMMENT 'Saldo inicial',
  `saldo_actual` decimal(15,2) DEFAULT 0.00 COMMENT 'Saldo actual',
  `cuenta_contable_id` int(11) DEFAULT NULL COMMENT 'Cuenta contable asociada',
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cuenta_contable_id` (`cuenta_contable_id`),
  KEY `idx_banco` (`banco`),
  KEY `idx_estatus` (`estatus`),
  CONSTRAINT `cuentas_bancarias_ibfk_1` FOREIGN KEY (`cuenta_contable_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cuentas bancarias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_bancarias`
--

LOCK TABLES `cuentas_bancarias` WRITE;
/*!40000 ALTER TABLE `cuentas_bancarias` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_bancarias` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cuentas_contables`
--

DROP TABLE IF EXISTS `cuentas_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cuentas_contables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL COMMENT 'Código de la cuenta (ej: 1.1.01.001)',
  `nombre` varchar(200) NOT NULL COMMENT 'Nombre de la cuenta',
  `tipo_cuenta` enum('Activo','Pasivo','Capital','Ingresos','Egresos','Costos') NOT NULL COMMENT 'Tipo principal de cuenta',
  `subtipo` varchar(100) DEFAULT NULL COMMENT 'Subtipo: Circulante, Fijo, etc.',
  `naturaleza` enum('Deudora','Acreedora') NOT NULL COMMENT 'Naturaleza de la cuenta',
  `nivel` int(11) NOT NULL COMMENT 'Nivel jerárquico (1=Mayor, 2=Submay or, etc.)',
  `cuenta_padre_id` int(11) DEFAULT NULL COMMENT 'ID de la cuenta padre',
  `es_afectable` tinyint(1) DEFAULT 1 COMMENT '1=Puede recibir movimientos, 0=Solo agrupadora',
  `requiere_auxiliar` tinyint(1) DEFAULT 0 COMMENT '1=Requiere auxiliar (cliente, proveedor, etc.)',
  `tipo_auxiliar` varchar(50) DEFAULT NULL COMMENT 'Tipo: cliente, proveedor, empleado, etc.',
  `saldo_inicial` decimal(15,2) DEFAULT 0.00 COMMENT 'Saldo inicial del ejercicio',
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_codigo` (`codigo`),
  KEY `idx_tipo` (`tipo_cuenta`),
  KEY `idx_nivel` (`nivel`),
  KEY `cuenta_padre_id` (`cuenta_padre_id`),
  CONSTRAINT `cuentas_contables_ibfk_1` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `cuentas_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de cuentas contables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cuentas_contables`
--

LOCK TABLES `cuentas_contables` WRITE;
/*!40000 ALTER TABLE `cuentas_contables` DISABLE KEYS */;
/*!40000 ALTER TABLE `cuentas_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departamentos`
--

DROP TABLE IF EXISTS `departamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_edicion` datetime DEFAULT NULL,
  `fecha_baja` datetime DEFAULT NULL,
  `fecha_alta` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `idx_estatus` (`estatus`),
  KEY `fk_depto_responsable` (`responsable_id`),
  CONSTRAINT `fk_depto_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departamentos`
--

LOCK TABLES `departamentos` WRITE;
/*!40000 ALTER TABLE `departamentos` DISABLE KEYS */;
INSERT INTO `departamentos` VALUES (1,'Ventas','Gestión de clientes, cotizaciones y seguimiento comercial',NULL,1,NULL,NULL,'2025-12-24'),(2,'Facturación','Emisión de CFDI y control de cobranza',NULL,1,NULL,NULL,'2025-12-24'),(3,'Producción','Control de fabricación y estándares de calidad',NULL,1,NULL,NULL,'2025-12-24'),(4,'Obras','Gestión de proyectos en campo y aplicación',NULL,1,NULL,NULL,'2025-12-24'),(5,'Compras','Adquisición de insumos y trato con proveedores',NULL,1,NULL,NULL,'2025-12-24'),(6,'Almacén','Control de inventarios y logística interna',NULL,1,NULL,NULL,'2025-12-24'),(7,'Recursos Humanos','Administración de personal y nómina',NULL,1,NULL,NULL,'2025-12-24'),(8,'Contabilidad','Registro financiero e impuestos',NULL,1,NULL,NULL,'2025-12-24'),(9,'Dirección General','Máxima autoridad, responsable de la estrategia global',NULL,1,'2025-12-24 00:00:00',NULL,'2025-12-24'),(10,'Administración','Apoyo general, archivo y atención telefónica',NULL,1,NULL,NULL,'2025-12-24');
/*!40000 ALTER TABLE `departamentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `descuentos`
--

DROP TABLE IF EXISTS `descuentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `descuentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre del descuento',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción detallada',
  `tipo_descuento` enum('Porcentaje','Monto Fijo') DEFAULT 'Porcentaje',
  `valor` decimal(10,2) NOT NULL COMMENT 'Porcentaje o monto del descuento',
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_estatus` (`estatus`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Descuentos y precios especiales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `descuentos`
--

LOCK TABLES `descuentos` WRITE;
/*!40000 ALTER TABLE `descuentos` DISABLE KEYS */;
INSERT INTO `descuentos` VALUES (1,'Descuento Cliente Frecuente','Descuento del 10% para clientes frecuentes','Porcentaje',10.00,'Activo','2025-12-25 10:51:04','2025-12-25 10:51:04'),(2,'Descuento Mayoreo','Descuento del 15% para compras mayores a $10,000','Porcentaje',15.00,'Activo','2025-12-25 10:51:04','2025-12-25 10:51:04'),(3,'Descuento Promoción','Descuento fijo de $500 en compras especiales','Monto Fijo',500.00,'Activo','2025-12-25 10:51:04','2025-12-25 10:51:04');
/*!40000 ALTER TABLE `descuentos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_entregas_almacen`
--

DROP TABLE IF EXISTS `detalle_entregas_almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_entregas_almacen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entrega_id` int(11) NOT NULL,
  `tipo_detalle` enum('Orden Venta','Obra') NOT NULL,
  `detalle_orden_id` int(11) DEFAULT NULL COMMENT 'ID de detalle_orden_venta',
  `obra_producto_id` int(11) DEFAULT NULL COMMENT 'ID de obras_productos',
  `producto_id` int(11) NOT NULL,
  `cantidad_entregada` decimal(10,2) NOT NULL,
  `movimiento_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_entrega` (`entrega_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_detalle_orden` (`detalle_orden_id`),
  KEY `idx_obra_producto` (`obra_producto_id`),
  KEY `idx_movimiento` (`movimiento_id`),
  KEY `idx_detalle_tipo` (`tipo_detalle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos entregados';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_entregas_almacen`
--

LOCK TABLES `detalle_entregas_almacen` WRITE;
/*!40000 ALTER TABLE `detalle_entregas_almacen` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_entregas_almacen` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_entrega_almacen` AFTER INSERT ON `detalle_entregas_almacen` FOR EACH ROW BEGIN
  DECLARE v_orden_id INT;
  DECLARE v_obra_id INT;
  
  -- Actualizar según tipo de detalle
  IF NEW.tipo_detalle = 'Orden Venta' THEN
    -- Actualizar cantidad entregada en detalle de orden
    UPDATE detalle_orden_venta 
    SET cantidad_entregada = cantidad_entregada + NEW.cantidad_entregada
    WHERE id = NEW.detalle_orden_id;
    
    -- Obtener orden_venta_id
    SELECT orden_venta_id INTO v_orden_id
    FROM detalle_orden_venta 
    WHERE id = NEW.detalle_orden_id;
    
    -- Actualizar estatus de orden de venta
    UPDATE ordenes_venta ov
    SET 
      estatus = CASE
        -- Si todo está entregado → Entregada
        WHEN (SELECT SUM(cantidad) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) = 
             (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id)
        THEN 'Entregada'
        -- Si hay algo entregado → En Preparación
        WHEN (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) > 0
        THEN 'En Preparación'
        ELSE estatus
      END,
      fecha_entrega_real = CASE
        WHEN (SELECT SUM(cantidad) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id) = 
             (SELECT SUM(cantidad_entregada) FROM detalle_orden_venta WHERE orden_venta_id = v_orden_id)
        THEN NOW()
        ELSE fecha_entrega_real
      END
    WHERE id = v_orden_id;
    
  ELSEIF NEW.tipo_detalle = 'Obra' THEN
    -- Actualizar cantidad entregada en obras_productos
    UPDATE obras_productos 
    SET cantidad_entregada = cantidad_entregada + NEW.cantidad_entregada
    WHERE id = NEW.obra_producto_id;
    
    -- Obtener obra_id
    SELECT obra_id INTO v_obra_id
    FROM obras_productos 
    WHERE id = NEW.obra_producto_id;
    
    -- Actualizar estatus de obra
    UPDATE obras o
    SET 
      estatus = CASE
        -- Si todo está entregado → Finalizada
        WHEN (SELECT SUM(cantidad) FROM obras_productos WHERE obra_id = v_obra_id) = 
             (SELECT SUM(cantidad_entregada) FROM obras_productos WHERE obra_id = v_obra_id)
        THEN 'Finalizada'
        -- Si hay algo entregado → En Proceso
        WHEN (SELECT SUM(cantidad_entregada) FROM obras_productos WHERE obra_id = v_obra_id) > 0
        THEN 'En Proceso'
        ELSE estatus
      END
    WHERE id = v_obra_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `detalle_formulacion`
--

DROP TABLE IF EXISTS `detalle_formulacion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_formulacion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formulacion_id` int(11) NOT NULL,
  `tipo_componente` enum('Insumo','Producto') NOT NULL COMMENT 'Insumo o Producto base',
  `insumo_id` int(11) DEFAULT NULL COMMENT 'Si es insumo',
  `producto_id` int(11) DEFAULT NULL COMMENT 'Si es producto base',
  `cantidad` decimal(10,3) NOT NULL,
  `unidad` enum('L','ml','Kg','g','Pza') NOT NULL,
  `porcentaje` decimal(5,2) DEFAULT NULL COMMENT 'Porcentaje en la formulación',
  `costo_unitario` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo del componente al momento',
  `costo_total` decimal(10,2) DEFAULT 0.00 COMMENT 'cantidad * costo_unitario',
  `observaciones` text DEFAULT NULL,
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de mezclado',
  PRIMARY KEY (`id`),
  KEY `idx_formulacion` (`formulacion_id`),
  KEY `idx_insumo` (`insumo_id`),
  KEY `idx_producto` (`producto_id`),
  CONSTRAINT `fk_detform_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detform_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`),
  CONSTRAINT `fk_detform_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`),
  CONSTRAINT `chk_componente` CHECK (`tipo_componente` = 'Insumo' and `insumo_id` is not null and `producto_id` is null or `tipo_componente` = 'Producto' and `producto_id` is not null and `insumo_id` is null)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de formulaciones (BOM)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_formulacion`
--

LOCK TABLES `detalle_formulacion` WRITE;
/*!40000 ALTER TABLE `detalle_formulacion` DISABLE KEYS */;
INSERT INTO `detalle_formulacion` VALUES (1,1,'Insumo',1,NULL,0.500,'g',NULL,0.00,0.00,'',0),(2,2,'Insumo',1,NULL,0.500,'g',NULL,0.00,0.00,NULL,0),(3,3,'Insumo',1,NULL,25.000,'L',NULL,0.00,0.00,'se agregan mililitos extra',0),(4,4,'Insumo',1,NULL,0.500,'g',NULL,0.00,0.00,NULL,0);
/*!40000 ALTER TABLE `detalle_formulacion` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_detalle_formulacion_costo` BEFORE INSERT ON `detalle_formulacion` FOR EACH ROW BEGIN
  SET NEW.costo_total = NEW.cantidad * NEW.costo_unitario;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_costo_formulacion_insert` AFTER INSERT ON `detalle_formulacion` FOR EACH ROW BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = NEW.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = NEW.formulacion_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_costo_formulacion_update` AFTER UPDATE ON `detalle_formulacion` FOR EACH ROW BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = NEW.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = NEW.formulacion_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_costo_formulacion_delete` AFTER DELETE ON `detalle_formulacion` FOR EACH ROW BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = OLD.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = OLD.formulacion_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `detalle_orden_compra`
--

DROP TABLE IF EXISTS `detalle_orden_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_orden_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_compra_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `cantidad_solicitada` decimal(10,2) NOT NULL,
  `cantidad_recibida` decimal(10,2) DEFAULT 0.00,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_orden` (`orden_compra_id`),
  KEY `idx_insumo` (`insumo_id`),
  CONSTRAINT `fk_doc_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`),
  CONSTRAINT `fk_doc_orden` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de órdenes de compra';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_compra`
--

LOCK TABLES `detalle_orden_compra` WRITE;
/*!40000 ALTER TABLE `detalle_orden_compra` DISABLE KEYS */;
INSERT INTO `detalle_orden_compra` VALUES (1,1,1,5.00,5.00,100.00,500.00,NULL),(7,2,1,20.00,15.00,100.00,2000.00,NULL);
/*!40000 ALTER TABLE `detalle_orden_compra` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `trg_calcular_subtotal_detalle` BEFORE INSERT ON `detalle_orden_compra` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad_solicitada * NEW.precio_unitario;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `trg_actualizar_totales_oc` AFTER INSERT ON `detalle_orden_compra` FOR EACH ROW BEGIN
  DECLARE v_subtotal DECIMAL(12,2);
  DECLARE v_iva DECIMAL(12,2);
  
  SELECT SUM(subtotal) INTO v_subtotal
  FROM detalle_orden_compra
  WHERE orden_compra_id = NEW.orden_compra_id;
  
  SET v_iva = v_subtotal * 0.16;
  
  UPDATE ordenes_compra
  SET subtotal = v_subtotal,
      iva = v_iva,
      total = v_subtotal + v_iva
  WHERE id = NEW.orden_compra_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `trg_calcular_subtotal_detalle_update` BEFORE UPDATE ON `detalle_orden_compra` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad_solicitada * NEW.precio_unitario;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `detalle_orden_produccion`
--

DROP TABLE IF EXISTS `detalle_orden_produccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_orden_produccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_produccion_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `formulacion_id` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) DEFAULT 'kg',
  `completado` tinyint(1) DEFAULT 0,
  `fecha_completado` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_orden` (`orden_produccion_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_completado` (`completado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en órdenes de producción';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_produccion`
--

LOCK TABLES `detalle_orden_produccion` WRITE;
/*!40000 ALTER TABLE `detalle_orden_produccion` DISABLE KEYS */;
/*!40000 ALTER TABLE `detalle_orden_produccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detalle_orden_venta`
--

DROP TABLE IF EXISTS `detalle_orden_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `detalle_orden_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_venta_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `cantidad_entregada` decimal(10,2) DEFAULT 0.00,
  `precio_unitario` decimal(10,2) NOT NULL,
  `descuento` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de descuento',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'cantidad * precio_unitario * (1 - descuento/100)',
  `stock_disponible_al_crear` decimal(10,2) DEFAULT NULL COMMENT 'Stock disponible al momento de crear la orden',
  `requiere_produccion` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `formulacion_id` int(11) DEFAULT NULL COMMENT 'ID de la formulación específica usada para esta venta',
  `formulacion_version` varchar(50) DEFAULT NULL COMMENT 'Versión de la formulación usada',
  PRIMARY KEY (`id`),
  KEY `idx_orden` (`orden_venta_id`),
  KEY `idx_producto` (`producto_id`),
  CONSTRAINT `fk_dov_orden` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_dov_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en órdenes de venta';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detalle_orden_venta`
--

LOCK TABLES `detalle_orden_venta` WRITE;
/*!40000 ALTER TABLE `detalle_orden_venta` DISABLE KEYS */;
INSERT INTO `detalle_orden_venta` VALUES (1,1,3,1.00,0.00,500.00,0.00,500.00,0.00,1,NULL,NULL,NULL),(2,2,3,1.00,0.00,500.00,0.00,500.00,-1.00,1,NULL,NULL,NULL),(3,3,3,1.00,0.00,500.00,0.00,500.00,-2.00,1,NULL,NULL,NULL),(4,4,3,1.00,0.00,500.00,0.00,500.00,-3.00,1,NULL,NULL,NULL),(5,5,3,10.00,0.00,500.00,0.00,5000.00,-4.00,1,NULL,NULL,NULL),(6,6,3,1.00,0.00,500.00,0.00,500.00,-14.00,1,NULL,NULL,NULL),(7,7,3,4.00,0.00,500.00,0.00,2000.00,-15.00,1,NULL,NULL,NULL),(8,8,3,4.00,0.00,500.00,0.00,2000.00,-19.00,1,NULL,NULL,NULL),(9,9,3,4.00,0.00,500.00,0.00,2000.00,-23.00,1,NULL,NULL,NULL),(10,10,3,4.00,0.00,500.00,0.00,2000.00,-27.00,1,NULL,NULL,NULL),(11,11,3,4.00,0.00,500.00,0.00,2000.00,-31.00,1,NULL,NULL,NULL),(12,12,3,4.00,0.00,500.00,0.00,2000.00,-35.00,1,NULL,NULL,NULL),(13,13,3,2.00,0.00,500.00,0.00,1000.00,-39.00,1,NULL,4,'4'),(14,14,3,3.00,0.00,500.00,0.00,1500.00,-39.00,1,NULL,4,'4'),(15,15,3,5.00,0.00,500.00,0.00,2500.00,-42.00,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `detalle_orden_venta` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_detalle_ov_subtotal_insert` BEFORE INSERT ON `detalle_orden_venta` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario * (1 - NEW.descuento / 100);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_totales_ov_insert` AFTER INSERT ON `detalle_orden_venta` FOR EACH ROW BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = NEW.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = NEW.orden_venta_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_detalle_ov_subtotal_update` BEFORE UPDATE ON `detalle_orden_venta` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario * (1 - NEW.descuento / 100);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_totales_ov_update` AFTER UPDATE ON `detalle_orden_venta` FOR EACH ROW BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = NEW.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = NEW.orden_venta_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_totales_ov_delete` AFTER DELETE ON `detalle_orden_venta` FOR EACH ROW BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = OLD.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = OLD.orden_venta_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ejercicios_fiscales`
--

DROP TABLE IF EXISTS `ejercicios_fiscales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ejercicios_fiscales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `año` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estatus` enum('Abierto','Cerrado') DEFAULT 'Abierto',
  `fecha_cierre` datetime DEFAULT NULL,
  `usuario_cierre` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `año` (`año`),
  KEY `idx_año` (`año`),
  KEY `idx_estatus` (`estatus`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ejercicios fiscales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ejercicios_fiscales`
--

LOCK TABLES `ejercicios_fiscales` WRITE;
/*!40000 ALTER TABLE `ejercicios_fiscales` DISABLE KEYS */;
INSERT INTO `ejercicios_fiscales` VALUES (1,2025,'2025-01-01','2025-12-31','Abierto',NULL,NULL,'2025-12-25 23:18:18');
/*!40000 ALTER TABLE `ejercicios_fiscales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empleados`
--

DROP TABLE IF EXISTS `empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numero_empleado` varchar(20) NOT NULL COMMENT 'Número interno de empleado',
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) NOT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date NOT NULL,
  `genero` enum('M','F','Otro') NOT NULL,
  `estado_civil` enum('Soltero','Casado','Divorciado','Viudo','Union Libre') DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `telefono_emergencia` varchar(15) DEFAULT NULL,
  `email_personal` varchar(150) DEFAULT NULL,
  `email_corporativo` varchar(150) DEFAULT NULL,
  `calle` varchar(200) DEFAULT NULL,
  `numero_exterior` varchar(20) DEFAULT NULL,
  `numero_interior` varchar(20) DEFAULT NULL,
  `colonia` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(5) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `pais` varchar(50) DEFAULT 'México',
  `rfc` varchar(13) NOT NULL COMMENT 'Registro Federal de Contribuyentes',
  `curp` varchar(18) NOT NULL COMMENT 'Clave Única de Registro de Población',
  `nss` varchar(11) DEFAULT NULL COMMENT 'Número de Seguro Social (IMSS)',
  `afore` varchar(100) DEFAULT NULL COMMENT 'Nombre de la AFORE del empleado',
  `afore_numero_cuenta` varchar(20) DEFAULT NULL COMMENT 'Número de cuenta individual AFORE',
  `tipo_trabajador` enum('Planta','Temporal','Por Proyecto','Honorarios','Practicante') NOT NULL,
  `departamento_id` int(11) DEFAULT NULL COMMENT 'FK a tabla departamentos',
  `puesto` varchar(100) NOT NULL,
  `jefe_directo_id` int(11) DEFAULT NULL COMMENT 'FK a empleados (auto-referencia)',
  `fecha_ingreso` date NOT NULL,
  `fecha_baja` date DEFAULT NULL,
  `motivo_baja` text DEFAULT NULL,
  `salario_base_mensual` decimal(10,2) NOT NULL COMMENT 'Salario mensual bruto',
  `salario_base_diario` decimal(10,2) NOT NULL COMMENT 'Salario diario integrado',
  `tipo_nomina` enum('Semanal','Quincenal','Mensual') DEFAULT 'Quincenal',
  `forma_pago` enum('Transferencia','Efectivo','Cheque') DEFAULT 'Transferencia',
  `banco` varchar(100) DEFAULT NULL,
  `cuenta_bancaria` varchar(18) DEFAULT NULL COMMENT 'CLABE interbancaria',
  `tiene_fonacot` tinyint(1) DEFAULT 0,
  `tiene_infonavit` tinyint(1) DEFAULT 0,
  `descuento_infonavit` decimal(10,2) DEFAULT 0.00,
  `estatus` tinyint(1) DEFAULT 1 COMMENT '1=Activo, 0=Inactivo',
  `fecha_alta` date NOT NULL,
  `fecha_edicion` date DEFAULT NULL,
  `usuario_alta_id` int(11) DEFAULT NULL COMMENT 'FK a administradores',
  `usuario_edicion_id` int(11) DEFAULT NULL COMMENT 'FK a administradores',
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero_empleado` (`numero_empleado`),
  UNIQUE KEY `rfc` (`rfc`),
  UNIQUE KEY `curp` (`curp`),
  UNIQUE KEY `nss` (`nss`),
  KEY `idx_departamento` (`departamento_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_tipo_trabajador` (`tipo_trabajador`),
  KEY `idx_fecha_ingreso` (`fecha_ingreso`),
  CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empleados`
--

LOCK TABLES `empleados` WRITE;
/*!40000 ALTER TABLE `empleados` DISABLE KEYS */;
INSERT INTO `empleados` VALUES (1,'EMP-2025-0001','EHWEB','EHWEB','EHWEB','1993-08-22','M','Soltero','5546852145',NULL,'soporte2@especialistasweb.com.mx',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'México','PERJ850510HT5','PERJ850510HDFRRN01','12345678901',NULL,NULL,'Planta',10,'Auxiliar Contable',0,'2025-12-24',NULL,NULL,9500.00,316.67,'Quincenal','Transferencia','BBVA','125487456985423654',0,0,0.00,1,'2025-12-24','2025-12-24',NULL,NULL),(2,'EMP-2025-0002','Pedro','Lopez','Morales','1982-06-25','M','Casado','5546521546',NULL,'mail1@mail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'México','DFVJ850510HT5','QPRM541210HOCAOS82','151845111',NULL,NULL,'Planta',8,'Auxiliar Contable',0,'2025-12-24',NULL,NULL,7850.00,261.67,'Quincenal','Transferencia','','',0,0,0.00,1,'2025-12-24',NULL,NULL,NULL),(3,'EMP-2025-0003','Ana Karina','Roman','Martinez','1975-02-12','F','Casado','6655331221',NULL,'karina@mail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'México','GUMA900515H2A','GUMA900515MDFTRN05','656514451',NULL,NULL,'Por Proyecto',1,'Auxiliar de Ventas',1,'2024-12-24',NULL,NULL,3500.00,116.67,'Quincenal','Efectivo','Santander','126475142365478412',0,0,0.00,1,'2025-12-24',NULL,NULL,NULL),(4,'EMP-2025-0004','Maria','Pilar','Gomez','1982-12-25','F','Divorciado','1236545645',NULL,'mariapilar@mail.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'México','GOLM8803257K9','GOLM880325MJCMRR04','12459785123',NULL,NULL,'Planta',5,'Compradora',3,'2022-12-24',NULL,NULL,4500.00,150.00,'Quincenal','Transferencia','bbva','124574896542354125',0,0,0.00,1,'2025-12-24',NULL,NULL,NULL);
/*!40000 ALTER TABLE `empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entregas_almacen`
--

DROP TABLE IF EXISTS `entregas_almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `entregas_almacen` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'ENT-2025-0001',
  `tipo_origen` enum('Orden Venta','Obra') NOT NULL,
  `orden_venta_id` int(11) DEFAULT NULL,
  `obra_id` int(11) DEFAULT NULL,
  `fecha_entrega` datetime NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Activa','Cancelada') DEFAULT 'Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  KEY `idx_tipo_origen` (`tipo_origen`),
  KEY `idx_fecha` (`fecha_entrega`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_orden_venta` (`orden_venta_id`),
  KEY `idx_obra` (`obra_id`),
  KEY `idx_usuario` (`usuario_id`),
  KEY `idx_entregas_fecha_tipo` (`fecha_entrega`,`tipo_origen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de entregas de almacén (órdenes y obras)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entregas_almacen`
--

LOCK TABLES `entregas_almacen` WRITE;
/*!40000 ALTER TABLE `entregas_almacen` DISABLE KEYS */;
/*!40000 ALTER TABLE `entregas_almacen` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_venta_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `rfc` varchar(13) NOT NULL,
  `razon_social` varchar(200) NOT NULL,
  `regimen_fiscal` varchar(100) NOT NULL,
  `uso_cfdi` varchar(5) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `folio_fiscal` varchar(36) NOT NULL COMMENT 'UUID simulado',
  `serie` varchar(10) DEFAULT 'A',
  `folio` varchar(50) NOT NULL,
  `fecha_emision` datetime DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estatus` enum('Emitida','Cancelada') DEFAULT 'Emitida',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_folio_fiscal` (`folio_fiscal`),
  KEY `idx_orden` (`orden_venta_id`),
  KEY `idx_cliente` (`cliente_id`),
  CONSTRAINT `fk_factura_orden` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de facturas emitidas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
INSERT INTO `facturas` VALUES (1,10,2,'STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','616','G03','44130','9372a72c-2be4-4c98-88cc-516312871f29','A','F-','2025-12-25 12:40:43',2000.00,320.00,2320.00,'Emitida'),(2,11,2,'STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','616','G03','44130','f26b518e-20c3-4ee5-99f9-d0a43a8217dc','A','F-OV-2025-0011','2025-12-25 12:41:53',2000.00,320.00,2320.00,'Emitida'),(3,12,2,'STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','616','G03','44130','827d4c1e-8093-4920-92ca-cea9aba7fcc5','A','F-OV-2025-0012','2025-12-25 12:52:36',2000.00,288.00,2088.00,'Emitida'),(4,13,2,'STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','616','G03','44130','028eb165-e005-4cc1-a8e1-3d273a06b1cc','A','F-OV-2025-0013','2025-12-25 13:06:24',1000.00,160.00,1160.00,'Emitida'),(5,14,2,'STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','616','G03','44130','31ad8719-0eca-435c-8660-691ac8314cab','A','F-OV-2025-0014','2025-12-25 13:40:21',1500.00,240.00,1740.00,'Emitida'),(6,15,2,'STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','616','G03','44130','8129b05a-5281-4bb4-b0f8-56e18da1416b','A','F-OV-2025-0015','2025-12-25 13:50:13',2500.00,360.00,2610.00,'Emitida');
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas_obras`
--

DROP TABLE IF EXISTS `facturas_obras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas_obras` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `obra_id` int(10) unsigned NOT NULL,
  `folio` varchar(50) NOT NULL COMMENT 'Folio de la factura (F-OB-XXXXX)',
  `fecha_emision` datetime NOT NULL DEFAULT current_timestamp(),
  `subtotal` decimal(10,2) NOT NULL,
  `iva` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `rfc_emisor` varchar(13) NOT NULL,
  `razon_social_emisor` varchar(255) NOT NULL,
  `direccion_emisor` text DEFAULT NULL,
  `rfc_receptor` varchar(13) NOT NULL,
  `razon_social_receptor` varchar(255) NOT NULL,
  `direccion_receptor` text DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `creado_por` int(10) unsigned NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  KEY `idx_obra` (`obra_id`),
  KEY `idx_folio` (`folio`),
  KEY `idx_fecha_emision` (`fecha_emision`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Facturas simuladas generadas para obras';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas_obras`
--

LOCK TABLES `facturas_obras` WRITE;
/*!40000 ALTER TABLE `facturas_obras` DISABLE KEYS */;
INSERT INTO `facturas_obras` VALUES (1,2,'F-OB-00001','2025-12-25 21:24:18',36000.00,5760.00,41760.00,'XAXX010101000','Mi Empresa S.A. de C.V.','Calle Principal #123, Col. Centro','STB150820GH1','Soluciones Tecnológicas del Bajío S.A. de C.V.','Amores 50, int b',NULL,1,'2025-12-25 21:24:18');
/*!40000 ALTER TABLE `facturas_obras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formulaciones`
--

DROP TABLE IF EXISTS `formulaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `formulaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `version` int(11) DEFAULT 1 COMMENT 'Versión de la formulación',
  `nombre_version` varchar(100) DEFAULT NULL COMMENT 'Ej: V1.0, V2.0 Mejorada',
  `descripcion` text DEFAULT NULL,
  `cantidad_producida` decimal(10,2) NOT NULL COMMENT 'Cantidad que produce esta formulación',
  `unidad_produccion` enum('L','ml','Kg','g','Pza') DEFAULT 'L',
  `costo_total_insumos` decimal(10,2) DEFAULT 0.00 COMMENT 'Calculado automáticamente',
  `costo_mano_obra` decimal(10,2) DEFAULT 0.00,
  `costo_indirecto` decimal(10,2) DEFAULT 0.00,
  `costo_total` decimal(10,2) DEFAULT 0.00,
  `es_activa` tinyint(1) DEFAULT 1 COMMENT 'Solo una versión activa por producto',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_activacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_activa` (`es_activa`),
  KEY `idx_formulaciones_producto_activa` (`producto_id`,`es_activa`),
  CONSTRAINT `fk_form_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formulaciones de productos (BOM)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formulaciones`
--

LOCK TABLES `formulaciones` WRITE;
/*!40000 ALTER TABLE `formulaciones` DISABLE KEYS */;
INSERT INTO `formulaciones` VALUES (1,3,1,'V1.0','Formulación para el hospital IMSS Monterrey',19.00,'L',0.00,200.00,80.00,280.00,1,'2025-12-25 09:27:22',NULL,NULL),(2,3,2,'V1.0','Formulación para el hospital IMSS Monterrey',19.00,'L',0.00,200.00,60.00,260.00,1,'2025-12-25 09:27:57',NULL,NULL),(3,3,3,'V1.1','Formulación para el hospital IMSS Monterrey',19.00,'L',0.00,220.00,80.00,300.00,1,'2025-12-25 09:44:14',NULL,NULL),(4,3,4,'V1.4','Formulación para el hospital IMSS Guadalajara',19.00,'L',0.00,200.00,80.00,280.00,1,'2025-12-25 09:45:32',NULL,NULL);
/*!40000 ALTER TABLE `formulaciones` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_costo_producto` AFTER UPDATE ON `formulaciones` FOR EACH ROW BEGIN
  IF NEW.es_activa = TRUE THEN
    UPDATE productos 
    SET costo_produccion = NEW.costo_total
    WHERE id = NEW.producto_id;
  END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `horarios_empleados`
--

DROP TABLE IF EXISTS `horarios_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `horarios_empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `hora_entrada_comida` time DEFAULT NULL COMMENT 'Inicio de comida/descanso',
  `hora_salida_comida` time DEFAULT NULL COMMENT 'Fin de comida/descanso',
  `es_dia_laboral` tinyint(1) DEFAULT 1 COMMENT '1=Día laboral, 0=Día de descanso',
  `turno` varchar(50) DEFAULT NULL COMMENT 'Matutino, Vespertino, Nocturno, etc.',
  `fecha_inicio` date NOT NULL COMMENT 'Fecha desde la que aplica este horario',
  `fecha_fin` date DEFAULT NULL COMMENT 'Fecha hasta la que aplica (NULL = indefinido)',
  `observaciones` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_dia` (`dia_semana`),
  KEY `idx_vigencia` (`fecha_inicio`,`fecha_fin`),
  KEY `idx_empleado_vigente` (`empleado_id`,`estatus`,`fecha_inicio`,`fecha_fin`),
  CONSTRAINT `fk_horario_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Horarios laborales de empleados por día de la semana';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `horarios_empleados`
--

LOCK TABLES `horarios_empleados` WRITE;
/*!40000 ALTER TABLE `horarios_empleados` DISABLE KEYS */;
INSERT INTO `horarios_empleados` VALUES (11,4,'Lunes','09:00:00','18:00:00','14:00:00','15:00:00',1,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo'),(12,4,'Martes','09:00:00','18:00:00','14:00:00','15:00:00',1,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo'),(13,4,'Miércoles','09:00:00','18:00:00','14:00:00','15:00:00',1,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo'),(14,4,'Jueves','09:00:00','18:00:00','14:00:00','15:00:00',1,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo'),(15,4,'Viernes','09:00:00','18:00:00','14:00:00','15:00:00',1,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo'),(16,4,'Sábado','00:00:00','00:00:00',NULL,NULL,0,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo'),(17,4,'Domingo','00:00:00','00:00:00',NULL,NULL,0,NULL,'2025-12-25',NULL,NULL,NULL,'2025-12-25 02:26:03','Activo');
/*!40000 ALTER TABLE `horarios_empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incidencias_empleados`
--

DROP TABLE IF EXISTS `incidencias_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `incidencias_empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `tipo_incidencia` enum('Retardo','Falta','Falta Justificada','Permiso','Incapacidad','Suspensión','Amonestación','Renuncia','Otro') NOT NULL,
  `fecha_incidencia` date NOT NULL,
  `hora_incidencia` time DEFAULT NULL COMMENT 'Para retardos',
  `descripcion` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `tiene_descuento` tinyint(1) DEFAULT 0 COMMENT 'Si aplica descuento en nómina',
  `monto_descuento` decimal(10,2) DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL COMMENT 'Ruta del archivo (incapacidad, justificante)',
  `registrado_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que registró',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `estatus` enum('Activa','Cancelada','Procesada') DEFAULT 'Activa',
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_fecha` (`fecha_incidencia`),
  KEY `idx_tipo` (`tipo_incidencia`),
  CONSTRAINT `fk_incidencia_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de incidencias de empleados';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incidencias_empleados`
--

LOCK TABLES `incidencias_empleados` WRITE;
/*!40000 ALTER TABLE `incidencias_empleados` DISABLE KEYS */;
INSERT INTO `incidencias_empleados` VALUES (1,4,'Falta Justificada','2025-12-24','00:00:00','Se solicitó una baja justificada','Se entrego una solicitud de falta y se otorgo permiso sin descuento',0,0.00,NULL,NULL,'2025-12-24 19:32:46','Activa');
/*!40000 ALTER TABLE `incidencias_empleados` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insumos`
--

DROP TABLE IF EXISTS `insumos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `insumos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL COMMENT 'SKU o código interno',
  `nombre_tecnico` varchar(255) NOT NULL COMMENT 'Nombre técnico/real del producto',
  `alias` varchar(255) DEFAULT NULL COMMENT 'Nombre que usan los trabajadores',
  `marca` varchar(100) DEFAULT NULL COMMENT 'Marca del insumo',
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `unidad_medida` enum('Kg','g','mg','L','mL','Pza','Cubeta','Tambo','Galón','m²','m³','Ton','Servicio','Otro') NOT NULL DEFAULT 'Pza',
  `precio_promedio` decimal(10,2) DEFAULT 0.00 COMMENT 'Precio promedio de todos los proveedores',
  `stock_minimo` decimal(10,2) DEFAULT 0.00,
  `stock_actual` decimal(10,2) DEFAULT 0.00,
  `stock_maximo` decimal(10,2) DEFAULT 0.00,
  `ubicacion_almacen` varchar(100) DEFAULT NULL,
  `ficha_tecnica` varchar(255) DEFAULT NULL COMMENT 'Ruta al PDF',
  `hoja_seguridad` varchar(255) DEFAULT NULL COMMENT 'Para químicos peligrosos',
  `es_peligroso` tinyint(1) DEFAULT 0 COMMENT 'Químico peligroso',
  `requiere_refrigeracion` tinyint(1) DEFAULT 0,
  `vida_util_dias` int(4) DEFAULT NULL COMMENT 'Días de vida útil',
  `observaciones` text DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estatus` enum('Activo','Inactivo','Descontinuado') DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `registrado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_nombre_tecnico` (`nombre_tecnico`),
  KEY `idx_alias` (`alias`),
  KEY `idx_marca` (`marca`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_stock_bajo` (`stock_actual`,`stock_minimo`),
  KEY `idx_insumo_activo` (`estatus`,`nombre_tecnico`),
  KEY `idx_insumo_stock_bajo` (`stock_actual`,`stock_minimo`),
  KEY `idx_insumo_busqueda` (`nombre_tecnico`,`alias`,`marca`),
  CONSTRAINT `fk_insumo_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_insumos` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de insumos para compra (materias primas, materiales, servicios)';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insumos`
--

LOCK TABLES `insumos` WRITE;
/*!40000 ALTER TABLE `insumos` DISABLE KEYS */;
INSERT INTO `insumos` VALUES (1,'INS00001','Pintura Vinílica Blanca','Pintura Vinílica Blanca','COMEX','Base pintura blanca',70,'Cubeta',0.00,5.00,120.00,NULL,NULL,NULL,NULL,0,0,NULL,NULL,NULL,'Activo','2025-12-25 07:31:12',NULL);
/*!40000 ALTER TABLE `insumos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes_movimientos`
--

DROP TABLE IF EXISTS `lotes_movimientos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes_movimientos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lote_id` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida','Ajuste','Merma') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `cantidad_anterior` decimal(10,2) NOT NULL,
  `cantidad_nueva` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `documento_referencia` varchar(100) DEFAULT NULL COMMENT 'Folio de orden de venta, ajuste, etc',
  PRIMARY KEY (`id`),
  KEY `idx_lote` (`lote_id`),
  KEY `idx_tipo` (`tipo_movimiento`),
  KEY `idx_fecha` (`fecha_movimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de lotes para trazabilidad completa';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes_movimientos`
--

LOCK TABLES `lotes_movimientos` WRITE;
/*!40000 ALTER TABLE `lotes_movimientos` DISABLE KEYS */;
/*!40000 ALTER TABLE `lotes_movimientos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lotes_produccion`
--

DROP TABLE IF EXISTS `lotes_produccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `lotes_produccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_barras` varchar(50) NOT NULL,
  `orden_produccion_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `formulacion_id` int(11) DEFAULT NULL,
  `formulacion_version` varchar(50) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad` varchar(20) DEFAULT 'kg',
  `fecha_produccion` datetime NOT NULL,
  `fecha_vencimiento` date DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL COMMENT 'Usuario que produjo el lote',
  `estatus` enum('Producido','En Almacén','Despachado','Merma') DEFAULT 'Producido',
  `ubicacion_almacen` varchar(100) DEFAULT NULL COMMENT 'Ubicación física en almacén',
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo_barras` (`codigo_barras`),
  KEY `idx_codigo_barras` (`codigo_barras`),
  KEY `idx_orden` (`orden_produccion_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_fecha` (`fecha_produccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lotes de productos terminados con códigos de barras para trazabilidad';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lotes_produccion`
--

LOCK TABLES `lotes_produccion` WRITE;
/*!40000 ALTER TABLE `lotes_produccion` DISABLE KEYS */;
/*!40000 ALTER TABLE `lotes_produccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_bancarios`
--

DROP TABLE IF EXISTS `movimientos_bancarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_bancarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cuenta_bancaria_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo_movimiento` enum('Depósito','Retiro','Transferencia','Comisión','Interés','Ajuste') NOT NULL,
  `concepto` varchar(255) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Cheque, transferencia, etc.',
  `monto` decimal(15,2) NOT NULL,
  `saldo` decimal(15,2) DEFAULT NULL COMMENT 'Saldo después del movimiento',
  `poliza_id` int(11) DEFAULT NULL COMMENT 'Póliza contable generada',
  `conciliado` tinyint(1) DEFAULT 0 COMMENT '1=Conciliado con estado de cuenta',
  `fecha_conciliacion` date DEFAULT NULL,
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `poliza_id` (`poliza_id`),
  KEY `idx_cuenta` (`cuenta_bancaria_id`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_conciliado` (`conciliado`),
  CONSTRAINT `movimientos_bancarios_ibfk_1` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`),
  CONSTRAINT `movimientos_bancarios_ibfk_2` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Movimientos bancarios';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_bancarios`
--

LOCK TABLES `movimientos_bancarios` WRITE;
/*!40000 ALTER TABLE `movimientos_bancarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_bancarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida','Ajuste') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de orden, producción, etc.',
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_fecha` (`fecha_movimiento`),
  KEY `idx_tipo` (`tipo_movimiento`),
  CONSTRAINT `fk_mi_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (1,3,'Salida',1.00,'Venta - Orden 3',NULL,'2025-12-25 10:30:53',NULL),(2,3,'Salida',1.00,'Venta - Orden 4',NULL,'2025-12-25 11:25:19',NULL),(3,3,'Salida',10.00,'Venta - Orden 5',NULL,'2025-12-25 11:30:27',NULL),(4,3,'Salida',1.00,'Venta - Orden 6',NULL,'2025-12-25 11:31:20',NULL),(5,3,'Salida',4.00,'Venta - Orden 7',NULL,'2025-12-25 12:37:40',NULL),(6,3,'Salida',4.00,'Venta - Orden 8',NULL,'2025-12-25 12:37:57',NULL),(7,3,'Salida',4.00,'Venta - Orden 9',NULL,'2025-12-25 12:39:20',NULL),(8,3,'Salida',4.00,'Venta - Orden 10',NULL,'2025-12-25 12:40:43',NULL),(9,3,'Salida',4.00,'Venta - Orden 11',NULL,'2025-12-25 12:41:53',NULL),(10,3,'Salida',4.00,'Venta - Orden 12',NULL,'2025-12-25 12:52:36',NULL),(11,3,'Salida',3.00,'Venta - Orden 14',NULL,'2025-12-25 13:40:21',NULL);
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movimientos_productos`
--

DROP TABLE IF EXISTS `movimientos_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida','Ajuste','Produccion','Venta','Devolucion') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_anterior` decimal(10,2) NOT NULL,
  `stock_nuevo` decimal(10,2) NOT NULL,
  `orden_produccion_id` int(11) DEFAULT NULL COMMENT 'Si viene de producción',
  `venta_id` int(11) DEFAULT NULL COMMENT 'Si es una venta',
  `motivo` text DEFAULT NULL,
  `escaneado_barras` tinyint(1) DEFAULT 0 COMMENT 'Si se escaneó código de barras/QR',
  `codigo_escaneado` varchar(100) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_tipo` (`tipo_movimiento`),
  KEY `idx_fecha` (`fecha_movimiento`),
  KEY `idx_movimientos_fecha_tipo` (`fecha_movimiento`,`tipo_movimiento`),
  CONSTRAINT `fk_movprod_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_productos`
--

LOCK TABLES `movimientos_productos` WRITE;
/*!40000 ALTER TABLE `movimientos_productos` DISABLE KEYS */;
/*!40000 ALTER TABLE `movimientos_productos` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_actualizar_stock_producto` AFTER INSERT ON `movimientos_productos` FOR EACH ROW BEGIN
  UPDATE productos 
  SET stock_actual = NEW.stock_nuevo
  WHERE id = NEW.producto_id;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `nomina_detalles`
--

DROP TABLE IF EXISTS `nomina_detalles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nomina_detalles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `salario_base` decimal(10,2) DEFAULT NULL,
  `comisiones` decimal(10,2) DEFAULT 0.00,
  `bonos` decimal(10,2) DEFAULT 0.00,
  `horas_extra` decimal(10,2) DEFAULT 0.00,
  `deducciones_imss` decimal(10,2) DEFAULT 0.00,
  `deducciones_isr` decimal(10,2) DEFAULT 0.00,
  `otras_deducciones` decimal(10,2) DEFAULT 0.00,
  `total_percepciones` decimal(10,2) DEFAULT NULL,
  `total_deducciones` decimal(10,2) DEFAULT NULL,
  `neto_pagar` decimal(10,2) DEFAULT NULL,
  `estatus` enum('Pendiente','Pagada','Cancelada') DEFAULT 'Pendiente',
  `fecha_pago` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empleado_id` (`empleado_id`),
  CONSTRAINT `nomina_detalles_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nomina_detalles`
--

LOCK TABLES `nomina_detalles` WRITE;
/*!40000 ALTER TABLE `nomina_detalles` DISABLE KEYS */;
/*!40000 ALTER TABLE `nomina_detalles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nominas`
--

DROP TABLE IF EXISTS `nominas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nominas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `tipo_nomina` enum('Semanal','Quincenal','Mensual','Extraordinaria','Aguinaldo','Finiquito') NOT NULL,
  `fecha_pago` date NOT NULL,
  `total_percepciones` decimal(15,2) DEFAULT 0.00,
  `total_deducciones` decimal(15,2) DEFAULT 0.00,
  `total_neto` decimal(15,2) DEFAULT 0.00,
  `poliza_id` int(11) DEFAULT NULL COMMENT 'Póliza contable generada',
  `estatus` enum('Borrador','Calculada','Pagada','Cancelada') DEFAULT 'Borrador',
  `observaciones` text DEFAULT NULL,
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  KEY `poliza_id` (`poliza_id`),
  KEY `idx_folio` (`folio`),
  KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`),
  KEY `idx_estatus` (`estatus`),
  CONSTRAINT `nominas_ibfk_1` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Nóminas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nominas`
--

LOCK TABLES `nominas` WRITE;
/*!40000 ALTER TABLE `nominas` DISABLE KEYS */;
INSERT INTO `nominas` VALUES (1,'NOM000001','2025-12-15','2025-12-31','Quincenal','2025-12-25',0.00,0.00,0.00,NULL,'Borrador',NULL,NULL,'2025-12-25 23:57:57');
/*!40000 ALTER TABLE `nominas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nominas_conceptos`
--

DROP TABLE IF EXISTS `nominas_conceptos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nominas_conceptos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomina_detalle_id` int(11) NOT NULL,
  `tipo` enum('Percepción','Deducción') NOT NULL,
  `concepto` varchar(100) NOT NULL COMMENT 'Sueldo, Bono, ISR, IMSS, etc.',
  `monto` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_detalle` (`nomina_detalle_id`),
  CONSTRAINT `nominas_conceptos_ibfk_1` FOREIGN KEY (`nomina_detalle_id`) REFERENCES `nominas_detalle` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Conceptos de percepciones y deducciones';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nominas_conceptos`
--

LOCK TABLES `nominas_conceptos` WRITE;
/*!40000 ALTER TABLE `nominas_conceptos` DISABLE KEYS */;
/*!40000 ALTER TABLE `nominas_conceptos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nominas_detalle`
--

DROP TABLE IF EXISTS `nominas_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `nominas_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomina_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `dias_trabajados` decimal(5,2) DEFAULT 0.00,
  `sueldo_base` decimal(10,2) DEFAULT 0.00,
  `percepciones` decimal(10,2) DEFAULT 0.00 COMMENT 'Total de percepciones',
  `deducciones` decimal(10,2) DEFAULT 0.00 COMMENT 'Total de deducciones',
  `neto` decimal(10,2) DEFAULT 0.00 COMMENT 'Neto a pagar',
  `estatus` enum('Pendiente','Pagado','Cancelado') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_nomina` (`nomina_id`),
  KEY `idx_empleado` (`empleado_id`),
  CONSTRAINT `nominas_detalle_ibfk_1` FOREIGN KEY (`nomina_id`) REFERENCES `nominas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `nominas_detalle_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de nóminas por empleado';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nominas_detalle`
--

LOCK TABLES `nominas_detalle` WRITE;
/*!40000 ALTER TABLE `nominas_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `nominas_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obras`
--

DROP TABLE IF EXISTS `obras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `obras` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'Folio único de la obra (ej: OB-00001)',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre descriptivo de la obra',
  `cliente_id` int(10) unsigned NOT NULL COMMENT 'ID del cliente asociado',
  `direccion` text NOT NULL COMMENT 'Dirección completa de la obra',
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `coordenadas_gps` varchar(100) DEFAULT NULL COMMENT 'Latitud,Longitud (opcional)',
  `area_total` decimal(10,2) DEFAULT NULL COMMENT 'Área total en m²',
  `tipo_superficie` varchar(100) DEFAULT NULL COMMENT 'Tipo de superficie (concreto, metal, etc)',
  `condiciones_ambientales` text DEFAULT NULL COMMENT 'Condiciones especiales del ambiente',
  `especificaciones_tecnicas` text DEFAULT NULL COMMENT 'Especificaciones técnicas generales',
  `costo_estimado` decimal(12,2) DEFAULT 0.00 COMMENT 'Costo estimado de materiales y mano de obra',
  `costo_real` decimal(12,2) DEFAULT 0.00 COMMENT 'Costo real final de la obra',
  `subtotal` decimal(12,2) DEFAULT 0.00 COMMENT 'Subtotal antes de descuentos e impuestos',
  `descuento_porcentaje` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de descuento aplicado',
  `descuento_monto` decimal(12,2) DEFAULT 0.00 COMMENT 'Monto del descuento en pesos',
  `iva_porcentaje` decimal(5,2) DEFAULT 16.00 COMMENT 'Porcentaje de IVA (default 16%)',
  `iva_monto` decimal(12,2) DEFAULT 0.00 COMMENT 'Monto del IVA en pesos',
  `total` decimal(12,2) DEFAULT 0.00 COMMENT 'Total final de la obra',
  `margen_utilidad` decimal(5,2) DEFAULT 0.00 COMMENT 'Margen de utilidad en porcentaje',
  `utilidad_neta` decimal(12,2) DEFAULT 0.00 COMMENT 'Utilidad neta en pesos (total - costo_real)',
  `condiciones_pago` text DEFAULT NULL COMMENT 'Condiciones de pago acordadas',
  `tiempo_entrega` varchar(100) DEFAULT NULL COMMENT 'Tiempo de entrega estimado',
  `anticipo_porcentaje` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de anticipo requerido',
  `anticipo_monto` decimal(12,2) DEFAULT 0.00 COMMENT 'Monto del anticipo en pesos',
  `total_pagado` decimal(12,2) DEFAULT 0.00 COMMENT 'Total pagado hasta el momento',
  `saldo_pendiente` decimal(12,2) DEFAULT 0.00 COMMENT 'Saldo pendiente de pago',
  `estatus_pago` enum('Pendiente','Anticipo Recibido','Parcialmente Pagado','Pagado') DEFAULT 'Pendiente' COMMENT 'Estatus del pago',
  `estatus` enum('Planificación','En Cotización','Aprobada','En Ejecución','Pausada','Completada','Cancelada') NOT NULL DEFAULT 'Planificación',
  `porcentaje_avance` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de avance (0-100)',
  `fecha_inicio_estimada` date DEFAULT NULL,
  `fecha_fin_estimada` date DEFAULT NULL,
  `fecha_inicio_real` date DEFAULT NULL,
  `fecha_fin_real` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL COMMENT 'Descripción general de la obra',
  `notas_internas` text DEFAULT NULL COMMENT 'Notas internas no visibles al cliente',
  `responsable_tecnico_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario responsable técnico',
  `responsable_ventas_id` int(10) unsigned DEFAULT NULL COMMENT 'Usuario responsable de ventas',
  `creado_por` int(10) unsigned NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado_por` int(10) unsigned DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_folio` (`folio`),
  KEY `idx_fecha_creacion` (`fecha_creacion`),
  KEY `idx_obras_cliente_estatus` (`cliente_id`,`estatus`,`activo`),
  KEY `idx_obras_fechas` (`fecha_inicio_estimada`,`fecha_fin_estimada`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla principal de obras para cálculo de materiales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras`
--

LOCK TABLES `obras` WRITE;
/*!40000 ALTER TABLE `obras` DISABLE KEYS */;
INSERT INTO `obras` VALUES (1,'OB-00001','EHWEB',2,'Avenida Amores, NO 605 Int A','CDMX','BENITO JUAREZ','04000',NULL,10.00,'Tablaroca',NULL,NULL,50000.00,0.00,5000.00,0.00,0.00,16.00,800.00,5800.00,100.00,5800.00,'50% anticipo','25 días hábiles',10.00,580.00,0.00,0.00,'Pendiente','Planificación',0.00,'2025-12-28','2025-12-31',NULL,NULL,'Prueba de nueva obnra',NULL,NULL,NULL,1,'2025-12-25 18:43:40',NULL,'2025-12-25 19:02:30',1),(2,'OB-00002','Hospital Luz',2,'Amores 50, int b','CDMX','BENITO JUAREZ','04000',NULL,25.00,'Rugosa','Humedad alta','',35000.00,0.00,36000.00,0.00,0.00,16.00,5760.00,41760.00,100.00,41760.00,'','15',10.00,4176.00,2000.00,39760.00,'Anticipo Recibido','En Ejecución',10.00,'2025-12-25','2026-01-11',NULL,NULL,'Prueba',NULL,NULL,NULL,1,'2025-12-25 19:21:42',1,'2025-12-25 19:47:28',1);
/*!40000 ALTER TABLE `obras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obras_archivos`
--

DROP TABLE IF EXISTS `obras_archivos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `obras_archivos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `obra_id` int(10) unsigned NOT NULL,
  `nombre_original` varchar(255) NOT NULL COMMENT 'Nombre original del archivo',
  `nombre_archivo` varchar(255) NOT NULL COMMENT 'Nombre del archivo en el servidor',
  `ruta_archivo` varchar(500) NOT NULL COMMENT 'Ruta completa del archivo',
  `tipo_archivo` varchar(100) NOT NULL COMMENT 'Tipo MIME del archivo',
  `extension` varchar(10) NOT NULL COMMENT 'Extensión del archivo',
  `tamano` int(10) unsigned NOT NULL COMMENT 'Tamaño en bytes',
  `categoria` enum('Foto','Plano','CAD','Documento','Otro') NOT NULL DEFAULT 'Otro',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción del archivo',
  `etiquetas` varchar(500) DEFAULT NULL COMMENT 'Etiquetas separadas por comas',
  `subido_por` int(10) unsigned NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_obra` (`obra_id`),
  KEY `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archivos adjuntos asociados a las obras';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras_archivos`
--

LOCK TABLES `obras_archivos` WRITE;
/*!40000 ALTER TABLE `obras_archivos` DISABLE KEYS */;
/*!40000 ALTER TABLE `obras_archivos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obras_comentarios`
--

DROP TABLE IF EXISTS `obras_comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `obras_comentarios` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `obra_id` int(10) unsigned NOT NULL,
  `comentario` text NOT NULL,
  `tipo` enum('General','Técnico','Avance','Problema','Solución') NOT NULL DEFAULT 'General',
  `usuario_id` int(10) unsigned NOT NULL,
  `fecha_comentario` datetime NOT NULL DEFAULT current_timestamp(),
  `editado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_edicion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_obra` (`obra_id`),
  KEY `idx_fecha` (`fecha_comentario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comentarios y seguimiento de las obras';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras_comentarios`
--

LOCK TABLES `obras_comentarios` WRITE;
/*!40000 ALTER TABLE `obras_comentarios` DISABLE KEYS */;
/*!40000 ALTER TABLE `obras_comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obras_pagos`
--

DROP TABLE IF EXISTS `obras_pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `obras_pagos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `obra_id` int(11) NOT NULL COMMENT 'ID de la obra',
  `folio_recibo` varchar(50) NOT NULL COMMENT 'Folio del recibo (REC-00001)',
  `fecha_pago` datetime NOT NULL COMMENT 'Fecha y hora del pago',
  `monto` decimal(12,2) NOT NULL COMMENT 'Monto del pago',
  `metodo_pago` enum('Efectivo','Transferencia','Cheque','Tarjeta','Otro') DEFAULT 'Transferencia' COMMENT 'Método de pago',
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Referencia bancaria o número de cheque',
  `concepto` varchar(255) DEFAULT NULL COMMENT 'Concepto del pago (Anticipo, Pago parcial, Liquidación)',
  `notas` text DEFAULT NULL COMMENT 'Notas adicionales del pago',
  `recibido_por` int(11) DEFAULT NULL COMMENT 'ID del usuario que registró el pago',
  `fecha_registro` datetime NOT NULL COMMENT 'Fecha de registro del pago',
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Pago activo (para cancelaciones)',
  PRIMARY KEY (`id`),
  KEY `idx_obra_id` (`obra_id`),
  KEY `idx_folio_recibo` (`folio_recibo`),
  KEY `idx_fecha_pago` (`fecha_pago`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos recibidos de obras';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras_pagos`
--

LOCK TABLES `obras_pagos` WRITE;
/*!40000 ALTER TABLE `obras_pagos` DISABLE KEYS */;
INSERT INTO `obras_pagos` VALUES (1,2,'REC-00001','2025-12-25 19:23:32',2000.00,'Transferencia','','Pago Parcial','Pago 1',1,'2025-12-25 19:23:32',1);
/*!40000 ALTER TABLE `obras_pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obras_productos`
--

DROP TABLE IF EXISTS `obras_productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `obras_productos` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `obra_id` int(10) unsigned NOT NULL,
  `producto_id` int(10) unsigned NOT NULL COMMENT 'ID del producto del catálogo',
  `cantidad_calculada` decimal(10,2) NOT NULL COMMENT 'Cantidad calculada necesaria',
  `cantidad_ajustada` decimal(10,2) DEFAULT NULL COMMENT 'Cantidad ajustada manualmente',
  `cantidad_entregada` decimal(10,2) DEFAULT 0.00 COMMENT 'Cantidad ya entregada',
  `unidad` varchar(50) NOT NULL COMMENT 'Unidad de medida',
  `area_aplicacion` decimal(10,2) DEFAULT NULL COMMENT 'Área donde se aplicará en m²',
  `rendimiento_teorico` decimal(10,2) DEFAULT NULL COMMENT 'Rendimiento teórico del producto',
  `factor_desperdicio` decimal(5,2) DEFAULT 1.10 COMMENT 'Factor de desperdicio (ej: 1.10 = 10% extra)',
  `notas` text DEFAULT NULL COMMENT 'Notas sobre este producto en la obra',
  `seccion_obra` varchar(100) DEFAULT NULL COMMENT 'Sección de la obra donde se usará',
  `precio_unitario` decimal(10,2) DEFAULT NULL COMMENT 'Precio unitario de referencia',
  `formulacion_id` int(11) DEFAULT NULL COMMENT 'ID de la formulación utilizada',
  `formulacion_version` decimal(3,1) DEFAULT NULL COMMENT 'Versión de la formulación (ej: 1.0, 2.5)',
  `fecha_agregado` datetime NOT NULL DEFAULT current_timestamp(),
  `agregado_por` int(10) unsigned NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_obra` (`obra_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_formulacion` (`formulacion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Productos y materiales calculados para cada obra';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras_productos`
--

LOCK TABLES `obras_productos` WRITE;
/*!40000 ALTER TABLE `obras_productos` DISABLE KEYS */;
INSERT INTO `obras_productos` VALUES (2,1,3,1.10,10.00,0.00,'Cubeta',5.00,5.00,1.10,'Prueba','sala 1',500.00,NULL,NULL,'2025-12-25 19:02:30',1,NULL),(3,2,3,2.75,2.00,0.00,'Cubeta',25.00,10.00,1.10,'2 cubetas','Sala 1',500.00,4,4.0,'2025-12-25 19:22:35',1,'2025-12-25 19:42:53');
/*!40000 ALTER TABLE `obras_productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_compra`
--

DROP TABLE IF EXISTS `ordenes_compra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_compra` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'Folio único de la orden',
  `proveedor_id` int(11) NOT NULL,
  `fecha_orden` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `fecha_entrega_real` date DEFAULT NULL,
  `subtotal` decimal(12,2) DEFAULT 0.00,
  `iva` decimal(12,2) DEFAULT 0.00,
  `total` decimal(12,2) DEFAULT 0.00,
  `forma_pago` enum('Efectivo','Transferencia','Cheque','Crédito') DEFAULT 'Transferencia',
  `condiciones_pago` varchar(255) DEFAULT NULL COMMENT 'Ej: 50% anticipo, 50% contra entrega',
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Borrador','Enviada','Confirmada','En Tránsito','Recibida Parcial','Recibida','Cancelada') DEFAULT 'Borrador',
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `aprobado_por` int(11) DEFAULT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_folio` (`folio`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_fecha` (`fecha_orden`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_oc_pendiente` (`estatus`,`fecha_orden`),
  CONSTRAINT `fk_oc_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de compra a proveedores';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_compra`
--

LOCK TABLES `ordenes_compra` WRITE;
/*!40000 ALTER TABLE `ordenes_compra` DISABLE KEYS */;
INSERT INTO `ordenes_compra` VALUES (1,'OC-2025-0001',1,'2025-12-25','2025-12-27','2025-12-25',500.00,80.00,580.00,'Transferencia','','','Recibida',NULL,'2025-12-25 08:10:23',NULL,'2025-12-25 08:11:06'),(2,'OC-2025-0002',1,'2025-12-25','2025-12-26','2025-12-25',2000.00,320.00,2320.00,'Transferencia','','','Recibida Parcial',NULL,'2025-12-25 08:13:11',NULL,'2025-12-25 08:14:26');
/*!40000 ALTER TABLE `ordenes_compra` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ordenes_produccion`
--

DROP TABLE IF EXISTS `ordenes_produccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_produccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'OP-2025-0001',
  `producto_id` int(11) NOT NULL,
  `formulacion_id` int(11) NOT NULL COMMENT 'Versión de formulación usada',
  `cantidad_programada` decimal(10,2) NOT NULL,
  `cantidad_producida` decimal(10,2) DEFAULT 0.00,
  `unidad_medida` enum('L','ml','Kg','g','Pza') DEFAULT 'L',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_inicio` datetime DEFAULT NULL,
  `fecha_fin` datetime DEFAULT NULL,
  `costo_unitario_estimado` decimal(10,2) DEFAULT 0.00,
  `costo_total_estimado` decimal(10,2) DEFAULT 0.00,
  `costo_real` decimal(10,2) DEFAULT 0.00,
  `estatus` enum('Pendiente','En Proceso','Completada','Cancelada') DEFAULT 'Pendiente',
  `creado_por` int(11) DEFAULT NULL,
  `iniciado_por` int(11) DEFAULT NULL,
  `finalizado_por` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_completado` datetime DEFAULT NULL COMMENT 'Fecha cuando se completa la producción',
  `orden_venta_id` int(11) DEFAULT NULL COMMENT 'Orden de venta que generó esta producción',
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  UNIQUE KEY `uk_folio` (`folio`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_formulacion` (`formulacion_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  CONSTRAINT `fk_op_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`),
  CONSTRAINT `fk_op_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de producción';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_produccion`
--

LOCK TABLES `ordenes_produccion` WRITE;
/*!40000 ALTER TABLE `ordenes_produccion` DISABLE KEYS */;
/*!40000 ALTER TABLE `ordenes_produccion` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `tr_op_actualizar_solicitudes` AFTER UPDATE ON `ordenes_produccion` FOR EACH ROW BEGIN
    IF NEW.estatus = 'En Proceso' THEN
        UPDATE solicitudes_produccion 
        SET estatus = 'En Proceso', fecha_inicio_produccion = NOW()
        WHERE orden_produccion_id = NEW.id;
    ELSEIF NEW.estatus = 'Completada' THEN
        UPDATE solicitudes_produccion 
        SET estatus = 'Completada', fecha_fin_produccion = NOW(), cantidad_producida = cantidad_solicitada
        WHERE orden_produccion_id = NEW.id;
    ELSEIF NEW.estatus = 'Cancelada' THEN
        UPDATE solicitudes_produccion 
        SET estatus = 'Pendiente', orden_produccion_id = NULL
        WHERE orden_produccion_id = NEW.id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `ordenes_venta`
--

DROP TABLE IF EXISTS `ordenes_venta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ordenes_venta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'OV-2025-0001',
  `cliente_id` int(11) NOT NULL,
  `fecha_orden` date NOT NULL,
  `fecha_entrega_estimada` date DEFAULT NULL,
  `fecha_entrega_real` date DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `iva` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `monto_pagado` decimal(10,2) DEFAULT 0.00,
  `saldo_pendiente` decimal(10,2) DEFAULT 0.00,
  `estatus_pago` enum('Pendiente','Parcial','Pagado') DEFAULT 'Pendiente',
  `forma_pago` enum('Efectivo','Transferencia','Cheque','Tarjeta','Crédito') DEFAULT 'Efectivo',
  `descuento_id` int(11) DEFAULT NULL,
  `descuento_nombre` varchar(100) DEFAULT NULL,
  `descuento_tipo` varchar(20) DEFAULT NULL,
  `descuento_valor` decimal(10,2) DEFAULT 0.00,
  `descuento_aplicado` decimal(10,2) DEFAULT 0.00,
  `condiciones_pago` varchar(200) DEFAULT NULL COMMENT 'Ej: 50% anticipo, 50% contra entrega',
  `estatus` enum('Cotización','Confirmada','En Preparación','Entregada','Cancelada') DEFAULT 'Cotización',
  `tipo_venta` enum('Mostrador','Pedido') DEFAULT 'Mostrador',
  `observaciones` text DEFAULT NULL,
  `motivo_cancelacion` text DEFAULT NULL,
  `requiere_produccion` tinyint(1) DEFAULT 0 COMMENT 'Si algún producto no tiene stock',
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `direccion_envio` text DEFAULT NULL COMMENT 'Dirección de entrega para pedidos (no mostrador)',
  `costo_envio` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo adicional por envío a domicilio',
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  UNIQUE KEY `uk_folio` (`folio`),
  KEY `idx_cliente` (`cliente_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_fecha` (`fecha_orden`),
  KEY `idx_tipo_venta` (`tipo_venta`),
  KEY `idx_ordenes_fecha_estatus` (`fecha_orden`,`estatus`),
  KEY `idx_descuento` (`descuento_id`),
  CONSTRAINT `fk_ov_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de venta y cotizaciones';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_venta`
--

LOCK TABLES `ordenes_venta` WRITE;
/*!40000 ALTER TABLE `ordenes_venta` DISABLE KEYS */;
INSERT INTO `ordenes_venta` VALUES (1,'OV-2025-0001',1,'2025-12-25',NULL,NULL,500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 10:29:20','2025-12-25 11:29:36',NULL,0.00),(2,'OV-2025-0002',1,'2025-12-25',NULL,NULL,500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 10:30:00','2025-12-25 11:29:36',NULL,0.00),(3,'OV-2025-0003',1,'2025-12-25',NULL,'2025-12-25',500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 10:30:53','2025-12-25 11:29:36',NULL,0.00),(4,'OV-2025-0004',2,'2025-12-25',NULL,'2025-12-25',500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 11:25:19','2025-12-25 11:29:36',NULL,0.00),(5,'OV-2025-0005',2,'2025-12-25',NULL,'2025-12-25',5000.00,800.00,5800.00,5800.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 11:30:27','2025-12-25 11:30:27',NULL,0.00),(6,'OV-2025-0006',1,'2025-12-25',NULL,'2025-12-25',500.00,80.00,580.00,0.00,0.00,'Pendiente','Crédito',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 11:31:20','2025-12-25 11:31:20',NULL,0.00),(7,'OV-2025-0007',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:37:40','2025-12-25 12:37:40',NULL,0.00),(8,'OV-2025-0008',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:37:56','2025-12-25 12:37:57',NULL,0.00),(9,'OV-2025-0009',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:39:20','2025-12-25 12:39:20',NULL,0.00),(10,'OV-2025-0010',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,2320.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:40:43','2025-12-25 12:40:43',NULL,0.00),(11,'OV-2025-0011',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,2320.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:41:53','2025-12-25 12:41:53',NULL,0.00),(12,'OV-2025-0012',2,'2025-12-25',NULL,'2025-12-25',2000.00,288.00,2088.00,0.00,0.00,'Pendiente','Crédito',1,'Descuento Cliente Frecuente','Porcentaje',10.00,200.00,NULL,'Entregada','Mostrador','Prueba de pago pendiente',NULL,0,NULL,'2025-12-25 12:52:36','2025-12-25 12:52:36',NULL,0.00),(13,'OV-2025-0013',2,'2025-12-25',NULL,NULL,1000.00,160.00,1160.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'En Preparación','Mostrador','prueba 2 con formulación y pago pendiente o cotización',NULL,1,NULL,'2025-12-25 13:06:24','2025-12-25 13:07:31',NULL,0.00),(14,'OV-2025-0014',2,'2025-12-25','2025-12-27','2025-12-25',1500.00,240.00,1740.00,0.00,0.00,'Pendiente','Crédito',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Pedido','Prueba de compra a crédito',NULL,0,NULL,'2025-12-25 13:40:21','2025-12-25 13:40:21','Av. Juárez 2915, Int. 301, Col. La Paz, C.P. 72160, Puebla, Puebla, México.',100.00),(15,'OV-2025-0015',2,'2025-12-25','2025-12-27',NULL,2500.00,360.00,2610.00,0.00,2610.00,'Pendiente','Crédito',1,'Descuento Cliente Frecuente','Porcentaje',10.00,250.00,NULL,'Confirmada','Pedido','prueba  con envio y a credito',NULL,0,NULL,'2025-12-25 13:50:13','2025-12-25 13:50:13','Av. Juárez 2915, Int. 301, Col. La Paz, C.P. 72160, Puebla, Puebla, México.',100.00);
/*!40000 ALTER TABLE `ordenes_venta` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `trg_ordenes_venta_calcular_totales` BEFORE UPDATE ON `ordenes_venta` FOR EACH ROW BEGIN
    -- Calcular descuento aplicado
    IF NEW.descuento_tipo = 'Porcentaje' THEN
        SET NEW.descuento_aplicado = NEW.subtotal * (NEW.descuento_valor / 100);
    ELSEIF NEW.descuento_tipo = 'Monto Fijo' THEN
        SET NEW.descuento_aplicado = NEW.descuento_valor;
    ELSE
        SET NEW.descuento_aplicado = 0;
    END IF;
    
    -- Recalcular IVA y total con descuento
    SET NEW.iva = (NEW.subtotal - NEW.descuento_aplicado) * 0.16;
    SET NEW.total = NEW.subtotal - NEW.descuento_aplicado + NEW.iva;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pagos_ordenes`
--

DROP TABLE IF EXISTS `pagos_ordenes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_ordenes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_venta_id` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL COMMENT 'PAG-2025-0001',
  `fecha_pago` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Tarjeta','Cheque') NOT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de cheque, transferencia, etc.',
  `notas` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  UNIQUE KEY `uk_folio` (`folio`),
  KEY `idx_orden` (`orden_venta_id`),
  KEY `idx_fecha` (`fecha_pago`),
  CONSTRAINT `fk_pago_orden` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos aplicados a órdenes de venta';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_ordenes`
--

LOCK TABLES `pagos_ordenes` WRITE;
/*!40000 ALTER TABLE `pagos_ordenes` DISABLE KEYS */;
INSERT INTO `pagos_ordenes` VALUES (1,5,'PAG-2025-0001','2025-12-25',5800.00,'Efectivo','Venta Mostrador','Pago automático al generar venta','2025-12-25 11:30:27'),(2,10,'PAG-2025-0002','2025-12-25',2320.00,'Efectivo','Pago automático POS','Pago registrado automáticamente al cobrar en POS','2025-12-25 12:40:43'),(3,11,'PAG-2025-0003','2025-12-25',2320.00,'Efectivo','Pago automático POS','Pago registrado automáticamente al cobrar en POS','2025-12-25 12:41:53');
/*!40000 ALTER TABLE `pagos_ordenes` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_unicode_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER `trg_pago_actualizar_orden` AFTER INSERT ON `pagos_ordenes` FOR EACH ROW BEGIN
    DECLARE total_orden DECIMAL(10,2);
    DECLARE nuevo_monto_pagado DECIMAL(10,2);
    DECLARE nuevo_saldo DECIMAL(10,2);
    DECLARE nuevo_estatus VARCHAR(20);
    
    -- Obtener total de la orden
    SELECT total INTO total_orden
    FROM ordenes_venta
    WHERE id = NEW.orden_venta_id;
    
    -- Calcular nuevo monto pagado
    SELECT COALESCE(SUM(monto), 0) INTO nuevo_monto_pagado
    FROM pagos_ordenes
    WHERE orden_venta_id = NEW.orden_venta_id;
    
    -- Calcular saldo pendiente
    SET nuevo_saldo = total_orden - nuevo_monto_pagado;
    
    -- Determinar estatus de pago
    IF nuevo_saldo <= 0 THEN
        SET nuevo_estatus = 'Pagado';
    ELSEIF nuevo_monto_pagado > 0 THEN
        SET nuevo_estatus = 'Parcial';
    ELSE
        SET nuevo_estatus = 'Pendiente';
    END IF;
    
    -- Actualizar orden
    UPDATE ordenes_venta
    SET monto_pagado = nuevo_monto_pagado,
        saldo_pendiente = nuevo_saldo,
        estatus_pago = nuevo_estatus
    WHERE id = NEW.orden_venta_id;
    
    -- Actualizar saldo del cliente
    UPDATE clientes c
    SET c.saldo_pendiente = (
        SELECT COALESCE(SUM(ov.saldo_pendiente), 0)
        FROM ordenes_venta ov
        WHERE ov.cliente_id = c.id
        AND ov.estatus != 'Cancelada'
    )
    WHERE c.id = (SELECT cliente_id FROM ordenes_venta WHERE id = NEW.orden_venta_id);
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `pagos_servicios_recurrentes`
--

DROP TABLE IF EXISTS `pagos_servicios_recurrentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos_servicios_recurrentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `servicio_recurrente_id` int(11) NOT NULL,
  `periodo` varchar(7) NOT NULL COMMENT 'Periodo en formato YYYY-MM',
  `fecha_vencimiento` date NOT NULL,
  `fecha_pago` date DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de referencia o factura',
  `estatus` enum('Pendiente','Pagado','Vencido','Cancelado') DEFAULT 'Pendiente',
  `poliza_id` int(11) DEFAULT NULL COMMENT 'ID de la póliza generada',
  `movimiento_bancario_id` int(11) DEFAULT NULL COMMENT 'ID del movimiento bancario',
  `notas` text DEFAULT NULL,
  `usuario_registro` int(11) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_servicio_periodo` (`servicio_recurrente_id`,`periodo`),
  KEY `idx_servicio` (`servicio_recurrente_id`),
  KEY `idx_periodo` (`periodo`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_fecha_vencimiento` (`fecha_vencimiento`),
  KEY `idx_poliza` (`poliza_id`),
  KEY `idx_fecha_pago` (`fecha_pago`),
  KEY `idx_servicio_estatus` (`servicio_recurrente_id`,`estatus`),
  CONSTRAINT `fk_pago_servicio` FOREIGN KEY (`servicio_recurrente_id`) REFERENCES `servicios_recurrentes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de pagos de servicios recurrentes';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos_servicios_recurrentes`
--

LOCK TABLES `pagos_servicios_recurrentes` WRITE;
/*!40000 ALTER TABLE `pagos_servicios_recurrentes` DISABLE KEYS */;
/*!40000 ALTER TABLE `pagos_servicios_recurrentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `periodos_contables`
--

DROP TABLE IF EXISTS `periodos_contables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `periodos_contables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ejercicio_id` int(11) NOT NULL,
  `numero_periodo` int(11) NOT NULL COMMENT 'Número del mes (1-12)',
  `nombre` varchar(50) NOT NULL COMMENT 'Enero, Febrero, etc.',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estatus` enum('Abierto','Cerrado') DEFAULT 'Abierto',
  `fecha_cierre` datetime DEFAULT NULL,
  `usuario_cierre` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_periodo` (`ejercicio_id`,`numero_periodo`),
  KEY `idx_ejercicio` (`ejercicio_id`),
  KEY `idx_estatus` (`estatus`),
  CONSTRAINT `periodos_contables_ibfk_1` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicios_fiscales` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Periodos contables mensuales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `periodos_contables`
--

LOCK TABLES `periodos_contables` WRITE;
/*!40000 ALTER TABLE `periodos_contables` DISABLE KEYS */;
INSERT INTO `periodos_contables` VALUES (1,1,1,'Enero','2025-01-01','2025-01-31','Abierto',NULL,NULL),(2,1,2,'Febrero','2025-02-01','2025-02-28','Abierto',NULL,NULL),(3,1,3,'Marzo','2025-03-01','2025-03-31','Abierto',NULL,NULL),(4,1,4,'Abril','2025-04-01','2025-04-30','Abierto',NULL,NULL),(5,1,5,'Mayo','2025-05-01','2025-05-31','Abierto',NULL,NULL),(6,1,6,'Junio','2025-06-01','2025-06-30','Abierto',NULL,NULL),(7,1,7,'Julio','2025-07-01','2025-07-31','Abierto',NULL,NULL),(8,1,8,'Agosto','2025-08-01','2025-08-31','Abierto',NULL,NULL),(9,1,9,'Septiembre','2025-09-01','2025-09-30','Abierto',NULL,NULL),(10,1,10,'Octubre','2025-10-01','2025-10-31','Abierto',NULL,NULL),(11,1,11,'Noviembre','2025-11-01','2025-11-30','Abierto',NULL,NULL),(12,1,12,'Diciembre','2025-12-01','2025-12-31','Abierto',NULL,NULL);
/*!40000 ALTER TABLE `periodos_contables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `polizas`
--

DROP TABLE IF EXISTS `polizas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `polizas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'Folio de la póliza',
  `tipo_poliza` enum('Diario','Ingresos','Egresos','Cheque') NOT NULL COMMENT 'Tipo de póliza',
  `fecha` date NOT NULL COMMENT 'Fecha de la póliza',
  `periodo_id` int(11) NOT NULL COMMENT 'Periodo contable',
  `concepto` text NOT NULL COMMENT 'Concepto general de la póliza',
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Referencia externa',
  `origen` varchar(50) DEFAULT NULL COMMENT 'Módulo origen: ventas, compras, nomina, manual',
  `origen_id` int(11) DEFAULT NULL COMMENT 'ID del registro origen',
  `total_debe` decimal(15,2) DEFAULT 0.00 COMMENT 'Total de cargos',
  `total_haber` decimal(15,2) DEFAULT 0.00 COMMENT 'Total de abonos',
  `estatus` enum('Borrador','Autorizada','Cancelada') DEFAULT 'Borrador',
  `usuario_creacion` int(11) DEFAULT NULL,
  `usuario_autorizacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_autorizacion` datetime DEFAULT NULL,
  `motivo_cancelacion` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  KEY `idx_folio` (`folio`),
  KEY `idx_fecha` (`fecha`),
  KEY `idx_tipo` (`tipo_poliza`),
  KEY `idx_periodo` (`periodo_id`),
  KEY `idx_origen` (`origen`,`origen_id`),
  KEY `idx_estatus` (`estatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pólizas contables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `polizas`
--

LOCK TABLES `polizas` WRITE;
/*!40000 ALTER TABLE `polizas` DISABLE KEYS */;
/*!40000 ALTER TABLE `polizas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `polizas_detalle`
--

DROP TABLE IF EXISTS `polizas_detalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `polizas_detalle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `poliza_id` int(11) NOT NULL COMMENT 'ID de la póliza',
  `cuenta_id` int(11) NOT NULL COMMENT 'ID de la cuenta contable',
  `concepto` varchar(255) DEFAULT NULL COMMENT 'Concepto del movimiento',
  `debe` decimal(15,2) DEFAULT 0.00 COMMENT 'Monto del cargo',
  `haber` decimal(15,2) DEFAULT 0.00 COMMENT 'Monto del abono',
  `auxiliar_tipo` varchar(50) DEFAULT NULL COMMENT 'Tipo de auxiliar',
  `auxiliar_id` int(11) DEFAULT NULL COMMENT 'ID del auxiliar',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de visualización',
  PRIMARY KEY (`id`),
  KEY `idx_poliza` (`poliza_id`),
  KEY `idx_cuenta` (`cuenta_id`),
  CONSTRAINT `polizas_detalle_ibfk_1` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `polizas_detalle_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de pólizas contables';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `polizas_detalle`
--

LOCK TABLES `polizas_detalle` WRITE;
/*!40000 ALTER TABLE `polizas_detalle` DISABLE KEYS */;
/*!40000 ALTER TABLE `polizas_detalle` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `presentaciones_producto`
--

DROP TABLE IF EXISTS `presentaciones_producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `presentaciones_producto` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL COMMENT 'Ej: Cubeta 19L, Cubeta 4L, Galon',
  `contenido` decimal(10,2) NOT NULL,
  `unidad` enum('L','ml','Kg','g','Pza') NOT NULL,
  `codigo_barras` varchar(50) DEFAULT NULL COMMENT 'Código de barras específico de esta presentación',
  `precio_venta` decimal(10,2) DEFAULT 0.00,
  `es_principal` tinyint(1) DEFAULT 0,
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_pres_codigo_barras` (`codigo_barras`),
  KEY `idx_producto` (`producto_id`),
  CONSTRAINT `fk_pres_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Presentaciones de productos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `presentaciones_producto`
--

LOCK TABLES `presentaciones_producto` WRITE;
/*!40000 ALTER TABLE `presentaciones_producto` DISABLE KEYS */;
/*!40000 ALTER TABLE `presentaciones_producto` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `privilege`
--

DROP TABLE IF EXISTS `privilege`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `privilege` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin` tinyint(4) NOT NULL,
  `permiso` varchar(100) NOT NULL,
  `valor` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_admin_priv` (`admin`),
  CONSTRAINT `fk_privilege_admin` FOREIGN KEY (`admin`) REFERENCES `administradores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `privilege`
--

LOCK TABLES `privilege` WRITE;
/*!40000 ALTER TABLE `privilege` DISABLE KEYS */;
INSERT INTO `privilege` VALUES (87,1,'user_add',1),(88,1,'user_edit',1),(89,1,'user_consult',1),(90,1,'user_delete',1),(91,1,'user_bitacora',1),(92,1,'rh_empleados_add',1),(93,1,'rh_empleados_edit',1),(94,1,'rh_empleados_consult',1),(95,1,'rh_empleados_delete',1),(96,1,'rh_departamentos',1),(97,1,'clientes_add',1),(98,1,'clientes_edit',1),(99,1,'clientes_consult',1),(100,1,'clientes_delete',1),(101,1,'proveedores_add',1),(102,1,'proveedores_edit',1),(103,1,'proveedores_consult',1),(104,1,'proveedores_delete',1),(105,1,'ventas_ordenes_add',1),(106,1,'ventas_ordenes_edit',1),(107,1,'ventas_ordenes_consult',1),(108,1,'ventas_ordenes_delete',1),(109,1,'ventas_cotizaciones',1),(110,1,'compras_ordenes_add',1),(111,1,'compras_ordenes_edit',1),(112,1,'compras_ordenes_consult',1),(113,1,'compras_ordenes_delete',1),(114,1,'compras_recepcion',1),(115,1,'produccion_productos_add',1),(116,1,'produccion_productos_edit',1),(117,1,'produccion_productos_consult',1),(118,1,'produccion_formulaciones',1),(119,1,'produccion_ordenes',1),(120,1,'produccion_ver_costos',1),(121,1,'almacen_inventario_consult',1),(122,1,'almacen_ajustes',1),(123,1,'almacen_entregas',1),(124,1,'almacen_movimientos',1),(125,1,'almacen_insumos',1),(126,1,'obras_add',1),(127,1,'obras_edit',1),(128,1,'obras_consult',1),(129,1,'obras_delete',1),(130,1,'obras_pagos',1),(131,1,'contabilidad_cuentas',1),(132,1,'contabilidad_polizas',1),(133,1,'contabilidad_nomina',1),(134,1,'contabilidad_reportes',1),(135,1,'contabilidad_gastos',1),(136,1,'contabilidad_ingresos',1),(137,1,'reportes_ventas',1),(138,1,'reportes_compras',1),(139,1,'reportes_inventario',1),(140,1,'reportes_produccion',1),(141,1,'reportes_financieros',1),(142,1,'reportes_obras',1),(143,1,'dashboard_main',1),(144,1,'dashboard_ventas',1),(145,1,'dashboard_produccion',1),(146,1,'dashboard_almacen',1);
/*!40000 ALTER TABLE `privilege` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `produccion_historial_estatus`
--

DROP TABLE IF EXISTS `produccion_historial_estatus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produccion_historial_estatus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orden_produccion_id` int(11) NOT NULL,
  `estatus_anterior` varchar(50) DEFAULT NULL,
  `estatus_nuevo` varchar(50) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_cambio` datetime DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_orden` (`orden_produccion_id`),
  KEY `idx_fecha` (`fecha_cambio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Historial de cambios de estatus en órdenes de producción';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `produccion_historial_estatus`
--

LOCK TABLES `produccion_historial_estatus` WRITE;
/*!40000 ALTER TABLE `produccion_historial_estatus` DISABLE KEYS */;
/*!40000 ALTER TABLE `produccion_historial_estatus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productos`
--

DROP TABLE IF EXISTS `productos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `productos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `producto_padre_id` int(11) DEFAULT NULL COMMENT 'ID del producto base. NULL si es producto independiente o base de familia',
  `es_variante` tinyint(1) DEFAULT 0 COMMENT '0=Producto base o independiente, 1=Variante de otro producto',
  `variante_tipo` varchar(50) DEFAULT NULL COMMENT 'Tipo de variación: color, tamaño, acabado, etc.',
  `variante_valor` varchar(100) DEFAULT NULL COMMENT 'Valor específico de la variante: Rojo, 30x30, Mate, etc.',
  `codigo` varchar(50) NOT NULL COMMENT 'Código único del producto (PROD-0001)',
  `nombre` varchar(200) NOT NULL,
  `alias` varchar(200) DEFAULT NULL COMMENT 'Nombre alternativo del producto',
  `descripcion` text DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `tipo_producto` enum('Fabricado','Reventa') NOT NULL DEFAULT 'Fabricado',
  `unidad_venta` enum('Cubeta','Litro','Galon','Kg','Pieza','Caja','Metro','M2') DEFAULT 'Cubeta',
  `presentacion_principal` varchar(50) DEFAULT NULL COMMENT 'Ej: 19L, 4L, 1L',
  `contenido_neto` decimal(10,2) DEFAULT NULL COMMENT 'Contenido en unidad base',
  `unidad_contenido` enum('L','ml','Kg','g','Pza') DEFAULT 'L',
  `codigo_barras` varchar(50) DEFAULT NULL COMMENT 'Código de barras EAN-13, UPC, etc.',
  `codigo_qr` text DEFAULT NULL COMMENT 'Datos para generar QR (JSON o texto)',
  `sku` varchar(50) DEFAULT NULL COMMENT 'SKU interno',
  `stock_actual` decimal(10,2) DEFAULT 0.00,
  `stock_minimo` decimal(10,2) DEFAULT 0.00,
  `stock_maximo` decimal(10,2) DEFAULT 0.00,
  `ubicacion_almacen` varchar(100) DEFAULT NULL,
  `costo_produccion` decimal(10,2) DEFAULT 0.00 COMMENT 'Calculado de formulación o costo de compra',
  `precio_venta` decimal(10,2) DEFAULT 0.00,
  `margen_utilidad` decimal(5,2) DEFAULT 0.00 COMMENT 'Porcentaje de utilidad',
  `foto_producto` varchar(255) DEFAULT NULL COMMENT 'Ruta de la foto',
  `ficha_tecnica` varchar(255) DEFAULT NULL COMMENT 'Ruta del PDF de ficha técnica',
  `hoja_seguridad` varchar(255) DEFAULT NULL COMMENT 'Ruta del PDF de hoja de seguridad',
  `peso_bruto` decimal(10,2) DEFAULT NULL COMMENT 'Peso con envase en Kg',
  `rendimiento` varchar(100) DEFAULT NULL COMMENT 'Ej: 10-12 m2/L',
  `tiempo_secado` varchar(100) DEFAULT NULL COMMENT 'Ej: 2-4 horas',
  `colores_disponibles` text DEFAULT NULL COMMENT 'JSON o texto separado por comas',
  `caracteristicas` text DEFAULT NULL COMMENT 'Características técnicas del producto',
  `texturas` varchar(255) DEFAULT NULL COMMENT 'Texturas disponibles',
  `forma` varchar(100) DEFAULT NULL COMMENT 'Forma del producto',
  `dimensiones` varchar(100) DEFAULT NULL COMMENT 'Dimensiones del producto (ej: 30x30 cm)',
  `resistencia` varchar(255) DEFAULT NULL COMMENT 'Resistencia y especificaciones',
  `colocacion` text DEFAULT NULL COMMENT 'Instrucciones de colocación',
  `mantenimiento_preventivo` text DEFAULT NULL COMMENT 'Instrucciones de mantenimiento preventivo',
  `mantenimiento_correctivo` text DEFAULT NULL COMMENT 'Instrucciones de mantenimiento correctivo',
  `observaciones` text DEFAULT NULL COMMENT 'Observaciones generales',
  `catalogo_pdf` varchar(500) DEFAULT NULL COMMENT 'Ruta del archivo PDF del catálogo',
  `fecha_actualizacion_catalogo` datetime DEFAULT NULL COMMENT 'Fecha de última actualización del catálogo',
  `proveedor_id` int(11) DEFAULT NULL COMMENT 'Solo si es producto de reventa',
  `estatus` enum('Activo','Inactivo','Descontinuado') DEFAULT 'Activo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  UNIQUE KEY `uk_codigo_barras` (`codigo_barras`),
  UNIQUE KEY `uk_sku` (`sku`),
  KEY `idx_categoria` (`categoria_id`),
  KEY `idx_tipo` (`tipo_producto`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_productos_stock_bajo` (`stock_actual`,`stock_minimo`),
  KEY `idx_productos_tipo_estatus` (`tipo_producto`,`estatus`),
  KEY `idx_productos_alias` (`alias`),
  KEY `idx_producto_padre` (`producto_padre_id`),
  KEY `idx_es_variante` (`es_variante`),
  KEY `idx_variante_tipo` (`variante_tipo`),
  CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_productos` (`id`),
  CONSTRAINT `fk_prod_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_producto_padre` FOREIGN KEY (`producto_padre_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de productos terminados';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productos`
--

LOCK TABLES `productos` WRITE;
/*!40000 ALTER TABLE `productos` DISABLE KEYS */;
INSERT INTO `productos` VALUES (3,NULL,0,NULL,NULL,'PROD-0001','CHISA GLASS MICRO','CHISA GLASS MICRO','Recubrimiento multicolor, totalmente decorativo, aséptico.',2,'Fabricado','Cubeta','19L',19.00,'L','7500001000014',NULL,'chsr100',-42.00,1.00,0.00,NULL,280.00,500.00,5.00,'uploads/productos/d80981155677ccca6f5e701ca9d4280b.jpg',NULL,NULL,2.00,'12m','4 horas','Multicolor',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'Activo','2025-12-25 09:09:28',NULL,'2025-12-25 13:40:21'),(4,3,1,'color','Beige','PROD-0002','CHISA GLASS MICRO COLOR 2','CHISA GLASS MICRO','Recubrimiento multicolor, totalmente decorativo, aséptico.',2,'Fabricado','Cubeta','19L',19.00,'L','7500001000021',NULL,'',0.00,0.00,0.00,NULL,0.00,520.00,0.00,'uploads/productos/6d8e0fb3dd532104de2ff4ff8077a9e2.jpg',NULL,NULL,2.00,'12m','4 horas','','Recubrimiento multicolor, totalmente decorativo, aséptico, cuenta con alta lavabilidad y estabilidad de colores, es fungicida, flexible, durable, adherente, resistente a la abrasión, no propaga el fuego, y repelente al agua. Cuenta con garantía de diez años.','Micro','Líquido','Película de 25 micras','A la intemperie lavable, elástico, no toxico, no propaga el fuego','Por aspersión de aire con equipo neumático de alta presión','Limpiar las superficies con agua y jabón, cepillo de cerdas o raíz, y/o franela de color distinto al rojo, no utilizar ningún solvente','En muros dañados se deberá aplicar el recubrimiento solo en el detalle','Para uso en Fachadas, plafones, encamados, cubos de escalera,cocinas, salas de espera, etc. Se recomienda de superficies lisas para su aplicación. \r\n',NULL,NULL,NULL,'Activo','2025-12-25 23:09:17',NULL,'2025-12-25 23:10:45');
/*!40000 ALTER TABLE `productos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedor_insumo`
--

DROP TABLE IF EXISTS `proveedor_insumo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedor_insumo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `codigo_proveedor` varchar(100) DEFAULT NULL COMMENT 'Código/SKU que usa el proveedor',
  `nombre_proveedor` varchar(255) DEFAULT NULL COMMENT 'Nombre que le da el proveedor a este insumo',
  `precio_compra` decimal(10,2) DEFAULT 0.00 COMMENT 'Precio de este proveedor específico',
  `tiempo_entrega_dias` int(3) DEFAULT 0,
  `cantidad_minima` decimal(10,2) DEFAULT 1.00,
  `es_proveedor_principal` tinyint(1) DEFAULT 0 COMMENT 'Proveedor preferido para este insumo',
  `calidad` tinyint(1) DEFAULT 3 COMMENT '1-5 estrellas',
  `ultima_compra` date DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_proveedor_insumo` (`proveedor_id`,`insumo_id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_insumo` (`insumo_id`),
  KEY `idx_principal` (`es_proveedor_principal`),
  CONSTRAINT `fk_pi_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pi_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación proveedores-insumos con precios específicos por proveedor';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedor_insumo`
--

LOCK TABLES `proveedor_insumo` WRITE;
/*!40000 ALTER TABLE `proveedor_insumo` DISABLE KEYS */;
INSERT INTO `proveedor_insumo` VALUES (1,1,1,'PMKSDJKN',NULL,100.00,1,1.00,0,3,NULL,'Solicitud con mínimo 24 horas de anticipación','Activo','2025-12-25 07:50:30');
/*!40000 ALTER TABLE `proveedor_insumo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proveedores`
--

DROP TABLE IF EXISTS `proveedores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) DEFAULT NULL,
  `razon_social` varchar(255) NOT NULL,
  `nombre_comercial` varchar(255) DEFAULT NULL,
  `rfc` varchar(13) NOT NULL,
  `tipo_proveedor` enum('Materia Prima','Servicios','Materiales','Mixto') NOT NULL DEFAULT 'Mixto',
  `contacto_principal` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `telefono_alternativo` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `sitio_web` varchar(255) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `estado` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(10) DEFAULT NULL,
  `pais` varchar(100) DEFAULT 'México',
  `dias_credito` int(3) DEFAULT 0 COMMENT 'Días de crédito otorgados',
  `limite_credito` decimal(12,2) DEFAULT 0.00,
  `cuenta_bancaria` varchar(50) DEFAULT NULL,
  `banco` varchar(100) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `calificacion` tinyint(1) DEFAULT 3 COMMENT '1-5 estrellas',
  `estatus` enum('Activo','Inactivo','Suspendido') DEFAULT 'Activo',
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `registrado_por` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_rfc` (`rfc`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx_razon_social` (`razon_social`),
  KEY `idx_tipo` (`tipo_proveedor`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_proveedor_activo` (`estatus`,`razon_social`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de proveedores';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proveedores`
--

LOCK TABLES `proveedores` WRITE;
/*!40000 ALTER TABLE `proveedores` DISABLE KEYS */;
INSERT INTO `proveedores` VALUES (1,'PROV00001','PINTURAS COMEX SARAPE SA DE CV','COMEX','PCC951205QT7','Materia Prima','JUAN CARLOS LOPEZ MARTINES','5566331454141','','comexjuan@mail.com','','Avenida Francisco Trujillo No. 526 Local A, Colonia José María Pino Suárez','TABASCO','TABASCO','86029','México',0,0.00,'125487456985423654','BBVA','Proveedor de prueba en el sistema',3,'Activo','2025-12-25 07:49:28',NULL);
/*!40000 ALTER TABLE `proveedores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `servicios_recurrentes`
--

DROP TABLE IF EXISTS `servicios_recurrentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicios_recurrentes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proveedor_id` int(11) DEFAULT NULL COMMENT 'ID del proveedor (si aplica)',
  `nombre_servicio` varchar(200) NOT NULL COMMENT 'Nombre del servicio (Luz, Agua, Internet, etc.)',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción del servicio',
  `tipo_servicio` enum('Servicios Públicos','Renta','Seguros','Suscripciones','Mantenimiento','Otros') DEFAULT 'Otros',
  `frecuencia` enum('Mensual','Bimestral','Trimestral','Semestral','Anual') DEFAULT 'Mensual',
  `dia_vencimiento` int(2) NOT NULL COMMENT 'Día del mes de vencimiento (1-31)',
  `monto_estimado` decimal(10,2) DEFAULT 0.00 COMMENT 'Monto estimado del pago',
  `cuenta_contable_id` int(11) DEFAULT NULL COMMENT 'Cuenta contable para el gasto',
  `cuenta_bancaria_id` int(11) DEFAULT NULL COMMENT 'Cuenta bancaria para el pago',
  `activo` tinyint(1) DEFAULT 1,
  `fecha_inicio` date DEFAULT NULL COMMENT 'Fecha de inicio del servicio',
  `fecha_fin` date DEFAULT NULL COMMENT 'Fecha de fin del servicio (si aplica)',
  `notas` text DEFAULT NULL,
  `usuario_creacion` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_proveedor` (`proveedor_id`),
  KEY `idx_cuenta_contable` (`cuenta_contable_id`),
  KEY `idx_cuenta_bancaria` (`cuenta_bancaria_id`),
  KEY `idx_activo` (`activo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Servicios recurrentes y pagos mensuales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `servicios_recurrentes`
--

LOCK TABLES `servicios_recurrentes` WRITE;
/*!40000 ALTER TABLE `servicios_recurrentes` DISABLE KEYS */;
/*!40000 ALTER TABLE `servicios_recurrentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_produccion`
--

DROP TABLE IF EXISTS `solicitudes_produccion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_produccion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `folio` varchar(50) NOT NULL COMMENT 'SP-2025-0001',
  `orden_venta_id` int(11) DEFAULT NULL COMMENT 'Orden de venta que generó la solicitud',
  `producto_id` int(11) NOT NULL,
  `cantidad_solicitada` decimal(10,2) NOT NULL,
  `cantidad_producida` decimal(10,2) DEFAULT 0.00,
  `fecha_solicitud` date NOT NULL,
  `fecha_requerida` date DEFAULT NULL COMMENT 'Fecha en que se necesita el producto',
  `fecha_inicio_produccion` date DEFAULT NULL,
  `fecha_fin_produccion` date DEFAULT NULL,
  `estatus` enum('Pendiente','En Proceso','Completada','Cancelada') DEFAULT 'Pendiente',
  `orden_produccion_id` int(11) DEFAULT NULL,
  `prioridad` enum('Baja','Media','Alta','Urgente') DEFAULT 'Media',
  `observaciones` text DEFAULT NULL,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `formulacion_id` int(11) DEFAULT NULL COMMENT 'Formulación específica solicitada desde ventas',
  PRIMARY KEY (`id`),
  UNIQUE KEY `folio` (`folio`),
  UNIQUE KEY `uk_folio` (`folio`),
  KEY `idx_orden_venta` (`orden_venta_id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_prioridad` (`prioridad`),
  KEY `idx_solicitudes_fecha_estatus` (`fecha_solicitud`,`estatus`),
  KEY `fk_solprod_formulacion` (`formulacion_id`),
  KEY `idx_orden_prod` (`orden_produccion_id`),
  CONSTRAINT `fk_solprod_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`),
  CONSTRAINT `fk_sp_orden_prod` FOREIGN KEY (`orden_produccion_id`) REFERENCES `ordenes_produccion` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sp_orden_venta` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sp_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de producción generadas por ventas';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_produccion`
--

LOCK TABLES `solicitudes_produccion` WRITE;
/*!40000 ALTER TABLE `solicitudes_produccion` DISABLE KEYS */;
INSERT INTO `solicitudes_produccion` VALUES (1,'SP-2025-0001',13,3,41.00,0.00,'2025-12-25',NULL,NULL,NULL,'Pendiente',NULL,'Media',NULL,NULL,'2025-12-25 13:07:31',4);
/*!40000 ALTER TABLE `solicitudes_produccion` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `solicitudes_vacaciones`
--

DROP TABLE IF EXISTS `solicitudes_vacaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `solicitudes_vacaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `periodo_vacaciones_id` int(11) NOT NULL COMMENT 'De qué período se toman',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `dias_solicitados` int(11) NOT NULL,
  `estatus` enum('Pendiente','Aprobada','Rechazada','Cancelada') DEFAULT 'Pendiente',
  `motivo_rechazo` text DEFAULT NULL,
  `aprobado_por` tinyint(4) DEFAULT NULL COMMENT 'ID del admin que aprobó',
  `fecha_solicitud` datetime NOT NULL,
  `fecha_aprobacion` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_estatus` (`estatus`),
  KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  KEY `fk_solicitud_periodo` (`periodo_vacaciones_id`),
  KEY `fk_solicitud_aprobador` (`aprobado_por`),
  CONSTRAINT `fk_solicitud_aprobador` FOREIGN KEY (`aprobado_por`) REFERENCES `administradores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_solicitud_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_solicitud_periodo` FOREIGN KEY (`periodo_vacaciones_id`) REFERENCES `vacaciones_empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `solicitudes_vacaciones`
--

LOCK TABLES `solicitudes_vacaciones` WRITE;
/*!40000 ALTER TABLE `solicitudes_vacaciones` DISABLE KEYS */;
INSERT INTO `solicitudes_vacaciones` VALUES (1,4,1,'2025-12-23','2025-12-27',4,'Aprobada',NULL,NULL,'2025-12-24 18:13:09','2025-12-24 18:20:09','Solicitud extemporánea ');
/*!40000 ALTER TABLE `solicitudes_vacaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vacaciones`
--

DROP TABLE IF EXISTS `vacaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vacaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `periodo_inicio` date NOT NULL,
  `periodo_fin` date NOT NULL,
  `dias_solicitados` int(11) NOT NULL,
  `dias_disponibles` int(11) DEFAULT NULL COMMENT 'Días disponibles al momento de solicitud',
  `fecha_solicitud` date NOT NULL,
  `fecha_autorizacion` date DEFAULT NULL,
  `estatus` enum('Pendiente','Autorizada','Rechazada','Tomada','Cancelada') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL,
  `motivo_rechazo` text DEFAULT NULL,
  `usuario_autorizacion` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`),
  KEY `idx_estatus` (`estatus`),
  CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Solicitudes de vacaciones';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vacaciones`
--

LOCK TABLES `vacaciones` WRITE;
/*!40000 ALTER TABLE `vacaciones` DISABLE KEYS */;
/*!40000 ALTER TABLE `vacaciones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vacaciones_empleados`
--

DROP TABLE IF EXISTS `vacaciones_empleados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vacaciones_empleados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `empleado_id` int(11) NOT NULL,
  `periodo_inicio` date NOT NULL COMMENT 'Inicio del período de vacaciones (aniversario)',
  `periodo_fin` date NOT NULL COMMENT 'Fin del período',
  `anios_antiguedad` int(11) NOT NULL COMMENT 'Años cumplidos en este período',
  `dias_correspondientes` int(11) NOT NULL COMMENT 'Días que le tocan por ley',
  `dias_adicionales` int(11) DEFAULT 0 COMMENT 'Días extra otorgados por la empresa',
  `dias_totales` int(11) NOT NULL COMMENT 'Total disponibles',
  `dias_tomados` int(11) DEFAULT 0 COMMENT 'Días ya utilizados',
  `dias_disponibles` int(11) NOT NULL COMMENT 'Días restantes',
  `fecha_creacion` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_empleado` (`empleado_id`),
  KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`),
  CONSTRAINT `fk_vacaciones_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vacaciones_empleados`
--

LOCK TABLES `vacaciones_empleados` WRITE;
/*!40000 ALTER TABLE `vacaciones_empleados` DISABLE KEYS */;
INSERT INTO `vacaciones_empleados` VALUES (1,4,'2025-12-24','2026-12-23',3,10,0,10,4,6,'2025-12-24 18:05:59'),(2,3,'2025-12-24','2026-12-23',1,6,0,6,0,6,'2025-12-24 18:06:45');
/*!40000 ALTER TABLE `vacaciones_empleados` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-26 14:49:39
