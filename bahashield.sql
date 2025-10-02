-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 02, 2025 at 10:56 AM
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
  `water_level` float NOT NULL,
  `rainfall` float NOT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(33, 'Admin1', 'admin1@gmail.com', 'Barangay Official', '', '', '09123456787', '$2y$10$tmtNY6Y57cWYbAwJJXRd2eyjdIUk7gwzCbGvur5kKcVsHju7LRNTG', '2025-09-20 17:17:00', 0, NULL, NULL, NULL, 1, NULL, NULL),
(37, 'Christopher M. Jacob Jr', '07207169@dwc-legazpi.edu', 'Barangay Official', '', '1', '09566757100', '$2y$10$igUdbP0xRIl/I8hNh7a6FeGrb5WBKxlOnyPDjsBj.lpCdVL3xsOn6', '2025-09-20 17:29:50', 1, NULL, 'a97dca293a1eed49a48b5cbc25c17ca52a6ff010f19966f826283a594764930d', NULL, 1, NULL, NULL),
(38, 'Christopher M. Jacob Jr', 'christopherjacob305@gmail.com', 'Barangay Official', '', '', '09123456123', '$2y$10$j88B0qhR62guFiLmm1Q4r.YRAHT4snDmRXbb.B8b6SjRrRTtenYnC', '2025-09-20 17:48:38', 1, NULL, 'd35c33ae3d9efc945bdb9b108facc3c31b100b9d361b4d023941be47c1db5c2c', NULL, 1, '$2y$10', '2025-09-21 02:12:17'),
(39, 'Resident3', 'resident3@gmail.com', 'Resident', '', '1', '09123456781', '$2y$10$/Bz97oKYmvKscmrYO2CMuOlMrypzViq9jUd9yLam2UOURzye/9ixS', '2025-09-28 16:47:55', 0, NULL, '8f68944bb010e28e3bab47dee7cdd7c34d73964aeacdf9d6afd4fa19729124b2', NULL, 1, NULL, NULL),
(40, 'Resident2', 'resident2@gmail.com', 'Resident', '', '6', '09123456780', '$2y$10$lyo00idathwzOaNfFti0eeXK6pYW5Soa3NW5WuG9mssSY7rFtb0iC', '2025-09-28 18:43:25', 0, NULL, '69e8622a8bc61c6593f4eae8a9a402968534524277d5fca97a378c9ee1a5e38d', '3d2474952c3de26fde2268baade39d12', 1, NULL, NULL),
(41, 'Resident1', 'resident1@gmail.com', 'Resident', '', '1', '09123456783', '$2y$10$fHvqsC/YYPcCnC8RSW0xneqYPl3ow8pS996zhJLFfdbZfiFAUhGXi', '2025-09-28 18:44:10', 0, NULL, 'c89f4a734c83d8e1cdc55f085112a5d60a8312072227958044b25d20c294d05e', 'd365b3dc53339a03e77ce917229a8213', 1, NULL, NULL),
(42, 'Resident4', 'resident4@gmail.com', 'Resident', '', '5', '09123456790', '$2y$10$7Tqjz6bkIwPTjzTtrVS1Tuq39kncHcUWG5xtcPJaQ83qbZ2UYRXxG', '2025-09-29 05:25:05', 0, NULL, 'f09beca5ebb6f5aa6997d977d999b04aa5497ccef229980635613cc53450e400', '148545a7b5aec8f223f266930d107592', 1, NULL, NULL);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensor_data`
--
ALTER TABLE `sensor_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscribers`
--
ALTER TABLE `subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

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
