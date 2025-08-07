-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Време на генериране:  7 авг 2025 в 16:41
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
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `job_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `connections`
--

INSERT INTO `connections` (`id`, `user1_id`, `user2_id`, `created_at`, `job_id`) VALUES
(1, 13, 15, '2025-07-27 23:35:17', NULL),
(2, 13, 15, '2025-07-27 23:35:51', NULL),
(3, 13, 15, '2025-07-28 08:55:00', NULL),
(4, 15, 13, '2025-07-28 09:12:41', NULL),
(5, 15, 13, '2025-07-30 00:28:28', NULL),
(6, 13, 15, '2025-08-01 10:32:29', NULL),
(7, 15, 13, '2025-08-01 23:35:29', NULL),
(8, 15, 13, '2025-08-01 23:35:32', NULL),
(11, 15, 13, '2025-08-02 23:55:26', 32),
(12, 13, 15, '2025-08-03 00:09:19', 33),
(13, 15, 13, '2025-08-03 00:27:14', 34),
(14, 13, 15, '2025-08-04 12:49:51', 36),
(15, 13, 15, '2025-08-04 13:26:22', 37),
(16, 13, 15, '2025-08-04 13:30:03', 38),
(17, 13, 15, '2025-08-04 13:34:17', 39),
(18, 13, 15, '2025-08-04 13:51:10', 40),
(19, 15, 13, '2025-08-04 17:52:41', 41),
(20, 15, 13, '2025-08-05 17:33:54', NULL),
(21, 15, 13, '2025-08-05 17:34:28', NULL),
(22, 15, 13, '2025-08-05 17:34:42', NULL),
(23, 15, 13, '2025-08-05 17:46:32', 43),
(24, 13, 15, '2025-08-05 17:46:58', 44),
(25, 13, 15, '2025-08-05 17:53:44', 45),
(26, 13, 15, '2025-08-05 17:59:47', 46),
(27, 13, 15, '2025-08-05 18:05:19', 47),
(30, 8, 13, '2025-08-07 08:49:02', 49),
(31, 13, 8, '2025-08-07 08:52:27', 47),
(32, 15, 8, '2025-08-07 09:02:49', 48),
(33, 8, 15, '2025-08-07 09:53:16', 49);

-- --------------------------------------------------------

--
-- Структура на таблица `connection_requests`
--

CREATE TABLE `connection_requests` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `job_id` int(11) DEFAULT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `connection_requests`
--

INSERT INTO `connection_requests` (`id`, `sender_id`, `receiver_id`, `job_id`, `status`, `created_at`) VALUES
(20, 15, 13, NULL, 'accepted', '2025-07-28 08:54:42'),
(21, 15, 13, NULL, 'accepted', '2025-07-27 23:35:37'),
(22, 15, 13, NULL, 'accepted', '2025-07-27 23:32:54'),
(23, 15, 13, NULL, 'declined', '2025-07-27 23:29:15'),
(24, 13, 15, NULL, 'accepted', '2025-07-28 09:12:23'),
(27, 13, 15, NULL, 'accepted', '2025-07-30 00:27:10'),
(28, 15, 13, 23, 'accepted', '2025-08-01 10:31:50'),
(29, 13, 15, 30, 'accepted', '2025-08-01 11:27:59'),
(30, 13, 15, NULL, 'accepted', '2025-08-01 23:35:10'),
(31, 13, 15, 28, 'accepted', '2025-08-02 23:41:55'),
(32, 15, 13, 31, 'accepted', '2025-08-02 23:44:03'),
(33, 13, 15, 32, 'accepted', '2025-08-02 23:52:09'),
(34, 15, 13, 33, 'accepted', '2025-08-03 00:09:01'),
(35, 13, 15, 34, 'accepted', '2025-08-03 00:27:04'),
(36, 15, 13, 36, 'accepted', '2025-08-04 12:49:08'),
(37, 15, 13, 37, 'accepted', '2025-08-04 13:26:10'),
(38, 15, 13, 38, 'accepted', '2025-08-04 13:29:53'),
(39, 15, 13, 39, 'accepted', '2025-08-04 13:34:08'),
(40, 15, 13, 40, 'accepted', '2025-08-04 13:51:00'),
(41, 13, 15, 41, 'accepted', '2025-08-04 17:52:06'),
(42, 13, 15, NULL, 'accepted', '2025-08-05 17:33:13'),
(43, 13, 15, 43, 'accepted', '2025-08-05 17:42:38'),
(44, 15, 13, 44, 'accepted', '2025-08-05 17:46:40'),
(45, 15, 13, 45, 'accepted', '2025-08-05 17:53:28'),
(46, 15, 13, 46, 'accepted', '2025-08-05 17:59:34'),
(47, 15, 13, 47, 'accepted', '2025-08-05 18:05:06'),
(48, 13, 8, 49, 'accepted', '2025-08-07 08:30:25'),
(49, 13, 8, 0, 'declined', '2025-08-07 08:34:00'),
(50, 13, 6, 14, 'pending', '2025-08-07 08:36:07'),
(51, 8, 13, 47, 'accepted', '2025-08-07 08:51:47'),
(52, 8, 15, 48, 'accepted', '2025-08-07 09:02:13'),
(53, 15, 8, 49, 'accepted', '2025-08-07 09:51:36');

-- --------------------------------------------------------

--
-- Структура на таблица `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `job_id`, `created_at`) VALUES
(89, 13, 45, '2025-08-06 14:44:02'),
(167, 13, 46, '2025-08-06 19:01:23'),
(194, 13, 48, '2025-08-06 19:59:37'),
(195, 13, 40, '2025-08-06 19:59:43'),
(238, 8, 49, '2025-08-07 11:31:00');

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
(24, 15, 'offer', 'zidar', 'Бургас', NULL, 20.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-07-28 06:12:00', NULL, '[\"uploads\\/jobs\\/job_688714b034eee.jpg\"]', 'гхйфйф'),
(25, 15, 'seek', 'zidar', NULL, 'Ямбол', 30.00, 150.00, 'team', 2, NULL, 0, 0, '2025-07-29 05:23:46', '[\"\\u0425\\u0440\\u0438\\u0441\\u0442\\u043e\",\"\\u042f\\u043d\\u043a\\u043e\"]', '[]', ''),
(26, 15, 'offer', 'boqjdiq', 'Бургас', NULL, 32.00, 234.00, NULL, NULL, NULL, 0, 0, '2025-07-29 05:58:54', NULL, '[\"uploads\\/jobs\\/job_6888631e1b004.JPG\"]', '4444444444444444444444444444444444444444444444444444444444444444444444444444444444444'),
(27, 15, 'seek', 'boqjdiq', NULL, 'Ямбол', 33.00, 321.00, 'solo', 1, NULL, 0, 0, '2025-07-29 13:14:40', '[\"\\u0425\\u0440\\u0438\\u0441\\u0442\\u043e\"]', '[]', 'фсфсф'),
(28, 15, 'seek', 'kofraj', NULL, 'Айтос', 15.00, 100.00, 'team', 3, NULL, 0, 0, '2025-07-31 21:00:23', '[\"\\u0420\\u0435\\u0434\\u0436\\u0435\\u0431\",\"\\u0420\\u0435\\u043c\\u0437\\u0438\",\"\\u0410\\u043b\\u0438\"]', '[]', ''),
(29, 15, 'seek', 'boqjdiq', NULL, 'Созопол', 10.00, 400.00, 'team', 2, NULL, 0, 0, '2025-07-31 21:13:57', '[\"\\u0421\\u0435\\u0440\\u0433\\u0435\\u0439\",\"\\u0418\\u0433\\u043e\\u0440\"]', '[]', ''),
(30, 15, 'offer', 'boqjdiq', 'Бургас', NULL, 23.00, 2532.00, NULL, NULL, NULL, 0, 0, '2025-07-31 21:43:54', NULL, '[\"uploads\\/jobs\\/job_688be39aa0728.jpg\"]', ''),
(31, 13, 'offer', 'zidar', 'Бургас', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-02 20:42:39', NULL, '[]', ''),
(32, 15, 'seek', 'boqjdiq', NULL, 'Добрич', NULL, NULL, 'solo', 1, NULL, 0, 0, '2025-08-02 20:51:48', '[]', '[]', ''),
(33, 13, 'seek', 'boqjdiq', NULL, 'Добрич', NULL, NULL, 'solo', 1, NULL, 0, 0, '2025-08-02 21:08:48', '[]', '[]', ''),
(34, 15, 'offer', 'elektrikar', 'Бургас', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-02 21:26:48', NULL, '[]', ''),
(35, 15, 'seek', 'zidar', NULL, 'Айтос', NULL, NULL, 'solo', 1, NULL, 0, 0, '2025-08-04 09:47:35', '[]', '[]', ''),
(36, 13, 'offer', 'kofraj', 'Ямбол', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-04 09:48:56', NULL, '[]', ''),
(37, 13, 'offer', 'elektrikar', 'Смолян', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-04 10:25:50', NULL, '[]', ''),
(38, 13, 'offer', 'kofraj', 'Смолян', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-04 10:29:43', NULL, '[]', ''),
(39, 13, 'offer', 'zidar', 'Смолян', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-04 10:33:59', NULL, '[]', ''),
(40, 13, 'offer', 'boqjdiq', 'Смолян', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-04 10:50:39', NULL, '[]', ''),
(41, 15, 'offer', 'elektrikar', 'Айтос', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-04 14:51:36', NULL, '[]', ''),
(42, 15, 'offer', 'zidar', 'Созопол', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-05 14:31:57', NULL, '[]', ''),
(43, 15, 'offer', 'boqjdiq', 'Созопол', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-05 14:35:35', NULL, '[]', ''),
(44, 13, 'offer', 'elektrikar', 'Созопол', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-05 14:46:19', NULL, '[]', ''),
(45, 13, 'offer', 'boqjdiq', 'Перник', NULL, 15.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-08-05 14:53:16', NULL, '[\"uploads\\/jobs\\/job_68921adc8bb2b.jpg\"]', ''),
(46, 13, 'offer', 'zidar', 'Перник', NULL, 45.00, 150.00, NULL, NULL, NULL, 0, 0, '2025-08-05 14:59:16', NULL, '[\"uploads\\/jobs\\/job_68921c444beb7.JPG\"]', ''),
(47, 13, 'offer', 'kofraj', 'Перник', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, '2025-08-05 15:04:53', NULL, '[]', ''),
(48, 15, 'seek', 'boqjdiq', NULL, 'Петрич', NULL, NULL, 'solo', 1, NULL, 0, 0, '2025-08-05 21:44:37', '[]', '[]', ''),
(49, 8, 'seek', 'zidar', NULL, 'Петрич', NULL, NULL, 'solo', 1, NULL, 0, 0, '2025-08-05 21:51:14', '[]', '[]', '');

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
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0,
  `job_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `created_at`, `is_read`, `job_id`) VALUES
(1, 13, 15, 'Ehoooo', '2025-07-27 23:38:15', 1, NULL),
(2, 13, 15, 'Вашата заявка е удобрена', '2025-07-28 08:55:31', 1, NULL),
(3, 15, 13, 'хууу', '2025-07-28 09:12:52', 1, NULL),
(4, 15, 13, 'dqwdqd', '2025-07-29 09:28:35', 1, NULL),
(5, 15, 13, 'fdgbhbas', '2025-07-30 00:28:36', 1, NULL),
(6, 13, 15, 'Тралала лалаал', '2025-07-31 21:05:19', 1, NULL),
(7, 15, 13, 'ддажадж', '2025-08-01 01:10:42', 1, NULL),
(8, 15, 13, 'аджажадж', '2025-08-01 01:10:58', 1, NULL),
(9, 15, 13, 'ava', '2025-08-01 01:16:23', 1, NULL),
(10, 15, 13, 'жгбхъггиуххо', '2025-08-01 10:32:00', 1, 23),
(11, 13, 15, 'явфяфя', '2025-08-01 11:36:03', 0, 23),
(12, 13, 15, 'Тест', '2025-08-03 00:09:39', 0, 33),
(13, 13, 15, 'Проба за активна страница чат', '2025-08-03 00:09:54', 0, 33),
(14, 15, 13, 'Съобщението е получено', '2025-08-03 00:15:27', 0, 33),
(15, 15, 13, 'cansjcnac', '2025-08-03 00:34:13', 0, 33),
(16, 15, 13, 'sdsd', '2025-08-03 08:31:53', 0, 34),
(17, 15, 13, 'eho', '2025-08-03 08:39:00', 0, 33),
(18, 13, 15, 'ехооооо', '2025-08-04 12:49:57', 0, 36),
(19, 13, 15, 'хфгткфкгътугъфу', '2025-08-04 13:26:26', 0, 37),
(20, 13, 15, 'хйггйъуйкхг', '2025-08-04 13:30:06', 0, 38),
(21, 8, 13, 'Проба тест', '2025-08-07 08:49:13', 0, 49);

-- --------------------------------------------------------

--
-- Структура на таблица `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `link`, `is_read`, `created_at`) VALUES
(1, 13, 'Получихте ново съобщение.', 'chat.php?with=15', 1, '2025-07-30 00:28:36'),
(2, 15, 'Получихте ново съобщение.', 'chat.php?with=13', 1, '2025-07-31 21:05:19'),
(3, 13, 'Получихте ново съобщение.', 'chat.php?with=15', 1, '2025-08-01 01:10:42'),
(4, 13, 'Получихте ново съобщение.', 'chat.php?with=15', 1, '2025-08-01 01:10:58'),
(5, 13, 'Получихте ново съобщение.', 'chat.php?with=15', 1, '2025-08-01 01:16:23'),
(6, 13, 'Получихте нова заявка за контакт.', 'profil.php', 1, '2025-08-01 10:31:50'),
(7, 13, 'Получихте ново съобщение.', 'chat.php?with=15&job=23', 1, '2025-08-01 10:32:00'),
(8, 15, 'Получихте нова заявка за контакт.', 'profil.php', 0, '2025-08-01 11:27:59'),
(9, 15, 'Получихте ново съобщение.', 'chat.php?with=13&job=23', 0, '2025-08-01 11:36:03'),
(10, 13, 'Вашата заявка беше приета!', 'chat.php?with=15&job=32', 1, '2025-08-02 23:55:26'),
(11, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=33', 0, '2025-08-03 00:09:19'),
(12, 13, 'Вашата заявка беше приета!', 'chat.php?with=15&job=34', 0, '2025-08-03 00:27:14'),
(13, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=36', 0, '2025-08-04 12:49:51'),
(14, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=37', 0, '2025-08-04 13:26:22'),
(15, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=38', 0, '2025-08-04 13:30:03'),
(16, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=39', 0, '2025-08-04 13:34:17'),
(17, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=40', 0, '2025-08-04 13:51:10'),
(18, 13, 'Вашата заявка беше приета!', 'chat.php?with=15&job=41', 0, '2025-08-04 17:52:41'),
(19, 13, 'Вашата заявка беше приета!', 'chat.php?with=15&job=43', 0, '2025-08-05 17:46:32'),
(20, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=44', 0, '2025-08-05 17:46:58'),
(21, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=45', 0, '2025-08-05 17:53:44'),
(22, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=46', 0, '2025-08-05 17:59:47'),
(23, 15, 'Вашата заявка беше приета!', 'chat.php?with=13&job=47', 0, '2025-08-05 18:05:19'),
(24, 13, 'Вашата заявка беше приета!', 'chat.php?with=8&job=49', 0, '2025-08-07 08:49:02'),
(25, 8, 'Вашата заявка беше приета!', 'chat.php?with=13&job=47', 1, '2025-08-07 08:52:27'),
(26, 8, 'Вашата заявка беше приета!', 'chat.php?with=15&job=48', 1, '2025-08-07 09:02:49'),
(27, 15, 'Вашата заявка беше приета!', 'chat.php?with=8&job=49', 0, '2025-08-07 09:53:16');

-- --------------------------------------------------------

--
-- Структура на таблица `project_status`
--

CREATE TABLE `project_status` (
  `id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `user1_id` int(11) NOT NULL,
  `user2_id` int(11) NOT NULL,
  `user1_started` tinyint(1) DEFAULT 0,
  `user2_started` tinyint(1) DEFAULT 0,
  `user1_rated` tinyint(1) DEFAULT 0,
  `user2_rated` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `project_status`
--

INSERT INTO `project_status` (`id`, `job_id`, `user1_id`, `user2_id`, `user1_started`, `user2_started`, `user1_rated`, `user2_rated`, `created_at`) VALUES
(1, 38, 13, 15, 1, 1, 1, 1, '2025-08-04 13:30:03'),
(2, 39, 13, 15, 1, 1, 1, 1, '2025-08-04 13:34:17'),
(3, 40, 13, 15, 1, 1, 1, 1, '2025-08-04 13:51:10'),
(4, 41, 15, 13, 1, 1, 0, 1, '2025-08-04 17:52:41'),
(5, 43, 15, 13, 1, 1, 0, 1, '2025-08-05 17:46:32'),
(6, 44, 13, 15, 1, 1, 1, 1, '2025-08-05 17:46:58'),
(7, 45, 13, 15, 1, 1, 1, 1, '2025-08-05 17:53:44'),
(8, 46, 13, 15, 1, 1, 1, 1, '2025-08-05 17:59:47'),
(9, 47, 13, 15, 1, 1, 0, 1, '2025-08-05 18:05:19'),
(10, 49, 8, 13, 1, 1, 0, 1, '2025-08-07 08:49:02'),
(11, 47, 13, 8, 0, 0, 0, 0, '2025-08-07 08:52:27'),
(12, 48, 15, 8, 0, 0, 0, 0, '2025-08-07 09:02:49'),
(13, 49, 8, 15, 0, 0, 0, 0, '2025-08-07 09:53:16');

-- --------------------------------------------------------

--
-- Структура на таблица `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `ratings`
--

INSERT INTO `ratings` (`id`, `from_user_id`, `to_user_id`, `job_id`, `rating`, `comment`, `created_at`) VALUES
(1, 13, 15, 38, 4, 'Горе долу', '2025-08-04 13:38:34'),
(2, 15, 13, 40, 2, 'оп оп ', '2025-08-04 13:51:50'),
(3, 15, 13, 38, 3, '', '2025-08-04 13:55:54'),
(4, 13, 15, 39, 4, '', '2025-08-04 17:10:30'),
(5, 15, 13, 39, 5, '', '2025-08-04 17:48:58'),
(6, 13, 15, 40, 1, '', '2025-08-04 17:52:29'),
(7, 15, 13, 41, 5, '', '2025-08-05 17:19:08'),
(8, 15, 13, 44, 5, '', '2025-08-05 17:49:34'),
(9, 13, 15, 44, 1, '', '2025-08-05 17:49:52'),
(10, 15, 13, 43, 5, '', '2025-08-05 17:52:20'),
(11, 15, 13, 45, 5, '', '2025-08-05 17:54:26'),
(12, 13, 15, 45, 5, '', '2025-08-05 17:54:45'),
(13, 15, 13, 46, 5, '', '2025-08-05 18:00:22'),
(14, 13, 15, 46, 5, '', '2025-08-05 18:00:34'),
(15, 15, 13, 47, 5, '', '2025-08-05 18:05:50'),
(16, 13, 8, 49, 5, '', '2025-08-07 08:54:04');

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
  `profile_image` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Схема на данните от таблица `users`
--

INSERT INTO `users` (`id`, `username`, `ime`, `familiq`, `telefon`, `email`, `created_at`, `password`, `city`, `age`, `city_visible`, `age_visible`, `show_phone`, `show_email`, `show_city`, `show_age`, `profile_image`, `role`) VALUES
(1, 'testuser', 'Тест', 'Потребител', '0888123456', 'test@fixora.bg', '2025-07-10 11:17:43', '', 'София', 25, 0, 0, 1, 1, 0, 1, 'user.png', 'user'),
(2, 'криси', 'криси', 'миланова', '0887879742', 'kmilanova04@gamil.com', '2025-07-10 12:07:36', '', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(3, 'Miti0', 'Адам', 'Кънев', '54154', 'miti0.kaloyanov@gmail.com', '2025-07-10 13:18:14', '$2y$10$byc0qw.Zz639LmtfTQRDTOzPYdLbRkb98Ad2fuEGl6ZL2fU.G72S6', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(4, 'Ivan', 'Иван', 'Костов', '1235489895', 'ivan@gmail.com', '2025-07-10 14:05:29', '$2y$10$V4.Dw0ul7qqvy0N15H3bq.yG20QlW9clHrByHVkZ0VT5gIj/N87gi', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(5, 'Георги', 'Георги', 'Корадов', '12345676', 'koradov@gmail.com', '2025-07-10 20:48:41', '$2y$10$ikZ4lfmx2DbJq14woWUisemjgwepqmkjv5iKDJ4hKYkQKXVUq.v4i', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(6, 'Иван', 'Патрашков', 'Патрашков', '92930320', 'ivan@abv', '2025-07-14 13:38:38', '$2y$10$kfb/FBsBU51Pm3c5m6U8heYALhpxrXhTEzLFn8ayXPOUjHX/i3Y1S', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(7, 'DEKaloyanov', 'Димитър', 'Калоянов', '0885662686', 'mtiko.kaloqnov523@gmail.com', '2025-07-15 20:25:02', '$2y$10$VPNL9Iw9Fg8KferxD4a9r.pgjFYiaMwSHI4vlFgtUo4xXjSh8A16e', '', 0, 0, 0, 0, 0, 0, 0, NULL, 'user'),
(8, 'KristinaKaloyanova', 'Криси', 'Калоянова', '0887879742', 'krisimkrisi04@gmail.com', '2025-07-22 20:43:29', '$2y$10$.y6WMOCb6enF7xNGF3MtDO5DH/cK0JjxwVDcwCQPgQDsPbB.DHc7e', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(9, 'Miti0_0', 'Адам', 'gt', '54154', 'Tralala@gmail.com', '2025-07-22 21:10:58', '$2y$10$Q63DK6kRvhuEqQNdXX05Le7ak5AEFd59SYQG8rV2ZcnEcuyLQ7NIe', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(12, 'Miti0_0e', 'mitko', 'gt', '54859', 'miti0.kaloyaeenov@gmail.com', '2025-07-22 21:21:30', '$2y$10$VFl9iFbdl3YGnVW7vL0hV.ZCvzxeWCxoL2A4VbkBRrPWtOfgAtcv.', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(13, 'DKaloyanov', 'Димитър', 'Калоянов', '0885662686', 'mitko.kaloqnov523@gmail.com', '2025-07-23 15:40:38', '$2y$10$4uYx8M3ZX9SKo4GZe5IvLe1sIZieXEp0PUa5w4bFCCEh6oQ9fAWme', 'Бургас', 20, 0, 0, 1, 1, 1, 1, 'MVP_8777.jpg', 'admin'),
(14, 'ккк', 'ккк', 'ккк', 'ккк', 'kkkk@gmail.com', '2025-07-23 19:07:15', '$2y$10$pdl893X5pYAzpSeBQ4.yUuDXsbT6XJ48GbnwNFp5RDOaVoJaaAH9e', NULL, NULL, 0, 0, 1, 1, 1, 1, NULL, 'user'),
(15, 'GKanev', 'Георги', 'Кънев', '56466464', 'kanefgeorgi@gmail.com', '2025-07-27 14:19:44', '$2y$10$p/yrJsjFOAwtz7289NFvguVo44Inb6gqnMX5whz2IwooXun76jz0e', 'Добрич', 21, 0, 0, 1, 1, 1, 1, 'Nilesh3073-new.jpg', 'user');

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
  ADD KEY `user2_id` (`user2_id`),
  ADD KEY `fk_job_id` (`job_id`);

--
-- Индекси за таблица `connection_requests`
--
ALTER TABLE `connection_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Индекси за таблица `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`job_id`);

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
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `fk_message_job_id` (`job_id`);

--
-- Индекси за таблица `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Индекси за таблица `project_status`
--
ALTER TABLE `project_status`
  ADD PRIMARY KEY (`id`);

--
-- Индекси за таблица `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `connection_requests`
--
ALTER TABLE `connection_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=239;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `project_status`
--
ALTER TABLE `project_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

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
  ADD CONSTRAINT `connections_ibfk_2` FOREIGN KEY (`user2_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL;

--
-- Ограничения за таблица `connection_requests`
--
ALTER TABLE `connection_requests`
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
  ADD CONSTRAINT `fk_message_job_id` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE SET NULL,
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
