-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 31-05-2026 a las 11:38:13
-- Versión del servidor: 8.0.45-cll-lve
-- Versión de PHP: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `discoper_fabcoins`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `financing_agreements`
--

CREATE TABLE `financing_agreements` (
  `id` int NOT NULL,
  `lab_id` int NOT NULL,
  `maker_id` int NOT NULL,
  `amount_initial` decimal(10,2) NOT NULL,
  `amount_remaining` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` enum('pending','active','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `financing_agreements`
--

INSERT INTO `financing_agreements` (`id`, `lab_id`, `maker_id`, `amount_initial`, `amount_remaining`, `description`, `status`, `created_at`) VALUES
(1, 3, 4, 3750.00, 1850.00, 'Crédito Fab Academy', 'active', '2026-05-07 17:18:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `global_catalog`
--

CREATE TABLE `global_catalog` (
  `id` int NOT NULL,
  `asset_type` enum('machine','service','workshop','space','material') NOT NULL DEFAULT 'machine',
  `generic_name` varchar(150) NOT NULL,
  `generic_name_en` varchar(255) DEFAULT NULL,
  `measurement_unit` varchar(50) NOT NULL,
  `suggested_price_fc` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `global_catalog`
--

INSERT INTO `global_catalog` (`id`, `asset_type`, `generic_name`, `generic_name_en`, `measurement_unit`, `suggested_price_fc`) VALUES
(1, 'machine', 'Impresora 3D FDM (Filamento)', '3D Printer FDM (Filament)', 'hora', 5.00),
(2, 'machine', 'Impresora 3D SLA (Resina)', '3D Printer SLA (Resin)', 'hora', 8.00),
(3, 'machine', 'Cortadora Láser (CO2)', 'Laser Cutter (CO2)', 'hora', 20.00),
(4, 'machine', 'Router (Formato Grande)', 'CNC Router (Large Format)', 'hora', 35.00),
(5, 'machine', 'Plotter de Corte (Vinilo)', 'Vinyl Cutter', 'hora', 10.00),
(6, 'machine', 'Fresadora de Precisión (PCBs)', 'Precision Milling (PCBs)', 'hora', 15.00),
(7, 'machine', 'Escáner 3D', '3D Scanner', 'hora', 12.00),
(8, 'machine', 'Otro Equipo / Maquinaria (Comodín)', 'Other Equipment / Machinery', 'hora', 10.00),
(9, 'service', 'Consultoría en Modelado 3D (CAD)', '3D Modeling Consultancy (CAD)', 'hora', 25.00),
(10, 'service', 'Asesoría en Electrónica / Programación', 'Electronics / Programming Advisory', 'hora', 30.00),
(11, 'service', 'Diseño de Placas Electrónicas (PCB)', 'PCB Design', 'hora', 35.00),
(12, 'service', 'Acompañamiento en Prototipado', 'Prototyping Support', 'hora', 20.00),
(13, 'service', 'Operación de Máquina Asistida', 'Assisted Machine Operation', 'hora', 15.00),
(14, 'service', 'Otro Servicio Profesional (Comodín)', 'Other Professional Service', 'hora', 25.00),
(15, 'workshop', 'Inducción de Seguridad Básica', 'Basic Safety Induction', 'cupo', 15.00),
(16, 'workshop', 'Taller Práctico: Impresión 3D', 'Practical Workshop: 3D Printing', 'cupo', 40.00),
(17, 'workshop', 'Taller Práctico: Corte Láser', 'Practical Workshop: Laser Cutting', 'cupo', 50.00),
(18, 'workshop', 'Bootcamp Fabricación Digital', 'Digital Fabrication Bootcamp', 'cupo', 150.00),
(19, 'workshop', 'Curso: Programación con Arduino', 'Course: Arduino Programming', 'cupo', 80.00),
(20, 'workshop', 'Otro Taller / Evento (Comodín)', 'Other Workshop / Event', 'cupo', 50.00),
(21, 'space', 'Estación de Trabajo (Coworking Fab)', 'Workstation (Fab Coworking)', 'hora', 3.00),
(22, 'space', 'Mesa de Ensamblaje / Herramientas', 'Assembly Table / Tools', 'hora', 5.00),
(23, 'space', 'Cabina de Pintura / Acabados', 'Paint Booth / Finishing', 'hora', 8.00),
(24, 'space', 'Sala de Reuniones / Ideación', 'Meeting / Ideation Room', 'hora', 10.00),
(25, 'space', 'Sala de Capacitación / Aulas', 'Training Room / Classrooms', 'hora', 15.00),
(26, 'space', 'Otro Espacio Físico (Comodín)', 'Other Space', 'hora', 5.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `global_settings`
--

CREATE TABLE `global_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `global_settings`
--

INSERT INTO `global_settings` (`setting_key`, `setting_value`) VALUES
('tokenization_pct', '35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lab_assets`
--

CREATE TABLE `lab_assets` (
  `id` int NOT NULL,
  `lab_id` int NOT NULL,
  `catalog_id` int NOT NULL,
  `asset_type` enum('machine','service','workshop','space','material') NOT NULL DEFAULT 'machine',
  `custom_name` varchar(150) NOT NULL,
  `useful_life_hours` int NOT NULL DEFAULT '1000',
  `consumed_hours` decimal(12,2) DEFAULT '0.00',
  `tokenization_pct` int NOT NULL DEFAULT '30',
  `usd_value` decimal(10,2) DEFAULT NULL,
  `useful_life_units` int DEFAULT NULL,
  `wear_pct` decimal(5,2) DEFAULT NULL,
  `allocation_pct` decimal(5,2) DEFAULT NULL,
  `generated_fc` decimal(12,2) DEFAULT '0.00',
  `status` enum('active','retired') DEFAULT 'active',
  `set_price_fc` decimal(10,2) NOT NULL,
  `registered_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `lab_assets`
--

INSERT INTO `lab_assets` (`id`, `lab_id`, `catalog_id`, `asset_type`, `custom_name`, `useful_life_hours`, `consumed_hours`, `tokenization_pct`, `usd_value`, `useful_life_units`, `wear_pct`, `allocation_pct`, `generated_fc`, `status`, `set_price_fc`, `registered_at`, `expires_at`) VALUES
(1, 3, 12, 'service', 'Especialista', 100, 2.00, 100, NULL, NULL, NULL, NULL, 2000.00, 'active', 20.00, '2026-05-04 17:54:09', '2028-05-04'),
(2, 3, 1, 'machine', 'Flashforge AD5X', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 7280.00, 'active', 5.00, '2026-05-07 20:26:02', '2028-05-08'),
(3, 3, 3, 'machine', 'Creality C1', 1456, 2.00, 35, NULL, NULL, NULL, NULL, 29120.00, 'active', 20.00, '2026-05-07 21:35:50', '2028-05-08'),
(4, 3, 10, 'service', 'Especialista', 20, 0.00, 100, NULL, NULL, NULL, NULL, 600.00, 'active', 30.00, '2026-05-07 21:35:50', '2028-05-08'),
(5, 3, 19, 'workshop', 'prueba', 18, 0.00, 100, NULL, NULL, NULL, NULL, 1440.00, 'active', 80.00, '2026-05-07 21:35:50', '2028-05-08'),
(6, 3, 25, 'space', 'Flashforge AD5X', 52, 0.00, 100, NULL, NULL, NULL, NULL, 780.00, 'active', 15.00, '2026-05-07 21:35:50', '2028-05-08'),
(7, 3, 7, 'machine', 'Creality C1', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 17472.00, 'active', 12.00, '2026-05-07 21:40:42', '2028-05-08'),
(8, 3, 12, 'service', 'Creality C1', 0, 0.00, 100, NULL, NULL, NULL, NULL, 0.00, 'active', 20.00, '2026-05-07 21:40:42', '2028-05-08'),
(9, 3, 6, 'machine', 'Flashforge AD5X', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 21840.00, 'active', 15.00, '2026-05-07 21:40:42', '2028-05-08'),
(10, 3, 24, 'space', 'prueba', 0, 0.00, 100, NULL, NULL, NULL, NULL, 0.00, 'active', 10.00, '2026-05-07 21:40:42', '2028-05-08'),
(11, 3, 3, 'machine', 'Creality C1', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 29120.00, 'retired', 20.00, '2026-05-07 21:59:05', '2028-05-08'),
(12, 3, 12, 'service', 'Flashforge AD5X', 40, 0.00, 100, NULL, NULL, NULL, NULL, 800.00, 'active', 20.00, '2026-05-07 21:59:05', '2028-05-08'),
(13, 3, 19, 'workshop', 'Flashforge AD5X', 40, 0.00, 100, NULL, NULL, NULL, NULL, 3200.00, 'active', 80.00, '2026-05-07 21:59:05', '2028-05-08'),
(14, 3, 26, 'space', 'Especialista', 40, 0.00, 100, NULL, NULL, NULL, NULL, 200.00, 'active', 5.00, '2026-05-07 21:59:05', '2028-05-08'),
(15, 10, 3, 'machine', 'Creality C1', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 29120.00, 'active', 20.00, '2026-05-11 22:23:42', '2028-05-12'),
(16, 10, 12, 'service', 'Especialista', 10, 0.00, 100, NULL, NULL, NULL, NULL, 200.00, 'active', 20.00, '2026-05-11 22:23:42', '2028-05-12'),
(17, 10, 7, 'machine', 'Especialista', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 17472.00, 'active', 12.00, '2026-05-11 22:37:23', '2028-05-12'),
(18, 10, 23, 'space', 'Especialista', 40, 0.00, 100, NULL, NULL, NULL, NULL, 320.00, 'active', 8.00, '2026-05-11 22:37:23', '2028-05-12'),
(19, 3, 7, 'machine', 'Creality CR-Scan Ferret Pro', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 11648.00, 'active', 8.00, '2026-05-23 23:05:41', '2028-05-24'),
(20, 3, 3, 'machine', 'Falcon A1 Pro 20W', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 17472.00, 'active', 15.00, '2026-05-23 23:08:22', '2028-05-24'),
(21, 3, 6, 'machine', 'TTC 6050', 1456, 0.00, 35, NULL, NULL, NULL, NULL, 7280.00, 'active', 5.00, '2026-05-23 23:08:22', '2028-05-24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `missions`
--

CREATE TABLE `missions` (
  `id` int NOT NULL,
  `lab_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `deadline` date NOT NULL,
  `reference_link` varchar(255) DEFAULT NULL,
  `reward_fc` decimal(12,2) NOT NULL,
  `status` enum('open','assigned','completed','cancelled') DEFAULT 'open',
  `assigned_maker_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `target_maker_id` int DEFAULT NULL,
  `spots_total` int DEFAULT '1',
  `spots_filled` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `missions`
--

INSERT INTO `missions` (`id`, `lab_id`, `title`, `description`, `deadline`, `reference_link`, `reward_fc`, `status`, `assigned_maker_id`, `created_at`, `updated_at`, `target_maker_id`, `spots_total`, `spots_filled`) VALUES
(1, 3, 'Misión 1 prueba calficación fabber', 'con fé', '2026-05-07', '', 50.00, 'completed', 4, '2026-05-04 21:54:39', '2026-05-05 00:18:31', NULL, 1, 0),
(2, 3, 'Prueba 2', 'Prueba', '2026-05-07', '', 100.00, 'completed', 4, '2026-05-04 23:04:38', '2026-05-05 00:18:31', NULL, 1, 0),
(3, 3, 'Crear mapa de google maps para regen', 'Prueba', '2026-05-22', '', 50.00, 'completed', 4, '2026-05-05 01:51:44', '2026-05-05 01:56:34', NULL, 1, 0),
(4, 3, 'Misión para Henry prueba deuda', 'Prueba', '2026-05-30', '', 1000.00, 'completed', 4, '2026-05-07 20:31:55', '2026-05-12 23:10:29', 4, 1, 0),
(5, 3, 'Trabajo pro academy Henry', 'Prueba 2', '2026-05-22', '', 299.99, 'assigned', 4, '2026-05-07 21:15:53', '2026-05-12 01:47:33', 4, 1, 0),
(6, 3, 'Misión Abierta', 'abierta', '2026-05-29', '', 50.01, 'assigned', 2, '2026-05-07 21:22:06', '2026-05-07 21:32:01', NULL, 1, 0),
(7, 3, 'Misión abierta 2', 'abierta', '2026-05-22', '', 50.00, 'completed', 4, '2026-05-07 22:21:36', '2026-05-07 22:25:19', NULL, 1, 0),
(8, 3, 'Dirigida 3', 'Dirigida a Henry', '2026-05-30', '', 400.00, 'completed', 4, '2026-05-07 22:43:38', '2026-05-07 22:49:41', 4, 1, 0),
(9, 3, 'Misión Cerrada', 'Misión Cerrada', '2026-05-22', '', 500.00, 'completed', 4, '2026-05-08 00:28:11', '2026-05-08 00:53:06', 4, 1, 0),
(10, 3, 'Misión dirigida 100', 'Prueba', '2026-05-26', '', 1000.00, 'open', NULL, '2026-05-12 17:24:13', '2026-05-12 17:24:13', 4, 1, 0),
(11, 3, 'Misión abierta con cupos', 'cupos', '2026-05-30', '', 50.00, 'open', NULL, '2026-05-12 17:25:53', '2026-05-12 17:30:49', NULL, 20, 2),
(12, 3, 'Misión prueba nueva', 'Prueba nueva', '2026-05-20', '', 500.00, 'completed', NULL, '2026-05-12 23:19:40', '2026-05-25 03:35:01', NULL, 2, 2),
(13, 3, 'Misión botón scrip', 'prueba', '2026-05-28', '', 250.00, 'open', NULL, '2026-05-13 00:28:35', '2026-05-13 00:28:35', NULL, 1, 0),
(14, 3, 'Abierta 2', 'Abierta 2', '2026-05-28', '', 100.00, 'open', NULL, '2026-05-22 19:31:43', '2026-05-22 19:31:43', NULL, 10, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mission_applications`
--

CREATE TABLE `mission_applications` (
  `id` int NOT NULL,
  `mission_id` int NOT NULL,
  `maker_id` int NOT NULL,
  `message` text,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_reviewed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mission_applications`
--

INSERT INTO `mission_applications` (`id`, `mission_id`, `maker_id`, `message`, `status`, `applied_at`, `is_reviewed`) VALUES
(1, 1, 4, 'Estoy listo!', 'accepted', '2026-05-04 21:56:49', 1),
(2, 2, 4, 'Vamos x2', 'accepted', '2026-05-04 23:05:55', 1),
(3, 3, 4, 'Simulando que soy José', 'accepted', '2026-05-05 01:53:45', 1),
(4, 4, 4, 'Ya quiero pagar mi deuda', 'accepted', '2026-05-07 21:23:52', 1),
(5, 6, 4, 'soy yo', 'rejected', '2026-05-07 21:29:39', 0),
(6, 6, 2, 'Beno', 'accepted', '2026-05-07 21:30:16', 0),
(7, 7, 4, 'vamos', 'accepted', '2026-05-07 22:23:36', 1),
(8, 8, 4, 'Vamos!', 'accepted', '2026-05-07 22:45:09', 1),
(9, 9, 4, 'Soy yo', 'accepted', '2026-05-08 00:41:16', 1),
(10, 5, 4, 'Vamos', 'accepted', '2026-05-08 00:46:53', 0),
(11, 11, 2, 'prueba', 'accepted', '2026-05-12 17:28:44', 0),
(12, 11, 4, 'vamos', 'accepted', '2026-05-12 17:29:15', 0),
(13, 10, 4, 'Voy', 'pending', '2026-05-12 17:32:32', 0),
(14, 12, 4, 'Vamos', 'accepted', '2026-05-12 23:54:11', 1),
(15, 12, 2, 'Prueba 2', 'accepted', '2026-05-13 00:04:52', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` varchar(255) NOT NULL,
  `type` enum('info','success','warning') DEFAULT 'info',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `is_read`, `created_at`) VALUES
(1, 3, 'El Maker Henry Sánchez se ha postulado a tu misión.', 'info', 1, '2026-05-12 23:54:11'),
(2, 4, '¡Felicidades! El Lab Fab Lab Perú te ha asignado a la misión: Misión prueba nueva', 'success', 1, '2026-05-13 00:00:02'),
(3, 3, 'El Maker Beno (Super Admin) se ha postulado a la misión: ', 'info', 1, '2026-05-13 00:04:52'),
(4, 2, '¡Felicidades! El Lab Fab Lab Perú te ha asignado a la misión: Misión prueba nueva', 'success', 1, '2026-05-13 00:20:39'),
(5, 2, '¡Misión completada por Fab Lab Perú! Has recibido 5 estrellas.', 'success', 1, '2026-05-13 01:38:04'),
(6, 4, '💰 Has recibido 50 FC de Beno (Super Admin)', 'success', 1, '2026-05-19 22:45:54'),
(7, 2, '💰 Has recibido 60 FC de Henry Sánchez', 'success', 0, '2026-05-19 22:49:09'),
(8, 4, '🎯 Fab Lab Perú te ha invitado a la misión: Misión abierta con cupos', 'info', 1, '2026-05-22 19:24:19'),
(9, 4, '🎯 Fab Lab Perú te ha invitado a la misión: Abierta 2', 'info', 1, '2026-05-22 19:36:24'),
(10, 3, 'Tienes una nueva solicitud de alquiler/servicio en espera de aprobación.', 'warning', 1, '2026-05-22 23:58:38'),
(11, 4, 'Tu reserva para Especialista fue rechazada. Los FC han sido devueltos a tu cuenta.', 'warning', 1, '2026-05-23 02:42:00'),
(12, 3, '📅 Henry Sánchez solicitó Flashforge AD5X (3h) para el 29/05.', 'warning', 1, '2026-05-23 02:51:43'),
(13, 4, '📅 Fab Lab Perú propone reprogramar tu reserva para el 01/06', 'warning', 1, '2026-05-23 02:55:23'),
(14, 4, '📅 Fab Lab Perú propone reprogramar tu reserva para el 03/06', 'warning', 1, '2026-05-23 03:09:25'),
(15, 4, '📅 Fab Lab Perú propone reprogramar tu reserva para el 05/06', 'warning', 1, '2026-05-23 03:17:57'),
(16, 3, '❌ Henry Sánchez canceló la reserva por incompatibilidad de fechas.', '', 1, '2026-05-23 03:18:27'),
(17, 3, '📅 Henry Sánchez solicitó Creality C1 (2h) para el 29/05.', 'warning', 1, '2026-05-23 03:28:56'),
(18, 4, '📅 Fab Lab Perú propone reprogramar tu reserva para el 02/06', 'warning', 1, '2026-05-23 03:29:34'),
(19, 3, '✅ Henry Sánchez aceptó la nueva fecha. Ya puedes aprobar la reserva.', 'success', 1, '2026-05-23 03:30:03'),
(20, 4, 'Tu reserva para Creality C1 ha sido aprobada. ¡El Lab te espera!', 'success', 1, '2026-05-23 03:31:49'),
(21, 4, '¡Misión &quot;Misión prueba nueva&quot; completada! El Lab Fab Lab Perú te ha calificado con 5 estrellas. Has recibido +500.00 FC.', 'info', 1, '2026-05-25 03:35:01'),
(22, 3, '📅 Beno (Super Admin) solicitó Flashforge AD5X (34h) para el 04/06.', 'warning', 0, '2026-05-31 05:31:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `maker_id` int NOT NULL,
  `asset_id` int NOT NULL,
  `hours_requested` decimal(10,2) NOT NULL,
  `total_fc` decimal(12,2) NOT NULL,
  `reservation_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_reviewed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `orders`
--

INSERT INTO `orders` (`id`, `maker_id`, `asset_id`, `hours_requested`, `total_fc`, `reservation_date`, `status`, `created_at`, `is_reviewed`) VALUES
(1, 4, 1, 2.00, 40.00, '2026-05-29', 'completed', '2026-05-05 01:29:50', 1),
(2, 4, 4, 1.00, 30.00, '2026-06-03', 'rejected', '2026-05-22 23:58:38', 0),
(3, 4, 2, 3.00, 15.00, '2026-06-05', 'rejected', '2026-05-23 02:51:43', 0),
(4, 4, 3, 2.00, 40.00, '2026-06-02', 'completed', '2026-05-23 03:28:56', 0),
(5, 2, 2, 34.00, 170.00, '2026-06-04', 'pending', '2026-05-31 05:31:53', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`) VALUES
(2, 'henry@fablablima.org', 'c820c63cd8f38ba4b5e344f2b2fca19ee6c7f09bb2c33bceb08eaef7202affab', '2026-05-06 16:17:29'),
(4, 'hsanchez@colegioaleph.edu.pe', '7a7d799748915c90aae4943491334d3c6d8ad03069d8baf763c4c467e4ab0f2a', '2026-05-12 04:08:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `reviewee_id` int NOT NULL,
  `context_type` enum('mission','market') NOT NULL,
  `context_id` int NOT NULL,
  `rating` int NOT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Volcado de datos para la tabla `reviews`
--

INSERT INTO `reviews` (`id`, `reviewer_id`, `reviewee_id`, `context_type`, `context_id`, `rating`, `comment`, `created_at`) VALUES
(1, 3, 4, 'mission', 1, 5, 'El fabber brindó un gran desempeño en la tarea, entregó antes de tiempo.', '2026-05-04 22:07:07'),
(2, 3, 4, 'mission', 2, 5, 'Muy bien hecho. super recomendado A+++++++', '2026-05-04 23:36:17'),
(3, 3, 4, 'mission', 3, 5, 'Chévere José', '2026-05-05 01:56:34'),
(4, 4, 3, 'market', 1, 5, 'Mi pieza quedo increible dado que el Lab tenía su máquina bien conservada y configurada. Me atendieron rápido y fueron muy amables.', '2026-05-05 02:12:52'),
(5, 3, 4, 'mission', 4, 5, 'Muy bien.. trabajo dirigido', '2026-05-07 22:17:27'),
(6, 3, 4, 'mission', 7, 5, 'Abierta!', '2026-05-07 22:25:19'),
(7, 3, 4, 'mission', 8, 5, 'yes', '2026-05-07 22:49:43'),
(8, 3, 4, 'mission', 9, 5, 'Bien', '2026-05-08 00:53:08'),
(9, 3, 2, 'mission', 12, 5, 'Bien!', '2026-05-13 01:38:04'),
(10, 3, 4, 'mission', 12, 5, 'Buen trabajo', '2026-05-25 03:35:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `skills_catalog`
--

CREATE TABLE `skills_catalog` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` enum('hard','soft') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `skills_catalog`
--

INSERT INTO `skills_catalog` (`id`, `name`, `type`) VALUES
(1, 'Impresión 3D (FDM)', 'hard'),
(2, 'Impresión 3D (Resina)', 'hard'),
(3, 'Corte Láser', 'hard'),
(4, 'Mecanizado CNC', 'hard'),
(5, 'Diseño CAD / Fusión 360', 'hard'),
(6, 'Electrónica / Arduino', 'hard'),
(7, 'Soldadura SMD', 'hard'),
(8, 'Programación Python', 'hard'),
(9, 'Puntualidad Extrema', 'soft'),
(10, 'Resolución de Problemas', 'soft'),
(11, 'Comunicación Clara', 'soft'),
(12, 'Trabajo en Equipo', 'soft'),
(13, 'Cuidado de Maquinaria', 'soft');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `skill_endorsements`
--

CREATE TABLE `skill_endorsements` (
  `id` int NOT NULL,
  `maker_id` int NOT NULL,
  `skill_id` int NOT NULL,
  `lab_id` int NOT NULL,
  `review_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `skill_endorsements`
--

INSERT INTO `skill_endorsements` (`id`, `maker_id`, `skill_id`, `lab_id`, `review_id`, `created_at`) VALUES
(1, 4, 1, 3, 1, '2026-05-04 22:07:07'),
(2, 4, 10, 3, 1, '2026-05-04 22:07:07'),
(3, 4, 11, 3, 1, '2026-05-04 22:07:07'),
(4, 4, 12, 3, 1, '2026-05-04 22:07:07'),
(5, 4, 1, 3, 2, '2026-05-04 23:36:17'),
(6, 4, 3, 3, 2, '2026-05-04 23:36:17'),
(7, 4, 10, 3, 2, '2026-05-04 23:36:17'),
(8, 4, 11, 3, 2, '2026-05-04 23:36:17'),
(9, 4, 12, 3, 2, '2026-05-04 23:36:17'),
(10, 4, 5, 3, 3, '2026-05-05 01:56:34'),
(11, 4, 11, 3, 3, '2026-05-05 01:56:34'),
(12, 4, 1, 3, 5, '2026-05-07 22:17:27'),
(13, 4, 3, 3, 5, '2026-05-07 22:17:27'),
(14, 4, 10, 3, 5, '2026-05-07 22:17:27'),
(15, 4, 12, 3, 5, '2026-05-07 22:17:27'),
(16, 4, 1, 3, 6, '2026-05-07 22:25:19'),
(17, 4, 8, 3, 6, '2026-05-07 22:25:19'),
(18, 4, 9, 3, 6, '2026-05-07 22:25:19'),
(19, 4, 11, 3, 6, '2026-05-07 22:25:19'),
(20, 4, 1, 3, 7, '2026-05-07 22:49:43'),
(21, 4, 2, 3, 7, '2026-05-07 22:49:43'),
(22, 4, 9, 3, 7, '2026-05-07 22:49:43'),
(23, 4, 10, 3, 7, '2026-05-07 22:49:43'),
(24, 4, 2, 3, 8, '2026-05-08 00:53:08'),
(25, 4, 10, 3, 8, '2026-05-08 00:53:08'),
(26, 4, 1, 3, 10, '2026-05-25 03:35:01'),
(27, 4, 8, 3, 10, '2026-05-25 03:35:01'),
(28, 4, 10, 3, 10, '2026-05-25 03:35:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transactions`
--

CREATE TABLE `transactions` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` varchar(30) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `description`, `amount`, `type`, `created_at`) VALUES
(1, 3, 'Emisión (Mint): Especialista', 2000.00, 'mint', '2026-05-04 17:54:09'),
(2, 3, 'Reserva en Escrow: Misión 1 prueba calficación fabber (1 cupos)', 50.00, 'escrow', '2026-05-04 17:54:39'),
(4, 4, 'Pago recibido: Misión 1 prueba calficación fabber (Misión #1) por Fab Lab Perú', 50.00, 'income', '2026-05-04 18:07:07'),
(5, 3, 'Reserva en Escrow: Prueba 2 (1 cupos)', 100.00, 'escrow', '2026-05-04 19:04:38'),
(6, 4, 'Pago recibido: Prueba 2 (Misión #2) por Fab Lab Perú', 100.00, 'income', '2026-05-04 19:36:17'),
(7, 4, '🔥 Servicio consumido (Quema): Especialista', 40.00, 'burn', '2026-05-04 21:29:50'),
(10, 3, 'Reserva en Escrow: Crear mapa de google maps para regen (1 cupos)', 50.00, 'escrow', '2026-05-04 21:51:44'),
(11, 4, 'Pago recibido: Crear mapa de google maps para regen (Misión #3) por Fab Lab Perú', 50.00, 'income', '2026-05-04 21:56:34'),
(12, 3, 'Reserva en Escrow: Misión para Henry prueba deuda (1 cupos)', 1000.00, 'escrow', '2026-05-07 16:31:55'),
(13, 3, 'Reserva en Escrow: Trabajo pro academy Henry (1 cupos)', 299.99, 'escrow', '2026-05-07 17:15:53'),
(14, 3, 'Reserva en Escrow: Misión Abierta (1 cupos)', 50.01, 'escrow', '2026-05-07 17:22:06'),
(15, 3, 'Retorno de Crédito Fab (Misión #4)', 1000.00, 'income', '2026-05-07 18:17:27'),
(16, 3, 'Reserva en Escrow: Misión abierta 2 (1 cupos)', 50.00, 'escrow', '2026-05-07 18:21:36'),
(17, 4, 'Pago recibido: Misión abierta 2 (Misión #7) por Fab Lab Perú', 50.00, 'income', '2026-05-07 18:25:19'),
(18, 3, 'Reserva en Escrow: Dirigida 3 (1 cupos)', 400.00, 'escrow', '2026-05-07 18:43:38'),
(19, 3, 'Retorno de Crédito Fab (Misión #8)', 400.00, 'income', '2026-05-07 18:49:43'),
(20, 3, 'Emisión (Mint): Flashforge AD5X', 7280.00, 'mint', '2026-05-07 20:26:02'),
(21, 3, 'Reserva en Escrow: Misión Cerrada (1 cupos)', 500.00, 'escrow', '2026-05-07 20:28:11'),
(22, 3, 'Retorno de Crédito Fab (Misión #9)', 500.00, 'income', '2026-05-07 20:53:08'),
(23, 3, 'Emisión (Mint): Creality C1', 29120.00, 'mint', '2026-05-07 21:35:50'),
(24, 3, 'Emisión (Mint): Especialista', 600.00, 'mint', '2026-05-07 21:35:50'),
(25, 3, 'Emisión (Mint): prueba', 1440.00, 'mint', '2026-05-07 21:35:50'),
(26, 3, 'Emisión (Mint): Flashforge AD5X', 780.00, 'mint', '2026-05-07 21:35:50'),
(27, 3, 'Emisión (Mint): Creality C1', 17472.00, 'mint', '2026-05-07 21:40:42'),
(28, 3, 'Emisión (Mint): Creality C1', 0.00, 'mint', '2026-05-07 21:40:42'),
(29, 3, 'Emisión (Mint): Flashforge AD5X', 21840.00, 'mint', '2026-05-07 21:40:42'),
(30, 3, 'Emisión (Mint): prueba', 0.00, 'mint', '2026-05-07 21:40:42'),
(31, 3, 'Emisión (Mint): Creality C1', 29120.00, 'mint', '2026-05-07 21:59:05'),
(32, 3, 'Emisión (Mint): Flashforge AD5X', 800.00, 'mint', '2026-05-07 21:59:05'),
(33, 3, 'Emisión (Mint): Flashforge AD5X', 3200.00, 'mint', '2026-05-07 21:59:05'),
(34, 3, 'Emisión (Mint): Especialista', 200.00, 'mint', '2026-05-07 21:59:05'),
(35, 10, 'Emisión (Mint): Creality C1', 29120.00, 'mint', '2026-05-11 22:23:42'),
(36, 10, 'Emisión (Mint): Especialista', 200.00, 'mint', '2026-05-11 22:23:42'),
(37, 10, 'Emisión (Mint): Especialista', 17472.00, 'mint', '2026-05-11 22:37:23'),
(38, 10, 'Emisión (Mint): Especialista', 320.00, 'mint', '2026-05-11 22:37:23'),
(39, 3, 'Reserva en Escrow: Misión dirigida 100 (1 cupos)', 1000.00, 'escrow', '2026-05-12 13:24:13'),
(40, 3, 'Reserva en Escrow: Misión abierta con cupos (20 cupos)', 1000.00, 'escrow', '2026-05-12 13:25:53'),
(41, 3, 'Reserva en Escrow: Misión prueba nueva (2 cupos)', 1000.00, 'escrow', '2026-05-12 19:19:40'),
(42, 3, 'Reserva en Escrow: Misión botón scrip (1 cupos)', 250.00, 'escrow', '2026-05-12 20:28:35'),
(43, 2, 'Pago recibido: Misión prueba nueva (Misión #12) por Fab Lab Perú', 500.00, 'income', '2026-05-12 21:38:03'),
(44, 2, 'Envío P2P a Henry Sánchez', 50.00, 'expense', '2026-05-19 18:45:54'),
(45, 4, 'Recibido P2P de Beno (Super Admin)', 50.00, 'income', '2026-05-19 18:45:54'),
(46, 4, 'Envío P2P a Beno (Super Admin)', 60.00, 'expense', '2026-05-19 18:49:09'),
(47, 2, 'Recibido P2P de Henry Sánchez', 60.00, 'income', '2026-05-19 18:49:09'),
(48, 3, 'Reserva en Escrow: Abierta 2 (10 cupos)', 1000.00, 'escrow', '2026-05-22 15:31:43'),
(49, 4, 'Reserva en custodia: Especialista', 30.00, 'expense', '2026-05-22 19:58:38'),
(50, 4, 'Reembolso por reserva rechazada: Especialista', 30.00, 'income', '2026-05-22 22:42:00'),
(51, 4, 'Reserva en custodia: Flashforge AD5X', 15.00, 'expense', '2026-05-22 22:51:43'),
(52, 4, 'Reembolso por reserva cancelada (Incompatibilidad fechas): Flashforge AD5X', 15.00, 'income', '2026-05-22 23:18:27'),
(53, 4, '🔥 Servicio consumido (Quema): Creality C1', 40.00, 'burn', '2026-05-22 23:28:56'),
(56, 3, 'Reserva completada (Quema de Escrow): Especialista (40.00 FC amortizados)', 0.00, 'info', '2026-05-04 21:29:50'),
(57, 3, 'Reserva completada (Quema de Escrow): Creality C1 (40.00 FC amortizados)', 0.00, 'info', '2026-05-22 23:28:56'),
(59, 3, 'Emisión (Mint): Creality CR-Scan Ferret Pro', 11648.00, 'mint', '2026-05-23 23:05:41'),
(60, 3, 'Emisión (Mint): Falcon A1 Pro 20W', 17472.00, 'mint', '2026-05-23 23:08:22'),
(61, 3, 'Emisión (Mint): TTC 6050', 7280.00, 'mint', '2026-05-23 23:08:22'),
(62, 3, 'PENALIZACIÓN: Retiro de activo Creality C1', 29120.00, 'expense', '2026-05-23 23:12:23'),
(63, 4, 'Pago recibido: Misión prueba nueva (Misión #12) por Fab Lab Perú', 500.00, 'income', '2026-05-24 23:35:00'),
(64, 3, 'Liberación (Misión #1): 50.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(65, 3, 'Liberación (Misión #2): 100.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(66, 3, 'Liberación (Misión #3): 50.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(67, 3, 'Liberación (Misión #4): 1000.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(68, 3, 'Liberación (Misión #7): 50.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(69, 3, 'Liberación (Misión #8): 400.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(70, 3, 'Liberación (Misión #9): 500.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(71, 3, 'Liberación (Misión #12): 500.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(72, 3, 'Liberación (Misión #12): 500.00 FC transferidos a Henry Sánchez', 0.00, 'info', '2026-05-25 00:10:46'),
(73, 2, 'Reserva en custodia: Flashforge AD5X', 170.00, 'expense', '2026-05-31 01:31:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `role` enum('superadmin','lab','maker') NOT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `bio` text,
  `address` varchar(255) DEFAULT NULL,
  `force_password_change` tinyint(1) DEFAULT '1',
  `reputation_score` decimal(3,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `location` varchar(100) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT 'default_avatar.png',
  `portfolio_url` varchar(255) DEFAULT NULL,
  `github_url` varchar(255) DEFAULT NULL,
  `linkedin_url` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `user_tagline` varchar(100) DEFAULT NULL,
  `social_linkedin` varchar(255) DEFAULT NULL,
  `social_github` varchar(255) DEFAULT NULL,
  `social_portfolio` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_fabacademy` varchar(255) DEFAULT NULL,
  `deuda_inicial_fc` decimal(10,2) DEFAULT '0.00',
  `deuda_fc` decimal(10,2) DEFAULT '0.00',
  `deuda_lab_id` int DEFAULT NULL,
  `preferred_lang` varchar(2) DEFAULT 'es',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `onboarding_completed` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `slug`, `role`, `avatar_url`, `bio`, `address`, `force_password_change`, `reputation_score`, `created_at`, `location`, `profile_pic`, `portfolio_url`, `github_url`, `linkedin_url`, `phone`, `user_tagline`, `social_linkedin`, `social_github`, `social_portfolio`, `social_instagram`, `social_fabacademy`, `deuda_inicial_fc`, `deuda_fc`, `deuda_lab_id`, `preferred_lang`, `latitude`, `longitude`, `onboarding_completed`) VALUES
(1, 'hola@tinkilab.com', '$2y$10$jLz8GQwRpp6/jhelm.D3K..4wkDZdnbO55KnWSj10S9AFyGHy3s8a', 'Henry (Super Admin)', 'tinkilab', 'superadmin', 'https://ui-avatars.com/api/?name=Henry&background=0D8ABC&color=fff', NULL, NULL, 0, 0.00, '2026-04-27 10:25:32', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'es', NULL, NULL, 0),
(2, 'beno@fablablima.org', '$2y$10$BZWfxNwe5ot/wiKD3iuFQuP4De3XD2JJZ32WDGy3oxtLTv25dPbV2', 'Beno (Super Admin)', 'beno-juarez', 'maker', 'https://ui-avatars.com/api/?name=Beno&background=0D8ABC&color=fff', NULL, NULL, 0, 5.00, '2026-04-27 10:25:32', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'es', NULL, NULL, 1),
(3, 'contacto@fablablima.org', '$2y$10$HmhaDLVWJIarlz2hrwcyfenwN7372xHRFKdYTMFR0zTU.jVhxvBsq', 'Fab Lab Perú', 'fab-lab-peru', 'lab', 'https://ui-avatars.com/api/?name=Fab+Lab+Per%C3%BA&background=2ecc71&color=fff', 'Fab Lab Perú es una asociación civil sin fines de lucro que funciona como un centro de investigación aplicada y desarrollo tecnológico. Fue el pionero en Latinoamérica en el campo de la fabricación digital, impulsando una red de laboratorios que permiten \"crear casi cualquier cosa\" mediante el uso de herramientas tecnológicas avanzadas.', 'C. Pl. Bolívar, Pueblo Libre 15084', 0, 5.00, '2026-04-27 12:50:28', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, '', '', '', '', '', 0.00, 0.00, NULL, 'es', -12.07737006, -77.06228435, 1),
(4, 'henry@fablablima.org', '$2y$10$G3MOflc4kHLBLO9suWtSSOEo2ZVF1vE7oMxhaTHbwrzxgZPGGp0RC', 'Henry Sánchez', 'henry-sanchez', 'maker', 'https://ui-avatars.com/api/?name=Henry+S%C3%A1nchez&background=9b59b6&color=fff', '<p><strong>Henry Joel S&aacute;nchez Grimaldo</strong> es un emprendedor peruano enfocado en la educaci&oacute;n tecnol&oacute;gica y el aprendizaje interactivo. Actualmente, destaca como el CEO y Fundador de Tinki Lab, una organizaci&oacute;n dedicada a desarrollar metodolog&iacute;as de aprendizaje digital para ni&ntilde;os.</p>\r\n<p><strong>Perfil Profesional y Acad&eacute;mico Formaci&oacute;n: </strong>Cuenta con estudios del Diplomado Internacional del Fab Acacemy (Fab Foundation | MIT). Especialidad: Es l&iacute;der en el dise&ntilde;o e implementaci&oacute;n de metodolog&iacute;as educativas que integran herramientas tecnol&oacute;gicas para potenciar el aprendizaje infantil. Trayectoria: Su trabajo en Tinki Lab se centra en cerrar brechas educativas mediante la innovaci&oacute;n y el uso de plataformas digitales interactivas.</p>', 'Lima, Perú', 0, 5.00, '2026-04-27 21:06:12', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, 'https://www.linkedin.com/in/tiogeny/', '', 'https://canva.link/puqsldexgko4oo1', 'https://www.instagram.com/henrysanchez.pe/', 'https://fabacademy.org/2015/sa/students/sanchez.henry/index.html', 3750.00, 1850.00, 3, 'es', NULL, NULL, 1),
(5, 'chupignegocio@gmail.com', '$2y$10$0af/7LscwPxtPVuQQ4ubeOLjhMSZbmD6T/1Ibj4oeyRZn3g3oHN4q', 'Tyobiri Jaime Miguel Amaro Simón', 'tyobiri-amaro', 'maker', 'https://ui-avatars.com/api/?name=Tyobiri+Jaime+Miguel+Amaro+Sim%C3%B3n&background=9b59b6&color=fff', NULL, NULL, 0, 0.00, '2026-04-29 20:14:26', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'es', NULL, NULL, 0),
(6, 'tyobiri25@gmail.com', '$2y$10$x80uZs5Kl10/d3NREkPMlexa4f0Yc4ljWcrRPB806EvkMvyhP8Etm', 'Tyobiri Jaime Miguel Amaro Simón', 'tyobiri-amaro2', 'maker', 'https://ui-avatars.com/api/?name=Tyobiri+Jaime+Miguel+Amaro+Sim%C3%B3n&background=9b59b6&color=fff', NULL, NULL, 0, 0.00, '2026-04-29 20:17:35', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'es', NULL, NULL, 0),
(7, 'evkusi@gmail.com', '$2y$10$FpaV5xpk8otWKYOpAo.aQu/w7UG.l6GJALCZMr8hrw8YALfClhMX6', 'Evelyn Andrea Cuadrado Guerrero', 'evelyn-cuadrado', 'maker', 'https://ui-avatars.com/api/?name=Evelyn+Andrea+Cuadrado+Guerrero&background=9b59b6&color=fff', NULL, NULL, 0, 0.00, '2026-04-29 20:40:19', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'es', NULL, NULL, 0),
(8, 'info@fably.net', '$2y$10$VCily5aCn37.a61fW5RPle8Kuj32Xgu5/JwHfMEXi0RhWzlAxNq.m', 'FabLy Lab', 'fably-lab-301', 'lab', 'https://ui-avatars.com/api/?name=FabLy+Lab&background=2ecc71&color=fff', NULL, NULL, 1, 0.00, '2026-05-11 21:32:45', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'en', NULL, NULL, 0),
(9, 'cloud@fab.lat', '$2y$10$gFNAyXFJAkU8g0tU08fhIe/479lxEpk/5XoUkArAuUUaCOFaGuI.S', 'Fab Latam Test', 'fab-latam-test-672', 'lab', 'https://ui-avatars.com/api/?name=Fab+Latam+Test&background=2ecc71&color=fff', NULL, NULL, 1, 0.00, '2026-05-11 21:45:15', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'en', NULL, NULL, 0),
(10, 'hsanchez@colegioaleph.edu.pe', '$2y$10$vW2s5MCkXJuY/JZNirAquewcVx5ZM4ytbT0bjMruM56uxnrR4LY8i', 'Lab Test', 'lab-test-701', 'lab', 'https://ui-avatars.com/api/?name=Lab+Test&background=2ecc71&color=fff', 'Bio de prueba', 'Lima, Perú', 0, 0.00, '2026-05-11 21:54:47', NULL, 'default_avatar.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0.00, 0.00, NULL, 'en', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_skills`
--

CREATE TABLE `user_skills` (
  `user_id` int NOT NULL,
  `skill_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `user_skills`
--

INSERT INTO `user_skills` (`user_id`, `skill_id`) VALUES
(4, 1),
(4, 2),
(4, 3),
(4, 5),
(4, 8),
(4, 9),
(4, 10),
(4, 11),
(4, 12);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `financing_agreements`
--
ALTER TABLE `financing_agreements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `fabber_id` (`maker_id`);

--
-- Indices de la tabla `global_catalog`
--
ALTER TABLE `global_catalog`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `global_settings`
--
ALTER TABLE `global_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indices de la tabla `lab_assets`
--
ALTER TABLE `lab_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `catalog_id` (`catalog_id`);

--
-- Indices de la tabla `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `fk_target_fabber` (`target_maker_id`);

--
-- Indices de la tabla `mission_applications`
--
ALTER TABLE `mission_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mission_id` (`mission_id`),
  ADD KEY `fabber_id` (`maker_id`);

--
-- Indices de la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fabber_id` (`maker_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviewer_id` (`reviewer_id`),
  ADD KEY `reviewee_id` (`reviewee_id`);

--
-- Indices de la tabla `skills_catalog`
--
ALTER TABLE `skills_catalog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `skill_endorsements`
--
ALTER TABLE `skill_endorsements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fabber_id` (`maker_id`),
  ADD KEY `skill_id` (`skill_id`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `review_id` (`review_id`);

--
-- Indices de la tabla `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `fk_deuda_lab` (`deuda_lab_id`);

--
-- Indices de la tabla `user_skills`
--
ALTER TABLE `user_skills`
  ADD PRIMARY KEY (`user_id`,`skill_id`),
  ADD KEY `skill_id` (`skill_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `financing_agreements`
--
ALTER TABLE `financing_agreements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `global_catalog`
--
ALTER TABLE `global_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `lab_assets`
--
ALTER TABLE `lab_assets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `missions`
--
ALTER TABLE `missions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `mission_applications`
--
ALTER TABLE `mission_applications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `skills_catalog`
--
ALTER TABLE `skills_catalog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `skill_endorsements`
--
ALTER TABLE `skill_endorsements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `financing_agreements`
--
ALTER TABLE `financing_agreements`
  ADD CONSTRAINT `financing_agreements_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `financing_agreements_ibfk_2` FOREIGN KEY (`maker_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `lab_assets`
--
ALTER TABLE `lab_assets`
  ADD CONSTRAINT `lab_assets_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `lab_assets_ibfk_2` FOREIGN KEY (`catalog_id`) REFERENCES `global_catalog` (`id`);

--
-- Filtros para la tabla `missions`
--
ALTER TABLE `missions`
  ADD CONSTRAINT `fk_target_fabber` FOREIGN KEY (`target_maker_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `missions_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `mission_applications`
--
ALTER TABLE `mission_applications`
  ADD CONSTRAINT `mission_applications_ibfk_1` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`),
  ADD CONSTRAINT `mission_applications_ibfk_2` FOREIGN KEY (`maker_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`maker_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `lab_assets` (`id`);

--
-- Filtros para la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewee_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `skill_endorsements`
--
ALTER TABLE `skill_endorsements`
  ADD CONSTRAINT `skill_endorsements_ibfk_1` FOREIGN KEY (`maker_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `skill_endorsements_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills_catalog` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `skill_endorsements_ibfk_3` FOREIGN KEY (`lab_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `skill_endorsements_ibfk_4` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_deuda_lab` FOREIGN KEY (`deuda_lab_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `user_skills`
--
ALTER TABLE `user_skills`
  ADD CONSTRAINT `user_skills_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_skills_ibfk_2` FOREIGN KEY (`skill_id`) REFERENCES `skills_catalog` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
