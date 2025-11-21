-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2025 a las 00:51:56
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `helpdesk`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_d_usuario_01` (IN `xusu_id` INT)   BEGIN
	UPDATE tm_usuario 
	SET 
		est='0',
		fech_elim = now() 
	where usu_id=xusu_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_i_ticketdetalle_01` (IN `xtick_id` INT, IN `xusu_id` INT)   BEGIN
	INSERT INTO td_ticketdetalle 
    (tickd_id,tick_id,usu_id,tickd_descrip,fech_crea,est) 
    VALUES 
    (NULL,xtick_id,xusu_id,'Ticket Cerrado...',now(),'1');
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_l_usuario_01` ()   BEGIN
	SELECT * FROM tm_usuario where est='1';
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_l_usuario_02` (IN `xusu_id` INT)   BEGIN
	SELECT * FROM tm_usuario where usu_id=xusu_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `td_documento`
--

CREATE TABLE `td_documento` (
  `doc_id` int(11) NOT NULL,
  `tick_id` int(11) NOT NULL,
  `doc_nom` varchar(400) NOT NULL,
  `fech_crea` datetime NOT NULL,
  `est` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `td_ticketdetalle`
--

CREATE TABLE `td_ticketdetalle` (
  `tickd_id` int(11) NOT NULL,
  `tick_id` int(11) NOT NULL,
  `usu_id` int(11) NOT NULL,
  `tickd_descrip` mediumtext NOT NULL,
  `fech_crea` datetime NOT NULL,
  `est` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `td_ticketdetalle`
--

INSERT INTO `td_ticketdetalle` (`tickd_id`, `tick_id`, `usu_id`, `tickd_descrip`, `fech_crea`, `est`) VALUES
(7, 2, 2, 'Ticket Cerrado...', '2025-11-19 10:37:59', 1),
(8, 3, 2, 'Ticket en espera...', '2025-11-19 12:30:54', 0),
(12, 3, 2, 'Ticket Re-Abierto...', '2025-11-19 13:11:07', 1),
(13, 3, 2, 'Ticket en espera...', '2025-11-19 13:27:08', 0),
(14, 3, 2, 'Ticket Re-Abierto...', '2025-11-19 13:27:23', 1),
(15, 3, 2, 'Ticket en espera...', '2025-11-19 17:12:24', 0),
(16, 3, 2, 'Ticket Re-Abierto...', '2025-11-19 17:12:48', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_categoria`
--

CREATE TABLE `tm_categoria` (
  `cat_id` int(11) NOT NULL,
  `cat_nom` varchar(150) NOT NULL,
  `est` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tm_categoria`
--

INSERT INTO `tm_categoria` (`cat_id`, `cat_nom`, `est`) VALUES
(1, 'Falla de energía eléctrica', 1),
(2, 'Software', 1),
(3, 'Incidencia', 1),
(4, 'Petición de Servicio', 1),
(5, 'Corte de fibra', 1),
(6, 'Falla de proveedor', 1),
(7, 'Prueba_híbrido', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_clientes`
--

CREATE TABLE `tm_clientes` (
  `cli_id` int(11) NOT NULL,
  `cli_nom` varchar(250) NOT NULL,
  `cli_ape` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tm_clientes`
--

INSERT INTO `tm_clientes` (`cli_id`, `cli_nom`, `cli_ape`) VALUES
(3, 'Sanchez', 'Teapa'),
(5, 'ENLACE PARRILLA', '-TEAPA TEN0/20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_ticket`
--

CREATE TABLE `tm_ticket` (
  `tick_id` int(11) NOT NULL,
  `usu_id` int(11) NOT NULL,
  `cli_id` int(11) DEFAULT NULL,
  `cat_id` int(11) NOT NULL,
  `tick_titulo` varchar(250) NOT NULL,
  `tick_descrip` mediumtext NOT NULL,
  `tick_estado` varchar(15) DEFAULT NULL,
  `fech_crea` datetime DEFAULT NULL,
  `usu_asig` int(11) DEFAULT NULL,
  `fech_asig` datetime DEFAULT NULL,
  `est` int(11) NOT NULL,
  `tiempo_acumulado` int(11) DEFAULT 0,
  `fech_estado_ultimo` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `tm_ticket`
--

INSERT INTO `tm_ticket` (`tick_id`, `usu_id`, `cli_id`, `cat_id`, `tick_titulo`, `tick_descrip`, `tick_estado`, `fech_crea`, `usu_asig`, `fech_asig`, `est`, `tiempo_acumulado`, `fech_estado_ultimo`) VALUES
(2, 2, 3, 1, 'HOLA', '<p>HKJHKJ</p>', 'Cerrado', '2025-11-18 10:14:46', 12, '2025-11-18 18:21:51', 1, 0, '2025-11-18 10:14:46'),
(3, 2, 5, 5, 'OLA', '<p>CORTE DE FIBRA SE MANDA A TECNICO A MEDIR&nbsp;</p>', 'Abierto', '2025-11-18 10:45:53', 15, '2025-11-18 10:46:17', 1, 1826, '2025-11-19 17:12:48'),
(4, 2, 5, 6, 'ufyytvyvuvukt yfytf txre', '<p>qwertyuiopÁSDFGHJKLÑ{ZXCVBNM,.</p>', 'Abierto', '2025-11-19 10:23:32', NULL, NULL, 1, 0, '2025-11-19 10:23:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tm_usuario`
--

CREATE TABLE `tm_usuario` (
  `usu_id` int(11) NOT NULL,
  `usu_nom` varchar(150) DEFAULT NULL,
  `usu_ape` varchar(150) DEFAULT NULL,
  `usu_correo` varchar(150) NOT NULL,
  `usu_pass` varchar(150) NOT NULL,
  `rol_id` int(11) DEFAULT NULL,
  `fech_crea` datetime DEFAULT NULL,
  `fech_modi` datetime DEFAULT NULL,
  `fech_elim` datetime DEFAULT NULL,
  `est` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci COMMENT='Tabla Mantenedor de Usuarios';

--
-- Volcado de datos para la tabla `tm_usuario`
--

INSERT INTO `tm_usuario` (`usu_id`, `usu_nom`, `usu_ape`, `usu_correo`, `usu_pass`, `rol_id`, `fech_crea`, `fech_modi`, `fech_elim`, `est`) VALUES
(1, 'Anderson', 'Bastidas', 'davis_anderson_87@hotmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, '2020-12-14 19:46:22', NULL, '2025-11-13 12:58:05', 0),
(2, 'Jose', 'Jimenez', 'jimenez_alberto015@outlook.com', '25f9e794323b453885f5181f1b624d0b', 2, '2020-12-14 19:46:22', NULL, NULL, 1),
(12, 'Karen', 'Luna', 'karen.luna@fast-net.net', '781e5e245d69b566979b86e28d23f2c7', 2, '2025-11-06 17:17:34', NULL, NULL, 1),
(13, 'Ricardo', 'Cruz', 'ricardo.cruz@fast.net.net', '781e5e245d69b566979b86e28d23f2c7', 2, '2025-11-12 11:55:02', NULL, NULL, 1),
(14, 'Victor', 'Lugo', 'victor.lugo@fast-net.net', '781e5e245d69b566979b86e28d23f2c7', 2, '2025-11-12 11:56:26', NULL, NULL, 1),
(15, 'Bryan', 'Pérez', 'bryan.perez@fast-net.net', '781e5e245d69b566979b86e28d23f2c7', 2, '2025-11-12 12:06:50', NULL, NULL, 1),
(16, 'Vanessa', 'Alejandro', 'vanessa.alejandro@fast-net.net', '781e5e245d69b566979b86e28d23f2c7', 2, '2025-11-12 12:07:52', NULL, NULL, 1),
(17, 'Daniel', 'Echeverría', 'daniel.echeverria@fast-net.net', '25f9e794323b453885f5181f1b624d0b', 2, '2025-11-14 17:41:21', NULL, NULL, 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `td_documento`
--
ALTER TABLE `td_documento`
  ADD PRIMARY KEY (`doc_id`);

--
-- Indices de la tabla `td_ticketdetalle`
--
ALTER TABLE `td_ticketdetalle`
  ADD PRIMARY KEY (`tickd_id`);

--
-- Indices de la tabla `tm_categoria`
--
ALTER TABLE `tm_categoria`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indices de la tabla `tm_clientes`
--
ALTER TABLE `tm_clientes`
  ADD PRIMARY KEY (`cli_id`);

--
-- Indices de la tabla `tm_ticket`
--
ALTER TABLE `tm_ticket`
  ADD PRIMARY KEY (`tick_id`),
  ADD KEY `fk_cli_ticket` (`cli_id`);

--
-- Indices de la tabla `tm_usuario`
--
ALTER TABLE `tm_usuario`
  ADD PRIMARY KEY (`usu_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `td_documento`
--
ALTER TABLE `td_documento`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `td_ticketdetalle`
--
ALTER TABLE `td_ticketdetalle`
  MODIFY `tickd_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `tm_categoria`
--
ALTER TABLE `tm_categoria`
  MODIFY `cat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `tm_clientes`
--
ALTER TABLE `tm_clientes`
  MODIFY `cli_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tm_ticket`
--
ALTER TABLE `tm_ticket`
  MODIFY `tick_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tm_usuario`
--
ALTER TABLE `tm_usuario`
  MODIFY `usu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tm_ticket`
--
ALTER TABLE `tm_ticket`
  ADD CONSTRAINT `fk_cli_ticket` FOREIGN KEY (`cli_id`) REFERENCES `tm_clientes` (`cli_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
