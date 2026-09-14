/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.6.28-MariaDB, for linux-systemd (x86_64)
--
-- Host: 67.217.58.138    Database: st32477_chisa
-- ------------------------------------------------------
-- Server version	10.6.28-MariaDB

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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de venta y cotizaciones';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ordenes_venta`
--

LOCK TABLES `ordenes_venta` WRITE;
/*!40000 ALTER TABLE `ordenes_venta` DISABLE KEYS */;
INSERT INTO `ordenes_venta` VALUES (1,'OV-2025-0001',1,'2025-12-25',NULL,NULL,500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 10:29:20','2025-12-25 11:29:36',NULL,0.00),(2,'OV-2025-0002',1,'2025-12-25',NULL,NULL,500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 10:30:00','2025-12-25 11:29:36',NULL,0.00),(3,'OV-2025-0003',1,'2025-12-25',NULL,'2025-12-25',500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 10:30:53','2025-12-25 11:29:36',NULL,0.00),(4,'OV-2025-0004',2,'2025-12-25',NULL,'2025-12-25',500.00,80.00,580.00,0.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 11:25:19','2025-12-25 11:29:36',NULL,0.00),(5,'OV-2025-0005',2,'2025-12-25',NULL,'2025-12-25',5000.00,800.00,5800.00,5800.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 11:30:27','2025-12-25 11:30:27',NULL,0.00),(6,'OV-2025-0006',1,'2025-12-25',NULL,'2025-12-25',500.00,80.00,580.00,0.00,0.00,'Pendiente','Crédito',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2025-12-25 11:31:20','2025-12-25 11:31:20',NULL,0.00),(7,'OV-2025-0007',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:37:40','2025-12-25 12:37:40',NULL,0.00),(8,'OV-2025-0008',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:37:56','2025-12-25 12:37:57',NULL,0.00),(9,'OV-2025-0009',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:39:20','2025-12-25 12:39:20',NULL,0.00),(10,'OV-2025-0010',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,2320.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:40:43','2025-12-25 12:40:43',NULL,0.00),(11,'OV-2025-0011',2,'2025-12-25',NULL,'2025-12-25',2000.00,320.00,2320.00,2320.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara',NULL,0,NULL,'2025-12-25 12:41:53','2025-12-25 12:41:53',NULL,0.00),(12,'OV-2025-0012',2,'2025-12-25',NULL,'2025-12-25',2000.00,288.00,2088.00,0.00,0.00,'Pendiente','Crédito',1,'Descuento Cliente Frecuente','Porcentaje',10.00,200.00,NULL,'Entregada','Mostrador','Prueba de pago pendiente',NULL,0,NULL,'2025-12-25 12:52:36','2025-12-25 12:52:36',NULL,0.00),(13,'OV-2025-0013',2,'2025-12-25',NULL,NULL,1000.00,160.00,1160.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'En Preparación','Mostrador','prueba 2 con formulación y pago pendiente o cotización',NULL,1,NULL,'2025-12-25 13:06:24','2025-12-25 13:07:31',NULL,0.00),(14,'OV-2025-0014',2,'2025-12-25','2025-12-27','2025-12-25',1500.00,240.00,1740.00,0.00,0.00,'Pendiente','Crédito',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Pedido','Prueba de compra a crédito',NULL,0,NULL,'2025-12-25 13:40:21','2025-12-25 13:40:21','Av. Juárez 2915, Int. 301, Col. La Paz, C.P. 72160, Puebla, Puebla, México.',100.00),(15,'OV-2025-0015',2,'2025-12-25','2025-12-27',NULL,2500.00,360.00,2610.00,0.00,2610.00,'Pendiente','Crédito',1,'Descuento Cliente Frecuente','Porcentaje',10.00,250.00,NULL,'Confirmada','Pedido','prueba  con envio y a credito',NULL,0,NULL,'2025-12-25 13:50:13','2025-12-25 13:50:13','Av. Juárez 2915, Int. 301, Col. La Paz, C.P. 72160, Puebla, Puebla, México.',100.00),(16,'OV-2026-0001',1,'2026-02-11','0000-00-00','2026-02-11',500.00,80.00,580.00,580.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2026-02-11 14:08:26','2026-02-11 14:08:26','',0.00),(17,'OV-2026-0002',2,'2026-02-11','0000-00-00','2026-02-11',500.00,80.00,580.00,580.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2026-02-11 14:10:11','2026-02-11 14:10:11','',0.00),(21,'OV-TEST-001',9,'2026-02-23',NULL,NULL,2500.00,400.00,2900.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Confirmada','Pedido',NULL,NULL,0,NULL,'2026-02-23 06:41:59','2026-02-23 06:41:59',NULL,0.00),(22,'OV-2026-0003',1,'2026-05-19','0000-00-00','2026-05-19',5000.00,800.00,5800.00,5800.00,0.00,'Pagado','Tarjeta',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2026-05-19 13:40:34','2026-05-19 13:40:34','',0.00),(23,'OV-2026-0004',9,'2026-05-19','2026-05-30',NULL,6000.00,960.00,6960.00,0.00,6960.00,'Pendiente','Transferencia',NULL,NULL,NULL,0.00,0.00,NULL,'Confirmada','Pedido','Pedido pendiente de entrega',NULL,0,NULL,'2026-05-19 13:42:02','2026-05-19 13:42:02','Calle y numero de prueba',1000.00),(24,'OV-2026-0005',1,'2026-07-06','0000-00-00','2026-07-06',500.00,80.00,580.00,580.00,0.00,'Pagado','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Entregada','Mostrador','',NULL,0,NULL,'2026-07-06 13:19:02','2026-07-06 13:19:02','',0.00),(25,'OV-2026-0006',2,'2026-09-07','2026-10-15',NULL,1000.00,160.00,1160.00,0.00,53244.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,'Anticipo 30%','Cancelada','Pedido','Generada desde obra OB-00003',NULL,1,1,'2026-09-07 18:07:21','2026-09-07 16:19:23','Calle Prueba 123, Col. Test, Guadalajara, Jalisco',0.00),(26,'OV-2026-0007',9,'2026-09-14','0000-00-00',NULL,0.00,0.00,0.00,0.00,0.00,'Pendiente','Efectivo',NULL,NULL,NULL,0.00,0.00,NULL,'Cancelada','Mostrador','TEST-QA-UI-OV-01','Limpieza QA TEST-QA-UI-OV-01 cancelacion',1,1,'2026-09-14 06:03:41','2026-09-14 04:21:20','',0.00);
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
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

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
  `orden_venta_id` int(11) DEFAULT NULL COMMENT 'Orden de venta vinculada',
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
  KEY `idx_obras_fechas` (`fecha_inicio_estimada`,`fecha_fin_estimada`),
  KEY `idx_obras_orden_venta` (`orden_venta_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla principal de obras para cálculo de materiales';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras`
--

LOCK TABLES `obras` WRITE;
/*!40000 ALTER TABLE `obras` DISABLE KEYS */;
INSERT INTO `obras` VALUES (1,'OB-00001','EHWEB',2,NULL,'Avenida Amores, NO 605 Int A','CDMX','BENITO JUAREZ','04000',NULL,10.00,'Tablaroca',NULL,NULL,50000.00,0.00,5000.00,0.00,0.00,16.00,800.00,5800.00,100.00,5800.00,'50% anticipo','25 días hábiles',10.00,580.00,0.00,0.00,'Pendiente','Planificación',0.00,'2025-12-28','2025-12-31',NULL,NULL,'Prueba de nueva obnra',NULL,NULL,NULL,1,'2025-12-25 18:43:40',NULL,'2025-12-25 19:02:30',1),(2,'OB-00002','Hospital Luz',2,NULL,'Amores 50, int b','CDMX','BENITO JUAREZ','04000',NULL,25.00,'Rugosa','Humedad alta','',35000.00,0.00,36000.00,0.00,0.00,16.00,5760.00,41760.00,100.00,41760.00,'','15',10.00,4176.00,2000.00,39760.00,'Anticipo Recibido','En Ejecución',20.00,'2025-12-25','2026-01-11',NULL,NULL,'Prueba',NULL,NULL,NULL,1,'2025-12-25 19:21:42',1,'2026-09-07 12:54:10',1),(3,'OB-TEST-002','Remodelación Nave Industrial',9,NULL,'Calle de Prueba 123, Ciudad de Prueba',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,16.00,0.00,15000.00,0.00,0.00,NULL,NULL,0.00,0.00,0.00,0.00,'Pendiente','Aprobada',0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-02-23 06:41:59',NULL,'2026-09-07 14:28:12',0),(11,'OB-00003','TEST-QA-OBRA-001',2,25,'Calle Prueba 123, Col. Test','Guadalajara','Jalisco','44100',NULL,100.00,'',NULL,NULL,50000.00,NULL,51000.00,10.00,5100.00,16.00,7344.00,53244.00,100.00,53244.00,'Anticipo 30%','30 días',30.00,15973.20,0.00,58000.00,'Pendiente','Completada',NULL,'2026-09-15','2026-10-15',NULL,NULL,'Obra de prueba QA iteracion 3',NULL,NULL,NULL,1,'2026-09-07 18:03:36',1,'2026-09-07 16:19:23',0),(12,'OB-00004','',2,NULL,'Calle Test',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,16.00,0.00,0.00,0.00,0.00,NULL,NULL,0.00,0.00,0.00,0.00,'Pendiente','Planificación',0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-09-07 18:05:14',NULL,'2026-09-07 16:05:21',0),(13,'OB-00005','TEST-QA-VALIDACION-BUG4',1,NULL,'Calle Test 123','CDMX','CDMX','01000',NULL,NULL,NULL,NULL,NULL,0.00,0.00,0.00,0.00,0.00,16.00,0.00,0.00,0.00,0.00,NULL,NULL,0.00,0.00,0.00,0.00,'Pendiente','Planificación',0.00,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'2026-09-10 16:25:42',NULL,'2026-09-10 13:29:54',0),(14,'OB-00006','TEST-QA-UI-OBRA-01',9,NULL,'Av. Pruebas QA 123, Col. Test','Monterrey','Nuevo Leon','64000',NULL,50.00,'Muro interior','','',1000.00,0.00,1000.00,0.00,0.00,16.00,160.00,1160.00,100.00,1160.00,'','',30.00,348.00,0.00,1160.00,'Pendiente','Aprobada',0.00,'2026-09-14','2026-10-14',NULL,NULL,'Obra de prueba UI 2a pasada QA (TEST-QA-UI-OBRA-01)',NULL,NULL,NULL,1,'2026-09-14 02:05:34',NULL,'2026-09-14 04:33:29',0);
/*!40000 ALTER TABLE `obras` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_productos`
--

LOCK TABLES `movimientos_productos` WRITE;
/*!40000 ALTER TABLE `movimientos_productos` DISABLE KEYS */;
INSERT INTO `movimientos_productos` VALUES (1,3,'Salida',1.00,-55.00,-56.00,NULL,NULL,'Entrega de obra ENT-2026-0001',0,NULL,1,'2026-09-07 18:16:38'),(2,3,'Salida',1.00,-56.00,-57.00,NULL,NULL,'Entrega de obra ENT-2026-0002',0,NULL,1,'2026-09-07 18:16:56');
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
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `movimientos_inventario`
--

DROP TABLE IF EXISTS `movimientos_inventario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `insumo_id` int(11) DEFAULT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('Entrada','Salida','Ajuste') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_anterior` decimal(10,2) DEFAULT NULL,
  `stock_nuevo` decimal(10,2) DEFAULT NULL,
  `costo_unitario` decimal(10,2) DEFAULT NULL,
  `costo_total` decimal(12,2) DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de orden, producción, etc.',
  `orden_compra_id` int(11) DEFAULT NULL,
  `orden_produccion_id` int(11) DEFAULT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_producto` (`producto_id`),
  KEY `idx_fecha` (`fecha_movimiento`),
  KEY `idx_tipo` (`tipo_movimiento`),
  CONSTRAINT `fk_mi_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movimientos_inventario`
--

LOCK TABLES `movimientos_inventario` WRITE;
/*!40000 ALTER TABLE `movimientos_inventario` DISABLE KEYS */;
INSERT INTO `movimientos_inventario` VALUES (1,NULL,3,'Salida',1.00,NULL,NULL,NULL,NULL,'Venta - Orden 3',NULL,NULL,NULL,'2025-12-25 10:30:53',NULL),(2,NULL,3,'Salida',1.00,NULL,NULL,NULL,NULL,'Venta - Orden 4',NULL,NULL,NULL,'2025-12-25 11:25:19',NULL),(3,NULL,3,'Salida',10.00,NULL,NULL,NULL,NULL,'Venta - Orden 5',NULL,NULL,NULL,'2025-12-25 11:30:27',NULL),(4,NULL,3,'Salida',1.00,NULL,NULL,NULL,NULL,'Venta - Orden 6',NULL,NULL,NULL,'2025-12-25 11:31:20',NULL),(5,NULL,3,'Salida',4.00,NULL,NULL,NULL,NULL,'Venta - Orden 7',NULL,NULL,NULL,'2025-12-25 12:37:40',NULL),(6,NULL,3,'Salida',4.00,NULL,NULL,NULL,NULL,'Venta - Orden 8',NULL,NULL,NULL,'2025-12-25 12:37:57',NULL),(7,NULL,3,'Salida',4.00,NULL,NULL,NULL,NULL,'Venta - Orden 9',NULL,NULL,NULL,'2025-12-25 12:39:20',NULL),(8,NULL,3,'Salida',4.00,NULL,NULL,NULL,NULL,'Venta - Orden 10',NULL,NULL,NULL,'2025-12-25 12:40:43',NULL),(9,NULL,3,'Salida',4.00,NULL,NULL,NULL,NULL,'Venta - Orden 11',NULL,NULL,NULL,'2025-12-25 12:41:53',NULL),(10,NULL,3,'Salida',4.00,NULL,NULL,NULL,NULL,'Venta - Orden 12',NULL,NULL,NULL,'2025-12-25 12:52:36',NULL),(11,NULL,3,'Salida',3.00,NULL,NULL,NULL,NULL,'Venta - Orden 14',NULL,NULL,NULL,'2025-12-25 13:40:21',NULL),(12,NULL,3,'Salida',1.00,NULL,NULL,NULL,NULL,'Venta - Orden 16',NULL,NULL,NULL,'2026-02-11 14:08:26',NULL),(13,NULL,3,'Salida',1.00,NULL,NULL,NULL,NULL,'Venta - Orden 17',NULL,NULL,NULL,'2026-02-11 14:10:11',NULL),(16,NULL,3,'Salida',10.00,NULL,NULL,NULL,NULL,'Venta - Orden 22',NULL,NULL,NULL,'2026-05-19 13:40:34',NULL),(17,NULL,3,'Salida',1.00,NULL,NULL,NULL,NULL,'Venta - Orden 24',NULL,NULL,NULL,'2026-07-06 13:19:02',NULL),(18,1,NULL,'Entrada',10.00,4.00,14.00,100.00,1000.00,'Recepción de orden de compra OC-2026-DEMO1',NULL,3,NULL,'2026-07-10 05:04:26',9),(19,17,NULL,'Salida',0.00,200.00,200.00,32.00,0.02,'Pesaje producción VENTA #26','PESAJE-venta-26',NULL,NULL,'2026-09-14 06:11:39',1),(20,20,NULL,'Salida',0.97,80.00,79.03,95.00,92.15,'Pesaje producción VENTA #26','PESAJE-venta-26',NULL,NULL,'2026-09-14 06:11:39',1),(21,18,NULL,'Salida',0.03,150.00,149.97,45.00,1.35,'Pesaje producción VENTA #26','PESAJE-venta-26',NULL,NULL,'2026-09-14 06:11:39',1);
/*!40000 ALTER TABLE `movimientos_inventario` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`st32477_chisa`@`localhost`*/ /*!50003 TRIGGER trg_stock_insumos_movimiento
AFTER INSERT ON movimientos_inventario
FOR EACH ROW
BEGIN
    
    IF NEW.insumo_id IS NOT NULL THEN
    
        
        IF NEW.tipo_movimiento = 'Entrada' THEN
            UPDATE insumos 
            SET stock_actual = stock_actual + NEW.cantidad,
                
                precio_promedio = (
                    CASE 
                        WHEN NEW.costo_total > 0 AND (stock_actual + NEW.cantidad) > 0 THEN 
                            ((stock_actual * precio_promedio) + NEW.costo_total) / (stock_actual + NEW.cantidad)
                        ELSE precio_promedio
                    END
                ),
                ultima_compra = NEW.fecha_movimiento
            WHERE id = NEW.insumo_id;
            
        
        ELSEIF NEW.tipo_movimiento = 'Salida' THEN
            UPDATE insumos 
            SET stock_actual = stock_actual - NEW.cantidad
            WHERE id = NEW.insumo_id;
        END IF;
        
    END IF;
END 
*/;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-14  6:10:19
