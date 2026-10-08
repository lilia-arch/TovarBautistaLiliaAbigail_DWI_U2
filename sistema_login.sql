-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-10-2026 a las 05:36:50
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_login`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `usuario_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `producto_id`, `nombre_producto`, `precio`, `cantidad`, `usuario_id`, `total`, `fecha`) VALUES
(1, 9, 'Puma Casual', 1399.00, 1, 1, 1399.00, '2026-10-07 20:39:40'),
(2, 7, 'Nike Air Max', 2199.00, 1, 1, 2199.00, '2026-10-07 20:45:41'),
(3, 25, 'Tenis Deportivo Premium', 1899.00, 1, 1, 1899.00, '2026-10-07 20:51:35'),
(4, 25, 'Tenis Deportivo Premium', 1899.00, 1, 1, 1899.00, '2026-10-07 21:04:54'),
(5, 25, 'Tenis Deportivo Premium', 1899.00, 1, 1, 1899.00, '2026-10-07 21:31:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_compra`
--

CREATE TABLE `detalle_compra` (
  `id` int(11) NOT NULL,
  `compra_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `talla` varchar(50) NOT NULL DEFAULT '24 - 29',
  `imagen` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `stock`, `talla`, `imagen`) VALUES
(1, 'Nike Air Sport', 'Tenis deportivos para correr y realizar actividades físicas.', 1899.00, 10, '24 - 29', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80'),
(2, 'Adidas Urban', 'Tenis modernos y cómodos para utilizar todos los días.', 1699.00, 8, '25 - 30', 'https://images.unsplash.com/photo-1552346154-21d32810aba3?auto=format&fit=crop&w=800&q=80'),
(3, 'Nike', 'Tenis ligeros ideales para correr y entrenar.', 1499.00, 12, '24 - 28', 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=800&q=80'),
(5, 'Puma', 'Tenis con excelente comodidad y soporte.', 1799.00, 9, '25 - 30', 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&q=80'),
(7, 'Nike Air Max', 'Tenis deportivos con amortiguación para uso diario.', 2199.00, 5, '25 - 30', 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80'),
(23, 'Adidas Grand Court', 'Tenis Adidas de estilo casual en color blanco, ideales para uso diario.', 1599.00, 10, '24 - 29', 'https://tse3.mm.bing.net/th/id/OIP.yrwrK8OQyKENth-wwk3bjgHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'),
(24, 'ASICS Gel Kayano', 'Tenis ASICS para correr con diseño deportivo y excelente comodidad.', 2299.00, 8, '25 - 30', 'https://topdezmelhores.com.br/wp-content/uploads/2025/07/Os-10-melhores-tenis-Asics-para-corrida-femininos-em-2024-Kayano-Nimbus-e-muito-mais.jpg'),
(25, 'Tenis Deportivo Premium', 'Tenis deportivos modernos ideales para entrenamiento y actividades físicas.', 1899.00, 6, '24 - 29', 'https://tse2.mm.bing.net/th/id/OIP.RkRD7utlQASbRNf-4k06zQHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3'),
(26, 'Tenis Sport Urban', 'Tenis deportivos con diseño moderno y comodidad para uso cotidiano.', 1799.00, 12, '25 - 30', 'https://tse3.mm.bing.net/th/id/OIP.vaZX2Ajj-W1N8PRQy7BtKQHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `rol`) VALUES
(1, 'abi', '$2y$10$HvFOQROiZ1we/1soBFgjHuuD4bApBfKJgru5AAZ17UDRMNYAN8G0u', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD PRIMARY KEY (`id`),
  ADD KEY `compra_id` (`compra_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `detalle_compra`
--
ALTER TABLE `detalle_compra`
  ADD CONSTRAINT `detalle_compra_ibfk_1` FOREIGN KEY (`compra_id`) REFERENCES `compras` (`id`),
  ADD CONSTRAINT `detalle_compra_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
