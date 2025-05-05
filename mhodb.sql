-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 05, 2025 at 04:29 PM
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
-- Database: `mhodb`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `program` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(100) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `program`, `name`, `address`, `contact`, `appointment_date`, `created_at`) VALUES
(1, 'Dental Cleaning', 'Hahahahahahaa', 'samin', '0909099090909', '2025-03-15 19:35:00', '2025-03-15 11:35:45'),
(2, 'Mental Health Session', 'Bebetime', 'samin padin', '09123456789', '2025-03-15 19:41:00', '2025-03-15 11:41:57'),
(3, 'Dental Cleaning', 'haha', 'dfjjgfd', '0987645', '2025-04-22 14:32:00', '2025-04-22 06:32:38'),
(4, 'Medical Checkup', 'sad', 'dyan lang', '09123456789', '2025-04-22 16:15:00', '2025-04-22 08:15:39'),
(5, 'Medical Checkup', 'Christine', 'Sa bahay', '0999999999999', '2025-05-02 10:39:00', '2025-05-02 02:39:35');

-- --------------------------------------------------------

--
-- Table structure for table `chargeslip`
--

CREATE TABLE `chargeslip` (
  `id` int(200) NOT NULL,
  `services` varchar(255) NOT NULL,
  `fname` varchar(255) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `lname` varchar(255) NOT NULL,
  `discount` int(11) NOT NULL,
  `timeanddate` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chargeslip`
--

INSERT INTO `chargeslip` (`id`, `services`, `fname`, `mname`, `lname`, `discount`, `timeanddate`) VALUES
(1, 'Medical Certificate', 'Aaron', 'A.', 'Aramil', 10, '2025-05-02 07:32:44'),
(2, 'Health Certificate', 'Aaron', 'A.', 'Aramil', 10, '2025-05-02 10:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(100) NOT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `type`, `serial_no`, `expiry_date`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 'cute', 'coconut', '0998', '2025-03-17', 2147483647, '2025-03-17 05:23:01', '2025-03-17 05:23:01'),
(2, 'karen', 'coconuts', '099877', '2025-04-22', 12, '2025-04-22 08:16:42', '2025-04-22 08:16:42');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('cho_admin','cho_healthcare','abtc_admin','abtc_healthcare') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `created_at`, `updated_at`, `last_login`, `active`) VALUES
(1, 'admin', '$2y$10$XrEecLM/HDNw0s4QYX9vA.OZsLQ8HxzGRMrMFFA1Bue1QKx3Nl9ca', 'System Administrator', 'admin@sanpablo.gov.ph', 'cho_admin', '2025-05-05 12:49:49', '2025-05-05 12:49:49', NULL, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chargeslip`
--
ALTER TABLE `chargeslip`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_serial_no` (`serial_no`),
  ADD KEY `idx_expiry_date` (`expiry_date`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chargeslip`
--
ALTER TABLE `chargeslip`
  MODIFY `id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
