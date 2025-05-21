-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 09, 2025 at 06:30 AM
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
-- Database: `yna_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$ymj1k1p2qw2lsyfyv.A8XetcF7jE2KZhx8ql1t9tik8hkmadVkgVG', '2025-05-06 00:08:01'),
(2, 'admin1', 'admin2@gmail.com', '$2y$10$qQFDYKnts6uDBAmmzUHvpuUv3uER6UoVDyAvtd/quUIOsGgd/dJuy', '2025-05-07 03:19:46'),
(3, 'admin2', 'admin3@gmail.com', '$2y$10$S/VqzPvHZ4DmrOGSr.D2AO4466S2R6hRlFOCen18rSViI7BAeUSWa', '2025-05-07 23:08:18');

-- --------------------------------------------------------

--
-- Table structure for table `agents`
--

CREATE TABLE `agents` (
  `agent_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agents`
--

INSERT INTO `agents` (`agent_id`, `name`, `email`, `password`, `created_at`) VALUES
(3, 'agent234', 'agent2@gmail.com', '$2y$10$dLsGDdXGvDRGSPagh0xmlu5/WJUm7yXHMZYkKzuDDtmgTVCZzFZ06', '2025-05-06 02:02:46'),
(4, 'agent25', 'agent@gmail.com', '$2y$10$z6BI7/9xWkUcQdxMDa3WSODoNleZIj8/1IhcuGyW2vOISUoilFXeK', '2025-05-06 03:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignment_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignment_id`, `ticket_id`, `agent_id`, `assigned_at`) VALUES
(5, 3, 3, '2025-05-06 05:28:03'),
(6, 4, 4, '2025-05-06 05:59:58'),
(7, 5, 4, '2025-05-06 07:58:08'),
(10, 14, 4, '2025-05-07 06:23:47');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `ticket_id`, `agent_id`, `user_id`, `message`, `photo_path`, `created_at`) VALUES
(1, 4, 4, NULL, 'cscscscsc', NULL, '2025-05-06 04:24:51'),
(2, 4, 4, NULL, 'cscscscsc', NULL, '2025-05-06 04:24:54'),
(3, 4, 4, NULL, 'cscscscsc', NULL, '2025-05-06 04:25:07'),
(4, 4, 4, NULL, 'cssdsd', NULL, '2025-05-06 04:25:36'),
(5, 4, 4, NULL, 'csssscd', NULL, '2025-05-06 04:26:58'),
(6, 4, 4, NULL, 'hello', NULL, '2025-05-06 04:29:55'),
(7, 4, 4, NULL, 'that is okay', NULL, '2025-05-06 04:40:13'),
(8, 5, 4, NULL, 'hello', NULL, '2025-05-06 05:58:29'),
(9, 4, NULL, NULL, 'hello', NULL, '2025-05-06 16:52:12'),
(10, 4, 4, NULL, 'hiii', NULL, '2025-05-06 16:52:54'),
(11, 4, 4, NULL, '', NULL, '2025-05-06 16:53:18'),
(12, 4, 4, NULL, '', NULL, '2025-05-06 16:53:22'),
(13, 4, 4, NULL, '', NULL, '2025-05-06 16:53:25'),
(14, 4, 4, NULL, '', NULL, '2025-05-06 17:16:51'),
(15, 4, 4, NULL, '', 'uploads/img_681a472fcbfc76.01898976.jpg', '2025-05-06 17:30:23'),
(16, 4, 4, NULL, 'hahahahhaha', '', '2025-05-06 17:33:48'),
(17, 5, 4, NULL, 'ggghgghg', '', '2025-05-07 04:19:18'),
(18, 5, 4, NULL, '', 'uploads/img_681adf54e979e8.50011739.jpg', '2025-05-07 04:19:32'),
(19, 14, 4, NULL, 'hello', '', '2025-05-07 04:24:17'),
(20, 3, NULL, NULL, 'hello', NULL, '2025-05-07 04:24:50'),
(21, 14, NULL, NULL, 'hiddid', NULL, '2025-05-07 04:25:23'),
(22, 14, 4, NULL, 'cdwdwdwd', '', '2025-05-07 04:25:49');

-- --------------------------------------------------------

--
-- Table structure for table `sla_hours`
--

CREATE TABLE `sla_hours` (
  `sla_id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `sla_start_time` datetime DEFAULT NULL,
  `sla_limit` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT NULL,
  `sla` varchar(50) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `device_used` varchar(100) DEFAULT NULL,
  `issue_frequency` varchar(100) DEFAULT NULL,
  `urgency_reason` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('open','in_progress','closed') DEFAULT 'open',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`ticket_id`, `name`, `email`, `department`, `priority`, `sla`, `subject`, `device_used`, `issue_frequency`, `urgency_reason`, `description`, `attachment`, `status`, `created_at`, `updated_at`, `user_id`, `agent_id`) VALUES
(3, 'csss', 'caaca@gmail.com', 'csc', 'medium', 'Urgent (48h)', 'cscs', 'cscs', 'cscs', 'cscs', 'cscscs', '', 'in_progress', '2025-05-06 10:50:12', '2025-05-06 11:28:03', NULL, NULL),
(4, 'csscq', 'css@gmail.com', 'cscs', 'low', 'Standard (72h)', 'sdsds', 'sdsd', 'dsds', 'sdsd', 'dsdsds', '', 'closed', '2025-05-06 11:59:43', '2025-05-06 13:15:08', NULL, NULL),
(5, 'CSC2', 'CSCSCS@GMAIL.COM', 'IT', 'medium', 'Standard (72h)', 'sdsds2', 'sdsd', 'dsds', 'sdsd', 'CSCS2', '', 'closed', '2025-05-06 13:30:58', '2025-05-07 12:19:43', NULL, NULL),
(7, 'vxvxv', 'vvs@gmail.com', 'sdsd', 'low', NULL, 'dwd', 'dsds', 'dsds', 'dsds', 'dsds', '', 'closed', '2025-05-07 02:44:24', '2025-05-07 02:44:39', NULL, NULL),
(8, 'css', 'CSCSCS@GMAIL.COM', 'csc', 'low', NULL, 'cscs', 'cs', 'cscs', 'cscs', 'cscs', '', 'closed', '2025-05-07 02:50:17', '2025-05-07 02:55:48', NULL, NULL),
(9, 'hrhrhr', 'CSCSCS@GMAIL.COM', 'hrhr', 'critical', NULL, 'cscs', 'cs', 'cscs', 'cscs', 'xxaxa', '', 'closed', '2025-05-07 02:50:55', '2025-05-07 02:51:42', NULL, NULL),
(14, 'cdcdc', 'ccscs@gmail.com', 'cscs', 'critical', NULL, 'cscs', 'csc', 'cscs', 'cscs', 'cscs', '', 'closed', '2025-05-07 11:39:05', '2025-05-07 12:26:03', NULL, NULL),
(15, 'xs', 'tycas@gmail.com', 'cscs', 'low', NULL, 'cssc', 'cscs', 'ccs', 'cscs', 'cscsc', '', 'open', '2025-05-07 11:39:33', '2025-05-07 11:39:33', NULL, NULL),
(16, 'vdvd', 'vd@gmail.com', 'cscs', 'low', NULL, 'cscs', 'cscs', 'cscs', 'cscs', 'cscs', '', 'open', '2025-05-07 11:41:02', '2025-05-07 11:41:02', NULL, NULL),
(17, 'ggffwf', 'gdgshd@gmail.com', 'vssfsfs', 'critical', NULL, 'sfsdf', 'cscsc', 'csc', 'sccs', 'cscscs', '', 'closed', '2025-05-07 12:16:58', '2025-05-07 12:17:47', NULL, NULL),
(18, 'cscsc', 'cnscsc@gmail.com', 'cssjc', 'critical', NULL, 'clslcs', 'cmsmck', 'cmsmcskml', 'cmlslcl', 'vmldmv', '', 'open', '2025-05-07 12:22:55', '2025-05-07 12:22:55', NULL, NULL),
(19, 'csccs', 'cscs@gmail.com', 'It department', 'low', NULL, 'bug in the system', 'xcss', 'fsfs', 'fsfs', 'fsfsfs', 'uploads/Overview-of-Philippine-Mango-1-1536x1065.jpg', 'open', '2025-05-08 07:29:11', '2025-05-08 07:29:11', NULL, NULL),
(20, 'csccs', 'cscs@gmail.com', 'It department', 'low', NULL, 'bug in the system', 'xcss', 'fsfs', 'fsfs', 'fsfsfs', 'uploads/Overview-of-Philippine-Mango-1-1536x1065.jpg', 'open', '2025-05-08 07:30:42', '2025-05-08 07:30:42', NULL, NULL),
(21, 'sxs', 'cscs@gmail.com', 'It department', 'medium', NULL, 'bug in the system', 'xcss', 'fsfs', 'fsfs', 'xxssx', 'uploads/Overview-of-Philippine-Mango-1-1536x1065.jpg', 'open', '2025-05-08 07:30:54', '2025-05-08 07:30:54', NULL, NULL),
(22, 'rfef', 'fefef@gmail.com', 'cs', 'medium', NULL, 'cscsc', 'cscs', 'cscs', 'cscs', 'cscs', '', 'open', '2025-05-09 10:39:50', '2025-05-09 10:39:50', NULL, NULL),
(23, 'rfefxxsxc', 'fefef@gmail.com', 'cs', 'medium', NULL, 'cscsc', 'cscs', 'cscs', 'cscs', 'cscsccs', 'uploads/Overview-of-Philippine-Mango-1-1536x1065.jpg', 'open', '2025-05-09 11:15:44', '2025-05-09 11:15:44', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `created_at`) VALUES
(6, 'yna3', 'yna@gmail.com', '$2y$10$lfFScmwOx1oIlrsphfK39.vN52V1uraLJVcauiDJNBhWPEJ8ekiH.', '2025-05-07 03:29:18'),
(7, 'yna1', 'yna1@gmail.com', '$2y$10$q40pNRezHUsaCVWAsd5E5.wto64rpz16QeDVLFxumcTiIOkcilfgO', '2025-05-07 03:30:15'),
(8, 'yna', 'yna13@gmail.com', '$2y$10$hVRzGBhOirR3Qcbrpd5u.eXp.BmoEdCPtEZoMMwUJuuyy440mfUlq', '2025-05-07 22:51:03'),
(9, 'yna', 'yna15@gmail.com', '$2y$10$fxUzKE9tB7wlHKRq08fjluHJfW/YFNyWhlGykj2HMlybnMau4RrdW', '2025-05-07 22:51:39'),
(10, 'yna', 'yna17@gmail.com', '$2y$10$i9WHFc86StskB6PYlIn57uN5FIwHlf2NKKT6pupvGll0T75I4/IES', '2025-05-07 22:52:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `agents`
--
ALTER TABLE `agents`
  ADD PRIMARY KEY (`agent_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `agent_id` (`agent_id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `agent_id` (`agent_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sla_hours`
--
ALTER TABLE `sla_hours`
  ADD PRIMARY KEY (`sla_id`),
  ADD KEY `ticket_id` (`ticket_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `fk_agent` (`agent_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `agents`
--
ALTER TABLE `agents`
  MODIFY `agent_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `sla_hours`
--
ALTER TABLE `sla_hours`
  MODIFY `sla_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignment`
--
ALTER TABLE `assignment`
  ADD CONSTRAINT `assignment_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `assignment_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`agent_id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`agent_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `sla_hours`
--
ALTER TABLE `sla_hours`
  ADD CONSTRAINT `sla_hours_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`ticket_id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_agent` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`agent_id`),
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
