-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Време на генериране: 28 юли 2025 в 08:21
-- Версия на сървъра: 10.4.32-MariaDB
-- Версия на PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данни: `fixora`
--

-- --------------------------------------------------------

--
-- Структура на таблица `connections`
--

CREATE TABLE `connections` (
  `id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `connections`
--

INSERT INTO `connections` (`id`, `user1_id`, `user2_id`, `created_at`) VALUES
(1, 13, 15, '2025-07-27 23:35:17'),
(2, 13, 15, '2025-07-27 23:35:51'),
(3, 13, 15, '2025-07-28 08:55:00'),
(4, 15, 13, '2025-07-28 09:12:41');

-- --------------------------------------------------------

--
-- Структура на таблица `connection_requests`
--

CREATE TABLE `connection_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `connection_requests`
--

INSERT INTO `connection_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(20, 15, 13, 'accepted', '2025-07-28 08:54:42'),
(21, 15, 13, 'accepted', '2025-07-27 23:35:37'),
(22, 15, 13, 'accepted', '2025-07-27 23:32:54'),
(23, 15, 13, 'declined', '2025-07-27 23:29:15'),
(24, 13, 15, 'accepted', '2025-07-28 09:12:23');

-- --------------------------------------------------------

--
-- Структура на таблица `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_type` enum('offer','seek') NOT NULL,
  `profession` varchar(50) NOT NULL,
  `location` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `price_per_square` decimal(10,2) DEFAULT NULL,
  `price_per_day` decimal(10,2) DEFAULT NULL,
  `work_status` enum('solo','team') DEFAULT NULL,
  `team_size` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `show_price_per_square` tinyint(1) DEFAULT 0,
  `show_price_per_day` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `team_members` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `jobs`
--

INSERT INTO `jobs` (`id`, `user_id`, `job_type`, `profession`, `location`, `city`, `price_per_square`, `price_per_day`, `work_status`, `team_size`, `age`, `show_price_per_square`, `show_price_per_day`, `created_at`, `team_members`, `images`, `description`) VALUES
(8, 5, 'offer', 'boqjdiq', 'Бургас', NULL, 15.00, NULL, NULL, NULL, NULL, 0, 0, '2025-07-14 13:12:19', NULL, NULL, NULL),
(9, 5, 'seek', 'zidar', NULL, 'София', NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-07-14 13:12:48', NULL, NULL, NULL),
(10, 5, 'seek', 'elektrikar', NULL, 'Бургас', NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-07-14 13:16:44', NULL, NULL, NULL),
(11, 5, 'offer', 'boqjdiq', 'Бургас', NULL, 15.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-14 13:29:11', NULL, NULL, 'няма'),
(12, 5, 'offer', 'boqjdiq', 'Бургас', NULL, 15.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-14 13:29:43', NULL, NULL, 'няма'),
(13, 5, 'seek', 'zidar', NULL, 'София', 20.00, 200.00, 'team', 2, NULL, 0, 0, '2025-07-14 13:30:48', '[\"\\u0418\\u0432\\u0430\\u043d \\u041f\\u0435\\u043d\\u0435\\u0432\",\"\\u0414\\u0438\\u043c\\u0438\\u0442\\u044a\\u0440 \\u0425\\u0440\\u0438\\u0441\\u0442\\u043e\\u0432\"]', NULL, 'Има'),
(14, 6, 'offer', 'zidar', 'Бургас', NULL, 15.00, 155.00, NULL, NULL, NULL, 0, 0, '2025-07-14 13:40:03', NULL, NULL, 'iuj'),
(15, 7, 'offer', 'boqjdiq', 'Бургас', NULL, 20.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-15 20:25:43', NULL, NULL, 'Nqmam'),
(16, 7, 'offer', 'zidar', 'Бургас', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-07-15 20:50:49', NULL, NULL, ''),
(17, 7, 'offer', 'boqjdiq', 'Бургас', '', 20.00, 17.00, NULL, NULL, NULL, 0, 0, '2025-07-15 21:06:09', NULL, '', 'Боядисвам\r\n'),
(18, 7, 'offer', 'elektrikar', 'Бургас', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-07-15 21:07:29', NULL, '', ''),
(19, 7, 'seek', 'kofraj', NULL, 'Бургас', NULL, NULL, 'solo', 1, NULL, 0, 0, '2025-07-19 21:11:25', '[]', '[]', ''),
(20, 13, 'offer', 'zidar', 'Бургас', 'Бургас', 10.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-24 04:51:57', NULL, '[\"uploads\\/jobs\\/job_6881bbed8dad0.jpg\"]', 'Стриктен'),
(21, 13, 'offer', 'elektrikar', 'Бургас', NULL, 33.00, 34.00, NULL, NULL, NULL, 0, 0, '2025-07-24 13:54:06', NULL, '[\"uploads\\/jobs\\/job_68823afe2e91b.png\"]', '333333'),
(22, 13, 'offer', 'kofraj', 'Ямбол', NULL, 10.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-24 21:33:35', NULL, '[\"uploads\\/jobs\\/job_6882a6af4dd27.jpg\"]', ''),
(23, 13, 'seek', 'elektrikar', NULL, 'Сливен', NULL, 150.00, 'team', 3, NULL, 0, 0, '2025-07-25 20:52:35', '[\"\\u0418\\u0432\\u0430\\u043d \\u041f\\u0435\\u043d\\u0435\\u0432\",\"\\u0414\\u0438\\u043c\\u0438\\u0442\\u044a\\u0440 \\u0425\\u0440\\u0438\\u0441\\u0442\\u043e\\u0432\",\"\\u041a\\u0438\\u0440\\u0438\\u043b \\u041e\\u0432\\u0447\\u0430\\u0440\\u043e\\u0432\"]', '[]', 'Взимаме по 15 лв на час на човек'),
(24, 15, 'offer', 'zidar', 'Бургас', NULL, 20.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-28 06:12:00', NULL, '[\"uploads\\/jobs\\/job_688714b034eee.jpg\"]', 'гхйфйф');

-- --------------------------------------------------------

--
-- Структура на таблица `job_images`
--

CREATE TABLE `job_images` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура на таблица `maistori`
--

CREATE TABLE `maistori` (
  `id` int(11) NOT NULL,
  `ime` varchar(50) NOT NULL,
  `familiq` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `parola` varchar(255) NOT NULL,
  `grad` varchar(50) NOT NULL,
  `telefon` varchar(15) NOT NULL,
  `firma` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура на таблица `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(1, 13, 15, 'Ehoooo', '2025-07-27 23:38:15'),
(2, 13, 15, 'Вашата заявка е удобрена', '2025-07-28 08:55:31'),
(3, 15, 13, 'хууу', '2025-07-28 09:12:52');

-- --------------------------------------------------------

--
-- Структура на таблица `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `member_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура на таблица `test_users`
--

CREATE TABLE `test_users` (
  `id` int(11) NOT NULL,
  `ime` varchar(100) NOT NULL,
  `parola` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `test_users`
--

INSERT INTO `test_users` (`id`, `ime`, `parola`) VALUES
(1, 'Mitko', '12345678'),
(2, 'Иван', '87654321');

-- --------------------------------------------------------

--
-- Структура на таблица `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `ime` varchar(50) NOT NULL,
  `familiq` varchar(50) NOT NULL,
  `telefon` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `city_visible` tinyint(1) DEFAULT 0,
  `age_visible` tinyint(1) DEFAULT 0,
  `show_phone` tinyint(1) DEFAULT 1,
  `show_email` tinyint(1) DEFAULT 1,
  `show_city` tinyint(1) DEFAULT 1,
  `show_age` tinyint(1) DEFAULT 1,
  `profile_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `users`
--

INSERT INTO `users` (`id`, `username`, `ime`, `familiq`, `telefon`, `email`, `created_at`, `password`, `city`, `age`, `city_visible`, `age_visible`, `show_phone`, `show_email`, `show_city`, `show_age`, `profile_image`) VALUES
(1, 'testuser', 'Тест', 'Потребител', '0888123456', 'test@fixora.bg', '2025-07-10 11:17:43', '', 'София', 25, 0, 0, 1, 1, 0, 1, 'user.png'),
(2, 'криси', 'криси', 'миланова', '0887879742', 'kmilanova04@gamil.com', '2025-07-10 12:07:36', '', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(3, 'Miti0', 'Адам', 'Кънев', '54154', 'miti0.kaloyanov@gmail.com', '2025-07-10 13:18:14', '$2y$10$byc0qw.Zz639LmtfTQRDTOzPYdLbRkb98Ad2fuEGl6ZL2fU.G72S6', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(4, 'Ivan', 'Иван', 'Костов', '1235489895', 'ivan@gmail.com', '2025-07-10 14:05:29', '$2y$10$V4.Dw0ul7qqvy0N15H3bq.yG20QlW9clHrByHVkZ0VT5gIj/N87gi', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(5, 'Георги', 'Георги', 'Корадов', '12345676', 'koradov@gmail.com', '2025-07-10 20:48:41', '$2y$10$ikZ4lfmx2DbJq14woWUisemjgwepqmkjv5iKDJ4hKYkQKXVUq.v4i', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(6, 'Иван', 'Патрашков', 'Патрашков', '92930320', 'ivan@abv', '2025-07-14 13:38:38', '$2y$10$kfb/FBsBU51Pm3c5m6U8heYALhpxrXhTEzLFn8ayXPOUjHX/i3Y1S', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(7, 'DEKaloyanov', 'Димитър', 'Калоянов', '0885662686', 'mtiko.kaloqnov523@gmail.com', '2025-07-15 20:25:02', '$2y$10$VPNL9Iw9Fg8KferxD4a9r.pgjFYiaMwSHI4vlFgtUo4xXjSh8A16e', '', 0, 0, 0, 0, 0, 0, 0, NULL),
(8, 'KristinaKaloyanova', 'Криси', 'Калоянова', '0887879742', 'krisimkrisi04@gmail.com', '2025-07-22 20:43:29', '$2y$10$.y6WMOCb6enF7xNGF3MtDO5DH/cK0JjxwVDcwCQPgQDsPbB.DHc7e', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(9, 'Miti0_0', 'Адам', 'gt', '54154', 'Tralala@gmail.com', '2025-07-22 21:10:58', '$2y$10$Q63DK6kRvhuEqQNdXX05Le7ak5AEFd59SYQG8rV2ZcnEcuyLQ7NIe', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(12, 'Miti0_0e', 'mitko', 'gt', '54859', 'miti0.kaloyaeenov@gmail.com', '2025-07-22 21:21:30', '$2y$10$VFl9iFbdl3YGnVW7vL0hV.ZCvzxeWCxoL2A4VbkBRrPWtOfgAtcv.', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(13, 'DKaloyanov', 'Димитър', 'Калоянов', '0885662686', 'mitko.kaloqnov523@gmail.com', '2025-07-23 15:40:38', '$2y$10$4uYx8M3ZX9SKo4GZe5IvLe1sIZieXEp0PUa5w4bFCCEh6oQ9fAWme', 'Бургас', 35, 0, 0, 1, 1, 1, 1, 'MVP_8777.jpg'),
(14, 'ккк', 'ккк', 'ккк', 'ккк', 'kkkk@gmail.com', '2025-07-23 19:07:15', '$2y$10$pdl893X5pYAzpSeBQ4.yUuDXsbT6XJ48GbnwNFp5RDOaVoJaaAH9e', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL),
(15, 'GKanev', 'Георги', 'Кънев', '56466464', 'kanefgeorgi@gmail.com', '2025-07-27 14:19:44', '$2y$10$p/yrJsjFOAwtz7289NFvguVo44Inb6gqnMX5whz2IwooXun76jz0e', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL);

-- --------------------------------------------------------

--
-- Структура на таблица `uslugi`
--

CREATE TABLE `uslugi` (
  `id` int(11) NOT NULL,
  `maistor_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `area` varchar(100) NOT NULL,
  `team_size` int(11) NOT NULL,
  `price_m2` decimal(10,2) DEFAULT NULL,
  `price_day` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Индекси за таблица `connections`
--
ALTER TABLE `connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user1_id` (`user1_id`),
  ADD KEY `user2_id` (`user2_id`);

--
-- Индекси за таблица `connection_requests`
--
ALTER TABLE `connection_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Индекси за таблица `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индекси за таблица `job_images`
--
ALTER TABLE `job_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Индекси за таблица `maistori`
--
ALTER TABLE `maistori`
  ADD PRIMARY KEY (`id`);

--
-- Индекси за таблица `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Индекси за таблица `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `job_id` (`job_id`);

--
-- Индекси за таблица `test_users`
--
ALTER TABLE `test_users`
  ADD PRIMARY KEY (`id`);

--
-- Индекси за таблица `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индекси за таблица `uslugi`
--
ALTER TABLE `uslugi`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `connections`
--
ALTER TABLE `connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `connection_requests`
--
ALTER TABLE `connection_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `job_images`
--
ALTER TABLE `job_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maistori`
--
ALTER TABLE `maistori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `test_users`
--
ALTER TABLE `test_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `uslugi`
--
ALTER TABLE `uslugi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения за дъмпнати таблици
--

--
-- Ограничения за таблица `connections`
--
ALTER TABLE `connections`
  ADD CONSTRAINT `connections_ibfk_1` FOREIGN KEY (`user1_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения за таблица `connection_requests`
--
ALTER TABLE `connection_requests`
  ADD CONSTRAINT `connection_requests_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `connection_requests_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения за таблица `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `jobs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения за таблица `job_images`
--
ALTER TABLE `job_images`
  ADD CONSTRAINT `job_images_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`);

--
-- Ограничения за таблица `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения за таблица `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
