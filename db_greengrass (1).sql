-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 22, 2026 at 01:12 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_greengrass`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `app_settings`
--

INSERT INTO `app_settings` (`setting_key`, `setting_value`) VALUES
('ki_counter', 3),
('kp_counter', 3);

-- --------------------------------------------------------

--
-- Table structure for table `installations`
--

CREATE TABLE `installations` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `mandor_name` varchar(100) DEFAULT NULL,
  `work_date` date DEFAULT NULL,
  `area_size` decimal(10,2) DEFAULT NULL,
  `service_price` decimal(15,2) DEFAULT NULL,
  `total_price` decimal(15,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `installations`
--

INSERT INTO `installations` (`id`, `order_id`, `mandor_name`, `work_date`, `area_size`, `service_price`, `total_price`) VALUES
(1, 1, 'Mang Ano', '2025-11-30', 2000.00, 35000.00, 70000000.00),
(2, 3, 'mang ayi', '2025-12-29', 20.00, 35000.00, 700000.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `kp_number` varchar(20) NOT NULL,
  `ki_number` varchar(20) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text,
  `maps_link` text,
  `warehouse_source` varchar(50) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `grand_total` decimal(15,2) NOT NULL,
  `pay_status` enum('Belum Bayar','DP','Lunas') DEFAULT 'Belum Bayar',
  `order_status` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `total_fee_r` decimal(15,2) DEFAULT '0.00',
  `total_fee_dc` decimal(15,2) DEFAULT '0.00',
  `marketing_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `kp_number`, `ki_number`, `customer_name`, `customer_phone`, `customer_address`, `maps_link`, `warehouse_source`, `delivery_date`, `grand_total`, `pay_status`, `order_status`, `total_fee_r`, `total_fee_dc`, `marketing_id`, `created_at`) VALUES
(1, 'KP-301125-XX', 'KI-301125-XX', 'Bapak abay', '08589442142', 'Kp. Bojong Rangkas RT', 'https://abayyydev.github.io/project-web-marketing/', 'Pusat', '2025-11-30', 70130000.00, 'Belum Bayar', 'Verified', 0.00, 3000.00, 2, '2025-11-30 08:56:23'),
(2, 'KP-AUTO', NULL, 'Bapak abay firdaus', '088577249', 'Ciampea, Bogr', 'https://abayyydev.github.io/project-web-marketing/', 'Pusat', '2025-11-01', 15000.00, 'Belum Bayar', 'Pending', 0.00, 0.00, 2, '2025-11-30 09:51:15'),
(3, 'KP-011225-03', 'KI-291225-03', 'Bapak abay firdaus', '02938985', 'kadshf', 'https://abayyydev.github.io/project-web-marketing/', 'Pusat', '2025-12-01', 805000.00, 'Belum Bayar', 'Verified', 0.00, 3000.00, 2, '2025-11-30 09:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `qty` decimal(10,2) NOT NULL,
  `deal_price` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `deal_price`, `subtotal`) VALUES
(1, 1, 2, 1.00, 100000.00, 100000.00),
(2, 1, 4, 2.00, 15000.00, 30000.00),
(3, 2, 4, 1.00, 15000.00, 15000.00),
(4, 3, 2, 1.00, 105000.00, 105000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `code` varchar(10) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `fee_amount` decimal(10,2) DEFAULT '0.00',
  `fee_code` varchar(5) DEFAULT NULL,
  `type` enum('goods','service') DEFAULT 'goods'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `code`, `name`, `unit`, `base_price`, `fee_amount`, `fee_code`, `type`) VALUES
(1, 'P01', 'Swiss Platinum 4cm', 'm2', 210000.00, 10500.00, 'R', 'goods'),
(2, 'Po-111', 'Drainase Cell', 'm2', 105000.00, 3000.00, 'Dc', 'goods'),
(3, 'P03', 'Pasir Silika', 'Sak', 50000.00, 2000.00, 'Ps', 'goods'),
(4, 'P04', 'Geotextile', 'm2', 15000.00, 500.00, 'Gt', 'goods'),
(5, 'POO-001', 'Rumput Japan', 'm2', 200000.00, 1000.00, 'E', 'goods');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','marketing') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `created_at`) VALUES
(1, 'admin', 'pass123', 'Administrator', 'admin', '2025-11-30 08:33:20'),
(2, 'marketing', 'pass123', 'Eneng Eka', 'marketing', '2025-11-30 08:33:20');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `installations`
--
ALTER TABLE `installations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kp_number` (`kp_number`),
  ADD KEY `marketing_id` (`marketing_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `installations`
--
ALTER TABLE `installations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `installations`
--
ALTER TABLE `installations`
  ADD CONSTRAINT `installations_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`marketing_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
