-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 18 Okt 2025 pada 07.50
-- Versi server: 8.0.43-0ubuntu0.24.04.1
-- Versi PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `bank_sampah`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `Penukaran`
--

CREATE TABLE `Penukaran` (
  `id_penukaran` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal` date NOT NULL,
  `item` varchar(100) NOT NULL,
  `poin_ditukar` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `Setor`
--

CREATE TABLE `Setor` (
  `id_setor` int NOT NULL,
  `id_user` int NOT NULL,
  `tanggal` date NOT NULL,
  `jenis_sampah` varchar(100) NOT NULL,
  `berat` decimal(10,2) NOT NULL,
  `poin` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `User`
--

CREATE TABLE `User` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data untuk tabel `User`
--

INSERT INTO `User` (`id_user`, `username`, `password`, `nama`, `role`) VALUES
(1, 'admin', '$2y$10$2bO.gqr0lcsCSxxc01D13.ZrdOB7dDmN6Qog.l6HyOOeH3CXkFw0G', 'admin', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `Penukaran`
--
ALTER TABLE `Penukaran`
  ADD PRIMARY KEY (`id_penukaran`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `Setor`
--
ALTER TABLE `Setor`
  ADD PRIMARY KEY (`id_setor`),
  ADD KEY `id_user` (`id_user`);

--
-- Indeks untuk tabel `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `Penukaran`
--
ALTER TABLE `Penukaran`
  MODIFY `id_penukaran` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `Setor`
--
ALTER TABLE `Setor`
  MODIFY `id_setor` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `User`
--
ALTER TABLE `User`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `Penukaran`
--
ALTER TABLE `Penukaran`
  ADD CONSTRAINT `Penukaran_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `User` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `Setor`
--
ALTER TABLE `Setor`
  ADD CONSTRAINT `Setor_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `User` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
