-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2026 at 12:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_penerbitan_buku`
--

-- --------------------------------------------------------

--
-- Table structure for table `penerbitan`
--

CREATE TABLE `penerbitan` (
  `no_penerbitan` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_penulis` int(11) DEFAULT NULL,
  `no_isbn` varchar(20) DEFAULT NULL,
  `judul_buku` varchar(200) NOT NULL,
  `tahun` year(4) DEFAULT NULL,
  `id_reviewer` int(11) DEFAULT NULL,
  `id_editor` int(11) DEFAULT NULL,
  `jumlah_terbit` int(11) DEFAULT 0,
  `keterangan` text DEFAULT NULL,
  `harga` decimal(15,2) DEFAULT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `status` enum('Diterima','Ditolak','Proses') DEFAULT 'Proses',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penerbitan`
--

INSERT INTO `penerbitan` (`no_penerbitan`, `tanggal`, `id_penulis`, `no_isbn`, `judul_buku`, `tahun`, `id_reviewer`, `id_editor`, `jumlah_terbit`, `keterangan`, `harga`, `kategori`, `status`, `created_at`, `updated_at`) VALUES
(1, '2024-10-01', 1, '978-3-16-148410-0', 'Pemrograman Web Modern', '2024', 1, 1, 1000, 'Buku pemrograman untuk pemula', 150000.00, 'Teknologi', 'Diterima', '2026-03-06 10:25:08', '2026-03-06 10:25:08'),
(2, '2024-10-05', 2, '978-3-16-148410-1', 'Sastra Indonesia Kontemporer', '2024', 1, 1, 500, 'Kumpulan esai sastra', 120000.00, 'Sastra', 'Proses', '2026-03-06 10:25:08', '2026-03-06 10:25:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `penerbitan`
--
ALTER TABLE `penerbitan`
  ADD PRIMARY KEY (`no_penerbitan`),
  ADD UNIQUE KEY `no_isbn` (`no_isbn`),
  ADD KEY `id_penulis` (`id_penulis`),
  ADD KEY `id_reviewer` (`id_reviewer`),
  ADD KEY `id_editor` (`id_editor`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `penerbitan`
--
ALTER TABLE `penerbitan`
  MODIFY `no_penerbitan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `penerbitan`
--
ALTER TABLE `penerbitan`
  ADD CONSTRAINT `penerbitan_ibfk_1` FOREIGN KEY (`id_penulis`) REFERENCES `penulis` (`id_penulis`) ON DELETE SET NULL,
  ADD CONSTRAINT `penerbitan_ibfk_2` FOREIGN KEY (`id_reviewer`) REFERENCES `reviewer` (`id_reviewer`) ON DELETE SET NULL,
  ADD CONSTRAINT `penerbitan_ibfk_3` FOREIGN KEY (`id_editor`) REFERENCES `editor` (`id_editor`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
