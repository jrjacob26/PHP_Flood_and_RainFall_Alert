-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2025 at 04:47 PM
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

--
-- Dumping data for table `alert_recipients`
--

INSERT INTO `alert_recipients` (`id`, `user_id`, `username`, `phone_number`, `role`, `created_at`) VALUES
(1, 13, '', '09123456780', '', '2025-09-02 17:08:19'),
(2, 14, '', '09123456779', 'Barangay Official', '2025-09-02 17:17:58'),
(4, 15, '', '09123456778', 'Resident', '2025-09-02 17:19:19'),
(5, 16, 'brgy.official4', '09123456777', 'Barangay Official', '2025-09-02 17:23:37'),
(8, 6, 'brgy.official1', '09123456785', 'Barangay Official', '2025-09-06 07:38:55'),
(10, 17, 'jacob26', '09566757100', 'Resident', '2025-09-06 08:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(150) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('Resident','Admin','Responder','Barangay Official') DEFAULT 'Resident',
  `address` varchar(255) NOT NULL,
  `purok` varchar(50) NOT NULL,
  `number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `role`, `address`, `purok`, `number`, `password`, `created_at`, `email_verified`, `email_verified_at`, `verification_token`) VALUES
(3, 'Resident2', 'resident2', 'resident2@gmail.com', 'Resident', 'Cabangan Daraga Albay', '2', '09123456787', '$2y$10$iNLRPQPePVbkzq3kTOOOKerbnaat5ZaZV0zJmFj5yVYVGKHsac8Ei', '2025-08-28 17:05:00', 0, NULL, NULL),
(6, 'Barangay Official1', 'brgy.official1', 'brgy.official1@gmail.com', 'Barangay Official', 'Cabangan Daraga Albay', '5', '09123456785', '$2y$10$IY9GkeT5z9dAw4aHIxpRLunH9E1jhXMO7Wle3kUxMoRtXaLy7cKQC', '2025-08-28 17:32:21', 0, NULL, NULL),
(7, 'Resident1', 'resident1', 'resident1@gmail.com', 'Resident', 'Cabangan Daraga Albay', '1', '09123456784', '$2y$10$sKs3SJo0/m0p7mdP2jVsYulhbgOdlw7Kd/Y7tc2K0K14hEwXeYd9C', '2025-08-30 04:43:26', 0, NULL, NULL),
(8, 'Barangay Official2', 'brgy.official2', 'barangayofficial2@gmail.com', 'Barangay Official', 'Cabangan Daraga Albay', '6', '09123456783', '$2y$10$ey31deQ9fEXH4FSV5wKaoeNmy2ppvbvyFa8UXGCcLkCXzAjOZPMia', '2025-08-30 04:45:02', 0, NULL, NULL),
(10, 'Resident3', 'resident3', 'resident3@gmail.com', 'Resident', 'Cabangan Daraga Albay', '7', '09123456781', '$2y$10$hb7/Lt1XLzqf6kOnjQQ.0e1TBZsmtKRJ17wCWij4xjlJKtf9UlASe', '2025-08-30 15:42:02', 0, NULL, NULL),
(13, 'Resident4', 'resident4', 'resident4@gmail.com', 'Resident', 'Cabangan Daraga Albay', '1', '09123456780', '$2y$10$HP9IUi/W.WspoEJQZMqe6u/5zytnpY6ucJ5vVBmysQjfi9f8H2nsi', '2025-09-02 17:07:58', 0, NULL, NULL),
(14, 'Barangay Official3', 'brgy.official3', 'barangayofficial3@gmail.com', 'Barangay Official', 'Cabangan Daraga Albay', '5', '09123456779', '$2y$10$Ji4Rl0tbhmuwpAkqMcrU6eK6VrEyTHK9RocvERK20ifEpyrozlHwa', '2025-09-02 17:15:46', 0, NULL, NULL),
(15, 'Resident5', 'resident5', 'resident5@gmail.com', 'Resident', 'Cabangan Daraga Albay', '5', '09123456778', '$2y$10$L/cHXsOtwOTf6sa9owTA5uusPAyT6YcOYC0L19c9rbRGrar4PBwjq', '2025-09-02 17:19:10', 0, NULL, NULL),
(16, 'Barangay Official4', 'brgy.official4', 'barangayofficial4@gmail.com', 'Barangay Official', 'Cabangan Daraga Albay', '3', '09123456777', '$2y$10$ts1VrjSaZY0FOC0wfP2/X.VdWF7u14rYTzULE8JmIE2JhkOTMmhqS', '2025-09-02 17:23:28', 1, NULL, '5c0add28aa43cbab2aec2072f073e365b9520cad27db22e0e05331717d337532'),
(17, 'Christopher M. Jacob Jr', 'jacob26', 'christopherjacob305@gmail.com', 'Resident', 'Cabangan Daraga Albay', '4', '09566757100', '$2y$10$E6pSFupPWGqJtVThwssAk.2IJxCz7EOuvrcsHHoeEpNJM3uzTJxuO', '2025-09-06 08:09:09', 1, NULL, 'c78af16c7bdf1d978c023e188ae5616f00c9104fd365928173b56a59c485bbea');

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
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `number` (`number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alert_recipients`
--
ALTER TABLE `alert_recipients`
  ADD CONSTRAINT `alert_recipients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
