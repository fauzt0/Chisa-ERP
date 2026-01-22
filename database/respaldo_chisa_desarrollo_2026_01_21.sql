-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 21-01-2026 a las 17:25:13
-- Versión del servidor: 10.6.24-MariaDB
-- Versión de PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `st32477_chisa`
--

DELIMITER $$
--
-- Procedimientos
--
$$

$$

$$

$$

$$

$$

$$

$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` tinyint(4) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `apellidos` varchar(150) NOT NULL,
  `username` varchar(250) NOT NULL,
  `password` varchar(255) NOT NULL,
  `privilegios` text NOT NULL,
  `departamento` varchar(40) NOT NULL DEFAULT '',
  `avatar` varchar(255) NOT NULL,
  `two_factor_code` varchar(6) DEFAULT NULL,
  `two_factor_expires` datetime DEFAULT NULL,
  `last_ip` varchar(45) DEFAULT NULL,
  `estatus` tinyint(4) NOT NULL,
  `meta_nombre` varchar(50) NOT NULL,
  `fecha_alta` date NOT NULL,
  `fecha_baja` date DEFAULT NULL,
  `fecha_edicion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `nombre`, `apellidos`, `username`, `password`, `privilegios`, `departamento`, `avatar`, `two_factor_code`, `two_factor_expires`, `last_ip`, `estatus`, `meta_nombre`, `fecha_alta`, `fecha_baja`, `fecha_edicion`) VALUES
(1, 'EHWEB', 'EHWEB', 'soporte2@especialistasweb.com.mx', '$2y$10$5NOfTw8/7ic8jymKv7PNS.DxZuFTVbcAqrZrc1KpFfAdQYgruAaSa', '', 'IT', '', NULL, '2026-01-13 11:54:05', '187.251.241.163', 1, '', '2025-12-24', NULL, '2026-01-13'),
(2, 'Vendedor1', 'Pérez Vazquez', 'ventas1@chisarecubrimientos.com.mx', '$2y$10$n9MKtMksSmJSrnQHsSwEkeVBdp6RWbKs4oe8ktNAEAFWLTGJsc57i', '', 'Ventas', '', NULL, NULL, NULL, 1, '', '2026-01-14', NULL, '2026-01-14'),
(3, 'Vendedor2', 'Sanchez Martinez', 'ventas2@chisarecubrimientos.com.mx', '$2y$10$H8mXOP6IHzD306cVCz39T.7f9JN6t2llLMTUNUIARfT/9bKJR1YOq', '', 'Ventas', '', NULL, NULL, NULL, 1, '', '2026-01-14', NULL, '2026-01-14'),
(4, 'Vendedor3', 'Alcazar', 'ventas3@chisarecubrimientos.com.mx', '$2y$10$ghU4NrCygKtm8KoA2Z9a5ufgS/FA71QQ/TVgfA93HHb1598oWZfuG', '', 'Ventas', '', NULL, NULL, NULL, 1, '', '2026-01-19', NULL, NULL),
(5, 'Vendedor 4', 'Trejo', 'ventas4@chisarecubrimientos.com.mx', '$2y$10$N00VhMZDZEda38ZAhXL6Q.XcD43ArLcEkoZ3gFcFP/80bGUfGqcMq', '', 'ventas', '', NULL, NULL, NULL, 1, '', '2026-01-19', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas_stock`
--

CREATE TABLE `alertas_stock` (
  `id` int(11) NOT NULL,
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
  `fecha_resolucion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Alertas de stock bajo';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `usuario` varchar(250) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id`, `mensaje`, `usuario`, `tipo`, `ip`, `fecha`, `hora`) VALUES
(1, 'Se ha agregado al Administrador con el ID: 1', 'soporte2@chisarecubrimientos.com', 'Registro agregado', '187.190.154.188', '2025-12-24', '14:19:16'),
(2, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.190.154.188', '2025-12-26', '07:05:50'),
(3, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.190.154.188', '2025-12-26', '07:14:27'),
(4, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.190.154.188', '2025-12-26', '07:14:38'),
(5, 'Se ha actualizado al Administrador con el ID: 1', 'soporte2@chisarecubrimientos.com', 'Registro actualizado', '187.190.154.188', '2025-12-26', '07:45:10'),
(6, 'Se ha actualizado al Administrador con el ID: 1', 'soporte2@chisarecubrimientos.com', 'Registro actualizado', '187.190.154.188', '2025-12-26', '08:10:17'),
(7, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '189.233.147.163', '2025-12-26', '08:51:49'),
(8, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '189.233.147.163', '2025-12-26', '08:52:06'),
(9, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '189.233.147.163', '2025-12-26', '09:20:32'),
(10, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.251.241.163', '2025-12-26', '13:36:52'),
(11, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2025-12-26', '13:36:58'),
(12, 'Intento de acceso erróneo', 'soporte2@especialistashosting.com', 'Ingreso erroneo', '187.251.241.163', '2025-12-26', '15:33:15'),
(13, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2025-12-26', '15:33:25'),
(14, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-07', '11:52:03'),
(15, 'Se ha actualizado al Administrador con el ID: 1', 'soporte2@chisarecubrimientos.com', 'Registro actualizado', '187.251.241.163', '2026-01-12', '12:45:37'),
(16, 'Intento de acceso erróneo', 'soporte2@especialistashosting.com', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '11:39:24'),
(17, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '11:40:02'),
(18, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '11:40:08'),
(19, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '11:40:29'),
(20, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '11:40:39'),
(21, 'Intento de acceso erróneo', 'soporte2@especialistasweb.com.mx', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '11:42:06'),
(22, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-13', '11:44:05'),
(23, 'Se ha actualizado al Administrador con el ID: 1', 'soporte2@especialistasweb.com.mx', 'Registro actualizado', '187.251.241.163', '2026-01-13', '12:58:08'),
(24, 'Intento de acceso erróneo', 'soporte2@especialistashosting.com', 'Ingreso erroneo', '187.251.241.163', '2026-01-13', '16:17:10'),
(25, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-13', '16:17:23'),
(26, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-14', '10:48:07'),
(27, 'Se ha agregado al Administrador con el ID: 2', 'soporte2@especialistasweb.com.mx', 'Registro agregado', '187.251.241.163', '2026-01-14', '11:49:58'),
(28, 'Se ha agregado al Administrador con el ID: 3', 'soporte2@especialistasweb.com.mx', 'Registro agregado', '187.251.241.163', '2026-01-14', '11:55:29'),
(29, 'Se ha actualizado al Administrador con el ID: 3', 'soporte2@especialistasweb.com.mx', 'Registro actualizado', '187.251.241.163', '2026-01-14', '11:59:51'),
(30, 'Se ha actualizado al Administrador con el ID: 2', 'soporte2@especialistasweb.com.mx', 'Registro actualizado', '187.251.241.163', '2026-01-14', '12:00:13'),
(31, 'Se ha actualizado al Administrador con el ID: 2', 'soporte2@especialistasweb.com.mx', 'Registro actualizado', '187.251.241.163', '2026-01-14', '12:02:26'),
(32, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-16', '13:05:40'),
(33, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-19', '11:43:54'),
(34, 'Se ha agregado al Administrador con el ID: 4', 'soporte2@especialistasweb.com.mx', 'Registro agregado', '187.251.241.163', '2026-01-19', '11:47:29'),
(35, 'Se ha agregado al Administrador con el ID: 5', 'soporte2@especialistasweb.com.mx', 'Registro agregado', '187.251.241.163', '2026-01-19', '11:50:21'),
(36, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-19', '15:47:29'),
(37, 'Ingreso al sistema', 'soporte2@especialistasweb.com.mx', 'Ingreso correcto', '187.251.241.163', '2026-01-21', '09:32:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_insumos`
--

CREATE TABLE `categorias_insumos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria_padre_id` int(11) DEFAULT NULL COMMENT 'Para categorías jerárquicas',
  `tipo` enum('Materia Prima','Material','Servicio') NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT 'fa-box' COMMENT 'Clase FontAwesome',
  `orden` int(3) DEFAULT 0,
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de insumos jerárquicas';

--
-- Volcado de datos para la tabla `categorias_insumos`
--

INSERT INTO `categorias_insumos` (`id`, `nombre`, `categoria_padre_id`, `tipo`, `descripcion`, `icono`, `orden`, `estatus`) VALUES
(1, 'Materias Primas Químicas', NULL, 'Materia Prima', 'Químicos y componentes para fabricación', 'fa-flask', 1, 'Activo'),
(2, 'Materiales y Herramientas', NULL, 'Material', 'Materiales de trabajo y herramientas', 'fa-tools', 2, 'Activo'),
(3, 'Servicios', NULL, 'Servicio', 'Servicios contratados', 'fa-handshake', 3, 'Activo'),
(4, 'Resinas', 1, 'Materia Prima', 'Resinas acrílicas, epóxicas, etc.', 'fa-vial', 1, 'Activo'),
(5, 'Solventes', 1, 'Materia Prima', 'Solventes y diluyentes', 'fa-flask-vial', 2, 'Activo'),
(6, 'Pigmentos', 1, 'Materia Prima', 'Pigmentos y colorantes', 'fa-palette', 3, 'Activo'),
(7, 'Aditivos', 1, 'Materia Prima', 'Aditivos y modificadores', 'fa-prescription-bottle', 4, 'Activo'),
(8, 'Cargas', 1, 'Materia Prima', 'Cargas minerales', 'fa-cubes', 5, 'Activo'),
(9, 'Envases', 2, 'Material', 'Cubetas, tambos, envases', 'fa-bucket', 1, 'Activo'),
(10, 'Herramientas', 2, 'Material', 'Espátulas, brochas, rodillos', 'fa-screwdriver-wrench', 2, 'Activo'),
(11, 'Empaques', 2, 'Material', 'Etiquetas, cajas, embalaje', 'fa-box', 3, 'Activo'),
(12, 'Equipo de Seguridad', 2, 'Material', 'EPP y equipo de protección', 'fa-hard-hat', 4, 'Activo'),
(13, 'Telecomunicaciones', 3, 'Servicio', 'Internet, telefonía', 'fa-wifi', 1, 'Activo'),
(14, 'Mantenimiento', 3, 'Servicio', 'Mantenimiento de equipos', 'fa-wrench', 2, 'Activo'),
(15, 'Transporte', 3, 'Servicio', 'Servicios de logística', 'fa-truck', 3, 'Activo'),
(16, 'Consultoría', 3, 'Servicio', 'Servicios profesionales', 'fa-user-tie', 4, 'Activo'),
(17, 'Materias Primas Químicas', NULL, 'Materia Prima', 'Químicos y componentes para fabricación', 'fa-flask', 1, 'Activo'),
(18, 'Materiales y Herramientas', NULL, 'Material', 'Materiales de trabajo y herramientas', 'fa-tools', 2, 'Activo'),
(19, 'Servicios', NULL, 'Servicio', 'Servicios contratados', 'fa-handshake', 3, 'Activo'),
(20, 'Resinas', 1, 'Materia Prima', 'Resinas acrílicas, epóxicas, etc.', 'fa-vial', 1, 'Activo'),
(21, 'Solventes', 1, 'Materia Prima', 'Solventes y diluyentes', 'fa-flask-vial', 2, 'Activo'),
(22, 'Pigmentos', 1, 'Materia Prima', 'Pigmentos y colorantes', 'fa-palette', 3, 'Activo'),
(23, 'Aditivos', 1, 'Materia Prima', 'Aditivos y modificadores', 'fa-prescription-bottle', 4, 'Activo'),
(24, 'Cargas', 1, 'Materia Prima', 'Cargas minerales', 'fa-cubes', 5, 'Activo'),
(25, 'Envases', 2, 'Material', 'Cubetas, tambos, envases', 'fa-bucket', 1, 'Activo'),
(26, 'Herramientas', 2, 'Material', 'Espátulas, brochas, rodillos', 'fa-screwdriver-wrench', 2, 'Activo'),
(27, 'Empaques', 2, 'Material', 'Etiquetas, cajas, embalaje', 'fa-box', 3, 'Activo'),
(28, 'Equipo de Seguridad', 2, 'Material', 'EPP y equipo de protección', 'fa-hard-hat', 4, 'Activo'),
(29, 'Telecomunicaciones', 3, 'Servicio', 'Internet, telefonía', 'fa-wifi', 1, 'Activo'),
(30, 'Mantenimiento', 3, 'Servicio', 'Mantenimiento de equipos', 'fa-wrench', 2, 'Activo'),
(31, 'Transporte', 3, 'Servicio', 'Servicios de logística', 'fa-truck', 3, 'Activo'),
(32, 'Consultoría', 3, 'Servicio', 'Servicios profesionales', 'fa-user-tie', 4, 'Activo'),
(34, 'Materiales y Herramientas', NULL, 'Material', 'Materiales de trabajo y herramientas', 'fa-tools', 2, 'Activo'),
(35, 'Servicios', NULL, 'Servicio', 'Servicios contratados', 'fa-handshake', 3, 'Activo'),
(36, 'Resinas', 1, 'Materia Prima', 'Resinas acrílicas, epóxicas, etc.', 'fa-vial', 1, 'Activo'),
(37, 'Solventes', 1, 'Materia Prima', 'Solventes y diluyentes', 'fa-flask-vial', 2, 'Activo'),
(38, 'Pigmentos', 1, 'Materia Prima', 'Pigmentos y colorantes', 'fa-palette', 3, 'Activo'),
(39, 'Aditivos', 1, 'Materia Prima', 'Aditivos y modificadores', 'fa-prescription-bottle', 4, 'Activo'),
(40, 'Cargas', 1, 'Materia Prima', 'Cargas minerales', 'fa-cubes', 5, 'Activo'),
(41, 'Envases', 2, 'Material', 'Cubetas, tambos, envases', 'fa-bucket', 1, 'Activo'),
(42, 'Herramientas', 2, 'Material', 'Espátulas, brochas, rodillos', 'fa-screwdriver-wrench', 2, 'Activo'),
(43, 'Empaques', 2, 'Material', 'Etiquetas, cajas, embalaje', 'fa-box', 3, 'Activo'),
(44, 'Equipo de Seguridad', 2, 'Material', 'EPP y equipo de protección', 'fa-hard-hat', 4, 'Activo'),
(45, 'Telecomunicaciones', 3, 'Servicio', 'Internet, telefonía', 'fa-wifi', 1, 'Activo'),
(46, 'Mantenimiento', 3, 'Servicio', 'Mantenimiento de equipos', 'fa-wrench', 2, 'Activo'),
(47, 'Transporte', 3, 'Servicio', 'Servicios de logística', 'fa-truck', 3, 'Activo'),
(48, 'Consultoría', 3, 'Servicio', 'Servicios profesionales', 'fa-user-tie', 4, 'Activo'),
(49, 'Materias Primas Químicas', NULL, 'Materia Prima', 'Químicos y componentes para fabricación', 'fa-flask', 1, 'Activo'),
(50, 'Materiales y Herramientas', NULL, 'Material', 'Materiales de trabajo y herramientas', 'fa-tools', 2, 'Activo'),
(51, 'Servicios', NULL, 'Servicio', 'Servicios contratados', 'fa-handshake', 3, 'Activo'),
(52, 'Resinas', 1, 'Materia Prima', 'Resinas acrílicas, epóxicas, etc.', 'fa-vial', 1, 'Activo'),
(53, 'Solventes', 1, 'Materia Prima', 'Solventes y diluyentes', 'fa-flask-vial', 2, 'Activo'),
(54, 'Pigmentos', 1, 'Materia Prima', 'Pigmentos y colorantes', 'fa-palette', 3, 'Activo'),
(55, 'Aditivos', 1, 'Materia Prima', 'Aditivos y modificadores', 'fa-prescription-bottle', 4, 'Activo'),
(56, 'Cargas', 1, 'Materia Prima', 'Cargas minerales', 'fa-cubes', 5, 'Activo'),
(57, 'Envases', 2, 'Material', 'Cubetas, tambos, envases', 'fa-bucket', 1, 'Activo'),
(58, 'Herramientas', 2, 'Material', 'Espátulas, brochas, rodillos', 'fa-screwdriver-wrench', 2, 'Activo'),
(59, 'Empaques', 2, 'Material', 'Etiquetas, cajas, embalaje', 'fa-box', 3, 'Activo'),
(60, 'Equipo de Seguridad', 2, 'Material', 'EPP y equipo de protección', 'fa-hard-hat', 4, 'Activo'),
(61, 'Telecomunicaciones', 3, 'Servicio', 'Internet, telefonía', 'fa-wifi', 1, 'Activo'),
(62, 'Mantenimiento', 3, 'Servicio', 'Mantenimiento de equipos', 'fa-wrench', 2, 'Activo'),
(63, 'Transporte', 3, 'Servicio', 'Servicios de logística', 'fa-truck', 3, 'Activo'),
(64, 'Consultoría', 3, 'Servicio', 'Servicios profesionales', 'fa-user-tie', 4, 'Activo'),
(65, 'Materias Primas Químicas', NULL, 'Materia Prima', 'Químicos y componentes para fabricación', 'fa-flask', 1, 'Activo'),
(66, 'Materiales y Herramientas', NULL, 'Material', 'Materiales de trabajo y herramientas', 'fa-tools', 2, 'Activo'),
(67, 'Servicios', NULL, 'Servicio', 'Servicios contratados', 'fa-handshake', 3, 'Activo'),
(68, 'Resinas', 1, 'Materia Prima', 'Resinas acrílicas, epóxicas, etc.', 'fa-vial', 1, 'Activo'),
(69, 'Solventes Fuertes', 1, 'Materia Prima', 'Solventes y diluyentes', 'fa-flask-vial', 2, 'Activo'),
(70, 'Pigmentos', 1, 'Materia Prima', 'Pigmentos y colorantes', 'fa-palette', 3, 'Activo'),
(71, 'Aditivos', 1, 'Materia Prima', 'Aditivos y modificadores', 'fa-prescription-bottle', 4, 'Activo'),
(72, 'Cargas', 1, 'Materia Prima', 'Cargas minerales', 'fa-cubes', 5, 'Activo'),
(73, 'Envases', 2, 'Material', 'Cubetas, tambos, envases', 'fa-bucket', 1, 'Activo'),
(74, 'Herramientas', 2, 'Material', 'Espátulas, brochas, rodillos', 'fa-screwdriver-wrench', 2, 'Activo'),
(75, 'Empaques', 2, 'Material', 'Etiquetas, cajas, embalaje', 'fa-box', 3, 'Activo'),
(76, 'Equipo de Seguridad', 2, 'Material', 'EPP y equipo de protección', 'fa-hard-hat', 4, 'Activo'),
(77, 'Telecomunicaciones', 3, 'Servicio', 'Internet, telefonía', 'fa-wifi', 1, 'Activo'),
(78, 'Mantenimiento', 3, 'Servicio', 'Mantenimiento de equipos', 'fa-wrench', 2, 'Activo'),
(79, 'Transporte', 3, 'Servicio', 'Servicios de logística', 'fa-truck', 3, 'Activo'),
(80, 'Consultoría', 3, 'Servicio', 'Servicios profesionales', 'fa-user-tie', 4, 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias_productos`
--

CREATE TABLE `categorias_productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria_padre_id` int(11) DEFAULT NULL COMMENT 'Para jerarquía de categorías',
  `estatus` enum('Activa','Inactiva') DEFAULT 'Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Categorías de productos terminados';

--
-- Volcado de datos para la tabla `categorias_productos`
--

INSERT INTO `categorias_productos` (`id`, `nombre`, `descripcion`, `categoria_padre_id`, `estatus`, `fecha_creacion`) VALUES
(1, 'Pinturas', 'Pinturas y recubrimientos', NULL, 'Activa', '2025-12-25 08:35:06'),
(2, 'Recubrimientos', 'Recubrimientos especiales', NULL, 'Activa', '2025-12-25 08:35:06'),
(3, 'Preparadores de Superficies', 'Primers, selladores, fondos', NULL, 'Activa', '2025-12-25 08:35:06'),
(4, 'Pastas', 'Pastas y masillas', NULL, 'Activa', '2025-12-25 08:35:06'),
(5, 'Selladores', 'Selladores y tapaporos', NULL, 'Activa', '2025-12-25 08:35:06'),
(6, 'Reventa', 'Productos de reventa directa', NULL, 'Activa', '2025-12-25 08:35:06'),
(7, 'Vinílicas', 'Pinturas vinílicas', 1, 'Activa', '2025-12-25 08:35:06'),
(8, 'Esmaltes', 'Esmaltes y acabados', 1, 'Activa', '2025-12-25 08:35:06'),
(9, 'Impermeabilizantes', 'Impermeabilizantes', 1, 'Activa', '2025-12-25 08:35:06'),
(10, 'Herramientas', 'Espátulas, rodillos, brochas', 6, 'Activa', '2025-12-25 08:35:06'),
(11, 'Accesorios', 'Accesorios diversos', 6, 'Activa', '2025-12-25 08:35:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
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
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Clientes del sistema';

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `codigo`, `razon_social`, `nombre_comercial`, `rfc`, `regimen_fiscal`, `uso_cfdi`, `contacto_nombre`, `telefono`, `email`, `email_facturacion`, `calle`, `numero_exterior`, `numero_interior`, `colonia`, `ciudad`, `estado`, `codigo_postal`, `limite_credito`, `dias_credito`, `saldo_pendiente`, `tipo_cliente`, `estatus`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 'CLI-00000', 'CLIENTE MOSTRADOR', NULL, 'XAXX010101000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0, 1740.00, 'Mostrador', 'Activo', '2025-12-25 09:59:10', '2025-12-25 11:01:45'),
(2, 'CLI-00001', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', 'IT bajio', 'STB150820GH1', '', NULL, 'Ing. Roberto Mendiola', '226620851501', 'cliente1@mail.com', NULL, 'Av. Vallarta', '2440', '4B', 'Arcos Vallarta', 'Guadalajara', 'Jalisto', '44130', 500000.00, 15, 0.00, 'Empresa', 'Activo', '2025-12-25 10:12:32', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos_empleados`
--

CREATE TABLE `contratos_empleados` (
  `id` int(11) NOT NULL,
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
  `contrato_texto` text DEFAULT NULL COMMENT 'Texto completo del contrato generado'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contratos_empleados`
--

INSERT INTO `contratos_empleados` (`id`, `empleado_id`, `version`, `tipo_contrato`, `vigente`, `puesto`, `departamento`, `tipo_trabajador`, `salario_base_mensual`, `salario_base_diario`, `tipo_nomina`, `jornada_laboral`, `fecha_inicio`, `fecha_fin`, `fecha_creacion`, `motivo_cambio`, `creado_por`, `contrato_texto`) VALUES
(1, 1, 1, 'Modificación Salarial', 1, 'Auxiliar Contable', 'Administración', 'Planta', 9500.00, 316.67, 'Quincenal', 'Tiempo Completo', '2025-12-24', NULL, '2025-12-24 17:20:04', 'Cambio de salario de $8,500.00 a $9,500.00', NULL, 'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Modificación Salarial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: EHWEB EHWEB EHWEB\nRFC: PERJ850510HT5\nCURP: PERJ850510HDFRRN01\nNSS: 12345678901\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Auxiliar Contable\nSEGUNDA.- DEPARTAMENTO: Administración\nTERCERA.- TIPO DE TRABAJADOR: Planta\nCUARTA.- SALARIO BASE MENSUAL: $9,500.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $316.67 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2025-12-24\nMOTIVO: Cambio de salario de $8,500.00 a $9,500.00\n\nFecha de generación: 24/12/2025 17:20:04'),
(2, 2, 1, 'Inicial', 1, 'Auxiliar Contable', 'Contabilidad', 'Planta', 7850.00, 261.67, 'Quincenal', 'Tiempo Completo', '2025-12-24', NULL, '2025-12-24 17:37:38', 'Contrato inicial de trabajo', NULL, 'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Inicial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: Pedro Lopez Morales\nRFC: DFVJ850510HT5\nCURP: QPRM541210HOCAOS82\nNSS: 151845111\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Auxiliar Contable\nSEGUNDA.- DEPARTAMENTO: Contabilidad\nTERCERA.- TIPO DE TRABAJADOR: Planta\nCUARTA.- SALARIO BASE MENSUAL: $7,850.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $261.67 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2025-12-24\nMOTIVO: Contrato inicial de trabajo\n\nFecha de generación: 24/12/2025 17:37:38'),
(3, 3, 1, 'Inicial', 1, 'Auxiliar de Ventas', 'Ventas', 'Por Proyecto', 3500.00, 116.67, 'Quincenal', 'Tiempo Completo', '2024-12-24', NULL, '2025-12-24 17:43:35', 'Contrato inicial de trabajo', NULL, 'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Inicial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: Ana Karina Roman Martinez\nRFC: GUMA900515H2A\nCURP: GUMA900515MDFTRN05\nNSS: 656514451\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Auxiliar de Ventas\nSEGUNDA.- DEPARTAMENTO: Ventas\nTERCERA.- TIPO DE TRABAJADOR: Por Proyecto\nCUARTA.- SALARIO BASE MENSUAL: $3,500.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $116.67 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2024-12-24\nMOTIVO: Contrato inicial de trabajo\n\nFecha de generación: 24/12/2025 17:43:35'),
(4, 4, 1, 'Inicial', 1, 'Compradora', 'Compras', 'Planta', 4500.00, 150.00, 'Quincenal', 'Tiempo Completo', '2022-12-24', NULL, '2025-12-24 18:02:32', 'Contrato inicial de trabajo', NULL, 'CONTRATO INDIVIDUAL DE TRABAJO\n\nCONTRATO No. 1\nTipo: Inicial\n\nEntre CHISA RECUBRIMIENTOS (en adelante \"LA EMPRESA\") y:\n\nNOMBRE: Maria Pilar Gomez\nRFC: GOLM8803257K9\nCURP: GOLM880325MJCMRR04\nNSS: 12459785123\n\nCLÁUSULAS:\n\nPRIMERA.- PUESTO: Compradora\nSEGUNDA.- DEPARTAMENTO: Compras\nTERCERA.- TIPO DE TRABAJADOR: Planta\nCUARTA.- SALARIO BASE MENSUAL: $4,500.00 MXN\nQUINTA.- SALARIO BASE DIARIO: $150.00 MXN\nSEXTA.- TIPO DE NÓMINA: Quincenal\nSÉPTIMA.- JORNADA LABORAL: Tiempo Completo\n\nFECHA DE INICIO: 2022-12-24\nMOTIVO: Contrato inicial de trabajo\n\nFecha de generación: 24/12/2025 18:02:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_bancarias`
--

CREATE TABLE `cuentas_bancarias` (
  `id` int(11) NOT NULL,
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
  `usuario_creacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Cuentas bancarias';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas_contables`
--

CREATE TABLE `cuentas_contables` (
  `id` int(11) NOT NULL,
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
  `usuario_creacion` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Catálogo de cuentas contables';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `responsable_id` int(11) DEFAULT NULL,
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_edicion` datetime DEFAULT NULL,
  `fecha_baja` datetime DEFAULT NULL,
  `fecha_alta` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `responsable_id`, `estatus`, `fecha_edicion`, `fecha_baja`, `fecha_alta`) VALUES
(1, 'Ventas', 'Gestión de clientes, cotizaciones y seguimiento comercial', NULL, 1, NULL, NULL, '2025-12-24'),
(2, 'Facturación', 'Emisión de CFDI y control de cobranza', NULL, 1, NULL, NULL, '2025-12-24'),
(3, 'Producción', 'Control de fabricación y estándares de calidad', NULL, 1, NULL, NULL, '2025-12-24'),
(4, 'Obras', 'Gestión de proyectos en campo y aplicación', NULL, 1, NULL, NULL, '2025-12-24'),
(5, 'Compras', 'Adquisición de insumos y trato con proveedores', NULL, 1, NULL, NULL, '2025-12-24'),
(6, 'Almacén', 'Control de inventarios y logística interna', NULL, 1, NULL, NULL, '2025-12-24'),
(7, 'Recursos Humanos', 'Administración de personal y nómina', NULL, 1, NULL, NULL, '2025-12-24'),
(8, 'Contabilidad', 'Registro financiero e impuestos', NULL, 1, NULL, NULL, '2025-12-24'),
(9, 'Dirección General', 'Máxima autoridad, responsable de la estrategia global', NULL, 1, '2025-12-24 00:00:00', NULL, '2025-12-24'),
(10, 'Administración', 'Apoyo general, archivo y atención telefónica', NULL, 1, NULL, NULL, '2025-12-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuentos`
--

CREATE TABLE `descuentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL COMMENT 'Nombre del descuento',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción detallada',
  `tipo_descuento` enum('Porcentaje','Monto Fijo') DEFAULT 'Porcentaje',
  `valor` decimal(10,2) NOT NULL COMMENT 'Porcentaje o monto del descuento',
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_modificacion` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Descuentos y precios especiales';

--
-- Volcado de datos para la tabla `descuentos`
--

INSERT INTO `descuentos` (`id`, `nombre`, `descripcion`, `tipo_descuento`, `valor`, `estatus`, `fecha_creacion`, `fecha_modificacion`) VALUES
(1, 'Descuento Cliente Frecuente', 'Descuento del 10% para clientes frecuentes', 'Porcentaje', 10.00, 'Activo', '2025-12-25 10:51:04', '2025-12-25 10:51:04'),
(2, 'Descuento Mayoreo', 'Descuento del 15% para compras mayores a $10,000', 'Porcentaje', 15.00, 'Activo', '2025-12-25 10:51:04', '2025-12-25 10:51:04'),
(3, 'Descuento Promoción', 'Descuento fijo de $500 en compras especiales', 'Monto Fijo', 500.00, 'Activo', '2025-12-25 10:51:04', '2025-12-25 10:51:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_entregas_almacen`
--

CREATE TABLE `detalle_entregas_almacen` (
  `id` int(11) NOT NULL,
  `entrega_id` int(11) NOT NULL,
  `tipo_detalle` enum('Orden Venta','Obra') NOT NULL,
  `detalle_orden_id` int(11) DEFAULT NULL COMMENT 'ID de detalle_orden_venta',
  `obra_producto_id` int(11) DEFAULT NULL COMMENT 'ID de obras_productos',
  `producto_id` int(11) NOT NULL,
  `cantidad_entregada` decimal(10,2) NOT NULL,
  `movimiento_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos entregados';

--
-- Disparadores `detalle_entregas_almacen`
--
DELIMITER $$
CREATE TRIGGER `tr_actualizar_entrega_almacen` AFTER INSERT ON `detalle_entregas_almacen` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_formulacion`
--

CREATE TABLE `detalle_formulacion` (
  `id` int(11) NOT NULL,
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
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de mezclado'
) ;

--
-- Volcado de datos para la tabla `detalle_formulacion`
--

INSERT INTO `detalle_formulacion` (`id`, `formulacion_id`, `tipo_componente`, `insumo_id`, `producto_id`, `cantidad`, `unidad`, `porcentaje`, `costo_unitario`, `costo_total`, `observaciones`, `orden`) VALUES
(1, 1, 'Insumo', 1, NULL, 0.500, 'g', NULL, 0.00, 0.00, '', 0),
(2, 2, 'Insumo', 1, NULL, 0.500, 'g', NULL, 0.00, 0.00, NULL, 0),
(3, 3, 'Insumo', 1, NULL, 25.000, 'L', NULL, 0.00, 0.00, 'se agregan mililitos extra', 0),
(4, 4, 'Insumo', 1, NULL, 0.500, 'g', NULL, 0.00, 0.00, NULL, 0);

--
-- Disparadores `detalle_formulacion`
--
DELIMITER $$
CREATE TRIGGER `tr_actualizar_costo_formulacion_delete` AFTER DELETE ON `detalle_formulacion` FOR EACH ROW BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = OLD.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = OLD.formulacion_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_actualizar_costo_formulacion_insert` AFTER INSERT ON `detalle_formulacion` FOR EACH ROW BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = NEW.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = NEW.formulacion_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_actualizar_costo_formulacion_update` AFTER UPDATE ON `detalle_formulacion` FOR EACH ROW BEGIN
  UPDATE formulaciones 
  SET costo_total_insumos = (
    SELECT COALESCE(SUM(costo_total), 0) 
    FROM detalle_formulacion 
    WHERE formulacion_id = NEW.formulacion_id
  ),
  costo_total = costo_total_insumos + costo_mano_obra + costo_indirecto
  WHERE id = NEW.formulacion_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_detalle_formulacion_costo` BEFORE INSERT ON `detalle_formulacion` FOR EACH ROW BEGIN
  SET NEW.costo_total = NEW.cantidad * NEW.costo_unitario;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_compra`
--

CREATE TABLE `detalle_orden_compra` (
  `id` int(11) NOT NULL,
  `orden_compra_id` int(11) NOT NULL,
  `insumo_id` int(11) NOT NULL,
  `cantidad_solicitada` decimal(10,2) NOT NULL,
  `cantidad_recibida` decimal(10,2) DEFAULT 0.00,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de órdenes de compra';

--
-- Volcado de datos para la tabla `detalle_orden_compra`
--

INSERT INTO `detalle_orden_compra` (`id`, `orden_compra_id`, `insumo_id`, `cantidad_solicitada`, `cantidad_recibida`, `precio_unitario`, `subtotal`, `observaciones`) VALUES
(1, 1, 1, 5.00, 5.00, 100.00, 500.00, NULL),
(7, 2, 1, 20.00, 15.00, 100.00, 2000.00, NULL);

--
-- Disparadores `detalle_orden_compra`
--
DELIMITER $$
CREATE TRIGGER `trg_actualizar_totales_oc` AFTER INSERT ON `detalle_orden_compra` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_calcular_subtotal_detalle` BEFORE INSERT ON `detalle_orden_compra` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad_solicitada * NEW.precio_unitario;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_calcular_subtotal_detalle_update` BEFORE UPDATE ON `detalle_orden_compra` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad_solicitada * NEW.precio_unitario;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_produccion`
--

CREATE TABLE `detalle_orden_produccion` (
  `id` int(11) NOT NULL,
  `orden_produccion_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `formulacion_id` int(11) DEFAULT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `unidad_medida` varchar(20) DEFAULT 'kg',
  `completado` tinyint(1) DEFAULT 0,
  `fecha_completado` datetime DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en órdenes de producción';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_orden_venta`
--

CREATE TABLE `detalle_orden_venta` (
  `id` int(11) NOT NULL,
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
  `formulacion_version` varchar(50) DEFAULT NULL COMMENT 'Versión de la formulación usada'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Detalle de productos en órdenes de venta';

--
-- Volcado de datos para la tabla `detalle_orden_venta`
--

INSERT INTO `detalle_orden_venta` (`id`, `orden_venta_id`, `producto_id`, `cantidad`, `cantidad_entregada`, `precio_unitario`, `descuento`, `subtotal`, `stock_disponible_al_crear`, `requiere_produccion`, `observaciones`, `formulacion_id`, `formulacion_version`) VALUES
(1, 1, 3, 1.00, 0.00, 500.00, 0.00, 500.00, 0.00, 1, NULL, NULL, NULL),
(2, 2, 3, 1.00, 0.00, 500.00, 0.00, 500.00, -1.00, 1, NULL, NULL, NULL),
(3, 3, 3, 1.00, 0.00, 500.00, 0.00, 500.00, -2.00, 1, NULL, NULL, NULL),
(4, 4, 3, 1.00, 0.00, 500.00, 0.00, 500.00, -3.00, 1, NULL, NULL, NULL),
(5, 5, 3, 10.00, 0.00, 500.00, 0.00, 5000.00, -4.00, 1, NULL, NULL, NULL),
(6, 6, 3, 1.00, 0.00, 500.00, 0.00, 500.00, -14.00, 1, NULL, NULL, NULL),
(7, 7, 3, 4.00, 0.00, 500.00, 0.00, 2000.00, -15.00, 1, NULL, NULL, NULL),
(8, 8, 3, 4.00, 0.00, 500.00, 0.00, 2000.00, -19.00, 1, NULL, NULL, NULL),
(9, 9, 3, 4.00, 0.00, 500.00, 0.00, 2000.00, -23.00, 1, NULL, NULL, NULL),
(10, 10, 3, 4.00, 0.00, 500.00, 0.00, 2000.00, -27.00, 1, NULL, NULL, NULL),
(11, 11, 3, 4.00, 0.00, 500.00, 0.00, 2000.00, -31.00, 1, NULL, NULL, NULL),
(12, 12, 3, 4.00, 0.00, 500.00, 0.00, 2000.00, -35.00, 1, NULL, NULL, NULL),
(13, 13, 3, 2.00, 0.00, 500.00, 0.00, 1000.00, -39.00, 1, NULL, 4, '4'),
(14, 14, 3, 3.00, 0.00, 500.00, 0.00, 1500.00, -39.00, 1, NULL, 4, '4'),
(15, 15, 3, 5.00, 0.00, 500.00, 0.00, 2500.00, -42.00, 1, NULL, NULL, NULL);

--
-- Disparadores `detalle_orden_venta`
--
DELIMITER $$
CREATE TRIGGER `tr_actualizar_totales_ov_delete` AFTER DELETE ON `detalle_orden_venta` FOR EACH ROW BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = OLD.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = OLD.orden_venta_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_actualizar_totales_ov_insert` AFTER INSERT ON `detalle_orden_venta` FOR EACH ROW BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = NEW.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = NEW.orden_venta_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_actualizar_totales_ov_update` AFTER UPDATE ON `detalle_orden_venta` FOR EACH ROW BEGIN
  UPDATE ordenes_venta 
  SET subtotal = (
    SELECT COALESCE(SUM(subtotal), 0) FROM detalle_orden_venta WHERE orden_venta_id = NEW.orden_venta_id
  ),
  iva = subtotal * 0.16,
  total = subtotal * 1.16
  WHERE id = NEW.orden_venta_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_detalle_ov_subtotal_insert` BEFORE INSERT ON `detalle_orden_venta` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario * (1 - NEW.descuento / 100);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_detalle_ov_subtotal_update` BEFORE UPDATE ON `detalle_orden_venta` FOR EACH ROW BEGIN
  SET NEW.subtotal = NEW.cantidad * NEW.precio_unitario * (1 - NEW.descuento / 100);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ejercicios_fiscales`
--

CREATE TABLE `ejercicios_fiscales` (
  `id` int(11) NOT NULL,
  `año` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estatus` enum('Abierto','Cerrado') DEFAULT 'Abierto',
  `fecha_cierre` datetime DEFAULT NULL,
  `usuario_cierre` int(11) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Ejercicios fiscales';

--
-- Volcado de datos para la tabla `ejercicios_fiscales`
--

INSERT INTO `ejercicios_fiscales` (`id`, `año`, `fecha_inicio`, `fecha_fin`, `estatus`, `fecha_cierre`, `usuario_cierre`, `fecha_creacion`) VALUES
(1, 2025, '2025-01-01', '2025-12-31', 'Abierto', NULL, NULL, '2025-12-25 23:18:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id` int(11) NOT NULL,
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
  `regimen_fiscal` varchar(10) DEFAULT NULL,
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
  `pension_alimenticia_porcentaje` decimal(5,2) DEFAULT 0.00,
  `pension_alimenticia_monto` decimal(10,2) DEFAULT 0.00,
  `isr_porcentaje` decimal(5,2) DEFAULT 0.00,
  `imss_cuota` decimal(10,2) DEFAULT 0.00,
  `infonavit_aportacion` decimal(10,2) DEFAULT 0.00,
  `afore_aportacion` decimal(10,2) DEFAULT 0.00,
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
  `usuario_edicion_id` int(11) DEFAULT NULL COMMENT 'FK a administradores'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id`, `numero_empleado`, `nombre`, `apellido_paterno`, `apellido_materno`, `fecha_nacimiento`, `genero`, `estado_civil`, `telefono`, `telefono_emergencia`, `email_personal`, `email_corporativo`, `calle`, `numero_exterior`, `numero_interior`, `colonia`, `codigo_postal`, `ciudad`, `estado`, `pais`, `rfc`, `regimen_fiscal`, `curp`, `nss`, `afore`, `afore_numero_cuenta`, `tipo_trabajador`, `departamento_id`, `puesto`, `jefe_directo_id`, `fecha_ingreso`, `fecha_baja`, `motivo_baja`, `salario_base_mensual`, `pension_alimenticia_porcentaje`, `pension_alimenticia_monto`, `isr_porcentaje`, `imss_cuota`, `infonavit_aportacion`, `afore_aportacion`, `salario_base_diario`, `tipo_nomina`, `forma_pago`, `banco`, `cuenta_bancaria`, `tiene_fonacot`, `tiene_infonavit`, `descuento_infonavit`, `estatus`, `fecha_alta`, `fecha_edicion`, `usuario_alta_id`, `usuario_edicion_id`) VALUES
(1, 'EMP-2025-0001', 'EHWEB', 'EHWEB', 'EHWEB', '1993-08-22', 'M', 'Soltero', '5546852145', NULL, 'soporte2@especialistasweb.com.mx', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', 'PERJ850510HT5', NULL, 'PERJ850510HDFRRN01', '12345678901', NULL, NULL, 'Planta', 10, 'Auxiliar Contable', 0, '2025-12-24', NULL, NULL, 9500.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 316.67, 'Quincenal', 'Transferencia', 'BBVA', '125487456985423654', 0, 0, 0.00, 1, '2025-12-24', '2025-12-24', NULL, NULL),
(2, 'EMP-2025-0002', 'Pedro', 'Lopez', 'Morales', '1982-06-30', 'M', 'Casado', '5546521546', '', 'mail1@mail.com', '', '', '', '', '', '', '', '', 'México', 'DFVJ850510HT5', NULL, 'QPRM541210HOCAOS82', '151845111', NULL, NULL, 'Planta', 8, 'Auxiliar Contable', 0, '2025-12-24', NULL, NULL, 7850.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 261.67, 'Quincenal', 'Transferencia', '', '', 0, 0, 0.00, 1, '2025-12-24', '2026-01-21', NULL, NULL),
(3, 'EMP-2025-0003', 'Ana Karina', 'Roman', 'Martinez', '1975-02-12', 'F', 'Casado', '6655331221', NULL, 'karina@mail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', 'GUMA900515H2A', NULL, 'GUMA900515MDFTRN05', '656514451', NULL, NULL, 'Por Proyecto', 1, 'Auxiliar de Ventas', 1, '2024-12-24', NULL, NULL, 3500.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 116.67, 'Quincenal', 'Efectivo', 'Santander', '126475142365478412', 0, 0, 0.00, 1, '2025-12-24', NULL, NULL, NULL),
(4, 'EMP-2025-0004', 'Maria', 'Pilar', 'Gomez', '1982-12-25', 'F', 'Divorciado', '1236545645', NULL, 'mariapilar@mail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'México', 'GOLM8803257K9', NULL, 'GOLM880325MJCMRR04', '12459785123', NULL, NULL, 'Planta', 5, 'Compradora', 3, '2022-12-24', NULL, NULL, 4500.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 150.00, 'Quincenal', 'Transferencia', 'bbva', '124574896542354125', 0, 0, 0.00, 1, '2025-12-24', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entregas_almacen`
--

CREATE TABLE `entregas_almacen` (
  `id` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL COMMENT 'ENT-2025-0001',
  `tipo_origen` enum('Orden Venta','Obra') NOT NULL,
  `orden_venta_id` int(11) DEFAULT NULL,
  `obra_id` int(11) DEFAULT NULL,
  `fecha_entrega` datetime NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Activa','Cancelada') DEFAULT 'Activa',
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de entregas de almacén (órdenes y obras)';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas`
--

CREATE TABLE `facturas` (
  `id` int(11) NOT NULL,
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
  `estatus` enum('Emitida','Cancelada') DEFAULT 'Emitida'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de facturas emitidas';

--
-- Volcado de datos para la tabla `facturas`
--

INSERT INTO `facturas` (`id`, `orden_venta_id`, `cliente_id`, `rfc`, `razon_social`, `regimen_fiscal`, `uso_cfdi`, `codigo_postal`, `folio_fiscal`, `serie`, `folio`, `fecha_emision`, `subtotal`, `iva`, `total`, `estatus`) VALUES
(1, 10, 2, 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', '616', 'G03', '44130', '9372a72c-2be4-4c98-88cc-516312871f29', 'A', 'F-', '2025-12-25 12:40:43', 2000.00, 320.00, 2320.00, 'Emitida'),
(2, 11, 2, 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', '616', 'G03', '44130', 'f26b518e-20c3-4ee5-99f9-d0a43a8217dc', 'A', 'F-OV-2025-0011', '2025-12-25 12:41:53', 2000.00, 320.00, 2320.00, 'Emitida'),
(3, 12, 2, 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', '616', 'G03', '44130', '827d4c1e-8093-4920-92ca-cea9aba7fcc5', 'A', 'F-OV-2025-0012', '2025-12-25 12:52:36', 2000.00, 288.00, 2088.00, 'Emitida'),
(4, 13, 2, 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', '616', 'G03', '44130', '028eb165-e005-4cc1-a8e1-3d273a06b1cc', 'A', 'F-OV-2025-0013', '2025-12-25 13:06:24', 1000.00, 160.00, 1160.00, 'Emitida'),
(5, 14, 2, 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', '616', 'G03', '44130', '31ad8719-0eca-435c-8660-691ac8314cab', 'A', 'F-OV-2025-0014', '2025-12-25 13:40:21', 1500.00, 240.00, 1740.00, 'Emitida'),
(6, 15, 2, 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', '616', 'G03', '44130', '8129b05a-5281-4bb4-b0f8-56e18da1416b', 'A', 'F-OV-2025-0015', '2025-12-25 13:50:13', 2500.00, 360.00, 2610.00, 'Emitida');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facturas_obras`
--

CREATE TABLE `facturas_obras` (
  `id` int(10) UNSIGNED NOT NULL,
  `obra_id` int(10) UNSIGNED NOT NULL,
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
  `creado_por` int(10) UNSIGNED NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Facturas simuladas generadas para obras';

--
-- Volcado de datos para la tabla `facturas_obras`
--

INSERT INTO `facturas_obras` (`id`, `obra_id`, `folio`, `fecha_emision`, `subtotal`, `iva`, `total`, `rfc_emisor`, `razon_social_emisor`, `direccion_emisor`, `rfc_receptor`, `razon_social_receptor`, `direccion_receptor`, `notas`, `creado_por`, `fecha_creacion`) VALUES
(1, 2, 'F-OB-00001', '2025-12-25 21:24:18', 36000.00, 5760.00, 41760.00, 'XAXX010101000', 'Mi Empresa S.A. de C.V.', 'Calle Principal #123, Col. Centro', 'STB150820GH1', 'Soluciones Tecnológicas del Bajío S.A. de C.V.', 'Amores 50, int b', NULL, 1, '2025-12-25 21:24:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formulaciones`
--

CREATE TABLE `formulaciones` (
  `id` int(11) NOT NULL,
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
  `fecha_activacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formulaciones de productos (BOM)';

--
-- Volcado de datos para la tabla `formulaciones`
--

INSERT INTO `formulaciones` (`id`, `producto_id`, `version`, `nombre_version`, `descripcion`, `cantidad_producida`, `unidad_produccion`, `costo_total_insumos`, `costo_mano_obra`, `costo_indirecto`, `costo_total`, `es_activa`, `fecha_creacion`, `usuario_creacion`, `fecha_activacion`) VALUES
(1, 3, 1, 'V1.0', 'Formulación para el hospital IMSS Monterrey', 19.00, 'L', 0.00, 200.00, 80.00, 280.00, 1, '2025-12-25 09:27:22', NULL, NULL),
(2, 3, 2, 'V1.0', 'Formulación para el hospital IMSS Monterrey', 19.00, 'L', 0.00, 200.00, 60.00, 260.00, 1, '2025-12-25 09:27:57', NULL, NULL),
(3, 3, 3, 'V1.1', 'Formulación para el hospital IMSS Monterrey', 19.00, 'L', 0.00, 220.00, 80.00, 300.00, 1, '2025-12-25 09:44:14', NULL, NULL),
(4, 3, 4, 'V1.4', 'Formulación para el hospital IMSS Guadalajara', 19.00, 'L', 0.00, 200.00, 80.00, 280.00, 1, '2025-12-25 09:45:32', NULL, NULL);

--
-- Disparadores `formulaciones`
--
DELIMITER $$
CREATE TRIGGER `tr_actualizar_costo_producto` AFTER UPDATE ON `formulaciones` FOR EACH ROW BEGIN
  IF NEW.es_activa = TRUE THEN
    UPDATE productos 
    SET costo_produccion = NEW.costo_total
    WHERE id = NEW.producto_id;
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_empleados`
--

CREATE TABLE `horarios_empleados` (
  `id` int(11) NOT NULL,
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
  `estatus` enum('Activo','Inactivo') DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Horarios laborales de empleados por día de la semana';

--
-- Volcado de datos para la tabla `horarios_empleados`
--

INSERT INTO `horarios_empleados` (`id`, `empleado_id`, `dia_semana`, `hora_entrada`, `hora_salida`, `hora_entrada_comida`, `hora_salida_comida`, `es_dia_laboral`, `turno`, `fecha_inicio`, `fecha_fin`, `observaciones`, `creado_por`, `fecha_creacion`, `estatus`) VALUES
(11, 4, 'Lunes', '09:00:00', '18:00:00', '14:00:00', '15:00:00', 1, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo'),
(12, 4, 'Martes', '09:00:00', '18:00:00', '14:00:00', '15:00:00', 1, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo'),
(13, 4, 'Miércoles', '09:00:00', '18:00:00', '14:00:00', '15:00:00', 1, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo'),
(14, 4, 'Jueves', '09:00:00', '18:00:00', '14:00:00', '15:00:00', 1, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo'),
(15, 4, 'Viernes', '09:00:00', '18:00:00', '14:00:00', '15:00:00', 1, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo'),
(16, 4, 'Sábado', '00:00:00', '00:00:00', NULL, NULL, 0, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo'),
(17, 4, 'Domingo', '00:00:00', '00:00:00', NULL, NULL, 0, NULL, '2025-12-25', NULL, NULL, NULL, '2025-12-25 02:26:03', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias_empleados`
--

CREATE TABLE `incidencias_empleados` (
  `id` int(11) NOT NULL,
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
  `estatus` enum('Activa','Cancelada','Procesada') DEFAULT 'Activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de incidencias de empleados';

--
-- Volcado de datos para la tabla `incidencias_empleados`
--

INSERT INTO `incidencias_empleados` (`id`, `empleado_id`, `tipo_incidencia`, `fecha_incidencia`, `hora_incidencia`, `descripcion`, `observaciones`, `tiene_descuento`, `monto_descuento`, `archivo_adjunto`, `registrado_por`, `fecha_registro`, `estatus`) VALUES
(1, 4, 'Falta Justificada', '2025-12-24', '00:00:00', 'Se solicitó una baja justificada', 'Se entrego una solicitud de falta y se otorgo permiso sin descuento', 0, 0.00, NULL, NULL, '2025-12-24 19:32:46', 'Activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `insumos`
--

CREATE TABLE `insumos` (
  `id` int(11) NOT NULL,
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
  `registrado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de insumos para compra (materias primas, materiales, servicios)';

--
-- Volcado de datos para la tabla `insumos`
--

INSERT INTO `insumos` (`id`, `codigo`, `nombre_tecnico`, `alias`, `marca`, `descripcion`, `categoria_id`, `unidad_medida`, `precio_promedio`, `stock_minimo`, `stock_actual`, `stock_maximo`, `ubicacion_almacen`, `ficha_tecnica`, `hoja_seguridad`, `es_peligroso`, `requiere_refrigeracion`, `vida_util_dias`, `observaciones`, `imagen`, `estatus`, `fecha_registro`, `registrado_por`) VALUES
(1, 'INS00001', 'Pintura Vinílica Blanca', 'Pintura Vinílica Blanca', 'COMEX', 'Base pintura blanca', 70, 'Cubeta', 0.00, 5.00, 120.00, NULL, NULL, NULL, NULL, 0, 0, NULL, NULL, NULL, 'Activo', '2025-12-25 07:31:12', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lotes_produccion`
--

CREATE TABLE `lotes_produccion` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lotes de productos terminados con códigos de barras para trazabilidad';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_bancarios`
--

CREATE TABLE `movimientos_bancarios` (
  `id` int(11) NOT NULL,
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
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Movimientos bancarios';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tipo_movimiento` enum('Entrada','Salida','Ajuste') NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de orden, producción, etc.',
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `producto_id`, `tipo_movimiento`, `cantidad`, `motivo`, `referencia`, `fecha_movimiento`, `usuario_id`) VALUES
(1, 3, 'Salida', 1.00, 'Venta - Orden 3', NULL, '2025-12-25 10:30:53', NULL),
(2, 3, 'Salida', 1.00, 'Venta - Orden 4', NULL, '2025-12-25 11:25:19', NULL),
(3, 3, 'Salida', 10.00, 'Venta - Orden 5', NULL, '2025-12-25 11:30:27', NULL),
(4, 3, 'Salida', 1.00, 'Venta - Orden 6', NULL, '2025-12-25 11:31:20', NULL),
(5, 3, 'Salida', 4.00, 'Venta - Orden 7', NULL, '2025-12-25 12:37:40', NULL),
(6, 3, 'Salida', 4.00, 'Venta - Orden 8', NULL, '2025-12-25 12:37:57', NULL),
(7, 3, 'Salida', 4.00, 'Venta - Orden 9', NULL, '2025-12-25 12:39:20', NULL),
(8, 3, 'Salida', 4.00, 'Venta - Orden 10', NULL, '2025-12-25 12:40:43', NULL),
(9, 3, 'Salida', 4.00, 'Venta - Orden 11', NULL, '2025-12-25 12:41:53', NULL),
(10, 3, 'Salida', 4.00, 'Venta - Orden 12', NULL, '2025-12-25 12:52:36', NULL),
(11, 3, 'Salida', 3.00, 'Venta - Orden 14', NULL, '2025-12-25 13:40:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_productos`
--

CREATE TABLE `movimientos_productos` (
  `id` int(11) NOT NULL,
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
  `fecha_movimiento` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Movimientos de inventario de productos';

--
-- Disparadores `movimientos_productos`
--
DELIMITER $$
CREATE TRIGGER `tr_actualizar_stock_producto` AFTER INSERT ON `movimientos_productos` FOR EACH ROW BEGIN
  UPDATE productos 
  SET stock_actual = NEW.stock_nuevo
  WHERE id = NEW.producto_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas`
--

CREATE TABLE `nominas` (
  `id` int(11) NOT NULL,
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
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Nóminas';

--
-- Volcado de datos para la tabla `nominas`
--

INSERT INTO `nominas` (`id`, `folio`, `periodo_inicio`, `periodo_fin`, `tipo_nomina`, `fecha_pago`, `total_percepciones`, `total_deducciones`, `total_neto`, `poliza_id`, `estatus`, `observaciones`, `usuario_creacion`, `fecha_creacion`) VALUES
(1, 'NOM000001', '2025-12-15', '2025-12-31', 'Quincenal', '2025-12-25', 0.00, 0.00, 0.00, NULL, 'Borrador', NULL, NULL, '2025-12-25 23:57:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas_conceptos`
--

CREATE TABLE `nominas_conceptos` (
  `id` int(11) NOT NULL,
  `nomina_detalle_id` int(11) NOT NULL,
  `tipo` enum('Percepción','Deducción') NOT NULL,
  `concepto` varchar(100) NOT NULL COMMENT 'Sueldo, Bono, ISR, IMSS, etc.',
  `monto` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Conceptos de percepciones y deducciones';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nominas_detalle`
--

CREATE TABLE `nominas_detalle` (
  `id` int(11) NOT NULL,
  `nomina_id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `dias_trabajados` decimal(5,2) DEFAULT 0.00,
  `sueldo_base` decimal(10,2) DEFAULT 0.00,
  `percepciones` decimal(10,2) DEFAULT 0.00 COMMENT 'Total de percepciones',
  `deducciones` decimal(10,2) DEFAULT 0.00 COMMENT 'Total de deducciones',
  `neto` decimal(10,2) DEFAULT 0.00 COMMENT 'Neto a pagar',
  `estatus` enum('Pendiente','Pagado','Cancelado') DEFAULT 'Pendiente',
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de nóminas por empleado';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras`
--

CREATE TABLE `obras` (
  `id` int(10) UNSIGNED NOT NULL,
  `folio` varchar(50) NOT NULL COMMENT 'Folio único de la obra (ej: OB-00001)',
  `nombre` varchar(255) NOT NULL COMMENT 'Nombre descriptivo de la obra',
  `cliente_id` int(10) UNSIGNED NOT NULL COMMENT 'ID del cliente asociado',
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
  `responsable_tecnico_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario responsable técnico',
  `responsable_ventas_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'Usuario responsable de ventas',
  `creado_por` int(10) UNSIGNED NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `modificado_por` int(10) UNSIGNED DEFAULT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla principal de obras para cálculo de materiales';

--
-- Volcado de datos para la tabla `obras`
--

INSERT INTO `obras` (`id`, `folio`, `nombre`, `cliente_id`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `coordenadas_gps`, `area_total`, `tipo_superficie`, `condiciones_ambientales`, `especificaciones_tecnicas`, `costo_estimado`, `costo_real`, `subtotal`, `descuento_porcentaje`, `descuento_monto`, `iva_porcentaje`, `iva_monto`, `total`, `margen_utilidad`, `utilidad_neta`, `condiciones_pago`, `tiempo_entrega`, `anticipo_porcentaje`, `anticipo_monto`, `total_pagado`, `saldo_pendiente`, `estatus_pago`, `estatus`, `porcentaje_avance`, `fecha_inicio_estimada`, `fecha_fin_estimada`, `fecha_inicio_real`, `fecha_fin_real`, `descripcion`, `notas_internas`, `responsable_tecnico_id`, `responsable_ventas_id`, `creado_por`, `fecha_creacion`, `modificado_por`, `fecha_modificacion`, `activo`) VALUES
(1, 'OB-00001', 'EHWEB', 2, 'Avenida Amores, NO 605 Int A', 'CDMX', 'BENITO JUAREZ', '04000', NULL, 10.00, 'Tablaroca', NULL, NULL, 50000.00, 0.00, 5000.00, 0.00, 0.00, 16.00, 800.00, 5800.00, 100.00, 5800.00, '50% anticipo', '25 días hábiles', 10.00, 580.00, 0.00, 0.00, 'Pendiente', 'Planificación', 0.00, '2025-12-28', '2025-12-31', NULL, NULL, 'Prueba de nueva obnra', NULL, NULL, NULL, 1, '2025-12-25 18:43:40', NULL, '2025-12-25 19:02:30', 1),
(2, 'OB-00002', 'Hospital Luz', 2, 'Amores 50, int b', 'CDMX', 'BENITO JUAREZ', '04000', NULL, 25.00, 'Rugosa', 'Humedad alta', '', 35000.00, 0.00, 36000.00, 0.00, 0.00, 16.00, 5760.00, 41760.00, 100.00, 41760.00, '', '15', 10.00, 4176.00, 2000.00, 39760.00, 'Anticipo Recibido', 'En Ejecución', 20.00, '2025-12-25', '2026-01-11', NULL, NULL, 'Prueba', NULL, NULL, NULL, 1, '2025-12-25 19:21:42', 1, '2026-01-14 11:26:33', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras_archivos`
--

CREATE TABLE `obras_archivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `obra_id` int(10) UNSIGNED NOT NULL,
  `nombre_original` varchar(255) NOT NULL COMMENT 'Nombre original del archivo',
  `nombre_archivo` varchar(255) NOT NULL COMMENT 'Nombre del archivo en el servidor',
  `ruta_archivo` varchar(500) NOT NULL COMMENT 'Ruta completa del archivo',
  `tipo_archivo` varchar(100) NOT NULL COMMENT 'Tipo MIME del archivo',
  `extension` varchar(10) NOT NULL COMMENT 'Extensión del archivo',
  `tamano` int(10) UNSIGNED NOT NULL COMMENT 'Tamaño en bytes',
  `categoria` enum('Foto','Plano','CAD','Documento','Otro') NOT NULL DEFAULT 'Otro',
  `descripcion` text DEFAULT NULL COMMENT 'Descripción del archivo',
  `etiquetas` varchar(500) DEFAULT NULL COMMENT 'Etiquetas separadas por comas',
  `subido_por` int(10) UNSIGNED NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Archivos adjuntos asociados a las obras';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras_comentarios`
--

CREATE TABLE `obras_comentarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `obra_id` int(10) UNSIGNED NOT NULL,
  `comentario` text NOT NULL,
  `tipo` enum('General','Técnico','Avance','Problema','Solución') NOT NULL DEFAULT 'General',
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `fecha_comentario` datetime NOT NULL DEFAULT current_timestamp(),
  `editado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_edicion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Comentarios y seguimiento de las obras';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras_pagos`
--

CREATE TABLE `obras_pagos` (
  `id` int(11) NOT NULL,
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
  `activo` tinyint(1) DEFAULT 1 COMMENT 'Pago activo (para cancelaciones)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos recibidos de obras';

--
-- Volcado de datos para la tabla `obras_pagos`
--

INSERT INTO `obras_pagos` (`id`, `obra_id`, `folio_recibo`, `fecha_pago`, `monto`, `metodo_pago`, `referencia`, `concepto`, `notas`, `recibido_por`, `fecha_registro`, `activo`) VALUES
(1, 2, 'REC-00001', '2025-12-25 19:23:32', 2000.00, 'Transferencia', '', 'Pago Parcial', 'Pago 1', 1, '2025-12-25 19:23:32', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras_productos`
--

CREATE TABLE `obras_productos` (
  `id` int(10) UNSIGNED NOT NULL,
  `obra_id` int(10) UNSIGNED NOT NULL,
  `producto_id` int(10) UNSIGNED NOT NULL COMMENT 'ID del producto del catálogo',
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
  `agregado_por` int(10) UNSIGNED NOT NULL,
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Productos y materiales calculados para cada obra';

--
-- Volcado de datos para la tabla `obras_productos`
--

INSERT INTO `obras_productos` (`id`, `obra_id`, `producto_id`, `cantidad_calculada`, `cantidad_ajustada`, `cantidad_entregada`, `unidad`, `area_aplicacion`, `rendimiento_teorico`, `factor_desperdicio`, `notas`, `seccion_obra`, `precio_unitario`, `formulacion_id`, `formulacion_version`, `fecha_agregado`, `agregado_por`, `fecha_modificacion`) VALUES
(2, 1, 3, 1.10, 10.00, 0.00, 'Cubeta', 5.00, 5.00, 1.10, 'Prueba', 'sala 1', 500.00, NULL, NULL, '2025-12-25 19:02:30', 1, NULL),
(3, 2, 3, 2.75, 2.00, 0.00, 'Cubeta', 25.00, 10.00, 1.10, '2 cubetas', 'Sala 1', 500.00, 4, 4.0, '2025-12-25 19:22:35', 1, '2025-12-25 19:42:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_compra`
--

CREATE TABLE `ordenes_compra` (
  `id` int(11) NOT NULL,
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
  `fecha_aprobacion` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de compra a proveedores';

--
-- Volcado de datos para la tabla `ordenes_compra`
--

INSERT INTO `ordenes_compra` (`id`, `folio`, `proveedor_id`, `fecha_orden`, `fecha_entrega_estimada`, `fecha_entrega_real`, `subtotal`, `iva`, `total`, `forma_pago`, `condiciones_pago`, `observaciones`, `estatus`, `creado_por`, `fecha_creacion`, `aprobado_por`, `fecha_aprobacion`) VALUES
(1, 'OC-2025-0001', 1, '2025-12-25', '2025-12-27', '2025-12-25', 500.00, 80.00, 580.00, 'Transferencia', '', '', 'Recibida', NULL, '2025-12-25 08:10:23', NULL, '2025-12-25 08:11:06'),
(2, 'OC-2025-0002', 1, '2025-12-25', '2025-12-26', '2025-12-25', 2000.00, 320.00, 2320.00, 'Transferencia', '', '', 'Recibida Parcial', NULL, '2025-12-25 08:13:11', NULL, '2025-12-25 08:14:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_produccion`
--

CREATE TABLE `ordenes_produccion` (
  `id` int(11) NOT NULL,
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
  `orden_venta_id` int(11) DEFAULT NULL COMMENT 'Orden de venta que generó esta producción'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de producción';

--
-- Disparadores `ordenes_produccion`
--
DELIMITER $$
CREATE TRIGGER `tr_op_actualizar_solicitudes` AFTER UPDATE ON `ordenes_produccion` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ordenes_venta`
--

CREATE TABLE `ordenes_venta` (
  `id` int(11) NOT NULL,
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
  `costo_envio` decimal(10,2) DEFAULT 0.00 COMMENT 'Costo adicional por envío a domicilio'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Órdenes de venta y cotizaciones';

--
-- Volcado de datos para la tabla `ordenes_venta`
--

INSERT INTO `ordenes_venta` (`id`, `folio`, `cliente_id`, `fecha_orden`, `fecha_entrega_estimada`, `fecha_entrega_real`, `subtotal`, `iva`, `total`, `monto_pagado`, `saldo_pendiente`, `estatus_pago`, `forma_pago`, `descuento_id`, `descuento_nombre`, `descuento_tipo`, `descuento_valor`, `descuento_aplicado`, `condiciones_pago`, `estatus`, `tipo_venta`, `observaciones`, `motivo_cancelacion`, `requiere_produccion`, `creado_por`, `fecha_creacion`, `fecha_modificacion`, `direccion_envio`, `costo_envio`) VALUES
(1, 'OV-2025-0001', 1, '2025-12-25', NULL, NULL, 500.00, 80.00, 580.00, 0.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', '', NULL, 0, NULL, '2025-12-25 10:29:20', '2025-12-25 11:29:36', NULL, 0.00),
(2, 'OV-2025-0002', 1, '2025-12-25', NULL, NULL, 500.00, 80.00, 580.00, 0.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', '', NULL, 0, NULL, '2025-12-25 10:30:00', '2025-12-25 11:29:36', NULL, 0.00),
(3, 'OV-2025-0003', 1, '2025-12-25', NULL, '2025-12-25', 500.00, 80.00, 580.00, 0.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', '', NULL, 0, NULL, '2025-12-25 10:30:53', '2025-12-25 11:29:36', NULL, 0.00),
(4, 'OV-2025-0004', 2, '2025-12-25', NULL, '2025-12-25', 500.00, 80.00, 580.00, 0.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', '', NULL, 0, NULL, '2025-12-25 11:25:19', '2025-12-25 11:29:36', NULL, 0.00),
(5, 'OV-2025-0005', 2, '2025-12-25', NULL, '2025-12-25', 5000.00, 800.00, 5800.00, 5800.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', '', NULL, 0, NULL, '2025-12-25 11:30:27', '2025-12-25 11:30:27', NULL, 0.00),
(6, 'OV-2025-0006', 1, '2025-12-25', NULL, '2025-12-25', 500.00, 80.00, 580.00, 0.00, 0.00, 'Pendiente', 'Crédito', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', '', NULL, 0, NULL, '2025-12-25 11:31:20', '2025-12-25 11:31:20', NULL, 0.00),
(7, 'OV-2025-0007', 2, '2025-12-25', NULL, '2025-12-25', 2000.00, 320.00, 2320.00, 0.00, 0.00, 'Pendiente', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', 'Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara', NULL, 0, NULL, '2025-12-25 12:37:40', '2025-12-25 12:37:40', NULL, 0.00),
(8, 'OV-2025-0008', 2, '2025-12-25', NULL, '2025-12-25', 2000.00, 320.00, 2320.00, 0.00, 0.00, 'Pendiente', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', 'Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara', NULL, 0, NULL, '2025-12-25 12:37:56', '2025-12-25 12:37:57', NULL, 0.00),
(9, 'OV-2025-0009', 2, '2025-12-25', NULL, '2025-12-25', 2000.00, 320.00, 2320.00, 0.00, 0.00, 'Pendiente', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', 'Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara', NULL, 0, NULL, '2025-12-25 12:39:20', '2025-12-25 12:39:20', NULL, 0.00),
(10, 'OV-2025-0010', 2, '2025-12-25', NULL, '2025-12-25', 2000.00, 320.00, 2320.00, 2320.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', 'Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara', NULL, 0, NULL, '2025-12-25 12:40:43', '2025-12-25 12:40:43', NULL, 0.00),
(11, 'OV-2025-0011', 2, '2025-12-25', NULL, '2025-12-25', 2000.00, 320.00, 2320.00, 2320.00, 0.00, 'Pagado', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Mostrador', 'Se utiliza la misma formualción para chisa glass micro, usada en imss guadalajara', NULL, 0, NULL, '2025-12-25 12:41:53', '2025-12-25 12:41:53', NULL, 0.00),
(12, 'OV-2025-0012', 2, '2025-12-25', NULL, '2025-12-25', 2000.00, 288.00, 2088.00, 0.00, 0.00, 'Pendiente', 'Crédito', 1, 'Descuento Cliente Frecuente', 'Porcentaje', 10.00, 200.00, NULL, 'Entregada', 'Mostrador', 'Prueba de pago pendiente', NULL, 0, NULL, '2025-12-25 12:52:36', '2025-12-25 12:52:36', NULL, 0.00),
(13, 'OV-2025-0013', 2, '2025-12-25', NULL, NULL, 1000.00, 160.00, 1160.00, 0.00, 0.00, 'Pendiente', 'Efectivo', NULL, NULL, NULL, 0.00, 0.00, NULL, 'En Preparación', 'Mostrador', 'prueba 2 con formulación y pago pendiente o cotización', NULL, 1, NULL, '2025-12-25 13:06:24', '2025-12-25 13:07:31', NULL, 0.00),
(14, 'OV-2025-0014', 2, '2025-12-25', '2025-12-27', '2025-12-25', 1500.00, 240.00, 1740.00, 0.00, 0.00, 'Pendiente', 'Crédito', NULL, NULL, NULL, 0.00, 0.00, NULL, 'Entregada', 'Pedido', 'Prueba de compra a crédito', NULL, 0, NULL, '2025-12-25 13:40:21', '2025-12-25 13:40:21', 'Av. Juárez 2915, Int. 301, Col. La Paz, C.P. 72160, Puebla, Puebla, México.', 100.00),
(15, 'OV-2025-0015', 2, '2025-12-25', '2025-12-27', NULL, 2500.00, 360.00, 2610.00, 0.00, 2610.00, 'Pendiente', 'Crédito', 1, 'Descuento Cliente Frecuente', 'Porcentaje', 10.00, 250.00, NULL, 'Confirmada', 'Pedido', 'prueba  con envio y a credito', NULL, 0, NULL, '2025-12-25 13:50:13', '2025-12-25 13:50:13', 'Av. Juárez 2915, Int. 301, Col. La Paz, C.P. 72160, Puebla, Puebla, México.', 100.00);

--
-- Disparadores `ordenes_venta`
--
DELIMITER $$
CREATE TRIGGER `trg_ordenes_venta_calcular_totales` BEFORE UPDATE ON `ordenes_venta` FOR EACH ROW BEGIN
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
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_ordenes`
--

CREATE TABLE `pagos_ordenes` (
  `id` int(11) NOT NULL,
  `orden_venta_id` int(11) NOT NULL,
  `folio` varchar(50) NOT NULL COMMENT 'PAG-2025-0001',
  `fecha_pago` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo_pago` enum('Efectivo','Transferencia','Tarjeta','Cheque') NOT NULL,
  `referencia` varchar(100) DEFAULT NULL COMMENT 'Número de cheque, transferencia, etc.',
  `notas` text DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Pagos aplicados a órdenes de venta';

--
-- Volcado de datos para la tabla `pagos_ordenes`
--

INSERT INTO `pagos_ordenes` (`id`, `orden_venta_id`, `folio`, `fecha_pago`, `monto`, `metodo_pago`, `referencia`, `notas`, `fecha_creacion`) VALUES
(1, 5, 'PAG-2025-0001', '2025-12-25', 5800.00, 'Efectivo', 'Venta Mostrador', 'Pago automático al generar venta', '2025-12-25 11:30:27'),
(2, 10, 'PAG-2025-0002', '2025-12-25', 2320.00, 'Efectivo', 'Pago automático POS', 'Pago registrado automáticamente al cobrar en POS', '2025-12-25 12:40:43'),
(3, 11, 'PAG-2025-0003', '2025-12-25', 2320.00, 'Efectivo', 'Pago automático POS', 'Pago registrado automáticamente al cobrar en POS', '2025-12-25 12:41:53');

--
-- Disparadores `pagos_ordenes`
--
DELIMITER $$
CREATE TRIGGER `trg_pago_actualizar_orden` AFTER INSERT ON `pagos_ordenes` FOR EACH ROW BEGIN
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
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_servicios_recurrentes`
--

CREATE TABLE `pagos_servicios_recurrentes` (
  `id` int(11) NOT NULL,
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
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de pagos de servicios recurrentes';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `periodos_contables`
--

CREATE TABLE `periodos_contables` (
  `id` int(11) NOT NULL,
  `ejercicio_id` int(11) NOT NULL,
  `numero_periodo` int(11) NOT NULL COMMENT 'Número del mes (1-12)',
  `nombre` varchar(50) NOT NULL COMMENT 'Enero, Febrero, etc.',
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estatus` enum('Abierto','Cerrado') DEFAULT 'Abierto',
  `fecha_cierre` datetime DEFAULT NULL,
  `usuario_cierre` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Periodos contables mensuales';

--
-- Volcado de datos para la tabla `periodos_contables`
--

INSERT INTO `periodos_contables` (`id`, `ejercicio_id`, `numero_periodo`, `nombre`, `fecha_inicio`, `fecha_fin`, `estatus`, `fecha_cierre`, `usuario_cierre`) VALUES
(1, 1, 1, 'Enero', '2025-01-01', '2025-01-31', 'Abierto', NULL, NULL),
(2, 1, 2, 'Febrero', '2025-02-01', '2025-02-28', 'Abierto', NULL, NULL),
(3, 1, 3, 'Marzo', '2025-03-01', '2025-03-31', 'Abierto', NULL, NULL),
(4, 1, 4, 'Abril', '2025-04-01', '2025-04-30', 'Abierto', NULL, NULL),
(5, 1, 5, 'Mayo', '2025-05-01', '2025-05-31', 'Abierto', NULL, NULL),
(6, 1, 6, 'Junio', '2025-06-01', '2025-06-30', 'Abierto', NULL, NULL),
(7, 1, 7, 'Julio', '2025-07-01', '2025-07-31', 'Abierto', NULL, NULL),
(8, 1, 8, 'Agosto', '2025-08-01', '2025-08-31', 'Abierto', NULL, NULL),
(9, 1, 9, 'Septiembre', '2025-09-01', '2025-09-30', 'Abierto', NULL, NULL),
(10, 1, 10, 'Octubre', '2025-10-01', '2025-10-31', 'Abierto', NULL, NULL),
(11, 1, 11, 'Noviembre', '2025-11-01', '2025-11-30', 'Abierto', NULL, NULL),
(12, 1, 12, 'Diciembre', '2025-12-01', '2025-12-31', 'Abierto', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `polizas`
--

CREATE TABLE `polizas` (
  `id` int(11) NOT NULL,
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
  `motivo_cancelacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Pólizas contables';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `polizas_detalle`
--

CREATE TABLE `polizas_detalle` (
  `id` int(11) NOT NULL,
  `poliza_id` int(11) NOT NULL COMMENT 'ID de la póliza',
  `cuenta_id` int(11) NOT NULL COMMENT 'ID de la cuenta contable',
  `concepto` varchar(255) DEFAULT NULL COMMENT 'Concepto del movimiento',
  `debe` decimal(15,2) DEFAULT 0.00 COMMENT 'Monto del cargo',
  `haber` decimal(15,2) DEFAULT 0.00 COMMENT 'Monto del abono',
  `auxiliar_tipo` varchar(50) DEFAULT NULL COMMENT 'Tipo de auxiliar',
  `auxiliar_id` int(11) DEFAULT NULL COMMENT 'ID del auxiliar',
  `orden` int(11) DEFAULT 0 COMMENT 'Orden de visualización'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Detalle de pólizas contables';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `privilege`
--

CREATE TABLE `privilege` (
  `id` int(11) NOT NULL,
  `admin` tinyint(4) NOT NULL,
  `permiso` varchar(100) NOT NULL,
  `valor` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `privilege`
--

INSERT INTO `privilege` (`id`, `admin`, `permiso`, `valor`) VALUES
(207, 1, 'user_add', 1),
(208, 1, 'user_edit', 1),
(209, 1, 'user_consult', 1),
(210, 1, 'user_delete', 1),
(211, 1, 'user_bitacora', 1),
(212, 1, 'rh_empleados_add', 1),
(213, 1, 'rh_empleados_edit', 1),
(214, 1, 'rh_empleados_consult', 1),
(215, 1, 'rh_empleados_delete', 1),
(216, 1, 'rh_departamentos', 1),
(217, 1, 'clientes_add', 1),
(218, 1, 'clientes_edit', 1),
(219, 1, 'clientes_consult', 1),
(220, 1, 'clientes_delete', 1),
(221, 1, 'proveedores_add', 1),
(222, 1, 'proveedores_edit', 1),
(223, 1, 'proveedores_consult', 1),
(224, 1, 'proveedores_delete', 1),
(225, 1, 'ventas_ordenes_add', 1),
(226, 1, 'ventas_ordenes_edit', 1),
(227, 1, 'ventas_ordenes_consult', 1),
(228, 1, 'ventas_ordenes_delete', 1),
(229, 1, 'ventas_cotizaciones', 1),
(230, 1, 'compras_ordenes_add', 1),
(231, 1, 'compras_ordenes_edit', 1),
(232, 1, 'compras_ordenes_consult', 1),
(233, 1, 'compras_ordenes_delete', 1),
(234, 1, 'compras_recepcion', 1),
(235, 1, 'produccion_productos_add', 1),
(236, 1, 'produccion_productos_edit', 1),
(237, 1, 'produccion_productos_consult', 1),
(238, 1, 'produccion_formulaciones', 1),
(239, 1, 'produccion_ordenes', 1),
(240, 1, 'produccion_ver_costos', 1),
(241, 1, 'almacen_inventario_consult', 1),
(242, 1, 'almacen_ajustes', 1),
(243, 1, 'almacen_entregas', 1),
(244, 1, 'almacen_movimientos', 1),
(245, 1, 'almacen_insumos', 1),
(246, 1, 'obras_add', 1),
(247, 1, 'obras_edit', 1),
(248, 1, 'obras_consult', 1),
(249, 1, 'obras_delete', 1),
(250, 1, 'obras_pagos', 1),
(251, 1, 'contabilidad_cuentas', 1),
(252, 1, 'contabilidad_polizas', 1),
(253, 1, 'contabilidad_nomina', 1),
(254, 1, 'contabilidad_reportes', 1),
(255, 1, 'contabilidad_gastos', 1),
(256, 1, 'contabilidad_ingresos', 1),
(257, 1, 'reportes_ventas', 1),
(258, 1, 'reportes_compras', 1),
(259, 1, 'reportes_inventario', 1),
(260, 1, 'reportes_produccion', 1),
(261, 1, 'reportes_financieros', 1),
(262, 1, 'reportes_obras', 1),
(263, 1, 'dashboard_main', 1),
(264, 1, 'dashboard_ventas', 1),
(265, 1, 'dashboard_produccion', 1),
(266, 1, 'dashboard_almacen', 1),
(387, 3, 'user_add', 0),
(388, 3, 'user_edit', 0),
(389, 3, 'user_consult', 0),
(390, 3, 'user_delete', 0),
(391, 3, 'user_bitacora', 0),
(392, 3, 'rh_empleados_add', 0),
(393, 3, 'rh_empleados_edit', 0),
(394, 3, 'rh_empleados_consult', 0),
(395, 3, 'rh_empleados_delete', 0),
(396, 3, 'rh_departamentos', 0),
(397, 3, 'clientes_add', 1),
(398, 3, 'clientes_edit', 1),
(399, 3, 'clientes_consult', 1),
(400, 3, 'clientes_delete', 1),
(401, 3, 'proveedores_add', 0),
(402, 3, 'proveedores_edit', 0),
(403, 3, 'proveedores_consult', 0),
(404, 3, 'proveedores_delete', 0),
(405, 3, 'ventas_ordenes_add', 1),
(406, 3, 'ventas_ordenes_edit', 1),
(407, 3, 'ventas_ordenes_consult', 1),
(408, 3, 'ventas_ordenes_delete', 1),
(409, 3, 'ventas_cotizaciones', 1),
(410, 3, 'compras_ordenes_add', 0),
(411, 3, 'compras_ordenes_edit', 0),
(412, 3, 'compras_ordenes_consult', 0),
(413, 3, 'compras_ordenes_delete', 0),
(414, 3, 'compras_recepcion', 0),
(415, 3, 'produccion_productos_add', 0),
(416, 3, 'produccion_productos_edit', 0),
(417, 3, 'produccion_productos_consult', 1),
(418, 3, 'produccion_formulaciones', 1),
(419, 3, 'produccion_ordenes', 1),
(420, 3, 'produccion_ver_costos', 1),
(421, 3, 'almacen_inventario_consult', 1),
(422, 3, 'almacen_ajustes', 0),
(423, 3, 'almacen_entregas', 1),
(424, 3, 'almacen_movimientos', 1),
(425, 3, 'almacen_insumos', 1),
(426, 3, 'obras_add', 1),
(427, 3, 'obras_edit', 1),
(428, 3, 'obras_consult', 1),
(429, 3, 'obras_delete', 1),
(430, 3, 'obras_pagos', 1),
(431, 3, 'contabilidad_cuentas', 0),
(432, 3, 'contabilidad_polizas', 0),
(433, 3, 'contabilidad_nomina', 0),
(434, 3, 'contabilidad_reportes', 0),
(435, 3, 'contabilidad_gastos', 0),
(436, 3, 'contabilidad_ingresos', 0),
(437, 3, 'reportes_ventas', 0),
(438, 3, 'reportes_compras', 0),
(439, 3, 'reportes_inventario', 0),
(440, 3, 'reportes_produccion', 0),
(441, 3, 'reportes_financieros', 0),
(442, 3, 'reportes_obras', 0),
(443, 3, 'dashboard_main', 1),
(444, 3, 'dashboard_ventas', 1),
(445, 3, 'dashboard_produccion', 0),
(446, 3, 'dashboard_almacen', 0),
(507, 2, 'user_add', 0),
(508, 2, 'user_edit', 0),
(509, 2, 'user_consult', 0),
(510, 2, 'user_delete', 0),
(511, 2, 'user_bitacora', 0),
(512, 2, 'rh_empleados_add', 0),
(513, 2, 'rh_empleados_edit', 0),
(514, 2, 'rh_empleados_consult', 0),
(515, 2, 'rh_empleados_delete', 0),
(516, 2, 'rh_departamentos', 0),
(517, 2, 'clientes_add', 1),
(518, 2, 'clientes_edit', 1),
(519, 2, 'clientes_consult', 1),
(520, 2, 'clientes_delete', 1),
(521, 2, 'proveedores_add', 0),
(522, 2, 'proveedores_edit', 0),
(523, 2, 'proveedores_consult', 0),
(524, 2, 'proveedores_delete', 0),
(525, 2, 'ventas_ordenes_add', 1),
(526, 2, 'ventas_ordenes_edit', 1),
(527, 2, 'ventas_ordenes_consult', 1),
(528, 2, 'ventas_ordenes_delete', 1),
(529, 2, 'ventas_cotizaciones', 1),
(530, 2, 'compras_ordenes_add', 0),
(531, 2, 'compras_ordenes_edit', 0),
(532, 2, 'compras_ordenes_consult', 0),
(533, 2, 'compras_ordenes_delete', 0),
(534, 2, 'compras_recepcion', 0),
(535, 2, 'produccion_productos_add', 0),
(536, 2, 'produccion_productos_edit', 0),
(537, 2, 'produccion_productos_consult', 1),
(538, 2, 'produccion_formulaciones', 1),
(539, 2, 'produccion_ordenes', 1),
(540, 2, 'produccion_ver_costos', 1),
(541, 2, 'almacen_inventario_consult', 1),
(542, 2, 'almacen_ajustes', 0),
(543, 2, 'almacen_entregas', 1),
(544, 2, 'almacen_movimientos', 1),
(545, 2, 'almacen_insumos', 1),
(546, 2, 'obras_add', 1),
(547, 2, 'obras_edit', 1),
(548, 2, 'obras_consult', 1),
(549, 2, 'obras_delete', 1),
(550, 2, 'obras_pagos', 1),
(551, 2, 'contabilidad_cuentas', 0),
(552, 2, 'contabilidad_polizas', 0),
(553, 2, 'contabilidad_nomina', 0),
(554, 2, 'contabilidad_reportes', 0),
(555, 2, 'contabilidad_gastos', 0),
(556, 2, 'contabilidad_ingresos', 0),
(557, 2, 'reportes_ventas', 0),
(558, 2, 'reportes_compras', 0),
(559, 2, 'reportes_inventario', 0),
(560, 2, 'reportes_produccion', 0),
(561, 2, 'reportes_financieros', 0),
(562, 2, 'reportes_obras', 0),
(563, 2, 'dashboard_main', 1),
(564, 2, 'dashboard_ventas', 1),
(565, 2, 'dashboard_produccion', 0),
(566, 2, 'dashboard_almacen', 0),
(567, 4, 'user_add', 0),
(568, 4, 'user_edit', 0),
(569, 4, 'user_consult', 0),
(570, 4, 'user_delete', 0),
(571, 4, 'user_bitacora', 0),
(572, 4, 'rh_empleados_add', 0),
(573, 4, 'rh_empleados_edit', 0),
(574, 4, 'rh_empleados_consult', 0),
(575, 4, 'rh_empleados_delete', 0),
(576, 4, 'rh_departamentos', 0),
(577, 4, 'clientes_add', 1),
(578, 4, 'clientes_edit', 1),
(579, 4, 'clientes_consult', 1),
(580, 4, 'clientes_delete', 1),
(581, 4, 'proveedores_add', 0),
(582, 4, 'proveedores_edit', 0),
(583, 4, 'proveedores_consult', 0),
(584, 4, 'proveedores_delete', 0),
(585, 4, 'ventas_ordenes_add', 1),
(586, 4, 'ventas_ordenes_edit', 1),
(587, 4, 'ventas_ordenes_consult', 1),
(588, 4, 'ventas_ordenes_delete', 1),
(589, 4, 'ventas_cotizaciones', 1),
(590, 4, 'compras_ordenes_add', 0),
(591, 4, 'compras_ordenes_edit', 0),
(592, 4, 'compras_ordenes_consult', 0),
(593, 4, 'compras_ordenes_delete', 0),
(594, 4, 'compras_recepcion', 0),
(595, 4, 'produccion_productos_add', 0),
(596, 4, 'produccion_productos_edit', 0),
(597, 4, 'produccion_productos_consult', 1),
(598, 4, 'produccion_formulaciones', 1),
(599, 4, 'produccion_ordenes', 1),
(600, 4, 'produccion_ver_costos', 1),
(601, 4, 'almacen_inventario_consult', 1),
(602, 4, 'almacen_ajustes', 0),
(603, 4, 'almacen_entregas', 1),
(604, 4, 'almacen_movimientos', 1),
(605, 4, 'almacen_insumos', 1),
(606, 4, 'obras_add', 1),
(607, 4, 'obras_edit', 1),
(608, 4, 'obras_consult', 1),
(609, 4, 'obras_delete', 1),
(610, 4, 'obras_pagos', 1),
(611, 4, 'contabilidad_cuentas', 0),
(612, 4, 'contabilidad_polizas', 0),
(613, 4, 'contabilidad_nomina', 0),
(614, 4, 'contabilidad_reportes', 0),
(615, 4, 'contabilidad_gastos', 0),
(616, 4, 'contabilidad_ingresos', 0),
(617, 4, 'reportes_ventas', 0),
(618, 4, 'reportes_compras', 0),
(619, 4, 'reportes_inventario', 0),
(620, 4, 'reportes_produccion', 0),
(621, 4, 'reportes_financieros', 0),
(622, 4, 'reportes_obras', 0),
(623, 4, 'dashboard_main', 1),
(624, 4, 'dashboard_ventas', 1),
(625, 4, 'dashboard_produccion', 0),
(626, 4, 'dashboard_almacen', 0),
(627, 5, 'user_add', 0),
(628, 5, 'user_edit', 0),
(629, 5, 'user_consult', 0),
(630, 5, 'user_delete', 0),
(631, 5, 'user_bitacora', 0),
(632, 5, 'rh_empleados_add', 0),
(633, 5, 'rh_empleados_edit', 0),
(634, 5, 'rh_empleados_consult', 0),
(635, 5, 'rh_empleados_delete', 0),
(636, 5, 'rh_departamentos', 0),
(637, 5, 'clientes_add', 1),
(638, 5, 'clientes_edit', 1),
(639, 5, 'clientes_consult', 1),
(640, 5, 'clientes_delete', 1),
(641, 5, 'proveedores_add', 0),
(642, 5, 'proveedores_edit', 0),
(643, 5, 'proveedores_consult', 0),
(644, 5, 'proveedores_delete', 0),
(645, 5, 'ventas_ordenes_add', 1),
(646, 5, 'ventas_ordenes_edit', 1),
(647, 5, 'ventas_ordenes_consult', 1),
(648, 5, 'ventas_ordenes_delete', 1),
(649, 5, 'ventas_cotizaciones', 1),
(650, 5, 'compras_ordenes_add', 0),
(651, 5, 'compras_ordenes_edit', 0),
(652, 5, 'compras_ordenes_consult', 0),
(653, 5, 'compras_ordenes_delete', 0),
(654, 5, 'compras_recepcion', 0),
(655, 5, 'produccion_productos_add', 0),
(656, 5, 'produccion_productos_edit', 0),
(657, 5, 'produccion_productos_consult', 1),
(658, 5, 'produccion_formulaciones', 1),
(659, 5, 'produccion_ordenes', 1),
(660, 5, 'produccion_ver_costos', 1),
(661, 5, 'almacen_inventario_consult', 1),
(662, 5, 'almacen_ajustes', 0),
(663, 5, 'almacen_entregas', 1),
(664, 5, 'almacen_movimientos', 1),
(665, 5, 'almacen_insumos', 1),
(666, 5, 'obras_add', 1),
(667, 5, 'obras_edit', 1),
(668, 5, 'obras_consult', 1),
(669, 5, 'obras_delete', 1),
(670, 5, 'obras_pagos', 1),
(671, 5, 'contabilidad_cuentas', 0),
(672, 5, 'contabilidad_polizas', 0),
(673, 5, 'contabilidad_nomina', 0),
(674, 5, 'contabilidad_reportes', 0),
(675, 5, 'contabilidad_gastos', 0),
(676, 5, 'contabilidad_ingresos', 0),
(677, 5, 'reportes_ventas', 0),
(678, 5, 'reportes_compras', 0),
(679, 5, 'reportes_inventario', 0),
(680, 5, 'reportes_produccion', 0),
(681, 5, 'reportes_financieros', 0),
(682, 5, 'reportes_obras', 0),
(683, 5, 'dashboard_main', 1),
(684, 5, 'dashboard_ventas', 1),
(685, 5, 'dashboard_produccion', 0),
(686, 5, 'dashboard_almacen', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
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
  `fecha_modificacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de productos terminados';

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `producto_padre_id`, `es_variante`, `variante_tipo`, `variante_valor`, `codigo`, `nombre`, `alias`, `descripcion`, `categoria_id`, `tipo_producto`, `unidad_venta`, `presentacion_principal`, `contenido_neto`, `unidad_contenido`, `codigo_barras`, `codigo_qr`, `sku`, `stock_actual`, `stock_minimo`, `stock_maximo`, `ubicacion_almacen`, `costo_produccion`, `precio_venta`, `margen_utilidad`, `foto_producto`, `ficha_tecnica`, `hoja_seguridad`, `peso_bruto`, `rendimiento`, `tiempo_secado`, `colores_disponibles`, `caracteristicas`, `texturas`, `forma`, `dimensiones`, `resistencia`, `colocacion`, `mantenimiento_preventivo`, `mantenimiento_correctivo`, `observaciones`, `catalogo_pdf`, `fecha_actualizacion_catalogo`, `proveedor_id`, `estatus`, `fecha_creacion`, `usuario_creacion`, `fecha_modificacion`) VALUES
(3, NULL, 0, NULL, NULL, 'PROD-0001', 'CHISA GLASS MICRO', 'CHISA GLASS MICRO', 'Recubrimiento multicolor, totalmente decorativo, aséptico.', 2, 'Fabricado', 'Cubeta', '19L', 19.00, 'L', '7500001000014', NULL, 'chsr100', -42.00, 1.00, 0.00, NULL, 280.00, 500.00, 5.00, 'uploads/productos/d80981155677ccca6f5e701ca9d4280b.jpg', NULL, NULL, 2.00, '12m', '4 horas', 'Multicolor', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Activo', '2025-12-25 09:09:28', NULL, '2025-12-25 13:40:21'),
(4, 3, 1, 'color', 'Beige', 'PROD-0002', 'CHISA GLASS MICRO COLOR 2', 'CHISA GLASS MICRO', 'Recubrimiento multicolor, totalmente decorativo, aséptico.', 2, 'Fabricado', 'Cubeta', '19L', 19.00, 'L', '7500001000021', NULL, '', 0.00, 0.00, 0.00, NULL, 0.00, 520.00, 0.00, 'uploads/productos/6d8e0fb3dd532104de2ff4ff8077a9e2.jpg', NULL, NULL, 2.00, '12m', '4 horas', '', 'Recubrimiento multicolor, totalmente decorativo, aséptico, cuenta con alta lavabilidad y estabilidad de colores, es fungicida, flexible, durable, adherente, resistente a la abrasión, no propaga el fuego, y repelente al agua. Cuenta con garantía de diez años.', 'Micro', 'Líquido', 'Película de 25 micras', 'A la intemperie lavable, elástico, no toxico, no propaga el fuego', 'Por aspersión de aire con equipo neumático de alta presión', 'Limpiar las superficies con agua y jabón, cepillo de cerdas o raíz, y/o franela de color distinto al rojo, no utilizar ningún solvente', 'En muros dañados se deberá aplicar el recubrimiento solo en el detalle', 'Para uso en Fachadas, plafones, encamados, cubos de escalera,cocinas, salas de espera, etc. Se recomienda de superficies lisas para su aplicación. \r\n', NULL, NULL, NULL, 'Activo', '2025-12-25 23:09:17', NULL, '2025-12-25 23:10:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
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
  `registrado_por` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo de proveedores';

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `codigo`, `razon_social`, `nombre_comercial`, `rfc`, `tipo_proveedor`, `contacto_principal`, `telefono`, `telefono_alternativo`, `email`, `sitio_web`, `direccion`, `ciudad`, `estado`, `codigo_postal`, `pais`, `dias_credito`, `limite_credito`, `cuenta_bancaria`, `banco`, `observaciones`, `calificacion`, `estatus`, `fecha_registro`, `registrado_por`) VALUES
(1, 'PROV00001', 'PINTURAS COMEX SARAPE SA DE CV', 'COMEX', 'PCC951205QT7', 'Materia Prima', 'JUAN CARLOS LOPEZ MARTINES', '5566331454141', '', 'comexjuan@mail.com', '', 'Avenida Francisco Trujillo No. 526 Local A, Colonia José María Pino Suárez', 'TABASCO', 'TABASCO', '86029', 'México', 0, 0.00, '125487456985423654', 'BBVA', 'Proveedor de prueba en el sistema', 3, 'Activo', '2025-12-25 07:49:28', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor_insumo`
--

CREATE TABLE `proveedor_insumo` (
  `id` int(11) NOT NULL,
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
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relación proveedores-insumos con precios específicos por proveedor';

--
-- Volcado de datos para la tabla `proveedor_insumo`
--

INSERT INTO `proveedor_insumo` (`id`, `proveedor_id`, `insumo_id`, `codigo_proveedor`, `nombre_proveedor`, `precio_compra`, `tiempo_entrega_dias`, `cantidad_minima`, `es_proveedor_principal`, `calidad`, `ultima_compra`, `observaciones`, `estatus`, `fecha_registro`) VALUES
(1, 1, 1, 'PMKSDJKN', NULL, 100.00, 1, 1.00, 0, 3, NULL, 'Solicitud con mínimo 24 horas de anticipación', 'Activo', '2025-12-25 07:50:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `permisos` text NOT NULL COMMENT 'JSON array',
  `estatus` tinyint(1) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `permisos`, `estatus`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Super Administrador', 'Acceso total del sistema', '{\"user_add\":1,\"user_edit\":1,\"user_consult\":1,\"user_delete\":1,\"user_bitacora\":1,\"rh_empleados_add\":1,\"rh_empleados_edit\":1,\"rh_empleados_consult\":1,\"rh_empleados_delete\":1,\"rh_departamentos\":1,\"clientes_add\":1,\"clientes_edit\":1,\"clientes_consult\":1,\"clientes_delete\":1,\"proveedores_add\":1,\"proveedores_edit\":1,\"proveedores_consult\":1,\"proveedores_delete\":1,\"ventas_ordenes_add\":1,\"ventas_ordenes_edit\":1,\"ventas_ordenes_consult\":1,\"ventas_ordenes_delete\":1,\"ventas_cotizaciones\":1,\"compras_ordenes_add\":1,\"compras_ordenes_edit\":1,\"compras_ordenes_consult\":1,\"compras_ordenes_delete\":1,\"compras_recepcion\":1,\"produccion_productos_add\":1,\"produccion_productos_edit\":1,\"produccion_productos_consult\":1,\"produccion_formulaciones\":1,\"produccion_ordenes\":1,\"produccion_ver_costos\":1,\"almacen_inventario_consult\":1,\"almacen_ajustes\":1,\"almacen_entregas\":1,\"almacen_movimientos\":1,\"almacen_insumos\":1,\"obras_add\":1,\"obras_edit\":1,\"obras_consult\":1,\"obras_delete\":1,\"obras_pagos\":1,\"contabilidad_cuentas\":1,\"contabilidad_polizas\":1,\"contabilidad_nomina\":1,\"contabilidad_reportes\":1,\"contabilidad_gastos\":1,\"contabilidad_ingresos\":1,\"reportes_ventas\":1,\"reportes_compras\":1,\"reportes_inventario\":1,\"reportes_produccion\":1,\"reportes_financieros\":1,\"reportes_obras\":1,\"dashboard_main\":1,\"dashboard_ventas\":1,\"dashboard_produccion\":1,\"dashboard_almacen\":1}', 1, '2026-01-14 11:47:00', NULL),
(2, 'Ventas', 'Vendedores', '{\"clientes_add\":1,\"clientes_edit\":1,\"clientes_consult\":1,\"clientes_delete\":1,\"ventas_ordenes_add\":1,\"ventas_ordenes_edit\":1,\"ventas_ordenes_consult\":1,\"ventas_ordenes_delete\":1,\"ventas_cotizaciones\":1,\"produccion_productos_consult\":1,\"produccion_formulaciones\":1,\"produccion_ordenes\":1,\"produccion_ver_costos\":1,\"almacen_inventario_consult\":1,\"almacen_entregas\":1,\"almacen_movimientos\":1,\"almacen_insumos\":1,\"obras_add\":1,\"obras_edit\":1,\"obras_consult\":1,\"obras_delete\":1,\"obras_pagos\":1,\"dashboard_main\":1,\"dashboard_ventas\":1}', 1, '2026-01-14 11:49:15', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios_recurrentes`
--

CREATE TABLE `servicios_recurrentes` (
  `id` int(11) NOT NULL,
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
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Servicios recurrentes y pagos mensuales';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_produccion`
--

CREATE TABLE `solicitudes_produccion` (
  `id` int(11) NOT NULL,
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
  `formulacion_id` int(11) DEFAULT NULL COMMENT 'Formulación específica solicitada desde ventas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Solicitudes de producción generadas por ventas';

--
-- Volcado de datos para la tabla `solicitudes_produccion`
--

INSERT INTO `solicitudes_produccion` (`id`, `folio`, `orden_venta_id`, `producto_id`, `cantidad_solicitada`, `cantidad_producida`, `fecha_solicitud`, `fecha_requerida`, `fecha_inicio_produccion`, `fecha_fin_produccion`, `estatus`, `orden_produccion_id`, `prioridad`, `observaciones`, `creado_por`, `fecha_creacion`, `formulacion_id`) VALUES
(1, 'SP-2025-0001', 13, 3, 41.00, 0.00, '2025-12-25', NULL, NULL, NULL, 'Pendiente', NULL, 'Media', NULL, NULL, '2025-12-25 13:07:31', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_vacaciones`
--

CREATE TABLE `solicitudes_vacaciones` (
  `id` int(11) NOT NULL,
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
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_vacaciones`
--

INSERT INTO `solicitudes_vacaciones` (`id`, `empleado_id`, `periodo_vacaciones_id`, `fecha_inicio`, `fecha_fin`, `dias_solicitados`, `estatus`, `motivo_rechazo`, `aprobado_por`, `fecha_solicitud`, `fecha_aprobacion`, `observaciones`) VALUES
(1, 4, 1, '2025-12-23', '2025-12-27', 4, 'Aprobada', NULL, NULL, '2025-12-24 18:13:09', '2025-12-24 18:20:09', 'Solicitud extemporánea ');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones`
--

CREATE TABLE `vacaciones` (
  `id` int(11) NOT NULL,
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
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Solicitudes de vacaciones';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones_empleados`
--

CREATE TABLE `vacaciones_empleados` (
  `id` int(11) NOT NULL,
  `empleado_id` int(11) NOT NULL,
  `periodo_inicio` date NOT NULL COMMENT 'Inicio del período de vacaciones (aniversario)',
  `periodo_fin` date NOT NULL COMMENT 'Fin del período',
  `anios_antiguedad` int(11) NOT NULL COMMENT 'Años cumplidos en este período',
  `dias_correspondientes` int(11) NOT NULL COMMENT 'Días que le tocan por ley',
  `dias_adicionales` int(11) DEFAULT 0 COMMENT 'Días extra otorgados por la empresa',
  `dias_totales` int(11) NOT NULL COMMENT 'Total disponibles',
  `dias_tomados` int(11) DEFAULT 0 COMMENT 'Días ya utilizados',
  `dias_disponibles` int(11) NOT NULL COMMENT 'Días restantes',
  `fecha_creacion` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vacaciones_empleados`
--

INSERT INTO `vacaciones_empleados` (`id`, `empleado_id`, `periodo_inicio`, `periodo_fin`, `anios_antiguedad`, `dias_correspondientes`, `dias_adicionales`, `dias_totales`, `dias_tomados`, `dias_disponibles`, `fecha_creacion`) VALUES
(1, 4, '2025-12-24', '2026-12-23', 3, 10, 0, 10, 4, 6, '2025-12-24 18:05:59'),
(2, 3, '2025-12-24', '2026-12-23', 1, 6, 0, 6, 0, 6, '2025-12-24 18:06:45');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `alertas_stock`
--
ALTER TABLE `alertas_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tipo` (`tipo_alerta`),
  ADD KEY `idx_nivel` (`nivel_alerta`),
  ADD KEY `idx_leida` (`leida`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_insumo` (`insumo_id`),
  ADD KEY `fk_alert_formulacion` (`formulacion_id`),
  ADD KEY `idx_alertas_no_resueltas` (`resuelta`,`nivel_alerta`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_fecha_hora` (`fecha`,`hora`),
  ADD KEY `idx_mensaje` (`mensaje`(768));

--
-- Indices de la tabla `categorias_insumos`
--
ALTER TABLE `categorias_insumos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_padre` (`categoria_padre_id`),
  ADD KEY `idx_tipo` (`tipo`);

--
-- Indices de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_padre` (`categoria_padre_id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_razon_social` (`razon_social`),
  ADD KEY `idx_tipo_cliente` (`tipo_cliente`),
  ADD KEY `idx_clientes_rfc` (`rfc`);

--
-- Indices de la tabla `contratos_empleados`
--
ALTER TABLE `contratos_empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_vigente` (`vigente`),
  ADD KEY `fk_contrato_admin` (`creado_por`);

--
-- Indices de la tabla `cuentas_bancarias`
--
ALTER TABLE `cuentas_bancarias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cuenta_contable_id` (`cuenta_contable_id`),
  ADD KEY `idx_banco` (`banco`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `cuentas_contables`
--
ALTER TABLE `cuentas_contables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_codigo` (`codigo`),
  ADD KEY `idx_tipo` (`tipo_cuenta`),
  ADD KEY `idx_nivel` (`nivel`),
  ADD KEY `cuenta_padre_id` (`cuenta_padre_id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `fk_depto_responsable` (`responsable_id`);

--
-- Indices de la tabla `descuentos`
--
ALTER TABLE `descuentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `detalle_entregas_almacen`
--
ALTER TABLE `detalle_entregas_almacen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_entrega` (`entrega_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_detalle_orden` (`detalle_orden_id`),
  ADD KEY `idx_obra_producto` (`obra_producto_id`),
  ADD KEY `idx_movimiento` (`movimiento_id`),
  ADD KEY `idx_detalle_tipo` (`tipo_detalle`);

--
-- Indices de la tabla `detalle_formulacion`
--
ALTER TABLE `detalle_formulacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_formulacion` (`formulacion_id`),
  ADD KEY `idx_insumo` (`insumo_id`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `detalle_orden_compra`
--
ALTER TABLE `detalle_orden_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orden` (`orden_compra_id`),
  ADD KEY `idx_insumo` (`insumo_id`);

--
-- Indices de la tabla `detalle_orden_produccion`
--
ALTER TABLE `detalle_orden_produccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orden` (`orden_produccion_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_completado` (`completado`);

--
-- Indices de la tabla `detalle_orden_venta`
--
ALTER TABLE `detalle_orden_venta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orden` (`orden_venta_id`),
  ADD KEY `idx_producto` (`producto_id`);

--
-- Indices de la tabla `ejercicios_fiscales`
--
ALTER TABLE `ejercicios_fiscales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `año` (`año`),
  ADD KEY `idx_año` (`año`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_empleado` (`numero_empleado`),
  ADD UNIQUE KEY `rfc` (`rfc`),
  ADD UNIQUE KEY `curp` (`curp`),
  ADD UNIQUE KEY `nss` (`nss`),
  ADD KEY `idx_departamento` (`departamento_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_tipo_trabajador` (`tipo_trabajador`),
  ADD KEY `idx_fecha_ingreso` (`fecha_ingreso`);

--
-- Indices de la tabla `entregas_almacen`
--
ALTER TABLE `entregas_almacen`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `idx_tipo_origen` (`tipo_origen`),
  ADD KEY `idx_fecha` (`fecha_entrega`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_orden_venta` (`orden_venta_id`),
  ADD KEY `idx_obra` (`obra_id`),
  ADD KEY `idx_usuario` (`usuario_id`),
  ADD KEY `idx_entregas_fecha_tipo` (`fecha_entrega`,`tipo_origen`);

--
-- Indices de la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_folio_fiscal` (`folio_fiscal`),
  ADD KEY `idx_orden` (`orden_venta_id`),
  ADD KEY `idx_cliente` (`cliente_id`);

--
-- Indices de la tabla `facturas_obras`
--
ALTER TABLE `facturas_obras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `idx_obra` (`obra_id`),
  ADD KEY `idx_folio` (`folio`),
  ADD KEY `idx_fecha_emision` (`fecha_emision`);

--
-- Indices de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_activa` (`es_activa`),
  ADD KEY `idx_formulaciones_producto_activa` (`producto_id`,`es_activa`);

--
-- Indices de la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_dia` (`dia_semana`),
  ADD KEY `idx_vigencia` (`fecha_inicio`,`fecha_fin`),
  ADD KEY `idx_empleado_vigente` (`empleado_id`,`estatus`,`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `incidencias_empleados`
--
ALTER TABLE `incidencias_empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_fecha` (`fecha_incidencia`),
  ADD KEY `idx_tipo` (`tipo_incidencia`);

--
-- Indices de la tabla `insumos`
--
ALTER TABLE `insumos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_nombre_tecnico` (`nombre_tecnico`),
  ADD KEY `idx_alias` (`alias`),
  ADD KEY `idx_marca` (`marca`),
  ADD KEY `idx_categoria` (`categoria_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_stock_bajo` (`stock_actual`,`stock_minimo`),
  ADD KEY `idx_insumo_activo` (`estatus`,`nombre_tecnico`),
  ADD KEY `idx_insumo_stock_bajo` (`stock_actual`,`stock_minimo`),
  ADD KEY `idx_insumo_busqueda` (`nombre_tecnico`,`alias`,`marca`);

--
-- Indices de la tabla `lotes_produccion`
--
ALTER TABLE `lotes_produccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_barras` (`codigo_barras`),
  ADD KEY `idx_codigo_barras` (`codigo_barras`),
  ADD KEY `idx_orden` (`orden_produccion_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_fecha` (`fecha_produccion`);

--
-- Indices de la tabla `movimientos_bancarios`
--
ALTER TABLE `movimientos_bancarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poliza_id` (`poliza_id`),
  ADD KEY `idx_cuenta` (`cuenta_bancaria_id`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_conciliado` (`conciliado`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_fecha` (`fecha_movimiento`),
  ADD KEY `idx_tipo` (`tipo_movimiento`);

--
-- Indices de la tabla `movimientos_productos`
--
ALTER TABLE `movimientos_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_tipo` (`tipo_movimiento`),
  ADD KEY `idx_fecha` (`fecha_movimiento`),
  ADD KEY `idx_movimientos_fecha_tipo` (`fecha_movimiento`,`tipo_movimiento`);

--
-- Indices de la tabla `nominas`
--
ALTER TABLE `nominas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `poliza_id` (`poliza_id`),
  ADD KEY `idx_folio` (`folio`),
  ADD KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `nominas_conceptos`
--
ALTER TABLE `nominas_conceptos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle` (`nomina_detalle_id`);

--
-- Indices de la tabla `nominas_detalle`
--
ALTER TABLE `nominas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nomina` (`nomina_id`),
  ADD KEY `idx_empleado` (`empleado_id`);

--
-- Indices de la tabla `obras`
--
ALTER TABLE `obras`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_folio` (`folio`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`),
  ADD KEY `idx_obras_cliente_estatus` (`cliente_id`,`estatus`,`activo`),
  ADD KEY `idx_obras_fechas` (`fecha_inicio_estimada`,`fecha_fin_estimada`);

--
-- Indices de la tabla `obras_archivos`
--
ALTER TABLE `obras_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_obra` (`obra_id`),
  ADD KEY `idx_categoria` (`categoria`);

--
-- Indices de la tabla `obras_comentarios`
--
ALTER TABLE `obras_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_obra` (`obra_id`),
  ADD KEY `idx_fecha` (`fecha_comentario`);

--
-- Indices de la tabla `obras_pagos`
--
ALTER TABLE `obras_pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_obra_id` (`obra_id`),
  ADD KEY `idx_folio_recibo` (`folio_recibo`),
  ADD KEY `idx_fecha_pago` (`fecha_pago`);

--
-- Indices de la tabla `obras_productos`
--
ALTER TABLE `obras_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_obra` (`obra_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_formulacion` (`formulacion_id`);

--
-- Indices de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_folio` (`folio`),
  ADD KEY `idx_proveedor` (`proveedor_id`),
  ADD KEY `idx_fecha` (`fecha_orden`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_oc_pendiente` (`estatus`,`fecha_orden`);

--
-- Indices de la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD UNIQUE KEY `uk_folio` (`folio`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_formulacion` (`formulacion_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`);

--
-- Indices de la tabla `ordenes_venta`
--
ALTER TABLE `ordenes_venta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD UNIQUE KEY `uk_folio` (`folio`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_fecha` (`fecha_orden`),
  ADD KEY `idx_tipo_venta` (`tipo_venta`),
  ADD KEY `idx_ordenes_fecha_estatus` (`fecha_orden`,`estatus`),
  ADD KEY `idx_descuento` (`descuento_id`);

--
-- Indices de la tabla `pagos_ordenes`
--
ALTER TABLE `pagos_ordenes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD UNIQUE KEY `uk_folio` (`folio`),
  ADD KEY `idx_orden` (`orden_venta_id`),
  ADD KEY `idx_fecha` (`fecha_pago`);

--
-- Indices de la tabla `pagos_servicios_recurrentes`
--
ALTER TABLE `pagos_servicios_recurrentes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_servicio_periodo` (`servicio_recurrente_id`,`periodo`),
  ADD KEY `idx_servicio` (`servicio_recurrente_id`),
  ADD KEY `idx_periodo` (`periodo`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_fecha_vencimiento` (`fecha_vencimiento`),
  ADD KEY `idx_poliza` (`poliza_id`),
  ADD KEY `idx_fecha_pago` (`fecha_pago`),
  ADD KEY `idx_servicio_estatus` (`servicio_recurrente_id`,`estatus`);

--
-- Indices de la tabla `periodos_contables`
--
ALTER TABLE `periodos_contables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_periodo` (`ejercicio_id`,`numero_periodo`),
  ADD KEY `idx_ejercicio` (`ejercicio_id`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `polizas`
--
ALTER TABLE `polizas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD KEY `idx_folio` (`folio`),
  ADD KEY `idx_fecha` (`fecha`),
  ADD KEY `idx_tipo` (`tipo_poliza`),
  ADD KEY `idx_periodo` (`periodo_id`),
  ADD KEY `idx_origen` (`origen`,`origen_id`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `polizas_detalle`
--
ALTER TABLE `polizas_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_poliza` (`poliza_id`),
  ADD KEY `idx_cuenta` (`cuenta_id`);

--
-- Indices de la tabla `privilege`
--
ALTER TABLE `privilege`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_admin_priv` (`admin`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD UNIQUE KEY `uk_codigo_barras` (`codigo_barras`),
  ADD UNIQUE KEY `uk_sku` (`sku`),
  ADD KEY `idx_categoria` (`categoria_id`),
  ADD KEY `idx_tipo` (`tipo_producto`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_proveedor` (`proveedor_id`),
  ADD KEY `idx_productos_stock_bajo` (`stock_actual`,`stock_minimo`),
  ADD KEY `idx_productos_tipo_estatus` (`tipo_producto`,`estatus`),
  ADD KEY `idx_productos_alias` (`alias`),
  ADD KEY `idx_producto_padre` (`producto_padre_id`),
  ADD KEY `idx_es_variante` (`es_variante`),
  ADD KEY `idx_variante_tipo` (`variante_tipo`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_rfc` (`rfc`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `idx_razon_social` (`razon_social`),
  ADD KEY `idx_tipo` (`tipo_proveedor`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_proveedor_activo` (`estatus`,`razon_social`);

--
-- Indices de la tabla `proveedor_insumo`
--
ALTER TABLE `proveedor_insumo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_proveedor_insumo` (`proveedor_id`,`insumo_id`),
  ADD KEY `idx_proveedor` (`proveedor_id`),
  ADD KEY `idx_insumo` (`insumo_id`),
  ADD KEY `idx_principal` (`es_proveedor_principal`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servicios_recurrentes`
--
ALTER TABLE `servicios_recurrentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_proveedor` (`proveedor_id`),
  ADD KEY `idx_cuenta_contable` (`cuenta_contable_id`),
  ADD KEY `idx_cuenta_bancaria` (`cuenta_bancaria_id`),
  ADD KEY `idx_activo` (`activo`);

--
-- Indices de la tabla `solicitudes_produccion`
--
ALTER TABLE `solicitudes_produccion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `folio` (`folio`),
  ADD UNIQUE KEY `uk_folio` (`folio`),
  ADD KEY `idx_orden_venta` (`orden_venta_id`),
  ADD KEY `idx_producto` (`producto_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_prioridad` (`prioridad`),
  ADD KEY `idx_solicitudes_fecha_estatus` (`fecha_solicitud`,`estatus`),
  ADD KEY `fk_solprod_formulacion` (`formulacion_id`),
  ADD KEY `idx_orden_prod` (`orden_produccion_id`);

--
-- Indices de la tabla `solicitudes_vacaciones`
--
ALTER TABLE `solicitudes_vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_estatus` (`estatus`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  ADD KEY `fk_solicitud_periodo` (`periodo_vacaciones_id`),
  ADD KEY `fk_solicitud_aprobador` (`aprobado_por`);

--
-- Indices de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`),
  ADD KEY `idx_estatus` (`estatus`);

--
-- Indices de la tabla `vacaciones_empleados`
--
ALTER TABLE `vacaciones_empleados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_empleado` (`empleado_id`),
  ADD KEY `idx_periodo` (`periodo_inicio`,`periodo_fin`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `alertas_stock`
--
ALTER TABLE `alertas_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `categorias_insumos`
--
ALTER TABLE `categorias_insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT de la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `contratos_empleados`
--
ALTER TABLE `contratos_empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `cuentas_bancarias`
--
ALTER TABLE `cuentas_bancarias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuentas_contables`
--
ALTER TABLE `cuentas_contables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `descuentos`
--
ALTER TABLE `descuentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `detalle_entregas_almacen`
--
ALTER TABLE `detalle_entregas_almacen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_formulacion`
--
ALTER TABLE `detalle_formulacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_compra`
--
ALTER TABLE `detalle_orden_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_produccion`
--
ALTER TABLE `detalle_orden_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `detalle_orden_venta`
--
ALTER TABLE `detalle_orden_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `ejercicios_fiscales`
--
ALTER TABLE `ejercicios_fiscales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `entregas_almacen`
--
ALTER TABLE `entregas_almacen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facturas`
--
ALTER TABLE `facturas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `facturas_obras`
--
ALTER TABLE `facturas_obras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `incidencias_empleados`
--
ALTER TABLE `incidencias_empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `insumos`
--
ALTER TABLE `insumos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `lotes_produccion`
--
ALTER TABLE `lotes_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_bancarios`
--
ALTER TABLE `movimientos_bancarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `movimientos_productos`
--
ALTER TABLE `movimientos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nominas`
--
ALTER TABLE `nominas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `nominas_conceptos`
--
ALTER TABLE `nominas_conceptos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nominas_detalle`
--
ALTER TABLE `nominas_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `obras`
--
ALTER TABLE `obras`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `obras_archivos`
--
ALTER TABLE `obras_archivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `obras_comentarios`
--
ALTER TABLE `obras_comentarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `obras_pagos`
--
ALTER TABLE `obras_pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `obras_productos`
--
ALTER TABLE `obras_productos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ordenes_venta`
--
ALTER TABLE `ordenes_venta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `pagos_ordenes`
--
ALTER TABLE `pagos_ordenes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pagos_servicios_recurrentes`
--
ALTER TABLE `pagos_servicios_recurrentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `periodos_contables`
--
ALTER TABLE `periodos_contables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `polizas`
--
ALTER TABLE `polizas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `polizas_detalle`
--
ALTER TABLE `polizas_detalle`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `privilege`
--
ALTER TABLE `privilege`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=687;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `proveedor_insumo`
--
ALTER TABLE `proveedor_insumo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `servicios_recurrentes`
--
ALTER TABLE `servicios_recurrentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes_produccion`
--
ALTER TABLE `solicitudes_produccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `solicitudes_vacaciones`
--
ALTER TABLE `solicitudes_vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `vacaciones_empleados`
--
ALTER TABLE `vacaciones_empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alertas_stock`
--
ALTER TABLE `alertas_stock`
  ADD CONSTRAINT `fk_alert_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_alert_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_alert_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categorias_insumos`
--
ALTER TABLE `categorias_insumos`
  ADD CONSTRAINT `fk_categoria_insumo_padre` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias_insumos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `categorias_productos`
--
ALTER TABLE `categorias_productos`
  ADD CONSTRAINT `fk_cat_prod_padre` FOREIGN KEY (`categoria_padre_id`) REFERENCES `categorias_productos` (`id`);

--
-- Filtros para la tabla `contratos_empleados`
--
ALTER TABLE `contratos_empleados`
  ADD CONSTRAINT `fk_contrato_admin` FOREIGN KEY (`creado_por`) REFERENCES `administradores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contrato_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cuentas_bancarias`
--
ALTER TABLE `cuentas_bancarias`
  ADD CONSTRAINT `cuentas_bancarias_ibfk_1` FOREIGN KEY (`cuenta_contable_id`) REFERENCES `cuentas_contables` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `cuentas_contables`
--
ALTER TABLE `cuentas_contables`
  ADD CONSTRAINT `cuentas_contables_ibfk_1` FOREIGN KEY (`cuenta_padre_id`) REFERENCES `cuentas_contables` (`id`);

--
-- Filtros para la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `fk_depto_responsable` FOREIGN KEY (`responsable_id`) REFERENCES `empleados` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `detalle_formulacion`
--
ALTER TABLE `detalle_formulacion`
  ADD CONSTRAINT `fk_detform_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detform_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`),
  ADD CONSTRAINT `fk_detform_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `detalle_orden_compra`
--
ALTER TABLE `detalle_orden_compra`
  ADD CONSTRAINT `fk_doc_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`),
  ADD CONSTRAINT `fk_doc_orden` FOREIGN KEY (`orden_compra_id`) REFERENCES `ordenes_compra` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `detalle_orden_venta`
--
ALTER TABLE `detalle_orden_venta`
  ADD CONSTRAINT `fk_dov_orden` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dov_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `empleados_ibfk_2` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `facturas`
--
ALTER TABLE `facturas`
  ADD CONSTRAINT `fk_factura_orden` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`);

--
-- Filtros para la tabla `formulaciones`
--
ALTER TABLE `formulaciones`
  ADD CONSTRAINT `fk_form_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios_empleados`
--
ALTER TABLE `horarios_empleados`
  ADD CONSTRAINT `fk_horario_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `incidencias_empleados`
--
ALTER TABLE `incidencias_empleados`
  ADD CONSTRAINT `fk_incidencia_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `insumos`
--
ALTER TABLE `insumos`
  ADD CONSTRAINT `fk_insumo_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_insumos` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimientos_bancarios`
--
ALTER TABLE `movimientos_bancarios`
  ADD CONSTRAINT `movimientos_bancarios_ibfk_1` FOREIGN KEY (`cuenta_bancaria_id`) REFERENCES `cuentas_bancarias` (`id`),
  ADD CONSTRAINT `movimientos_bancarios_ibfk_2` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `fk_mi_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `movimientos_productos`
--
ALTER TABLE `movimientos_productos`
  ADD CONSTRAINT `fk_movprod_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `nominas`
--
ALTER TABLE `nominas`
  ADD CONSTRAINT `nominas_ibfk_1` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `nominas_conceptos`
--
ALTER TABLE `nominas_conceptos`
  ADD CONSTRAINT `nominas_conceptos_ibfk_1` FOREIGN KEY (`nomina_detalle_id`) REFERENCES `nominas_detalle` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `nominas_detalle`
--
ALTER TABLE `nominas_detalle`
  ADD CONSTRAINT `nominas_detalle_ibfk_1` FOREIGN KEY (`nomina_id`) REFERENCES `nominas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nominas_detalle_ibfk_2` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `ordenes_compra`
--
ALTER TABLE `ordenes_compra`
  ADD CONSTRAINT `fk_oc_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`);

--
-- Filtros para la tabla `ordenes_produccion`
--
ALTER TABLE `ordenes_produccion`
  ADD CONSTRAINT `fk_op_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`),
  ADD CONSTRAINT `fk_op_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `ordenes_venta`
--
ALTER TABLE `ordenes_venta`
  ADD CONSTRAINT `fk_ov_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `pagos_ordenes`
--
ALTER TABLE `pagos_ordenes`
  ADD CONSTRAINT `fk_pago_orden` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`);

--
-- Filtros para la tabla `pagos_servicios_recurrentes`
--
ALTER TABLE `pagos_servicios_recurrentes`
  ADD CONSTRAINT `fk_pago_servicio` FOREIGN KEY (`servicio_recurrente_id`) REFERENCES `servicios_recurrentes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `periodos_contables`
--
ALTER TABLE `periodos_contables`
  ADD CONSTRAINT `periodos_contables_ibfk_1` FOREIGN KEY (`ejercicio_id`) REFERENCES `ejercicios_fiscales` (`id`);

--
-- Filtros para la tabla `polizas_detalle`
--
ALTER TABLE `polizas_detalle`
  ADD CONSTRAINT `polizas_detalle_ibfk_1` FOREIGN KEY (`poliza_id`) REFERENCES `polizas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `polizas_detalle_ibfk_2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas_contables` (`id`);

--
-- Filtros para la tabla `privilege`
--
ALTER TABLE `privilege`
  ADD CONSTRAINT `fk_privilege_admin` FOREIGN KEY (`admin`) REFERENCES `administradores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_prod_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_productos` (`id`),
  ADD CONSTRAINT `fk_prod_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_producto_padre` FOREIGN KEY (`producto_padre_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `proveedor_insumo`
--
ALTER TABLE `proveedor_insumo`
  ADD CONSTRAINT `fk_pi_insumo` FOREIGN KEY (`insumo_id`) REFERENCES `insumos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pi_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_produccion`
--
ALTER TABLE `solicitudes_produccion`
  ADD CONSTRAINT `fk_solprod_formulacion` FOREIGN KEY (`formulacion_id`) REFERENCES `formulaciones` (`id`),
  ADD CONSTRAINT `fk_sp_orden_prod` FOREIGN KEY (`orden_produccion_id`) REFERENCES `ordenes_produccion` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sp_orden_venta` FOREIGN KEY (`orden_venta_id`) REFERENCES `ordenes_venta` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sp_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `solicitudes_vacaciones`
--
ALTER TABLE `solicitudes_vacaciones`
  ADD CONSTRAINT `fk_solicitud_aprobador` FOREIGN KEY (`aprobado_por`) REFERENCES `administradores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitud_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_solicitud_periodo` FOREIGN KEY (`periodo_vacaciones_id`) REFERENCES `vacaciones_empleados` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vacaciones`
--
ALTER TABLE `vacaciones`
  ADD CONSTRAINT `vacaciones_ibfk_1` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`);

--
-- Filtros para la tabla `vacaciones_empleados`
--
ALTER TABLE `vacaciones_empleados`
  ADD CONSTRAINT `fk_vacaciones_empleado` FOREIGN KEY (`empleado_id`) REFERENCES `empleados` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
