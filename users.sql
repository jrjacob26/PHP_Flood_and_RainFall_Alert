-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 28, 2025 at 07:29 PM
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
(1, 'Admin2', 'admin2', 'admin2@gmail.com', 'Admin', 'Cabangan Daraga Albay', '1', '09123456789', '$2y$10$Spdp41As/jGKsmDCHS7cOeVlKrzZPAQ8eqDVXqbAyP5/eRXREEYme', '2025-08-28 17:02:54'),
(2, 'Admin1', 'admin1', 'admin1@gmail.com', 'Admin', 'Cabangan Daraga Albay', '6', '09123456788', '$2y$10$h1ZGaNkdI62f6f9SrKOMau7djJO/.MT8bUFccJgq5HwWVPWPDM6C2', '2025-08-28 17:04:03'),
(3, 'Resident', 'resident', 'resident@gmail.com', 'Resident', 'Cabangan Daraga Albay', '2', '09123456787', '$2y$10$iNLRPQPePVbkzq3kTOOOKerbnaat5ZaZV0zJmFj5yVYVGKHsac8Ei', '2025-08-28 17:05:00'),
(5, 'Admin3', 'admin3', 'admin3@gmail.com', 'Admin', '', '10', '09123456786', '$2y$10$j8LXF3pIMBwoP1m.ynFWxOpKhyW2NGbnBvQ2YHnClNUisXkNsVAgC', '2025-08-28 17:28:57');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
