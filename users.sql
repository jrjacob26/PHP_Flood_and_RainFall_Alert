-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 07:35 PM
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `role`, `address`, `purok`, `number`, `password`, `created_at`) VALUES
(3, 'Resident2', 'resident2', 'resident2@gmail.com', 'Resident', 'Cabangan Daraga Albay', '2', '09123456787', '$2y$10$iNLRPQPePVbkzq3kTOOOKerbnaat5ZaZV0zJmFj5yVYVGKHsac8Ei', '2025-08-28 17:05:00'),
(6, 'Barangay Official1', 'brgy.official1', 'brgy.official1@gmail.com', 'Barangay Official', 'Cabangan Daraga Albay', '5', '09123456785', '$2y$10$IY9GkeT5z9dAw4aHIxpRLunH9E1jhXMO7Wle3kUxMoRtXaLy7cKQC', '2025-08-28 17:32:21'),
(7, 'Resident1', 'resident1', 'resident1@gmail.com', 'Resident', 'Cabangan Daraga Albay', '1', '09123456784', '$2y$10$sKs3SJo0/m0p7mdP2jVsYulhbgOdlw7Kd/Y7tc2K0K14hEwXeYd9C', '2025-08-30 04:43:26'),
(8, 'Barangay Official2', 'brgy.official2', 'barangayofficial2@gmail.com', 'Barangay Official', 'Cabangan Daraga Albay', '6', '09123456783', '$2y$10$ey31deQ9fEXH4FSV5wKaoeNmy2ppvbvyFa8UXGCcLkCXzAjOZPMia', '2025-08-30 04:45:02'),
(10, 'Resident3', 'resident3', 'resident3@gmail.com', 'Resident', 'Cabangan Daraga Albay', '7', '09123456781', '$2y$10$hb7/Lt1XLzqf6kOnjQQ.0e1TBZsmtKRJ17wCWij4xjlJKtf9UlASe', '2025-08-30 15:42:02');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
