-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 07, 2025 at 10:58 PM
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
-- Database: `bahashield`
--

-- --------------------------------------------------------

--
-- Table structure for table `alert_recipients`
--

CREATE TABLE `alert_recipients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `role` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flood_history`
--

CREATE TABLE `flood_history` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `rainfall` int(11) NOT NULL,
  `flood` int(11) NOT NULL,
  `status` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `channel` varchar(20) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `response` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sensor_data`
--

CREATE TABLE `sensor_data` (
  `id` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `water` int(11) NOT NULL,
  `rain` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Safe'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sensor_data`
--

INSERT INTO `sensor_data` (`id`, `datetime`, `water`, `rain`, `status`) VALUES
(31, '2025-10-05 06:00:00', 12, '25', 'Safe'),
(32, '2025-10-05 09:00:00', 22, '40', 'Warning'),
(33, '2025-10-05 12:00:00', 35, '60', 'Danger'),
(34, '2025-10-03 18:00:13', 100, '50', 'Danger'),
(35, '2025-10-07 08:15:00', 12, '5', 'Safe'),
(36, '2025-10-07 09:00:00', 22, '12', 'Warning'),
(37, '2025-10-07 10:30:00', 31, '18', 'Danger'),
(38, '2025-10-07 11:45:00', 15, '8', 'Safe');

-- --------------------------------------------------------

--
-- Table structure for table `subscribers`
--

CREATE TABLE `subscribers` (
  `id` int(11) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `subscribed` tinyint(1) DEFAULT 1,
  `unsubscribe_token` varchar(64) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscribers`
--

INSERT INTO `subscribers` (`id`, `phone`, `name`, `subscribed`, `unsubscribe_token`, `created_at`) VALUES
(1, '9566757100', NULL, 1, 'cdffe9531fed3f3e64a9dc6d4c1bd45d', '2025-09-29 14:05:22'),
(5, '09566757100', NULL, 1, 'dd58cb4c5cbcad6c866fd31002497d5c', '2025-09-29 14:11:38'),
(8, '+639566757100', NULL, 1, '018135e7e7b976c4c7dfd1d3557a6811', '2025-09-29 14:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('Resident','Admin','Responder','Barangay Official') DEFAULT 'Resident',
  `address` varchar(255) NOT NULL,
  `purok` varchar(50) NOT NULL,
  `number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `unsubscribe_token` varchar(64) DEFAULT NULL,
  `subscribed` tinyint(1) DEFAULT 1,
  `otp` varchar(255) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `role`, `address`, `purok`, `number`, `password`, `created_at`, `email_verified`, `email_verified_at`, `verification_token`, `unsubscribe_token`, `subscribed`, `otp`, `otp_expiry`) VALUES
(44, 'Baha Shield', 'bahashield@gmail.com', 'Barangay Official', '', '4', '09566757100', '$2y$10$8B8D/lHKb0v.LyVuBRyGYOdk7PUCakIWHK7R7m09hmBMXhJOTI7iy', '2025-10-02 16:18:55', 0, NULL, NULL, NULL, 1, NULL, NULL),
(45, 'Adrian', 'adrianmanlangit125@gmail.com', 'Resident', '', '1', '09692596355', '$2y$10$iTLwmxL/rq/znUoQtKKNFOEAGMed017PCJm92ewfQqFW/5pgatqkG', '2025-10-06 12:42:49', 0, NULL, '51307503a3ee45856e5c50eb8ed2d0885dfe5324524d3a698c93feec40f5991d', '2a17d03be6bdacb6aece6ae795060c4b', 1, NULL, NULL),
(46, 'DITO No.', 'DitoNo.@gmail.com', 'Resident', '', '8', '09934725603', '$2y$10$ry56r8/lGOwHXR7.eZiIhODwYYZcYRajDWNkrfv0gsj1XsJIkmFpy', '2025-10-06 13:35:55', 0, NULL, '856cc910c213b0cafabdd93b4f8f4f3af637ace7f2a99eb56a2bdc5b90828d37', '2e9b539454637d9416a8bbda4102e5b3', 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `flood_history`
--
ALTER TABLE `flood_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sensor_data`
--
ALTER TABLE `sensor_data`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscribers`
--
ALTER TABLE `subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `number` (`number`),
  ADD UNIQUE KEY `unsubscribe_token` (`unsubscribe_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `flood_history`
--
ALTER TABLE `flood_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  ADD CONSTRAINT `alert_recipients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
