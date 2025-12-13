-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 13, 2025 at 11:15 AM
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
-- Database: `k73_nhom12`
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
(13, 4, 'Đăng nhập thành công', '2025-12-11 04:38:58'),
(14, 4, 'Đăng nhập thành công', '2025-12-12 07:10:56'),
(15, 4, 'Đã KHÓA user ID 1', '2025-12-12 07:11:23'),
(16, 4, 'Đăng nhập thành công', '2025-12-12 07:12:30'),
(17, 4, 'Đã MỞ KHÓA user ID 1', '2025-12-12 07:12:34'),
(18, 4, 'Đăng nhập thành công', '2025-12-12 07:14:44'),
(20, 6, 'Đăng nhập thành công', '2025-12-12 07:17:47'),
(21, 6, 'Đã duyệt user 3 vào dự án 5 với quyền contributor', '2025-12-12 07:18:39'),
(22, 4, 'Đăng nhập thành công', '2025-12-12 07:53:59'),
(24, 4, 'Đăng nhập thành công', '2025-12-12 08:02:15'),
(25, 7, 'Đăng nhập thành công', '2025-12-12 08:04:13'),
(26, 6, 'Đăng nhập thành công', '2025-12-12 08:16:19'),
(27, 6, 'Đã duyệt user 7 vào dự án 5 quyền contributor', '2025-12-12 08:16:49'),
(28, 7, 'Đăng nhập thành công', '2025-12-12 08:17:02'),
(29, 6, 'Đăng nhập thành công', '2025-12-12 08:17:17'),
(30, 7, 'Đăng nhập thành công', '2025-12-12 08:24:38'),
(31, 8, 'Đăng nhập thành công', '2025-12-12 08:26:47'),
(32, 6, 'Đăng nhập thành công', '2025-12-12 08:26:54'),
(33, 6, 'Đã duyệt user 8 vào dự án 5 quyền contributor', '2025-12-12 08:26:58'),
(34, 8, 'Đăng nhập thành công', '2025-12-12 08:27:05'),
(35, 6, 'Đăng nhập thành công', '2025-12-12 08:27:22'),
(36, 6, 'Đã xóa comment trong dự án 5', '2025-12-12 08:43:09'),
(37, 6, 'Đăng nhập thành công', '2025-12-12 08:43:59'),
(38, 4, 'Đăng nhập thành công', '2025-12-12 08:44:12'),
(39, 1, 'Đăng nhập thành công', '2025-12-12 08:44:35'),
(40, 1, 'Đã duyệt user 6 vào dự án 3 quyền moderator', '2025-12-12 08:46:46'),
(41, 1, 'Đã xóa comment trong dự án 3', '2025-12-12 09:19:13'),
(42, 4, 'Đăng nhập thành công', '2025-12-12 09:28:36'),
(43, 6, 'Đăng nhập thành công', '2025-12-12 09:29:07'),
(44, 6, 'Đăng nhập thành công', '2025-12-12 09:44:24'),
(45, 9, 'Đăng nhập thành công', '2025-12-12 09:55:46'),
(46, 6, 'Đăng nhập thành công', '2025-12-12 09:56:06'),
(47, 6, 'Đã duyệt user 9 vào dự án 5 quyền viewer', '2025-12-12 09:56:23'),
(48, 9, 'Đăng nhập thành công', '2025-12-12 09:56:46'),
(49, 9, 'Đã tạo dự án mới: Dự án ma', '2025-12-12 10:05:21'),
(50, 9, 'Đã xóa dự án ID 6', '2025-12-12 10:23:40'),
(51, 9, 'Đã tạo dự án mới: a', '2025-12-12 10:24:46'),
(52, 9, 'Đã xóa dự án ID 7', '2025-12-12 10:24:58'),
(53, 9, 'Đã tạo dự án mới: a', '2025-12-12 10:25:19'),
(54, 4, 'Đăng nhập thành công', '2025-12-12 10:35:58'),
(55, 9, 'Đăng nhập thành công', '2025-12-12 10:37:01'),
(56, 9, 'Đã thêm trực tiếp user 1@gmail.com vào dự án 8', '2025-12-12 10:40:36'),
(57, 7, 'Đăng nhập thành công', '2025-12-12 10:51:38'),
(58, 6, 'Đăng nhập thành công', '2025-12-12 10:52:11'),
(59, 6, 'Đã xóa comment trong dự án 5', '2025-12-12 10:52:50'),
(60, 7, 'Đăng nhập thành công', '2025-12-12 10:55:17'),
(61, 6, 'Đăng nhập thành công', '2025-12-12 11:14:58'),
(62, 6, 'Đã duyệt comment ID 19', '2025-12-12 11:15:06'),
(63, 9, 'Đăng nhập thành công', '2025-12-12 11:15:34'),
(64, 6, 'Đăng nhập thành công', '2025-12-13 01:49:09'),
(65, 4, 'Đăng nhập thành công', '2025-12-13 01:53:14'),
(66, 4, 'Đăng nhập thành công', '2025-12-13 01:53:57'),
(67, 4, 'Đăng nhập thành công', '2025-12-13 01:54:19'),
(68, 6, 'Đăng nhập thành công', '2025-12-13 01:55:04'),
(69, 4, 'Đăng nhập thành công', '2025-12-13 02:01:31'),
(70, 6, 'Đăng nhập thành công', '2025-12-13 02:05:20'),
(71, 6, 'Đã xóa comment trong dự án 8', '2025-12-13 02:22:12'),
(72, 9, 'Đăng nhập thành công', '2025-12-13 02:41:15'),
(73, 6, 'Đăng nhập thành công', '2025-12-13 02:50:27'),
(74, 4, 'Đăng nhập thành công', '2025-12-13 02:56:54'),
(75, 6, 'Đăng nhập thành công', '2025-12-13 02:57:31'),
(76, 6, 'Đăng nhập thành công', '2025-12-13 11:10:59');

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
(2, 4, 5, 'hahaha', '2025-12-09 14:03:08', 0),
(3, 4, 5, 'đsds', '2025-12-09 14:03:11', 0),
(4, 3, 1, 'did', '2025-12-09 14:36:04', 0),
(8, 5, 7, 'ola', '2025-12-12 08:24:43', 1),
(9, 5, 8, 'sdf', '2025-12-12 08:27:10', 1),
(10, 3, 1, 'ola', '2025-12-12 08:44:45', 1),
(11, 3, 1, 'a', '2025-12-12 09:19:18', 1),
(12, 3, 1, 'v', '2025-12-12 09:19:21', 1),
(13, 3, 6, 'sdfsd', '2025-12-12 09:56:40', 1),
(14, 5, 7, 'a', '2025-12-12 10:51:46', 1),
(15, 3, 6, 'sd', '2025-12-12 10:53:51', 1),
(16, 5, 7, 'hi', '2025-12-12 10:55:25', 1),
(17, 5, 7, 'alo alo', '2025-12-12 10:57:55', 1),
(18, 5, 7, 'a', '2025-12-12 11:05:40', 1),
(19, 5, 7, 'a', '2025-12-12 11:14:17', 1),
(20, 5, 7, 'b', '2025-12-12 11:14:48', 1),
(21, 3, 6, 'helo', '2025-12-13 02:06:31', 1);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `created_at`) VALUES
(3, 'dự án mới để cmt', 'hihi', '2025-12-09 13:38:31'),
(4, 'dự án ', 'hihihaha', '2025-12-09 13:53:56'),
(5, '1@', '1@', '2025-12-11 03:22:27'),
(8, 'a', 'a', '2025-12-12 10:25:19');

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
(5, 4, 'owner'),
(6, 3, 'moderator'),
(6, 5, 'owner'),
(6, 8, 'moderator'),
(7, 5, 'contributor'),
(8, 5, 'moderator'),
(9, 5, 'contributor'),
(9, 8, 'owner');

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
(1, 6, 4, 'pending', '2025-12-11 04:23:58'),
(3, 7, 5, 'approved', '2025-12-12 08:16:14'),
(4, 8, 5, 'approved', '2025-12-12 08:26:50'),
(5, 6, 3, 'approved', '2025-12-12 08:43:46'),
(6, 9, 5, 'approved', '2025-12-12 09:55:57'),
(7, 7, 4, 'pending', '2025-12-12 10:55:19');

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
(4, 'admin@gmail.com', '$2y$10$hPG4HLQLeaCkMe7a21ESwO9wfUiuCn3Om7dK2A5AvGasp/wIg4Dc.', '2025-12-09 09:38:02', 'admin', 1),
(5, 'test@gmail.com', '$2y$10$sRVnRLlIZtgdv2b7XNBSse2cjXJxohR2YtoGrp1RmT/8HvEwD/vJa', '2025-12-09 13:53:19', 'user', 1),
(6, '1@gmail.com', '$2y$10$7gQHS87/yZzUikWOhZvOr.Zj3tgwauo8Pgjg2awdiade4spnusk.m', '2025-12-11 03:15:14', 'user', 1),
(7, 'loc@gmail.com', '$2y$10$2dfG8r.GzY08RAt0N0BxXOonF/I5u8X26jOxN1w/x0mHRFkqvgHGm', '2025-12-12 08:03:59', 'user', 1),
(8, '2@gmail.com', '$2y$10$umn9nhyJWynXpWzp79x9Fu9Uxv1HzBvIJemt/JgYXyEbGCBvVGuSW', '2025-12-12 08:26:43', 'user', 1),
(9, 'c@gmail.com', '$2y$10$.qWiGFVbpWyYpkr1hW7PUu5kfNPqW78C8qDfR.RP4mSo4p9yfglhG', '2025-12-12 09:55:41', 'user', 1);

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
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `project_requests`
--
ALTER TABLE `project_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
