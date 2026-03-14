-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 10:42 PM
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
-- Database: `kcc`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblcustomers`
--

CREATE TABLE `tblcustomers` (
  `id` int(11) NOT NULL,
  `fullName` varchar(100) NOT NULL,
  `fatherName` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `tazkira` varchar(50) DEFAULT NULL,
  `job` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `id_card_photo` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblcustomers`
--

INSERT INTO `tblcustomers` (`id`, `fullName`, `fatherName`, `dob`, `tazkira`, `job`, `phone`, `address`, `photo`, `id_card_photo`, `created_at`) VALUES
(1, 'سید جان آقا', 'سید خان شیری', '1995-12-01', '14442', 'شغل آزاد', '0784949527', 'کابل ', '1762670849_Screenshot 2025-09-21 020709.jpg', NULL, '2025-11-05 21:09:40'),
(2, 'کاظم', 'باقر', '1996-01-05', '', 'مدیر', '0781572653', 'کابل', '1762410122_wide_11438354 copy1.png', '1762410122_phone-call_7045050 copy.png', '2025-11-05 22:22:02'),
(3, 'بهادری', 'حسین', '2002-02-22', '71788', 'دیزاینر', '0784848659', 'دشت برچی', '1762411893_Screenshot 2025-09-22 054747.jpg', '1762411893_Screenshot 2025-09-21 020709.jpg', '2025-11-05 22:51:33');

-- --------------------------------------------------------

--
-- Table structure for table `tblpayments`
--

CREATE TABLE `tblpayments` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `payment_date` datetime NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_file` varchar(255) DEFAULT NULL,
  `note` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblpayments`
--

INSERT INTO `tblpayments` (`id`, `sale_id`, `payment_date`, `amount`, `payment_method`, `receipt_file`, `note`, `created_at`) VALUES
(11, 7, '2026-01-26 10:27:00', 2000.00, 'بانک', '', '', '2026-01-26 10:27:28');

-- --------------------------------------------------------

--
-- Table structure for table `tblplots`
--

CREATE TABLE `tblplots` (
  `id` int(11) NOT NULL,
  `plot_code` varchar(30) NOT NULL,
  `project_id` int(11) NOT NULL,
  `plot_type` varchar(50) DEFAULT NULL,
  `area` float DEFAULT NULL,
  `length` float DEFAULT NULL,
  `width` float DEFAULT NULL,
  `boundaries` mediumtext DEFAULT NULL,
  `price` decimal(15,2) DEFAULT NULL,
  `status` enum('Available','Reserved','Sold') DEFAULT 'Available',
  `map_file` varchar(255) DEFAULT NULL,
  `owner_customer_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblplots`
--

INSERT INTO `tblplots` (`id`, `plot_code`, `project_id`, `plot_type`, `area`, `length`, `width`, `boundaries`, `price`, `status`, `map_file`, `owner_customer_id`, `created_at`) VALUES
(4, 'p1-000004', 3, 'دو بسوه یی', 200, 20, 20, 'غرب: خانه مسکونی شرق نمره خالی شمال: کوجه ', 3000.00, 'Sold', NULL, NULL, '2025-11-04 22:23:56'),
(7, 'p1-000002', 3, 'دو بسوه یی', 200, 20, 20, '', 3000.00, 'Available', NULL, NULL, '2025-11-09 00:10:18'),
(8, '123123', 13, '2 بسوه ای', 200, 20, 10, 'شسبییش سبتمنشتب بتمشبت بینمشبت', 3000.00, 'Reserved', NULL, 1, '2026-01-26 10:26:08'),
(9, '212313', 13, 'دو بسوه یی', 200, 20, 10, '', 3000.00, 'Available', NULL, NULL, '2026-02-05 01:49:50');

-- --------------------------------------------------------

--
-- Table structure for table `tblprojects`
--

CREATE TABLE `tblprojects` (
  `id` int(11) NOT NULL,
  `project_name` varchar(100) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblprojects`
--

INSERT INTO `tblprojects` (`id`, `project_name`, `location`, `description`, `created_at`) VALUES
(3, 'پروژه اول', 'ده سبز', 'پارسل ب فاز اول کابل جدید', '2025-11-03 23:55:00'),
(13, 'پروژه دوم', 'ده سبز', 'پارسل ب فاز اول کابل جدید', '2025-11-04 02:02:42'),
(14, 'پروژه سوم', 'ده سبز', 'پارسل ب فاز اول کابل جدید', '2025-11-04 02:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `tblsales`
--

CREATE TABLE `tblsales` (
  `id` int(11) NOT NULL,
  `sale_code` varchar(50) NOT NULL,
  `plot_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sale_type` enum('Full','Reserve') DEFAULT 'Full',
  `sale_date` datetime NOT NULL,
  `total_amount` decimal(15,2) NOT NULL,
  `payment_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remaining_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `sale_status` enum('Confirmed','Partial','Cancelled') DEFAULT 'Confirmed',
  `contract_file` varchar(255) DEFAULT NULL,
  `note` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tblsales`
--

INSERT INTO `tblsales` (`id`, `sale_code`, `plot_id`, `customer_id`, `sale_type`, `sale_date`, `total_amount`, `payment_amount`, `remaining_amount`, `sale_status`, `contract_file`, `note`, `created_at`) VALUES
(7, 'knc-000001', 4, 1, 'Full', '2025-11-10 00:12:00', 3000.00, 1000.00, 0.00, 'Confirmed', '', '', '2025-11-10 00:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Sales','Viewer') DEFAULT 'Admin',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'sebghat', '$2y$10$sqU9BCHPAL3x8FKPJlssAubb9EczUaC7vAXaAa7KoTrhWfCPd3xWS', 'Admin', '2025-11-02 23:13:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblcustomers`
--
ALTER TABLE `tblcustomers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblpayments`
--
ALTER TABLE `tblpayments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `tblplots`
--
ALTER TABLE `tblplots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plot_code` (`plot_code`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `idx_owner_customer_id` (`owner_customer_id`);

--
-- Indexes for table `tblprojects`
--
ALTER TABLE `tblprojects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblsales`
--
ALTER TABLE `tblsales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_code` (`sale_code`),
  ADD UNIQUE KEY `sale_code_2` (`sale_code`),
  ADD KEY `idx_plot_id` (`plot_id`),
  ADD KEY `idx_customer_id` (`customer_id`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblcustomers`
--
ALTER TABLE `tblcustomers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tblpayments`
--
ALTER TABLE `tblpayments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tblplots`
--
ALTER TABLE `tblplots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tblprojects`
--
ALTER TABLE `tblprojects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tblsales`
--
ALTER TABLE `tblsales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblpayments`
--
ALTER TABLE `tblpayments`
  ADD CONSTRAINT `tblpayments_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `tblsales` (`id`);

--
-- Constraints for table `tblplots`
--
ALTER TABLE `tblplots`
  ADD CONSTRAINT `fk_plot_customer` FOREIGN KEY (`owner_customer_id`) REFERENCES `tblcustomers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_plot_project` FOREIGN KEY (`project_id`) REFERENCES `tblprojects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tblsales`
--
ALTER TABLE `tblsales`
  ADD CONSTRAINT `fk_sales_customer` FOREIGN KEY (`customer_id`) REFERENCES `tblcustomers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sales_plot` FOREIGN KEY (`plot_id`) REFERENCES `tblplots` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
