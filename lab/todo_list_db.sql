-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 23 Okt 2024 pada 17.44
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `todo_list_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `completed` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tasks`
--

INSERT INTO `tasks` (`id`, `list_id`, `title`, `completed`, `created_at`) VALUES
(7, 2, 'gg', 0, '2024-10-21 09:11:22'),
(8, 2, 'hhgg', 0, '2024-10-21 09:11:23'),
(9, 2, 'hghgg', 0, '2024-10-21 09:11:25');

-- --------------------------------------------------------

--
-- Struktur dari tabel `todo_lists`
--

CREATE TABLE `todo_lists` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `todo_lists`
--

INSERT INTO `todo_lists` (`id`, `user_id`, `title`, `created_at`) VALUES
(2, 1, 'hgfhfhfhfhg', '2024-10-21 09:11:08');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_activation_hash` varchar(64) DEFAULT NULL,
  `password_reset_hash` varchar(64) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT 'img/user.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `account_activation_hash`, `password_reset_hash`, `password_reset_expires`, `avatar_url`) VALUES
(1, 'rafpo', '1@1.com', '$2y$10$CXYCcRa9wfB0IP6y8bkoMe3f01lZkUlBY3AiQvb.j.JPz/bgaTzKW', '2024-10-21 07:01:15', NULL, NULL, NULL, 'img/user.jpg'),
(20, 'qwe', 'zoom.elraffs2@gmail.com', '$2y$10$A4.AGpAIMZodLhjbUVIDB.vbvNkh4nUT4nJS5Y4.T0LQIZSrvynQS', '2024-10-21 16:09:39', NULL, NULL, NULL, 'img/user.jpg'),
(25, 'ray1', 'raymussenaw@gmail.com', '$2y$10$jjRKJ8lsme6nLUnN3WBCoupsUJcxLy.QcV2wMjd2bRZbZnzh5esdC', '2024-10-22 00:31:15', NULL, NULL, NULL, 'img/user.jpg'),
(32, 'eka', 'ekandra2204@gmail.com', '$2y$10$PznEdJpGbl7kC4DYbkg8BOmWIUwXHR.MTZnFNOT6nEhsKy7LmLg0m', '2024-10-23 14:46:23', NULL, NULL, NULL, 'uploads/avatars/1729698147_we are open.png');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`list_id`);

--
-- Indeks untuk tabel `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `account_activation_hash` (`account_activation_hash`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `todo_lists`
--
ALTER TABLE `todo_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`list_id`) REFERENCES `todo_lists` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `todo_lists`
--
ALTER TABLE `todo_lists`
  ADD CONSTRAINT `todo_lists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
