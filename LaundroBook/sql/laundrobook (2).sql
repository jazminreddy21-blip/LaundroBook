-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 06, 2026 at 10:29 AM
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
-- Database: `laundrobook`
--

-- --------------------------------------------------------

--
-- Table structure for table `booking`
--

CREATE TABLE `booking` (
  `booking_id` int(3) NOT NULL,
  `customer_id` int(3) NOT NULL,
  `machine_id` int(3) NOT NULL,
  `slot_id` int(3) NOT NULL,
  `service_id` int(3) NOT NULL,
  `manager_id` int(3) NOT NULL,
  `booking_reference` varchar(50) NOT NULL,
  `booking_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `machine_free_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(3) NOT NULL,
  `customer_name` varchar(60) NOT NULL,
  `customer_email` varchar(60) NOT NULL,
  `customer_phone` varchar(15) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` int(3) NOT NULL,
  `booking_id` int(3) NOT NULL,
  `groundworker_id` int(3) NOT NULL,
  `delivery_type` varchar(50) NOT NULL,
  `delivery_status` varchar(50) NOT NULL,
  `scheduled_time` timestamp(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enquiry`
--

CREATE TABLE `enquiry` (
  `enquiry_id` int(3) NOT NULL,
  `manager_id` int(3) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(300) NOT NULL,
  `message` varchar(500) NOT NULL,
  `date_submitted` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `status` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `groundworker`
--

CREATE TABLE `groundworker` (
  `groundworker_id` int(3) NOT NULL,
  `groundworker_name` varchar(30) NOT NULL,
  `groundworker_phone` varchar(15) NOT NULL,
  `groundworker_role` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `machine`
--

CREATE TABLE `machine` (
  `machine_id` int(3) NOT NULL,
  `manager_id` int(3) NOT NULL,
  `machine_name` varchar(50) NOT NULL,
  `machine_status` varchar(50) NOT NULL,
  `free_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `service`
--

CREATE TABLE `service` (
  `service_id` int(3) NOT NULL,
  `manager_id` int(3) NOT NULL,
  `wash_type` varchar(30) NOT NULL,
  `load_type` varchar(30) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_minutes` int(3) NOT NULL,
  `duration_slots` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `slot`
--

CREATE TABLE `slot` (
  `slot_id` int(3) NOT NULL,
  `manager_id` int(3) NOT NULL,
  `slot_label` varchar(50) NOT NULL,
  `start_time` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `end_time` timestamp(6) NOT NULL DEFAULT current_timestamp(6),
  `is_active` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_manager`
--

CREATE TABLE `system_manager` (
  `manager_id` int(3) NOT NULL,
  `manager_username` varchar(50) NOT NULL,
  `password_hash` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `booking`
--
ALTER TABLE `booking`
  ADD PRIMARY KEY (`booking_id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `machine_id` (`machine_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `service_id` (`service_id`),
  ADD KEY `slot_id` (`slot_id`);

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `customer_email` (`customer_email`),
  ADD UNIQUE KEY `customer_phone` (`customer_phone`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `groundworker_id` (`groundworker_id`);

--
-- Indexes for table `enquiry`
--
ALTER TABLE `enquiry`
  ADD PRIMARY KEY (`enquiry_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `groundworker`
--
ALTER TABLE `groundworker`
  ADD PRIMARY KEY (`groundworker_id`),
  ADD UNIQUE KEY `groundworker_phone` (`groundworker_phone`);

--
-- Indexes for table `machine`
--
ALTER TABLE `machine`
  ADD PRIMARY KEY (`machine_id`),
  ADD UNIQUE KEY `machine_name` (`machine_name`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `service`
--
ALTER TABLE `service`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `slot`
--
ALTER TABLE `slot`
  ADD PRIMARY KEY (`slot_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `system_manager`
--
ALTER TABLE `system_manager`
  ADD PRIMARY KEY (`manager_id`),
  ADD UNIQUE KEY `manager_username` (`manager_username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `booking`
--
ALTER TABLE `booking`
  MODIFY `booking_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enquiry`
--
ALTER TABLE `enquiry`
  MODIFY `enquiry_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `groundworker`
--
ALTER TABLE `groundworker`
  MODIFY `groundworker_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `machine`
--
ALTER TABLE `machine`
  MODIFY `machine_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service`
--
ALTER TABLE `service`
  MODIFY `service_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `slot`
--
ALTER TABLE `slot`
  MODIFY `slot_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_manager`
--
ALTER TABLE `system_manager`
  MODIFY `manager_id` int(3) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `booking`
--
ALTER TABLE `booking`
  ADD CONSTRAINT `booking_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_2` FOREIGN KEY (`machine_id`) REFERENCES `machine` (`machine_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_3` FOREIGN KEY (`manager_id`) REFERENCES `system_manager` (`manager_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_4` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `booking_ibfk_5` FOREIGN KEY (`slot_id`) REFERENCES `slot` (`slot_id`) ON UPDATE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `booking` (`booking_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_ibfk_2` FOREIGN KEY (`groundworker_id`) REFERENCES `groundworker` (`groundworker_id`) ON UPDATE CASCADE;

--
-- Constraints for table `enquiry`
--
ALTER TABLE `enquiry`
  ADD CONSTRAINT `enquiry_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `system_manager` (`manager_id`) ON UPDATE CASCADE;

--
-- Constraints for table `machine`
--
ALTER TABLE `machine`
  ADD CONSTRAINT `machine_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `system_manager` (`manager_id`) ON UPDATE CASCADE;

--
-- Constraints for table `service`
--
ALTER TABLE `service`
  ADD CONSTRAINT `service_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `system_manager` (`manager_id`) ON UPDATE CASCADE;

--
-- Constraints for table `slot`
--
ALTER TABLE `slot`
  ADD CONSTRAINT `slot_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `system_manager` (`manager_id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
