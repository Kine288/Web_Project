-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 11, 2025 at 12:46 PM
-- Server version: 8.4.2
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `spck`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `created_at`) VALUES
(1, 6, 'Đăng nhập thành công', '2025-12-11 03:18:50'),
(2, 6, 'Đã tạo dự án mới: 1@', '2025-12-11 03:22:27'),
(3, 6, 'Bình luận vào dự án 5', '2025-12-11 03:22:34'),
(4, 4, 'Đăng nhập thành công', '2025-12-11 04:05:09'),
(5, 4, 'Cập nhật quyền user 1 trong dự án 3 thành owner', '2025-12-11 04:05:29'),
(6, 4, 'Đã KHÓA user ID 3', '2025-12-11 04:07:46'),
(7, 4, 'Đã MỞ KHÓA user ID 3', '2025-12-11 04:07:48'),
(8, 4, 'Đã MỞ KHÓA user ID 3', '2025-12-11 04:10:11'),
(9, 4, 'Đã MỞ KHÓA user ID 3', '2025-12-11 04:11:03'),
(10, 4, 'Đã MỞ KHÓA user ID 3', '2025-12-11 04:12:52'),
(11, 4, 'Đã MỞ KHÓA user ID 3', '2025-12-11 04:23:18'),
(12, 6, 'Đăng nhập thành công', '2025-12-11 04:23:29'),
(13, 4, 'Đăng nhập thành công', '2025-12-11 04:38:58');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `project_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_approved` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `project_id`, `user_id`, `content`, `created_at`, `is_approved`) VALUES
(1, 3, 1, 'hihi', '2025-12-09 13:45:31', 1),
(2, 4, 5, 'hahaha', '2025-12-09 14:03:08', 0),
(3, 4, 5, 'đsds', '2025-12-09 14:03:11', 0),
(4, 3, 1, 'did', '2025-12-09 14:36:04', 0),
(5, 2, 3, 'ttttt', '2025-12-10 08:00:12', 0),
(6, 5, 6, 'helo', '2025-12-11 03:22:34', 1),
(7, 5, 6, 'helo', '2025-12-11 04:24:09', 1);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_approved` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `created_at`, `is_approved`) VALUES
(1, 'ttt', 'ttt', '2025-12-09 09:36:26', NULL),
(2, 'tttt', 'tt', '2025-12-09 09:44:47', 1),
(3, 'dự án mới để cmt', 'hihi', '2025-12-09 13:38:31', 0),
(4, 'dự án ', 'hihihaha', '2025-12-09 13:53:56', 0),
(5, '1@', '1@', '2025-12-11 03:22:27', 1);

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `role` enum('viewer','contributor','moderator','owner') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'viewer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`user_id`, `project_id`, `role`) VALUES
(1, 3, 'owner'),
(3, 2, 'moderator'),
(5, 4, 'owner'),
(6, 5, 'owner');

-- --------------------------------------------------------

--
-- Table structure for table `project_requests`
--

CREATE TABLE `project_requests` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `project_id` int NOT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_requests`
--

INSERT INTO `project_requests` (`id`, `user_id`, `project_id`, `status`, `created_at`) VALUES
(1, 6, 4, 'pending', '2025-12-11 04:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `registered_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('user','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user',
  `status` tinyint DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `registered_at`, `role`, `status`) VALUES
(1, 'supertuann02@gmail.com', '$2y$10$oI.4NqknoEJ7GCtxtea06elLqB9xxGl6dnLIKmhpj/aixDhXjWHM.', '2025-12-09 09:20:02', 'user', 1),
(3, 'ttt@gmail.com', '$2y$10$IuZfRALphUWLBHYnWYc4PuOfshuRwzlS/u2K8MnJka1hcwj7lz3oa', '2025-12-09 09:31:50', 'user', 1),
(4, 'admin@gmail.com', '$2y$10$hPG4HLQLeaCkMe7a21ESwO9wfUiuCn3Om7dK2A5AvGasp/wIg4Dc.', '2025-12-09 09:38:02', 'admin', 1),
(5, 'test@gmail.com', '$2y$10$sRVnRLlIZtgdv2b7XNBSse2cjXJxohR2YtoGrp1RmT/8HvEwD/vJa', '2025-12-09 13:53:19', 'user', 1),
(6, '1@gmail.com', '$2y$10$7gQHS87/yZzUikWOhZvOr.Zj3tgwauo8Pgjg2awdiade4spnusk.m', '2025-12-11 03:15:14', 'user', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`user_id`,`project_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `project_requests`
--
ALTER TABLE `project_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `project_id` (`project_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `project_requests`
--
ALTER TABLE `project_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `project_requests`
--
ALTER TABLE `project_requests`
  ADD CONSTRAINT `project_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `project_requests_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
