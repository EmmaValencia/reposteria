-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 12:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `galeria_3d`
--

-- --------------------------------------------------------

--
-- Table structure for table `modelos`
--

CREATE TABLE `modelos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `ruta_miniatura` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modelos`
--

INSERT INTO `modelos` (`id`, `nombre`, `descripcion`, `ruta_archivo`, `ruta_miniatura`) VALUES
(1, 'Modelo 1', 'Breve descripción del primer modelo 3D.', 'assets/modelo1.glb', 'assets/thumbnails/modelo1.jpg'),
(2, 'Modelo 2', 'Esta es la descripción del segundo modelo.', 'assets/modelo2.glb', 'assets/thumbnails/modelo2.jpg'),
(3, 'Modelo 3', 'Un tercer objeto increíble con detalles.', 'assets/modelo3.glb', 'assets/thumbnails/modelo3.jpg'),
(4, 'Modelo 4', 'Descripción del modelo número cuatro.', 'assets/modelo4.glb', 'assets/thumbnails/modelo4.jpg'),
(5, 'Modelo 5', 'Quinto modelo de la colección.', 'assets/modelo5.glb', 'assets/thumbnails/modelo5.jpg'),
(6, 'Modelo 6', 'Descripción del modelo número seis.', 'assets_modelo6.glb', 'assets/thumbnails/modelo6.jpg'),
(7, 'Modelo 7', 'Séptimo modelo de la colección.', 'assets/modelo7.glb', 'assets/thumbnails/modelo7.jpg'),
(8, 'Modelo 8', 'Octavo modelo de la colección.', 'assets/modelo8.glb', 'assets/thumbnails/modelo8.jpg'),
(9, 'Modelo 9', 'Noveno modelo de la colección.', 'assets/modelo9.glb', 'assets/thumbnails/modelo9.jpg'),
(10, 'Modelo 10', 'Décimo modelo de la colección.', 'assets/modelo10.glb', 'assets/thumbnails/modelo10.jpg'),
(11, 'Modelo 11', 'Onceavo modelo de la colección.', 'assets/modelo11.glb', 'assets/thumbnails/modelo11.jpg'),
(12, 'Modelo 12', 'Doceavo modelo de la colección.', 'assets/modelo12.glb', 'assets/thumbnails/modelo12.jpg'),
(13, 'Modelo 13', 'Descripción del modelo número trece.', 'assets/modelo13.glb', 'assets/thumbnails/modelo13.jpg'),
(14, 'Modelo 14', 'Descripción del modelo número catorce.', 'assets/modelo14.glb', 'assets/thumbnails/modelo14.jpg'),
(15, 'Modelo 15', 'Quinceavo modelo de la colección.', 'assets/modelo15.glb', 'assets/thumbnails/modelo15.jpg'),
(16, 'Modelo 16', 'Descripción del modelo número dieciséis.', 'assets/modelo16.glb', 'assets/thumbnails/modelo16.jpg'),
(17, 'Modelo 17', 'Descripción del modelo número diecisiete.', 'assets/modelo17.glb', 'assets/thumbnails/modelo17.jpg'),
(18, 'Modelo 18', 'Descripción del modelo número dieciocho.', 'assets/modelo18.glb', 'assets/thumbnails/modelo18.jpg'),
(19, 'Modelo 19', 'Descripción del modelo número diecinueve.', 'assets/modelo19.glb', 'assets/thumbnails/modelo19.jpg'),
(20, 'Modelo 20', 'El último modelo de esta galería.', 'assets/modelo20.glb', 'assets/thumbnails/modelo20.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `modelos`
--
ALTER TABLE `modelos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `modelos`
--
ALTER TABLE `modelos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
