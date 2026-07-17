-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 17, 2026 at 08:31 AM
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
-- Database: `business_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `type` enum('asset','liability','income','expense','equity') NOT NULL,
  `business_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_name`, `type`, `business_id`) VALUES
(1, 'payments', 'expense', 0),
(2, 'Loan', 'liability', 0),
(3, 'properties', 'asset', 0),
(4, 'product Sales', 'income', 0),
(5, 'owner quity', 'asset', 0);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `recipient` enum('all','business') DEFAULT 'all',
  `business_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `date_sent` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch`
--

CREATE TABLE `branch` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `contact` int(11) NOT NULL,
  `branch-key` varchar(255) NOT NULL,
  `business_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branch`
--

INSERT INTO `branch` (`id`, `name`, `location`, `contact`, `branch-key`, `business_id`) VALUES
(1, 'fish', 'Masaka', 2147483647, '1234', 1),
(2, 'mansa', 'gulu', 2147483647, '1234', 1),
(3, 'Kim branch1', 'Entebbe', 0, '12345', 3),
(4, 'Kim branch2', 'Entebbe', 0, 'Kampala1234', 3),
(5, 'CocaCola Ntinda', 'Ntinda', 769413480, '12345', 4);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branch_products`
--

CREATE TABLE `branch_products` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `stock_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `businesses`
--

CREATE TABLE `businesses` (
  `id` int(11) NOT NULL,
  `business_code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `admin_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `date_registered` datetime DEFAULT current_timestamp(),
  `status` enum('active','suspended') DEFAULT 'active',
  `subscription_start` date DEFAULT NULL,
  `subscription_end` date DEFAULT NULL,
  `subscription_status` enum('active','pending','expired') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `businesses`
--

INSERT INTO `businesses` (`id`, `business_code`, `name`, `admin_name`, `email`, `phone`, `address`, `date_registered`, `status`, `subscription_start`, `subscription_end`, `subscription_status`) VALUES
(1, '', 'Loko harvest', NULL, NULL, '25749859855', 'p.o.box272523', '2025-11-15 13:54:42', 'active', NULL, NULL, 'pending'),
(2, '', 'Kim Compnay LTd', NULL, NULL, '0785727208', 'Kampala', '2025-11-28 18:51:47', 'active', NULL, NULL, 'pending'),
(4, 'KCLK5722', 'Kim2 Company Ltd', 'kimManager2', 'kimk2@gmail.com', '0769413480', 'Kampala', '2025-11-28 20:30:21', 'active', NULL, NULL, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `cash_book`
--

CREATE TABLE `cash_book` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `cash` decimal(12,2) DEFAULT 0.00,
  `bank` decimal(12,2) DEFAULT 0.00,
  `discount` decimal(12,2) DEFAULT 0.00,
  `type` enum('receipt','payment') NOT NULL,
  `business_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cash_book`
--

INSERT INTO `cash_book` (`id`, `date`, `particulars`, `cash`, `bank`, `discount`, `type`, `business_id`) VALUES
(1, '2025-11-06', 'paying loan', 200000.00, 0.00, 0.00, 'payment', 0),
(2, '2025-11-06', 'sold goods ', 2000002.00, 0.00, 2.00, 'receipt', 0);

-- --------------------------------------------------------

--
-- Table structure for table `company_settings`
--

CREATE TABLE `company_settings` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `primary_color` varchar(7) DEFAULT '#4F46E5',
  `secondary_color` varchar(7) DEFAULT '#10B981',
  `qr_expiry_hours` int(11) DEFAULT 24
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_settings`
--

INSERT INTO `company_settings` (`id`, `company_name`, `logo`, `primary_color`, `secondary_color`, `qr_expiry_hours`) VALUES
(1, 'My Business', NULL, '#4F46E5', '#10B981', 24);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `credited_amount` decimal(10,2) DEFAULT 0.00,
  `account_balance` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount_credited` decimal(12,2) NOT NULL DEFAULT 0.00,
  `opening_date` date DEFAULT curdate(),
  `business_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `contact`, `email`, `payment_method`, `credited_amount`, `account_balance`, `created_at`, `amount_credited`, `opening_date`, `business_id`) VALUES
(3, 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 'cheque', 0.00, 0.00, '2025-10-25 14:00:15', 12000.00, '2025-10-25', 0),
(5, 'Test 2', '73729283839', 'test2@gmail.com', 'Efris', 0.00, 161000.00, '2025-11-01 22:15:30', 0.00, '2025-11-01', 0),
(6, 'Mega standard', '465789098765', 'mega@gmail.com', 'Invoice', 0.00, 0.00, '2025-11-15 21:30:29', 48000.00, '2025-11-15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customer_transactions`
--

CREATE TABLE `customer_transactions` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `date_time` datetime DEFAULT current_timestamp(),
  `products_bought` text DEFAULT NULL,
  `amount_paid` decimal(12,2) DEFAULT 0.00,
  `amount_credited` decimal(12,2) DEFAULT 0.00,
  `sold_by` varchar(255) DEFAULT NULL,
  `status` varchar(32) DEFAULT 'pending',
  `invoice_receipt_no` varchar(32) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `business_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_transactions`
--

INSERT INTO `customer_transactions` (`id`, `customer_id`, `branch_id`, `date_time`, `products_bought`, `amount_paid`, `amount_credited`, `sold_by`, `status`, `invoice_receipt_no`, `due_date`, `business_id`) VALUES
(2, 5, 2, '2025-11-19 09:11:09', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', 20000.00, 0.00, 'Aisha', 'paid', 'RP-09504', NULL, 0),
(3, 3, 2, '2025-11-19 09:12:07', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', 0.00, 20000.00, 'Aisha', 'paid', 'INV-00024', NULL, 0),
(4, 3, NULL, '2025-11-19 09:25:24', 'Payment for invoice number INV-00024', 20000.00, 0.00, 'Aisha', 'paid', 'RP-09505', NULL, 0),
(5, 6, 2, '2025-12-18 10:36:31', '[{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":20},{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":30},{\"id\":\"35\",\"name\":\"Files\",\"price\":2000,\"quantity\":20}]', 0.00, 110000.00, 'Aisha', 'paid', 'RP-09516', NULL, 0),
(6, 6, NULL, '2025-12-18 10:48:36', 'Payment for invoice number RP-09516', 110000.00, 0.00, 'Aisha', 'paid', 'RP-09517', NULL, 0),
(7, 6, 2, '2025-12-18 11:21:00', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', 0.00, 12000.00, 'Aisha', 'debtor', 'RP-09518', NULL, 0),
(8, 6, 2, '2025-12-18 11:34:12', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', 0.00, 12000.00, 'Aisha', 'debtor', 'RP-09519', NULL, 0),
(9, 6, 2, '2025-12-18 11:39:07', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', 0.00, 12000.00, 'Aisha', 'debtor', 'RP-09520', NULL, 0),
(10, 3, 2, '2025-12-18 11:48:40', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', 0.00, 12000.00, 'Aisha', 'debtor', 'RP-09521', NULL, 0),
(11, 6, 2, '2025-12-18 13:12:22', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', 0.00, 12000.00, 'Aisha', 'debtor', 'RP-09538', NULL, 0),
(12, 6, 2, '2025-12-19 17:48:39', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', 0.00, 2000.00, 'Aisha', 'paid', 'INV-00026', NULL, 0),
(13, 6, NULL, '2025-12-19 17:49:14', 'Payment for invoice number INV-00026', 2000.00, 0.00, 'Aisha', 'paid', 'RP-09542', NULL, 0),
(14, 3, 2, '2025-12-19 18:03:43', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', 0.00, 12000.00, 'Aisha', 'paid', 'INV-00027', NULL, 0),
(15, 3, 2, '2025-12-19 18:14:45', 'Repayment of invoice INV-00027 (maize x1 - taken on Dec 19, 2025)', 12000.00, 0.00, 'Aisha', 'paid', 'RP-09545', NULL, 0),
(16, 3, 2, '2026-01-09 11:05:32', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', 0.00, 2000.00, 'Aisha', 'paid', 'INV-00029', NULL, 0),
(17, 3, 2, '2026-01-09 11:05:52', 'Repayment of invoice INV-00029 (onions x2 - taken on Jan 09, 2026)', 2000.00, 0.00, 'Aisha', 'paid', 'RP-09547', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `debtors`
--

CREATE TABLE `debtors` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `debtor_name` varchar(100) NOT NULL,
  `debtor_contact` varchar(50) DEFAULT NULL,
  `debtor_email` varchar(100) DEFAULT NULL,
  `quantity_taken` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `amount_paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_paid` tinyint(1) NOT NULL DEFAULT 0,
  `branch_id` int(11) NOT NULL,
  `item_taken` varchar(255) NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `products_json` text DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(32) DEFAULT NULL,
  `receipt_no` varchar(32) DEFAULT NULL,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debtors`
--

INSERT INTO `debtors` (`id`, `date`, `time`, `debtor_name`, `debtor_contact`, `debtor_email`, `quantity_taken`, `payment_method`, `amount_paid`, `balance`, `is_paid`, `branch_id`, `item_taken`, `created_by`, `created_at`, `products_json`, `customer_id`, `invoice_no`, `receipt_no`, `due_date`) VALUES
(1, '0000-00-00', '00:00:00', 'Omar', '', '', 0, NULL, 0.00, 0.00, 0, 2, '', '2', '2025-09-17 13:28:47', NULL, NULL, NULL, NULL, NULL),
(17, '0000-00-00', '00:00:00', 'OMAR', '26565256565', 'katsomar60@gmail.com', 2, NULL, 0.00, 2000.00, 0, 2, 'onions', '2', '2025-11-05 13:27:37', NULL, NULL, NULL, NULL, NULL),
(18, '0000-00-00', '00:00:00', 'OMAR', '26565256565', 'katsomar60@gmail.com', 2, NULL, 0.00, 2000.00, 0, 2, 'onions', '2', '2025-11-05 13:27:56', NULL, NULL, NULL, NULL, NULL),
(19, '0000-00-00', '00:00:00', 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 20, NULL, 0.00, 240000.00, 0, 2, 'maize', '2', '2025-11-15 23:15:39', NULL, NULL, NULL, NULL, NULL),
(20, '0000-00-00', '00:00:00', 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 20, NULL, 0.00, 240000.00, 0, 2, 'maize', '2', '2025-11-15 23:16:51', NULL, NULL, NULL, NULL, NULL),
(21, '0000-00-00', '00:00:00', 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 15, NULL, 0.00, 30000.00, 0, 2, 'Biscuits', '2', '2025-11-15 23:18:41', NULL, NULL, NULL, NULL, NULL),
(22, '0000-00-00', '00:00:00', 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 15, NULL, 0.00, 30000.00, 0, 2, 'Biscuits', '2', '2025-11-15 23:18:56', NULL, NULL, NULL, NULL, NULL),
(23, '0000-00-00', '00:00:00', 'Ben ten', '56789007897', 'ben@gmail.com', 20, NULL, 0.00, 20000.00, 0, 2, 'onions', '2', '2025-11-15 23:20:44', NULL, NULL, NULL, NULL, NULL),
(24, '0000-00-00', '00:00:00', 'Ben ten', '56789007897', 'ben@gmail.com', 20, NULL, 0.00, 20000.00, 0, 2, 'onions', '2', '2025-11-15 23:20:50', NULL, NULL, NULL, NULL, NULL),
(25, '0000-00-00', '00:00:00', 'Ben ten', '56789007897', 'ben@gmail.com', 20, NULL, 0.00, 20000.00, 0, 2, 'onions', '2', '2025-11-15 23:21:09', NULL, NULL, NULL, NULL, NULL),
(26, '0000-00-00', '00:00:00', 'Ben ten', '56789007897', 'ben@gmail.com', 20, NULL, 0.00, 20000.00, 0, 2, 'onions', '2', '2025-11-15 23:23:29', NULL, NULL, NULL, NULL, NULL),
(28, '0000-00-00', '00:00:00', 'Mega standard', '465789098765', 'mega@gmail.com', 20, NULL, 0.00, 100000.00, 0, 2, 'mukene plus', '2', '2025-11-15 23:24:03', NULL, NULL, NULL, NULL, NULL),
(29, '0000-00-00', '00:00:00', 'Mega standard', '465789098765', 'mega@gmail.com', 20, NULL, 0.00, 100000.00, 0, 2, 'mukene plus', '2', '2025-11-15 23:24:10', NULL, NULL, NULL, NULL, NULL),
(30, '0000-00-00', '00:00:00', 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 2, NULL, 0.00, 40000.00, 0, 2, 'Loko eggs', '2', '2025-11-15 23:28:57', NULL, NULL, NULL, NULL, NULL),
(31, '0000-00-00', '00:00:00', 'Omar Muammar', '0771827046', 'katsomar60@gmail.com', 2, NULL, 0.00, 2000.00, 0, 2, 'onions', '2', '2025-11-15 23:30:43', NULL, NULL, NULL, NULL, NULL),
(47, '0000-00-00', '00:00:00', 'Ben ten', '56789007897', 'ben@gmail.com', 2, NULL, 0.00, 2000.00, 0, 2, 'onions', '2', '2025-11-16 14:02:45', NULL, NULL, NULL, NULL, NULL),
(49, '0000-00-00', '00:00:00', 'lukwago', '268265', 'katsomar60@gmail.com', 1, NULL, 0.00, 1000.00, 0, 2, 'onions', '2', '2025-11-16 14:22:37', NULL, NULL, NULL, NULL, NULL),
(51, '0000-00-00', '00:00:00', 'Jerry', '26565256565', 'katsomar60@gmail.com', 2, NULL, 24000.00, 0.00, 1, 2, 'maize', '2', '2025-11-16 14:44:01', NULL, NULL, NULL, 'RP-1459', NULL),
(52, '0000-00-00', '00:00:00', 'Jerry', '26565256565', 'katsomar60@gmail.com', 2, NULL, 24000.00, 0.00, 1, 2, 'maize', '2', '2025-11-16 14:44:08', NULL, NULL, NULL, 'RP-6285', NULL),
(54, '0000-00-00', '00:00:00', 'OMAR', '84894861416', 'katsomar60@gmail.com', 2, NULL, 0.00, 24000.00, 0, 2, 'maize', '2', '2025-11-16 15:07:23', NULL, NULL, NULL, NULL, NULL),
(55, '0000-00-00', '00:00:00', 'OMAR', '84894861416', 'katsomar60@gmail.com', 2, NULL, 0.00, 24000.00, 0, 2, 'maize', '2', '2025-11-16 15:07:38', NULL, NULL, NULL, NULL, NULL),
(58, '0000-00-00', '00:00:00', 'den', '46681464544', 'katsomar60@gmail.com', 15, NULL, 0.00, 147000.00, 0, 2, 'mukene, gonja, Vg kit', '2', '2025-11-17 06:31:37', NULL, NULL, NULL, NULL, NULL),
(60, '0000-00-00', '00:00:00', 'Ben ten', '56789007897', 'ben@gmail.com', 10, NULL, 0.00, 20000.00, 0, 2, 'mukene, onions, Sumz cookies', '2', '2025-11-17 06:45:29', '[{\"id\":\"24\",\"name\":\"mukene\",\"price\":1000,\"quantity\":6},{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2},{\"id\":\"38\",\"name\":\"Sumz cookies\",\"price\":6000,\"quantity\":2}]', NULL, NULL, NULL, NULL),
(61, '0000-00-00', '00:00:00', 'OMAR', '2548454', 'katsomar60@gmail.com', 4, NULL, 0.00, 40000.00, 0, 2, 'gonja x2, Jesa milk x2', '2', '2025-11-17 06:50:39', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2},{\"id\":\"37\",\"name\":\"Jesa milk\",\"price\":5000,\"quantity\":2}]', NULL, NULL, NULL, NULL),
(67, '0000-00-00', '00:00:00', 'Ben ten', '', 'ben@gmail.com', 2, NULL, 0.00, 30000.00, 0, 2, 'gonja x2', '2', '2025-11-17 11:27:28', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL, 'INV-00005', NULL, '2025-11-20'),
(68, '0000-00-00', '00:00:00', 'Ben ten', '', 'ben@gmail.com', 3, NULL, 0.00, 13000.00, 0, 2, 'onions x1, Sumz cookies x2', '2', '2025-11-17 11:28:07', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1},{\"id\":\"38\",\"name\":\"Sumz cookies\",\"price\":6000,\"quantity\":2}]', NULL, 'INV-00006', NULL, '2025-11-18'),
(69, '0000-00-00', '00:00:00', 'Ben ten', '3456789087654', 'ben@gmail.com', 2, NULL, 0.00, 30000.00, 0, 2, 'gonja x2', '2', '2025-11-17 13:45:19', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL, 'INV-00007', NULL, NULL),
(71, '0000-00-00', '00:00:00', 'Jane', '4654645', 'katsomar60@gmail.com', 2, NULL, 0.00, 4000.00, 0, 2, 'Biscuits x2', '2', '2025-11-17 14:32:58', '[{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":2}]', NULL, 'INV-00009', NULL, '2025-11-20'),
(72, '0000-00-00', '00:00:00', 'ben', '2345675432', 'k@gmail.com', 1, NULL, 0.00, 1000.00, 0, 2, 'onions x1', '2', '2025-11-18 21:27:13', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL, 'INV-00010', NULL, NULL),
(73, '0000-00-00', '00:00:00', 'br4mr', '3243212', 'b@gmail.com', 2, NULL, 0.00, 4000.00, 0, 2, 'Biscuits x2', '2', '2025-11-18 21:34:51', '[{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":2}]', NULL, 'INV-00011', NULL, NULL),
(74, '0000-00-00', '00:00:00', 'jhkl;', '878908908790', 'v@gmail.com', 1, NULL, 0.00, 15000.00, 0, 2, 'gonja x1', '2', '2025-11-18 21:48:08', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL, 'INV-00012', NULL, NULL),
(75, '0000-00-00', '00:00:00', 'JHGFDSFGHJ', '4564324567', 'v@gmail.com', 1, NULL, 0.00, 15000.00, 0, 2, 'gonja x1', '2', '2025-11-18 21:58:50', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL, 'INV-00013', NULL, NULL),
(76, '0000-00-00', '00:00:00', 'jhgfdxdcgvhbjnk', '9087657890', 'v@gmail.com', 1, NULL, 0.00, 6000.00, 0, 2, 'Vaseline x1', '2', '2025-11-18 22:14:09', '[{\"id\":\"40\",\"name\":\"Vaseline\",\"price\":6000,\"quantity\":1}]', NULL, 'INV-00017', NULL, NULL),
(77, '0000-00-00', '00:00:00', 'ghfdfhkjl', '456789087654', 'b@gmail.com', 1, NULL, 0.00, 20000.00, 0, 2, 'Loko eggs x1', '2', '2025-11-19 09:11:43', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', NULL, 'INV-00023', NULL, '2025-11-20'),
(78, '0000-00-00', '00:00:00', 'lkfghjkl;kjh', '5265254264', 'a@gmailcom', 1, NULL, 0.00, 6000.00, 0, 2, 'Sumz cookies x1', '2', '2025-12-18 11:50:02', '[{\"id\":\"38\",\"name\":\"Sumz cookies\",\"price\":6000,\"quantity\":1}]', NULL, 'RP-09522', NULL, NULL),
(79, '0000-00-00', '00:00:00', 'jhlk;sdff', '980734920', 'a@gmailcom', 1, NULL, 0.00, 1000.00, 0, 2, 'onions x1', '2', '2025-12-18 11:54:54', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL, 'RP-09523', NULL, NULL),
(80, '0000-00-00', '00:00:00', 'jhlk;sdff', '980734920', 'a@gmailcom', 1, NULL, 0.00, 1000.00, 0, 2, 'onions x1', '2', '2025-12-18 12:03:17', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL, 'RP-09524', NULL, NULL),
(81, '0000-00-00', '00:00:00', 'jhlk;sdff', '980734920', 'a@gmailcom', 1, NULL, 0.00, 1000.00, 0, 2, 'onions x1', '2', '2025-12-18 12:05:29', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL, 'RP-09525', NULL, NULL),
(82, '0000-00-00', '00:00:00', 'jhlk;sdff', '980734920', 'a@gmailcom', 1, NULL, 0.00, 1000.00, 0, 2, 'onions x1', '2', '2025-12-18 12:05:31', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL, 'RP-09526', NULL, NULL),
(83, '0000-00-00', '00:00:00', 'kiuhgvbuyghbj', '6545456', 'a@gmail.com', 1, NULL, 0.00, 15000.00, 0, 2, 'gonja x1', '2', '2025-12-18 12:09:57', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL, 'RP-09527', NULL, NULL),
(84, '0000-00-00', '00:00:00', 'kiuhgvbuyghbj', '6545456', 'a@gmail.com', 1, NULL, 0.00, 15000.00, 0, 2, 'gonja x1', '2', '2025-12-18 12:19:58', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL, 'RP-09528', NULL, NULL),
(85, '0000-00-00', '00:00:00', 'lkjbjhkkl;kj', '554554', 'a@gmailcom', 1, NULL, 0.00, 50000.00, 0, 2, 'Perfume x1', '2', '2025-12-18 12:20:19', '[{\"id\":\"39\",\"name\":\"Perfume\",\"price\":50000,\"quantity\":1}]', NULL, 'RP-09529', NULL, NULL),
(86, '0000-00-00', '00:00:00', 'lkjbjhkkl;kj', '554554', 'a@gmailcom', 1, NULL, 0.00, 50000.00, 0, 2, 'Perfume x1', '2', '2025-12-18 12:24:03', '[{\"id\":\"39\",\"name\":\"Perfume\",\"price\":50000,\"quantity\":1}]', NULL, 'RP-09530', NULL, NULL),
(87, '0000-00-00', '00:00:00', 'kljhgfhjklm;l', '96554563654', 'a@gmailcom', 1, NULL, 0.00, 12000.00, 0, 2, 'maize x1', '2', '2025-12-18 12:28:25', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', NULL, 'RP-09531', NULL, NULL),
(88, '0000-00-00', '00:00:00', 'kljhgfhjklm;l', '96554563654', 'a@gmailcom', 1, NULL, 0.00, 12000.00, 0, 2, 'maize x1', '2', '2025-12-18 12:39:42', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', NULL, 'RP-09532', NULL, NULL),
(89, '0000-00-00', '00:00:00', 'kljhgfhjk', '896546554', 'a@gmail.com', 1, NULL, 0.00, 2000.00, 0, 2, 'Files x1', '2', '2025-12-18 12:40:01', '[{\"id\":\"35\",\"name\":\"Files\",\"price\":2000,\"quantity\":1}]', NULL, 'RP-09533', NULL, NULL),
(90, '0000-00-00', '00:00:00', 'nvbnm', '8645564254', 'a@gmailcom', 1, NULL, 0.00, 12000.00, 0, 2, 'maize x1', '2', '2025-12-18 12:41:08', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', NULL, 'RP-09534', NULL, NULL),
(91, '0000-00-00', '00:00:00', 'kjlkhjklkhk', '656564645', 'a@gmailcom', 1, NULL, 0.00, 12000.00, 0, 2, 'maize x1', '2', '2025-12-18 12:43:24', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', NULL, 'RP-09535', NULL, NULL),
(92, '0000-00-00', '00:00:00', 'kjlkhjklkhk', '656564645', 'a@gmailcom', 1, NULL, 0.00, 12000.00, 0, 2, 'maize x1', '2', '2025-12-18 13:10:31', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', NULL, 'RP-09536', NULL, NULL),
(93, '0000-00-00', '00:00:00', 'kjhgfghjk', '54216541256', 'a@gmailcom', 1, NULL, 0.00, 1000.00, 0, 2, 'onions x1', '2', '2025-12-18 13:11:25', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL, 'RP-09537', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user-id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `base_salary` float NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` int(11) DEFAULT NULL,
  `position` varchar(255) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL,
  `business_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user-id`, `name`, `branch-id`, `base_salary`, `email`, `phone`, `position`, `hire_date`, `status`, `business_id`) VALUES
(13, 2, '', 2, 0, NULL, NULL, NULL, NULL, 'Active', 1),
(14, 8, 'Omar Muammar', 2, 200000, 'katsomar60@gmail.com', 771827046, 'manager', '2025-11-01', 'Active', 1),
(15, 6, '', 0, 0, NULL, NULL, NULL, NULL, 'Active', 1),
(16, 13, 'Sample Employee1', 1, 100000, NULL, NULL, 'driver', NULL, 'Active', 3),
(17, 13, 'Sample Employee2', 1, 100000, NULL, NULL, 'driver', NULL, 'Active', 3),
(19, 26, '', 0, 0, NULL, NULL, NULL, NULL, 'Active', 0),
(20, 27, '', 0, 0, NULL, NULL, NULL, NULL, 'Active', 0),
(21, 3, '', 2, 0, NULL, NULL, NULL, NULL, 'Active', 0),
(22, 4, '', 1, 0, NULL, NULL, NULL, NULL, 'Active', 0);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `supplier_id` int(11) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `product` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `spent-by` int(11) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `date`, `supplier_id`, `branch-id`, `category`, `product`, `quantity`, `unit_price`, `amount`, `spent-by`, `description`) VALUES
(28, '2025-11-07 00:00:00', 24, 2, 'Food', 4, 40, 3000.00, 120000.00, 2, NULL),
(29, '2025-11-07 00:00:00', 24, 2, 'Food', 5, 20, 14500.00, 290000.00, 2, NULL),
(30, '2025-11-07 00:00:00', 24, 2, 'Food', 6, 50, 5000.00, 250000.00, 2, NULL),
(31, '2025-11-07 00:00:00', 24, 2, 'Food', 3, 20, 9000.00, 180000.00, 2, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payment_proofs`
--

CREATE TABLE `payment_proofs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `order_reference` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `payment_method` enum('MTN Merchant','Airtel Merchant') NOT NULL,
  `delivery_location` text NOT NULL,
  `screenshot_path` varchar(255) NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_proofs`
--

INSERT INTO `payment_proofs` (`id`, `order_id`, `branch_id`, `order_reference`, `customer_name`, `customer_phone`, `payment_method`, `delivery_location`, `screenshot_path`, `status`, `verified_by`, `verified_at`, `created_at`) VALUES
(1, 18, 2, 'ORD-2025-129055', 'Omar', '98465618468354', 'MTN Merchant', 'Nakawuka Mbalala', 'proof_18_1766008884.png', 'verified', 1, '2025-12-17 23:15:17', '2025-12-17 22:01:24'),
(2, 19, 2, 'ORD-2025-055339', 'Omar', '07845616464', 'Airtel Merchant', 'Mbaale lwakaka', 'proof_19_1766009125.png', 'verified', 1, '2025-12-17 23:16:20', '2025-12-17 22:05:25'),
(3, 20, 2, 'ORD-2025-188101', 'Ben', '6102132131', 'MTN Merchant', 'Abaita', 'proof_20_1766014191.png', 'pending', NULL, NULL, '2025-12-17 23:29:51'),
(4, 23, 2, 'ORD-2025-412208', 'Omar', '0771824697', 'MTN Merchant', 'ndagire', 'proof_23_1766063499.png', 'pending', NULL, NULL, '2025-12-18 13:11:39');

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `user-id` int(11) NOT NULL,
  `base_salary` decimal(10,0) NOT NULL,
  `transport` decimal(10,0) DEFAULT NULL,
  `housing` decimal(10,0) DEFAULT NULL,
  `medical` decimal(10,0) DEFAULT NULL,
  `overtime` decimal(10,0) DEFAULT NULL,
  `nssf` decimal(10,0) DEFAULT NULL,
  `tax` decimal(10,0) DEFAULT NULL,
  `loan` decimal(10,0) DEFAULT NULL,
  `other_deductions` decimal(10,0) DEFAULT NULL,
  `gross_salary` decimal(10,0) NOT NULL,
  `net_salary` decimal(10,0) NOT NULL,
  `month` decimal(10,0) NOT NULL,
  `status` enum('Pending','Paid') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `staff_id` int(11) NOT NULL,
  `amount` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll`
--

INSERT INTO `payroll` (`id`, `user-id`, `base_salary`, `transport`, `housing`, `medical`, `overtime`, `nssf`, `tax`, `loan`, `other_deductions`, `gross_salary`, `net_salary`, `month`, `status`, `created_at`, `staff_id`, `amount`) VALUES
(15, 14, 200000, 20000, 20000, 20000, 0, 150000, 0, 0, 0, 260000, 110000, 2025, 'Paid', '2025-11-03 09:21:05', 0, 0),
(16, 14, 200000, 5000, 0, 10000, 0, 15000, 0, 0, 0, 215000, 200000, 2025, 'Paid', '2026-01-17 08:48:04', 0, 0),
(17, 14, 200000, 0, 0, 0, 0, 0, 0, 0, 0, 200000, 200000, 2025, 'Paid', '2026-01-17 08:47:38', 0, 0),
(18, 14, 200000, 0, 0, 0, 0, 0, 0, 0, 0, 200000, 200000, 2025, 'Paid', '2025-11-15 12:28:36', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_balance`
--

CREATE TABLE `petty_cash_balance` (
  `id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('add','remove') NOT NULL,
  `created_at` datetime NOT NULL,
  `approved_by` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petty_cash_balance`
--

INSERT INTO `petty_cash_balance` (`id`, `amount`, `type`, `created_at`, `approved_by`) VALUES
(4, 500000.00, 'add', '2025-11-01 13:36:13', NULL),
(5, 4000.00, 'add', '2025-11-01 13:38:13', NULL),
(6, 100000.00, 'add', '2025-11-01 13:38:26', NULL),
(7, 396000.00, 'add', '2025-11-01 13:38:39', NULL),
(8, 300000.00, 'remove', '2025-11-01 13:53:08', 'Victor kiberu');

-- --------------------------------------------------------

--
-- Table structure for table `petty_cash_transactions`
--

CREATE TABLE `petty_cash_transactions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `purpose` enum('company','personal') NOT NULL,
  `reason` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance` decimal(15,2) NOT NULL,
  `approved_by` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `action_type` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petty_cash_transactions`
--

INSERT INTO `petty_cash_transactions` (`id`, `name`, `branch_id`, `purpose`, `reason`, `amount`, `balance`, `approved_by`, `created_at`, `action_type`) VALUES
(1, 'Omar Muammar', 2, 'company', 'Electricity Bill', 30000.00, 0.00, 'Victor kiberu', '2025-11-01 11:39:28', NULL),
(2, 'Omar Muammar', 2, 'personal', 'Advance salary', 150000.00, 0.00, 'Victor kiberu', '2025-11-01 11:39:58', NULL),
(3, 'Omar Muammar', 2, 'personal', 'Advance salary', 150000.00, 0.00, 'Victor kiberu', '2025-11-01 11:40:19', 'repaid'),
(4, 'Omar Muammar', 2, 'company', 'Pornography ', 20000.00, 0.00, 'Omar', '2025-11-01 23:35:17', NULL),
(5, 'Omar Muammar', 2, 'company', 'salary advance', 50000.00, 0.00, 'Victor kiberu', '2025-11-02 14:54:03', NULL),
(6, 'jonhnson', 2, 'personal', 'more money', 20000.00, 0.00, 'Victor kiberu', '2025-11-02 14:54:30', NULL),
(7, 'jonhnson', 2, 'personal', 'more money', 20000.00, 0.00, 'Victor kiberu', '2025-11-02 14:54:40', 'repaid');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `buying-price` decimal(10,0) DEFAULT NULL,
  `selling-price` decimal(10,0) DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `branch-id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `location` varchar(20) DEFAULT 'shelf',
  `image_path` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `barcode`, `category`, `buying-price`, `selling-price`, `stock`, `branch-id`, `business_id`, `expiry_date`, `sms_sent`, `location`, `image_path`, `visible`) VALUES
(12, 'Dogs', NULL, NULL, 12000, 15000, 999, 0, 1, '2025-09-14', 0, 'shelf', NULL, 1),
(13, 'fish', NULL, NULL, 12000, 15000, 540, 0, 1, NULL, 0, 'shelf', NULL, 1),
(16, 'greens', NULL, NULL, 10000, 15000, 50, 0, 1, NULL, 0, 'shelf', NULL, 1),
(17, 'maize', NULL, NULL, 10000, 12000, 721, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_17_1767781946.jpg', 1),
(18, 'goilla', NULL, NULL, 2000, 12000, 14, 1, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_18_1767778994.jpg', 1),
(19, 'onions', NULL, NULL, 500, 1000, 793, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_19_1767782520.jpg', 1),
(21, 'gonja', NULL, NULL, 12000, 15000, 903, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_21_1767782642.jpg', 1),
(24, 'mukene', NULL, NULL, 2000, 1000, 478, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_24_1767782498.jpg', 1),
(25, 'mukene plus', NULL, NULL, 10000, 5000, 927, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_25_1767782509.jpg', 1),
(35, 'Files', '6164004669541', NULL, 3000, 2000, 472, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_35_1767782686.jpg', 1),
(37, 'Jesa milk', '6161103270500', NULL, 2000, 5000, 19, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_37_1767782623.jpg', 1),
(41, 'Vg kit', '8903489001426', NULL, 10000, 20000, 8, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_41_1767782529.jpg', 1),
(42, 'Book', '6164072733045', NULL, 800, 1000, 0, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_42_1767782711.jpg', 1),
(46, 'Eversest', '6164001011510', NULL, 20000, 25000, 0, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_46_1767782699.jpg', 1),
(47, 'Water', '6009622620027', NULL, 2000, 5000, 50, 2, 1, NULL, 0, 'shelf', 'uploads/product_images/prod_47_1767781933.jpg', 1),
(48, 'benzen', '5', NULL, 200, 50000, 300000, 2, 0, '2025-12-30', 0, 'shelf', 'uploads/product_images/prod_48_1767782722.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `profits`
--

CREATE TABLE `profits` (
  `date` date NOT NULL,
  `id` int(11) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `total` decimal(10,0) NOT NULL,
  `expenses` decimal(10,0) NOT NULL,
  `net-profits` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `profits`
--

INSERT INTO `profits` (`date`, `id`, `branch-id`, `business_id`, `total`, `expenses`, `net-profits`) VALUES
('2025-09-05', 1, 5, 4, 405000, 20000, 385000),
('2025-09-06', 2, 2, 0, 1859000, 0, 1859000),
('2025-09-06', 3, 1, 0, 6000, 0, 6000),
('2025-09-07', 4, 2, 0, 14000, 0, 14000),
('2025-09-14', 5, 2, 0, 4000, 0, 4000),
('2025-09-15', 6, 2, 0, 5000, 0, 5000),
('2025-09-17', 7, 2, 0, 5561000, 0, 5561000),
('2025-09-18', 8, 2, 0, 15000, 0, 15000),
('2025-09-30', 9, 2, 0, 413500, 0, 413500),
('2025-10-16', 10, 2, 0, 375000, 0, 375000),
('2025-10-17', 11, 2, 0, 449000, 0, 449000),
('2025-10-17', 12, 1, 0, 150000, 0, 150000),
('2025-10-19', 13, 2, 0, -31382000, 0, -31382000),
('2025-10-22', 14, 2, 0, -95000, 0, -95000),
('2025-10-23', 15, 2, 0, -55500, 0, -55500),
('2025-10-25', 16, 2, 0, -181000, 0, -181000),
('2025-11-01', 17, 2, 0, 312000, 0, 312000),
('2025-11-02', 18, 2, 0, -58000, 0, -58000),
('2025-11-03', 19, 2, 0, 628500, 0, 628500),
('2025-11-05', 20, 2, 0, 160400, 0, 160400),
('2025-11-06', 21, 2, 0, 280500, 0, 280500),
('2025-11-09', 22, 2, 0, -60000, 0, -60000),
('2025-11-15', 23, 2, 0, 277000, 0, 277000),
('2025-11-16', 24, 2, 0, 12500, 0, 12500),
('2025-11-17', 25, 2, 0, 99200, 0, 99200),
('2025-11-18', 26, 2, 0, 15000, 0, 15000),
('2025-11-19', 27, 2, 0, 10000, 0, 10000),
('2025-12-17', 28, 2, 0, 62200, 0, 62200),
('2025-12-18', 29, 2, 0, 1400, 0, 1400),
('2025-12-19', 30, 2, 0, 1494001000, 0, 1494001000),
('2026-07-17', 31, 2, 0, 4500, 0, 4500);

-- --------------------------------------------------------

--
-- Table structure for table `receipt_counter`
--

CREATE TABLE `receipt_counter` (
  `id` int(11) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0,
  `prefix` varchar(10) NOT NULL DEFAULT 'RP',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipt_counter`
--

INSERT INTO `receipt_counter` (`id`, `last_number`, `prefix`, `updated_at`) VALUES
(1, 9549, 'RP', '2026-07-17 06:27:29'),
(2, 29, 'INV', '2026-01-09 10:05:32'),
(4, 5, 'RC', '2025-12-17 19:08:16');

-- --------------------------------------------------------

--
-- Table structure for table `receipt_sequence`
--

CREATE TABLE `receipt_sequence` (
  `id` int(11) NOT NULL,
  `prefix` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipt_sequence`
--

INSERT INTO `receipt_sequence` (`id`, `prefix`, `date`, `last_number`) VALUES
(1, 'RC', '2025-12-17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `remote_orders`
--

CREATE TABLE `remote_orders` (
  `id` int(11) NOT NULL,
  `order_reference` varchar(50) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `payment_method` enum('cash','mobile_money','card','online') DEFAULT 'cash',
  `delivery_location` text DEFAULT NULL,
  `expected_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','ready','finished','cancelled','expired') DEFAULT 'pending',
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` datetime DEFAULT NULL,
  `qr_code` text DEFAULT NULL,
  `qr_code_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `completed_at` datetime DEFAULT NULL,
  `cancelled_at` datetime DEFAULT NULL,
  `customer_location_lat` decimal(10,8) DEFAULT NULL,
  `customer_location_lng` decimal(11,8) DEFAULT NULL,
  `customer_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remote_orders`
--

INSERT INTO `remote_orders` (`id`, `order_reference`, `branch_id`, `customer_name`, `customer_phone`, `payment_method`, `delivery_location`, `expected_amount`, `status`, `processed_by`, `processed_at`, `qr_code`, `qr_code_expires_at`, `created_at`, `updated_at`, `completed_at`, `cancelled_at`, `customer_location_lat`, `customer_location_lng`, `customer_address`) VALUES
(1, 'ORD-2025-437102', 1, 'OMAR', '154867146814', 'cash', NULL, 28000.00, 'pending', NULL, NULL, 'eyJvcmRlcl9pZCI6MSwib3JkZXJfcmVmZXJlbmNlIjoiT1JELTIwMjUtNDM3MTAyIiwidGltZXN0YW1wIjoxNzY1OTEwOTM5LCJ0eXBlIjoicmVtb3RlX29yZGVyIn0=', '2025-12-17 21:48:59', '2025-12-16 18:48:59', '2025-12-16 18:48:59', NULL, NULL, NULL, NULL, NULL),
(2, 'ORD-2025-148913', 2, 'Omar Muammar', '04324234324', 'cash', NULL, 162000.00, 'pending', NULL, NULL, 'eyJvcmRlcl9pZCI6Miwib3JkZXJfcmVmZXJlbmNlIjoiT1JELTIwMjUtMTQ4OTEzIiwidGltZXN0YW1wIjoxNzY1OTExOTI1LCJ0eXBlIjoicmVtb3RlX29yZGVyIn0=', '2025-12-17 22:05:25', '2025-12-16 19:05:25', '2025-12-16 19:05:25', NULL, NULL, NULL, NULL, NULL),
(6, 'ORD-2025-860316', 2, 'Omar', '07845616464', 'cash', NULL, 25000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6Niwib3JkZXJfcmVmZXJlbmNlIjoiT1JELTIwMjUtODYwMzE2IiwidGltZXN0YW1wIjoxNzY1OTc2NDc0LCJ0eXBlIjoicmVtb3RlX29yZGVyIn0=', '2025-12-18 16:01:14', '2025-12-17 13:01:14', '2025-12-17 13:12:55', '2025-12-17 16:12:55', NULL, NULL, NULL, NULL),
(7, 'ORD-2025-134560', 2, 'Omar Muammar', '0771827425', 'cash', NULL, 6000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6Nywib3JkZXJfcmVmZXJlbmNlIjoiT1JELTIwMjUtMTM0NTYwIiwidGltZXN0YW1wIjoxNzY1OTc3NDczLCJ0eXBlIjoicmVtb3RlX29yZGVyIn0=', '2025-12-18 16:17:53', '2025-12-17 13:17:53', '2025-12-17 13:24:05', '2025-12-17 16:24:05', NULL, NULL, NULL, NULL),
(8, 'ORD-2025-175082', 2, 'Omar Muammar', '0771827425', 'cash', NULL, 2000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6OCwib3JkZXJfcmVmZXJlbmNlIjoiT1JELTIwMjUtMTc1MDgyIiwidGltZXN0YW1wIjoxNzY1OTk4NDc3LCJ0eXBlIjoicmVtb3RlX29yZGVyIn0=', '2025-12-18 22:07:57', '2025-12-17 19:07:57', '2025-12-17 19:08:16', '2025-12-17 20:08:16', NULL, NULL, NULL, NULL),
(9, 'ORD-2025-543495', 2, 'Omar Muammar', '0771827425', 'cash', NULL, 25000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6OSwib3JkZXJfcmVmZXJlbmNlIjoiT1JELTIwMjUtNTQzNDk1IiwidGltZXN0YW1wIjoxNzY1OTk5MjI0LCJ0eXBlIjoicmVtb3RlX29yZGVyIn0=', '2025-12-18 22:20:24', '2025-12-17 19:20:24', '2025-12-17 19:21:07', '2025-12-17 20:21:07', NULL, NULL, NULL, NULL),
(10, 'ORD-2025-235111', 2, 'Omar Muammar', '0771827425', 'cash', NULL, 25000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MTAsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTIzNTExMSIsInRpbWVzdGFtcCI6MTc2NTk5OTYwMywidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-18 22:26:43', '2025-12-17 19:26:43', '2025-12-17 19:27:12', '2025-12-17 20:27:12', NULL, NULL, NULL, NULL),
(11, 'ORD-2025-041908', 2, 'Omar Muammar', '0771827425', 'cash', NULL, 25000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MTEsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTA0MTkwOCIsInRpbWVzdGFtcCI6MTc2NjAwMDU3MywidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-18 22:42:53', '2025-12-17 19:42:53', '2025-12-17 20:14:31', '2025-12-17 21:14:31', NULL, NULL, NULL, NULL),
(12, 'ORD-2025-865878', 2, 'Omar', '87548846646', 'cash', NULL, 27000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MTIsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTg2NTg3OCIsInRpbWVzdGFtcCI6MTc2NjAwMzU0NiwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-18 23:32:26', '2025-12-17 20:32:26', '2025-12-17 20:34:12', '2025-12-17 21:34:12', NULL, NULL, NULL, NULL),
(13, 'ORD-2025-323712', 2, 'omar', '2565326655326', 'cash', NULL, 15000.00, 'cancelled', NULL, NULL, 'eyJvcmRlcl9pZCI6MTMsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTMyMzcxMiIsInRpbWVzdGFtcCI6MTc2NjAwNjU3NSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 00:22:55', '2025-12-17 21:22:55', '2025-12-17 23:13:45', NULL, NULL, NULL, NULL, NULL),
(14, 'ORD-2025-579989', 2, 'omar', '2565326655326', 'cash', NULL, 15000.00, 'cancelled', NULL, NULL, 'eyJvcmRlcl9pZCI6MTQsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTU3OTk4OSIsInRpbWVzdGFtcCI6MTc2NjAwNzk5OSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 00:46:39', '2025-12-17 21:46:39', '2025-12-17 23:13:41', NULL, NULL, NULL, NULL, NULL),
(15, 'ORD-2025-645761', 2, 'omar', '2565326655326', '', NULL, 2000.00, 'cancelled', NULL, NULL, 'eyJvcmRlcl9pZCI6MTUsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTY0NTc2MSIsInRpbWVzdGFtcCI6MTc2NjAwODAxOSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 00:46:59', '2025-12-17 21:46:59', '2025-12-17 23:13:38', NULL, NULL, NULL, NULL, NULL),
(16, 'ORD-2025-514532', 2, 'omar', '2565326655326', '', NULL, 2000.00, 'cancelled', NULL, NULL, 'eyJvcmRlcl9pZCI6MTYsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTUxNDUzMiIsInRpbWVzdGFtcCI6MTc2NjAwODM2NCwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 00:52:44', '2025-12-17 21:52:44', '2025-12-17 23:13:33', NULL, NULL, NULL, NULL, NULL),
(17, 'ORD-2025-105248', 2, 'Omar', '98465618468354', 'cash', NULL, 15000.00, 'cancelled', NULL, NULL, 'eyJvcmRlcl9pZCI6MTcsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTEwNTI0OCIsInRpbWVzdGFtcCI6MTc2NjAwODgwNywidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 01:00:07', '2025-12-17 22:00:07', '2025-12-17 23:13:28', NULL, NULL, NULL, NULL, NULL),
(18, 'ORD-2025-129055', 2, 'Omar', '98465618468354', '', 'Nakawuka Mbalala', 15000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MTgsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTEyOTA1NSIsInRpbWVzdGFtcCI6MTc2NjAwODg4NCwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 01:01:24', '2025-12-17 22:01:24', '2025-12-17 22:02:57', '2025-12-17 23:02:57', NULL, NULL, NULL, NULL),
(19, 'ORD-2025-055339', 2, 'Omar', '07845616464', '', 'Mbaale lwakaka', 2000.00, 'cancelled', NULL, NULL, 'eyJvcmRlcl9pZCI6MTksIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTA1NTMzOSIsInRpbWVzdGFtcCI6MTc2NjAwOTEyNSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 01:05:25', '2025-12-17 22:05:25', '2025-12-17 23:13:18', NULL, NULL, NULL, NULL, NULL),
(20, 'ORD-2025-188101', 2, 'Ben', '6102132131', '', 'Abaita', 15000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MjAsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTE4ODEwMSIsInRpbWVzdGFtcCI6MTc2NjAxNDE5MSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 02:29:51', '2025-12-17 23:29:51', '2025-12-17 23:41:22', '2025-12-18 00:41:22', NULL, NULL, NULL, NULL),
(21, 'ORD-2025-411457', 2, 'Omar', '0771824697', 'cash', NULL, 2000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MjEsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTQxMTQ1NyIsInRpbWVzdGFtcCI6MTc2NjAxNjA0NCwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 03:00:44', '2025-12-18 00:00:44', '2025-12-18 00:02:42', '2025-12-18 01:02:42', NULL, NULL, NULL, NULL),
(22, 'ORD-2025-636588', 2, 'Omar', '0771824697', 'cash', NULL, 4000.00, 'finished', NULL, NULL, 'eyJvcmRlcl9pZCI6MjIsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTYzNjU4OCIsInRpbWVzdGFtcCI6MTc2NjA2MzI1MiwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 16:07:32', '2025-12-18 13:07:32', '2025-12-18 13:09:27', '2025-12-18 14:09:27', NULL, NULL, NULL, NULL),
(23, 'ORD-2025-412208', 2, 'Omar', '0771824697', '', 'ndagire', 2000.00, 'pending', NULL, NULL, 'eyJvcmRlcl9pZCI6MjMsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI1LTQxMjIwOCIsInRpbWVzdGFtcCI6MTc2NjA2MzQ5OSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2025-12-19 16:11:39', '2025-12-18 13:11:39', '2025-12-18 13:11:39', NULL, NULL, NULL, NULL, NULL),
(24, 'ORD-2026-874971', 2, 'Omar Muammar', '0771827046', 'cash', NULL, 87000.00, 'pending', NULL, NULL, 'eyJvcmRlcl9pZCI6MjQsIm9yZGVyX3JlZmVyZW5jZSI6Ik9SRC0yMDI2LTg3NDk3MSIsInRpbWVzdGFtcCI6MTc2Nzc4MzA1NSwidHlwZSI6InJlbW90ZV9vcmRlciJ9', '2026-01-08 13:50:55', '2026-01-07 10:50:55', '2026-01-07 10:50:55', NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `remote_order_audit_logs`
--

CREATE TABLE `remote_order_audit_logs` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `performed_by` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `old_status` varchar(20) DEFAULT NULL,
  `new_status` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remote_order_audit_logs`
--

INSERT INTO `remote_order_audit_logs` (`id`, `order_id`, `action`, `performed_by`, `user_id`, `old_status`, `new_status`, `notes`, `ip_address`, `created_at`) VALUES
(1, 1, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-16 18:48:59'),
(2, 2, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-16 19:05:25'),
(3, 3, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-16 19:38:22'),
(4, 4, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 11:30:23'),
(5, 5, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 11:38:28'),
(6, 6, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 13:01:14'),
(7, 6, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RC-00001', NULL, '2025-12-17 13:11:25'),
(8, 6, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RC-00002', NULL, '2025-12-17 13:12:54'),
(9, 6, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RC-00003', NULL, '2025-12-17 13:12:55'),
(10, 7, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 13:17:53'),
(11, 7, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RC-251217-0001', NULL, '2025-12-17 13:18:03'),
(12, 7, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RC-00004', NULL, '2025-12-17 13:24:05'),
(13, 8, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 19:07:57'),
(14, 8, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RC-00005', NULL, '2025-12-17 19:08:16'),
(15, 9, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 19:20:24'),
(16, 9, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09508', NULL, '2025-12-17 19:21:07'),
(17, 10, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 19:26:43'),
(18, 10, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09509', NULL, '2025-12-17 19:27:12'),
(19, 11, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 19:42:53'),
(20, 11, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09511', NULL, '2025-12-17 20:14:31'),
(21, 12, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 20:32:26'),
(22, 12, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09512', NULL, '2025-12-17 20:34:12'),
(23, 13, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 21:22:55'),
(24, 14, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 21:46:39'),
(25, 15, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 21:46:59'),
(26, 16, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 21:52:44'),
(27, 17, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 22:00:07'),
(28, 18, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 22:01:24'),
(29, 18, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09513', NULL, '2025-12-17 22:02:57'),
(30, 19, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 22:05:25'),
(31, 19, 'order_cancelled', 'Den', 1, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:13:18'),
(32, 17, 'order_cancelled', 'Den', 1, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:13:28'),
(33, 16, 'order_cancelled', 'Den', 1, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:13:33'),
(34, 15, 'order_cancelled', 'Den', 1, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:13:38'),
(35, 14, 'order_cancelled', 'Den', 1, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:13:41'),
(36, 13, 'order_cancelled', 'Den', 1, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:13:45'),
(37, 5, 'order_cancelled', 'Aisha', 2, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:28:17'),
(38, 4, 'order_cancelled', 'Aisha', 2, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:28:20'),
(39, 3, 'order_cancelled', 'Aisha', 2, 'pending', 'cancelled', 'Order cancelled by staff', NULL, '2025-12-17 23:28:24'),
(40, 20, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-17 23:29:51'),
(41, 20, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09514', NULL, '2025-12-17 23:41:22'),
(42, 21, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-18 00:00:44'),
(43, 21, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09515', NULL, '2025-12-18 00:02:42'),
(44, 22, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-18 13:07:32'),
(45, 22, 'order_completed', '0', 2, 'pending', 'finished', 'Sale recorded with receipt: RP-09539', NULL, '2025-12-18 13:09:27'),
(46, 23, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2025-12-18 13:11:39'),
(47, 24, 'order_created', '0', NULL, NULL, 'pending', 'Order created from customer website', NULL, '2026-01-07 10:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `remote_order_items`
--

CREATE TABLE `remote_order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `remote_order_items`
--

INSERT INTO `remote_order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `subtotal`, `created_at`) VALUES
(1, 1, 44, 'ODEON (body mist) Royal 200ML', 2, 14000.00, 28000.00, '2025-12-16 18:48:59'),
(2, 2, 39, 'Perfume', 2, 50000.00, 100000.00, '2025-12-16 19:05:25'),
(3, 2, 19, 'onions', 2, 1000.00, 2000.00, '2025-12-16 19:05:25'),
(4, 2, 45, 'Nivana', 2, 25000.00, 50000.00, '2025-12-16 19:05:25'),
(5, 2, 25, 'mukene plus', 2, 5000.00, 10000.00, '2025-12-16 19:05:25'),
(12, 6, 41, 'Vg kit', 1, 20000.00, 20000.00, '2025-12-17 13:01:14'),
(13, 6, 47, 'Water', 1, 5000.00, 5000.00, '2025-12-17 13:01:14'),
(14, 7, 35, 'Files', 3, 2000.00, 6000.00, '2025-12-17 13:17:53'),
(15, 8, 35, 'Files', 1, 2000.00, 2000.00, '2025-12-17 19:07:57'),
(16, 9, 46, 'Eversest', 1, 25000.00, 25000.00, '2025-12-17 19:20:24'),
(17, 10, 46, 'Eversest', 1, 25000.00, 25000.00, '2025-12-17 19:26:43'),
(18, 11, 46, 'Eversest', 1, 25000.00, 25000.00, '2025-12-17 19:42:53'),
(19, 12, 28, 'Biscuits', 1, 2000.00, 2000.00, '2025-12-17 20:32:26'),
(20, 12, 46, 'Eversest', 1, 25000.00, 25000.00, '2025-12-17 20:32:26'),
(21, 13, 21, 'gonja', 1, 15000.00, 15000.00, '2025-12-17 21:22:55'),
(22, 14, 21, 'gonja', 1, 15000.00, 15000.00, '2025-12-17 21:46:39'),
(23, 15, 28, 'Biscuits', 1, 2000.00, 2000.00, '2025-12-17 21:46:59'),
(24, 16, 35, 'Files', 1, 2000.00, 2000.00, '2025-12-17 21:52:44'),
(25, 17, 21, 'gonja', 1, 15000.00, 15000.00, '2025-12-17 22:00:07'),
(26, 18, 21, 'gonja', 1, 15000.00, 15000.00, '2025-12-17 22:01:24'),
(27, 19, 28, 'Biscuits', 1, 2000.00, 2000.00, '2025-12-17 22:05:25'),
(28, 20, 21, 'gonja', 1, 15000.00, 15000.00, '2025-12-17 23:29:51'),
(29, 21, 35, 'Files', 1, 2000.00, 2000.00, '2025-12-18 00:00:44'),
(30, 22, 28, 'Biscuits', 2, 2000.00, 4000.00, '2025-12-18 13:07:32'),
(31, 23, 28, 'Biscuits', 1, 2000.00, 2000.00, '2025-12-18 13:11:39'),
(32, 24, 48, 'benzen', 1, 50000.00, 50000.00, '2026-01-07 10:50:55'),
(33, 24, 17, 'maize', 1, 12000.00, 12000.00, '2026-01-07 10:50:55'),
(34, 24, 41, 'Vg kit', 1, 20000.00, 20000.00, '2026-01-07 10:50:55'),
(35, 24, 25, 'mukene plus', 1, 5000.00, 5000.00, '2026-01-07 10:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `generated-by` varchar(255) NOT NULL,
  `report-type` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `date_generated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product-id` int(11) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `amount` decimal(10,0) NOT NULL,
  `cost-price` decimal(10,0) NOT NULL,
  `sold-by` int(11) NOT NULL,
  `date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_profits` decimal(10,0) NOT NULL,
  `payment_method` enum('MTN MoMo','Airtel Money','Bank','Cash','Customer file') NOT NULL DEFAULT 'Cash',
  `customer_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(64) DEFAULT NULL,
  `invoice_no` varchar(32) DEFAULT NULL,
  `receipt_no` varchar(32) DEFAULT NULL,
  `products_json` text DEFAULT NULL,
  `original_debt_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product-id`, `branch-id`, `business_id`, `quantity`, `amount`, `cost-price`, `sold-by`, `date`, `total_profits`, `payment_method`, `customer_id`, `transaction_id`, `invoice_no`, `receipt_no`, `products_json`, `original_debt_date`) VALUES
(1, 8, 1, 0, 2, 30000, 24000, 0, '2025-09-05 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00001', NULL, NULL),
(2, 8, 1, 0, 15, 225000, 180000, 2, '2025-09-05 00:00:00', 45000, 'Cash', NULL, NULL, NULL, 'RP-00002', NULL, NULL),
(3, 8, 1, 0, 15, 225000, 180000, 2, '2025-09-05 00:00:00', 45000, 'Cash', NULL, NULL, NULL, 'RP-00003', NULL, NULL),
(4, 9, 1, 0, 15, 225000, 180000, 3, '2025-09-05 00:00:00', 45000, 'Cash', NULL, NULL, NULL, 'RP-00004', NULL, NULL),
(5, 9, 2, 0, 50, 750000, 600000, 3, '2025-09-05 00:00:00', 150000, 'Cash', NULL, NULL, NULL, 'RP-00005', NULL, NULL),
(6, 9, 2, 0, 55, 825000, 660000, 3, '2025-09-05 00:00:00', 165000, 'Cash', NULL, NULL, NULL, 'RP-00006', NULL, NULL),
(7, 12, 1, 0, 2, 30000, 24000, 2, '2025-09-06 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00007', NULL, NULL),
(8, 12, 1, 0, 595, 8925000, 7140000, 2, '2025-09-06 00:00:00', 1785000, 'Cash', NULL, NULL, NULL, 'RP-00008', NULL, NULL),
(9, 14, 2, 0, 2, 30000, 20000, 2, '2025-09-06 00:00:00', 10000, 'Cash', NULL, NULL, NULL, 'RP-00009', NULL, NULL),
(10, 12, 1, 0, 2, 30000, 24000, 4, '2025-09-06 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00010', NULL, NULL),
(11, 15, 2, 0, 15, 180000, 135000, 2, '2025-09-06 00:00:00', 45000, 'Cash', NULL, NULL, NULL, 'RP-00011', NULL, NULL),
(12, 21, 2, 0, 2, 30000, 24000, 0, '2025-09-06 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00012', NULL, NULL),
(13, 13, 1, 0, 2, 30000, 24000, 0, '2025-09-06 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00013', NULL, NULL),
(14, 13, 1, 0, 2, 30000, 24000, 0, '2025-09-06 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00014', NULL, NULL),
(15, 13, 1, 0, 2, 30000, 24000, 0, '2025-09-06 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00015', NULL, NULL),
(16, 13, 1, 0, 3, 45000, 36000, 0, '2025-09-06 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00016', NULL, NULL),
(17, 13, 1, 0, 1, 15000, 12000, 0, '2025-09-06 00:00:00', 0, 'Cash', NULL, NULL, NULL, 'RP-00017', NULL, NULL),
(18, 23, 2, 0, 1, 15000, 2000, 2, '2025-09-06 00:00:00', 13000, 'Cash', NULL, NULL, NULL, 'RP-00018', NULL, NULL),
(19, 14, 2, 0, 2, 30000, 20000, 2, '2025-09-07 00:00:00', 10000, 'Cash', NULL, NULL, NULL, 'RP-00019', NULL, NULL),
(20, 17, 2, 0, 2, 24000, 20000, 2, '2025-09-07 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00020', NULL, NULL),
(21, 17, 2, 0, 2, 24000, 20000, 2, '2025-09-14 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00021', NULL, NULL),
(22, 19, 2, 0, 10, 10000, 5000, 2, '2025-09-15 00:00:00', 5000, 'Cash', NULL, NULL, NULL, 'RP-00022', NULL, NULL),
(23, 15, 2, 0, 2, 24000, 18000, 2, '2025-09-17 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00023', NULL, NULL),
(24, 23, 2, 0, 15, 225000, 30000, 2, '2025-09-17 15:11:49', 195000, 'Cash', NULL, NULL, NULL, 'RP-00024', NULL, NULL),
(25, 17, 5, 4, 2, 24000, 20000, 2, '2025-09-17 15:12:07', 4000, 'Cash', NULL, NULL, NULL, 'RP-00025', NULL, NULL),
(26, 15, 2, 0, 2, 24000, 18000, 2, '2025-09-17 16:22:38', 6000, 'Cash', NULL, NULL, NULL, 'RP-00026', NULL, NULL),
(27, 19, 2, 0, 300, 300000, 150000, 2, '2025-09-17 16:26:12', 150000, 'Cash', NULL, NULL, NULL, 'RP-00027', NULL, NULL),
(28, 19, 2, 0, 300, 300000, 150000, 2, '2025-09-17 16:26:16', 150000, 'Cash', NULL, NULL, NULL, 'RP-00028', NULL, NULL),
(29, 19, 2, 0, 9000, 9000000, 4500000, 2, '2025-09-17 16:26:45', 4500000, 'Cash', NULL, NULL, NULL, 'RP-00029', NULL, NULL),
(30, 19, 2, 0, 20, 20000, 10000, 2, '2025-09-17 16:27:16', 10000, 'Cash', NULL, NULL, NULL, 'RP-00030', NULL, NULL),
(31, 19, 2, 0, 20, 20000, 10000, 2, '2025-09-17 16:27:24', 10000, 'Cash', NULL, NULL, NULL, 'RP-00031', NULL, NULL),
(32, 19, 2, 0, 20, 20000, 10000, 2, '2025-09-17 16:31:54', 10000, 'Cash', NULL, NULL, NULL, 'RP-00032', NULL, NULL),
(33, 14, 2, 0, 20, 300000, 200000, 2, '2025-09-17 16:32:12', 100000, 'Cash', NULL, NULL, NULL, 'RP-00033', NULL, NULL),
(34, 14, 2, 0, 20, 300000, 200000, 2, '2025-09-17 16:32:17', 100000, 'Cash', NULL, NULL, NULL, 'RP-00034', NULL, NULL),
(35, 14, 2, 0, 30, 450000, 300000, 2, '2025-09-17 16:32:35', 150000, 'Cash', NULL, NULL, NULL, 'RP-00035', NULL, NULL),
(36, 14, 2, 0, 30, 450000, 300000, 2, '2025-09-17 16:32:47', 150000, 'Cash', NULL, NULL, NULL, 'RP-00036', NULL, NULL),
(37, 14, 2, 0, 2, 30000, 20000, 2, '2025-09-17 16:40:47', 10000, 'Cash', NULL, NULL, NULL, 'RP-00037', NULL, NULL),
(38, 14, 2, 0, 2, 30000, 20000, 2, '2025-09-17 16:40:53', 10000, 'Cash', NULL, NULL, NULL, 'RP-00038', NULL, NULL),
(39, 17, 2, 0, 2, 24000, 20000, 2, '2025-09-17 15:54:17', 4000, 'Cash', NULL, NULL, NULL, 'RP-00039', NULL, NULL),
(40, 21, 2, 0, 2, 30000, 24000, 2, '2025-09-18 16:53:47', 6000, 'Cash', NULL, NULL, NULL, 'RP-00040', NULL, NULL),
(41, 21, 2, 0, 2, 30000, 24000, 2, '2025-09-18 16:53:53', 6000, 'Cash', NULL, NULL, NULL, 'RP-00041', NULL, NULL),
(42, 15, 2, 0, 1, 12000, 9000, 2, '2025-09-18 16:54:22', 3000, 'Cash', NULL, NULL, NULL, 'RP-00042', NULL, NULL),
(43, 19, 2, 0, 2, 2000, 1000, 2, '2025-09-18 15:56:11', 1000, 'Cash', NULL, NULL, NULL, 'RP-00043', NULL, NULL),
(44, 14, 2, 0, 2, 30000, 20000, 2, '2025-09-30 10:25:29', 10000, 'Cash', NULL, NULL, NULL, 'RP-00044', NULL, NULL),
(45, 19, 2, 0, 15, 15000, 7500, 2, '2025-09-30 10:25:29', 7500, 'Cash', NULL, NULL, NULL, 'RP-00045', NULL, NULL),
(46, 23, 2, 0, 30, 450000, 60000, 2, '2025-09-30 10:25:29', 390000, 'Cash', NULL, NULL, NULL, 'RP-00046', NULL, NULL),
(47, 21, 2, 0, 2, 30000, 24000, 2, '2025-09-30 10:25:30', 6000, 'Cash', NULL, NULL, NULL, 'RP-00047', NULL, NULL),
(50, 14, 2, 0, 15, 225000, 150000, 2, '2025-10-16 21:15:41', 75000, 'Cash', NULL, NULL, NULL, 'RP-00048', NULL, NULL),
(51, 14, 2, 0, 15, 225000, 150000, 2, '2025-10-16 21:16:04', 75000, 'Cash', NULL, NULL, NULL, 'RP-00049', NULL, NULL),
(52, 14, 2, 0, 15, 225000, 150000, 2, '2025-10-16 21:19:58', 75000, 'Cash', NULL, NULL, NULL, 'RP-00050', NULL, NULL),
(53, 14, 2, 0, 15, 225000, 150000, 2, '2025-10-16 21:28:52', 75000, 'Cash', NULL, NULL, NULL, 'RP-00051', NULL, NULL),
(54, 14, 2, 0, 15, 225000, 150000, 2, '2025-10-16 21:28:53', 75000, 'Cash', NULL, NULL, NULL, 'RP-00052', NULL, NULL),
(55, 19, 2, 0, 2, 2000, 1000, 2, '2025-10-17 06:49:41', 1000, 'Cash', NULL, NULL, NULL, 'RP-00053', NULL, NULL),
(56, 19, 2, 0, 50, 50000, 25000, 2, '2025-10-17 12:32:09', 25000, 'Bank', NULL, NULL, NULL, 'RP-00054', NULL, NULL),
(57, 19, 2, 0, 50, 50000, 25000, 2, '2025-10-17 12:33:47', 25000, 'Bank', NULL, NULL, NULL, 'RP-00055', NULL, NULL),
(58, 19, 2, 0, 50, 50000, 25000, 2, '2025-10-17 12:38:50', 25000, 'Bank', NULL, NULL, NULL, 'RP-00056', NULL, NULL),
(59, 14, 2, 0, 50, 750000, 500000, 2, '2025-10-17 13:15:32', 250000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00057', NULL, NULL),
(60, 19, 2, 0, 67, 67000, 33500, 2, '2025-10-17 13:16:16', 33500, 'Airtel Money', NULL, NULL, NULL, 'RP-00058', NULL, NULL),
(61, 13, 1, 0, 50, 750000, 600000, 7, '2025-10-17 13:29:31', 150000, 'Airtel Money', NULL, NULL, NULL, 'RP-00059', NULL, NULL),
(62, 19, 2, 0, 25, 25000, 12500, 2, '2025-10-17 14:29:43', 12500, 'Airtel Money', NULL, NULL, NULL, 'RP-00060', NULL, NULL),
(63, 14, 2, 0, 2, 30000, 20000, 2, '2025-10-17 15:23:14', 10000, 'Airtel Money', NULL, NULL, NULL, 'RP-00061', NULL, NULL),
(64, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 15:58:45', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00062', NULL, NULL),
(65, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 15:58:55', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00063', NULL, NULL),
(66, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 16:04:53', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00064', NULL, NULL),
(67, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 16:07:09', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00065', NULL, NULL),
(68, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 16:10:35', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00066', NULL, NULL),
(69, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 16:10:35', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00067', NULL, NULL),
(70, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 16:10:58', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00068', NULL, NULL),
(71, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-17 16:10:58', 6000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00069', NULL, NULL),
(72, 19, 2, 0, 20, 20000, 10000, 2, '2025-10-17 16:17:48', 10000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00070', NULL, NULL),
(73, 19, 2, 0, 20, 20000, 10000, 2, '2025-10-17 16:17:48', 10000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00071', NULL, NULL),
(74, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-19 09:25:30', 6000, 'Cash', NULL, NULL, NULL, 'RP-00072', NULL, NULL),
(75, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-19 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00073', NULL, NULL),
(76, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-19 09:25:36', 6000, 'Cash', NULL, NULL, NULL, 'RP-00074', NULL, NULL),
(77, 19, 2, 0, 12, 12000, 6000, 2, '2025-10-19 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00075', NULL, NULL),
(78, 19, 2, 0, 800, 800000, 400000, 2, '2025-10-19 09:25:55', 400000, 'Cash', NULL, NULL, NULL, 'RP-00076', NULL, NULL),
(79, 19, 2, 0, 800, 800000, 400000, 2, '2025-10-19 00:00:00', 400000, 'Cash', NULL, NULL, NULL, 'RP-00077', NULL, NULL),
(80, 19, 2, 0, 800, 800000, 400000, 2, '2025-10-19 09:25:57', 400000, 'Cash', NULL, NULL, NULL, 'RP-00078', NULL, NULL),
(81, 19, 2, 0, 800, 800000, 400000, 2, '2025-10-19 00:00:00', 400000, 'Cash', NULL, NULL, NULL, 'RP-00079', NULL, NULL),
(82, 19, 2, 0, 800, 800000, 400000, 2, '2025-10-19 00:00:00', 400000, 'Cash', NULL, NULL, NULL, 'RP-00080', NULL, NULL),
(83, 19, 2, 0, 800, 800000, 400000, 2, '2025-10-19 09:27:47', 400000, 'Cash', NULL, NULL, NULL, 'RP-00081', NULL, NULL),
(84, 19, 2, 0, 80, 80000, 40000, 2, '2025-10-19 00:00:00', 40000, 'Cash', NULL, NULL, NULL, 'RP-00082', NULL, NULL),
(85, 21, 2, 0, 10, 150000, 120000, 2, '2025-10-19 00:00:00', 30000, 'Cash', NULL, NULL, NULL, 'RP-00083', NULL, NULL),
(86, 14, 2, 0, 5, 75000, 50000, 2, '2025-10-19 00:00:00', 25000, 'Cash', NULL, NULL, NULL, 'RP-00084', NULL, NULL),
(87, 14, 2, 0, 5, 75000, 50000, 2, '2025-10-19 09:29:10', 25000, 'Cash', NULL, NULL, NULL, 'RP-00085', NULL, NULL),
(88, 23, 2, 0, 2, 30000, 4000, 2, '2025-10-19 00:00:00', 26000, 'Cash', NULL, NULL, NULL, 'RP-00086', NULL, NULL),
(89, 23, 2, 0, 2, 30000, 4000, 2, '2025-10-19 09:29:41', 26000, 'Cash', NULL, NULL, NULL, 'RP-00087', NULL, NULL),
(90, 19, 2, 0, 2, 2000, 1000, 2, '2025-10-19 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00088', NULL, NULL),
(91, 19, 2, 0, 2, 2000, 1000, 2, '2025-10-19 09:30:37', 1000, 'Cash', NULL, NULL, NULL, 'RP-00089', NULL, NULL),
(92, 14, 2, 0, 2, 30000, 20000, 2, '2025-10-19 00:00:00', 10000, 'Cash', NULL, NULL, NULL, 'RP-00090', NULL, NULL),
(93, 21, 2, 0, 1, 15000, 12000, 2, '2025-10-19 00:00:00', 3000, 'Cash', NULL, NULL, NULL, 'RP-00091', NULL, NULL),
(94, 21, 2, 0, 1, 15000, 12000, 2, '2025-10-19 09:31:21', 3000, 'Cash', NULL, NULL, NULL, 'RP-00092', NULL, NULL),
(95, 17, 2, 0, 1, 12000, 10000, 2, '2025-10-19 00:00:00', 2000, 'Cash', NULL, NULL, NULL, 'RP-00093', NULL, NULL),
(96, 17, 2, 0, 1, 12000, 10000, 2, '2025-10-19 09:33:02', 2000, 'Cash', NULL, NULL, NULL, 'RP-00094', NULL, NULL),
(97, 24, 2, 0, 2000, 2000000, 4000000, 2, '2025-10-19 00:00:00', -2000000, 'Cash', NULL, NULL, NULL, 'RP-00095', NULL, NULL),
(98, 24, 2, 0, 2000, 2000000, 4000000, 2, '2025-10-19 09:34:32', -2000000, 'Cash', NULL, NULL, NULL, 'RP-00096', NULL, NULL),
(99, 25, 2, 0, 3000, 15000000, 30000000, 2, '2025-10-19 00:00:00', -15000000, 'Cash', NULL, NULL, NULL, 'RP-00097', NULL, NULL),
(100, 25, 2, 0, 3000, 15000000, 30000000, 2, '2025-10-19 09:37:32', -15000000, 'Cash', NULL, NULL, NULL, 'RP-00098', NULL, NULL),
(101, 14, 2, 0, 1, 15000, 10000, 2, '2025-10-22 00:00:00', 5000, '', NULL, NULL, NULL, 'RP-00099', NULL, NULL),
(102, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-22 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00100', NULL, NULL),
(103, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-22 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00101', NULL, NULL),
(104, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-22 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00102', NULL, NULL),
(106, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-22 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00103', NULL, NULL),
(107, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-22 00:00:00', -20000, '', 2, NULL, NULL, 'RP-00104', NULL, NULL),
(108, 24, 2, 0, 10, 10000, 20000, 2, '2025-10-23 11:26:21', -10000, 'Cash', NULL, NULL, NULL, 'RP-00105', NULL, NULL),
(109, 19, 2, 0, 5, 5000, 2500, 2, '2025-10-23 11:26:36', 2500, 'Cash', NULL, NULL, NULL, 'RP-00106', NULL, NULL),
(110, 14, 2, 0, 12, 180000, 120000, 2, '2025-10-23 11:30:59', 60000, '', NULL, NULL, NULL, 'RP-00107', NULL, NULL),
(111, 14, 2, 0, 12, 180000, 120000, 2, '2025-10-23 11:31:13', 60000, '', NULL, NULL, NULL, 'RP-00108', NULL, NULL),
(112, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-23 11:38:07', -2000, '', NULL, NULL, NULL, 'RP-00109', NULL, NULL),
(113, 24, 2, 0, 10, 10000, 20000, 2, '2025-10-23 11:50:25', -10000, '', NULL, NULL, NULL, 'RP-00110', NULL, NULL),
(114, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-23 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00111', NULL, NULL),
(115, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-23 00:00:00', -20000, '', 2, NULL, NULL, 'RP-00112', NULL, NULL),
(116, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-23 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00113', NULL, NULL),
(117, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-23 00:00:00', -20000, '', 2, NULL, NULL, 'RP-00114', NULL, NULL),
(118, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-23 00:00:00', -20000, '', NULL, NULL, NULL, 'RP-00115', NULL, NULL),
(119, 24, 2, 0, 20, 20000, 40000, 2, '2025-10-23 00:00:00', -20000, '', 2, NULL, NULL, 'RP-00116', NULL, NULL),
(120, 24, 2, 0, 12, 12000, 24000, 2, '2025-10-23 00:00:00', -12000, '', NULL, NULL, NULL, 'RP-00117', NULL, NULL),
(121, 24, 2, 0, 12, 12000, 24000, 2, '2025-10-23 00:00:00', -12000, '', 2, NULL, NULL, 'RP-00118', NULL, NULL),
(122, 24, 2, 0, 12, 12000, 24000, 2, '2025-10-23 00:00:00', -12000, '', NULL, NULL, NULL, 'RP-00119', NULL, NULL),
(123, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 00:00:00', -2000, 'Cash', NULL, NULL, NULL, 'RP-00120', NULL, NULL),
(124, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 13:20:11', -2000, 'Cash', NULL, NULL, NULL, 'RP-00121', NULL, NULL),
(125, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 00:00:00', -2000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00122', NULL, NULL),
(126, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 13:20:27', -2000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00123', NULL, NULL),
(127, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 00:00:00', -2000, 'Airtel Money', NULL, NULL, NULL, 'RP-00124', NULL, NULL),
(128, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 13:20:52', -2000, 'Airtel Money', NULL, NULL, NULL, 'RP-00125', NULL, NULL),
(129, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 00:00:00', -2000, 'Bank', NULL, NULL, NULL, 'RP-00126', NULL, NULL),
(130, 24, 2, 0, 2, 2000, 4000, 2, '2025-10-25 13:21:12', -2000, 'Bank', NULL, NULL, NULL, 'RP-00127', NULL, NULL),
(131, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00128', NULL, NULL),
(132, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 13:32:18', -1000, 'Cash', NULL, NULL, NULL, 'RP-00129', NULL, NULL),
(133, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00130', NULL, NULL),
(134, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00131', NULL, NULL),
(135, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00132', NULL, NULL),
(136, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00133', NULL, NULL),
(137, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00134', NULL, NULL),
(138, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00135', NULL, NULL),
(139, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00136', NULL, NULL),
(140, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Cash', NULL, NULL, NULL, 'RP-00137', NULL, NULL),
(141, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00138', NULL, NULL),
(142, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Airtel Money', NULL, NULL, NULL, 'RP-00139', NULL, NULL),
(143, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Bank', NULL, NULL, NULL, 'RP-00140', NULL, NULL),
(144, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, '', NULL, NULL, NULL, 'RP-00141', NULL, NULL),
(145, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, '', 2, NULL, NULL, 'RP-00142', NULL, NULL),
(146, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00143', NULL, NULL),
(147, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00144', NULL, NULL),
(148, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00145', NULL, NULL),
(149, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00146', NULL, NULL),
(150, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00147', NULL, NULL),
(151, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00148', NULL, NULL),
(152, 24, 2, 0, 1, 1000, 2000, 2, '2025-10-25 00:00:00', -1000, 'Customer file', 2, NULL, NULL, 'RP-00149', NULL, NULL),
(153, 24, 2, 0, 8, 8000, 16000, 2, '2025-10-25 00:00:00', -8000, 'Customer file', 2, NULL, NULL, 'RP-00150', NULL, NULL),
(154, 24, 2, 0, 5, 5000, 10000, 2, '2025-10-25 00:00:00', -5000, 'Customer file', 2, NULL, NULL, 'RP-00151', NULL, NULL),
(155, 24, 2, 0, 10, 10000, 20000, 2, '2025-10-25 00:00:00', -10000, 'Customer file', 3, NULL, NULL, 'RP-00152', NULL, NULL),
(156, 24, 2, 0, 60, 60000, 120000, 2, '2025-10-25 00:00:00', -60000, 'Customer file', 3, NULL, NULL, 'RP-00153', NULL, NULL),
(157, 24, 2, 0, 60, 60000, 120000, 2, '2025-10-25 00:00:00', -60000, 'Customer file', 3, NULL, NULL, 'RP-00154', NULL, NULL),
(158, 17, 2, 0, 12, 144000, 120000, 2, '2025-11-01 00:00:00', 24000, 'Customer file', 3, NULL, NULL, 'RP-00155', NULL, NULL),
(159, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-01 00:00:00', 40000, 'Customer file', 5, NULL, NULL, 'RP-00156', NULL, NULL),
(160, 23, 2, 0, 20, 300000, 40000, 2, '2025-11-01 00:00:00', 260000, 'Customer file', 5, NULL, NULL, 'RP-00157', NULL, NULL),
(161, 21, 2, 0, 20, 300000, 240000, 2, '2025-11-01 00:00:00', 60000, 'Customer file', 5, NULL, NULL, 'RP-00158', NULL, NULL),
(162, 25, 2, 0, 20, 100000, 200000, 2, '2025-11-01 00:00:00', -100000, 'Customer file', 5, NULL, NULL, 'RP-00159', NULL, NULL),
(163, 17, 2, 0, 14, 168000, 140000, 2, '2025-11-01 00:00:00', 28000, 'Customer file', 5, NULL, NULL, 'RP-00160', NULL, NULL),
(164, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-02 01:00:33', 4000, '', NULL, NULL, NULL, 'RP-00161', NULL, NULL),
(165, 28, 2, 0, 20, 40000, 46000, 2, '2025-11-02 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00162', NULL, NULL),
(166, 34, 2, 0, 20, 40000, 100000, 2, '2025-11-02 00:00:00', -60000, 'Customer file', 5, NULL, NULL, 'RP-00163', NULL, NULL),
(167, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-02 00:00:00', 4000, 'Airtel Money', NULL, NULL, NULL, 'RP-00164', NULL, NULL),
(168, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-03 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00165', NULL, NULL),
(169, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-03 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00166', NULL, NULL),
(170, 19, 2, 0, 5, 5000, 2500, 2, '2025-11-03 00:00:00', 2500, 'Cash', NULL, NULL, NULL, 'RP-00167', NULL, NULL),
(171, 24, 2, 0, 2, 2000, 4000, 2, '2025-11-03 00:00:00', -2000, 'Cash', NULL, NULL, NULL, 'RP-00168', NULL, NULL),
(172, 37, 2, 0, 20, 100000, 40000, 2, '2025-11-03 00:00:00', 60000, 'Cash', NULL, NULL, NULL, 'RP-00169', NULL, NULL),
(173, 34, 2, 0, 15, 30000, 75000, 2, '2025-11-03 00:00:00', -45000, 'Cash', NULL, NULL, NULL, 'RP-00170', NULL, NULL),
(174, 39, 2, 0, 20, 1000000, 500000, 2, '2025-11-03 00:00:00', 500000, 'Cash', NULL, NULL, NULL, 'RP-00171', NULL, NULL),
(175, 40, 2, 0, 12, 72000, 60000, 2, '2025-11-03 00:00:00', 12000, 'Cash', NULL, NULL, NULL, 'RP-00172', NULL, NULL),
(176, 37, 2, 0, 2, 10000, 4000, 2, '2025-11-03 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00173', NULL, NULL),
(177, 34, 2, 0, 2, 4000, 10000, 2, '2025-11-03 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00174', NULL, NULL),
(178, 39, 2, 0, 2, 100000, 50000, 2, '2025-11-03 00:00:00', 50000, 'Cash', NULL, NULL, NULL, 'RP-00175', NULL, NULL),
(179, 40, 2, 0, 3, 18000, 15000, 2, '2025-11-03 00:00:00', 3000, 'Cash', NULL, NULL, NULL, 'RP-00176', NULL, NULL),
(180, 34, 2, 0, 2, 4000, 10000, 2, '2025-11-03 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00177', NULL, NULL),
(181, 39, 2, 0, 2, 100000, 50000, 2, '2025-11-03 00:00:00', 50000, 'Cash', NULL, NULL, NULL, 'RP-00178', NULL, NULL),
(182, 40, 2, 0, 2, 12000, 10000, 2, '2025-11-03 00:00:00', 2000, 'Cash', NULL, NULL, NULL, 'RP-00179', NULL, NULL),
(183, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-03 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00180', NULL, NULL),
(184, 40, 2, 0, 2, 12000, 10000, 2, '2025-11-03 00:00:00', 2000, 'Cash', NULL, NULL, NULL, 'RP-00181', NULL, NULL),
(185, 34, 2, 0, 2, 4000, 10000, 2, '2025-11-03 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00182', NULL, NULL),
(186, 34, 2, 0, 2, 4000, 10000, 2, '2025-11-03 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00183', NULL, NULL),
(187, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-05 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00184', NULL, NULL),
(188, 35, 2, 0, 2, 4000, 6000, 2, '2025-11-05 00:00:00', -2000, 'Cash', NULL, NULL, NULL, 'RP-00185', NULL, NULL),
(189, 37, 2, 0, 2, 10000, 4000, 2, '2025-11-05 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00186', NULL, NULL),
(190, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-05 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00187', NULL, NULL),
(191, 28, 2, 0, 4, 8000, 9200, 2, '2025-11-05 00:00:00', -1200, 'Cash', NULL, NULL, NULL, 'RP-00188', NULL, NULL),
(192, 21, 2, 0, 2, 30000, 24000, 2, '2025-11-05 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00189', NULL, NULL),
(193, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-05 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00190', NULL, NULL),
(194, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-05 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00191', NULL, NULL),
(195, 25, 2, 0, 9, 45000, 90000, 2, '2025-11-05 00:00:00', -45000, 'Cash', NULL, NULL, NULL, 'RP-00192', NULL, NULL),
(196, 28, 2, 0, 8, 16000, 18400, 2, '2025-11-05 00:00:00', -2400, 'Cash', NULL, NULL, NULL, 'RP-00193', NULL, NULL),
(197, 39, 2, 0, 1, 50000, 25000, 2, '2025-11-05 00:00:00', 25000, 'Cash', NULL, NULL, NULL, 'RP-00194', NULL, NULL),
(198, 41, 2, 0, 5, 100000, 50000, 2, '2025-11-05 00:00:00', 50000, 'Cash', NULL, NULL, NULL, 'RP-00195', NULL, NULL),
(199, 25, 2, 0, 2, 10000, 20000, 2, '2025-11-05 00:00:00', -10000, 'Cash', NULL, NULL, NULL, 'RP-00196', NULL, NULL),
(200, 34, 2, 0, 2, 4000, 10000, 2, '2025-11-05 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00197', NULL, NULL),
(201, 34, 2, 0, 2, 4000, 10000, 2, '2025-11-05 00:00:00', -6000, 'Cash', NULL, NULL, NULL, 'RP-00198', NULL, NULL),
(202, 37, 2, 0, 2, 10000, 4000, 2, '2025-11-05 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00199', NULL, NULL),
(203, 35, 2, 0, 2, 4000, 6000, 2, '2025-11-05 00:00:00', -2000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00200', NULL, NULL),
(204, 21, 2, 0, 4, 60000, 48000, 2, '2025-11-05 00:00:00', 12000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00201', NULL, NULL),
(205, 19, 2, 0, 4, 4000, 2000, 2, '2025-11-05 00:00:00', 2000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00202', NULL, NULL),
(206, 17, 2, 0, 6, 72000, 60000, 2, '2025-11-05 00:00:00', 12000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00203', NULL, NULL),
(207, 39, 2, 0, 5, 250000, 125000, 2, '2025-11-05 00:00:00', 125000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00204', NULL, NULL),
(208, 25, 2, 0, 4, 20000, 40000, 2, '2025-11-05 00:00:00', -20000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00205', NULL, NULL),
(209, 40, 2, 0, 1, 6000, 5000, 2, '2025-11-05 00:00:00', 1000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00206', NULL, NULL),
(210, 38, 2, 0, 5, 30000, 13500, 2, '2025-11-06 00:00:00', 16500, 'Cash', NULL, NULL, NULL, 'RP-00207', NULL, NULL),
(211, 42, 2, 0, 20, 20000, 16000, 2, '2025-11-06 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00208', NULL, NULL),
(212, 46, 2, 0, 52, 1300000, 1040000, 2, '2025-11-06 00:00:00', 260000, 'Bank', NULL, NULL, NULL, 'RP-00209', NULL, NULL),
(213, 25, 2, 0, 2, 10000, 20000, 2, '2025-11-09 00:00:00', -10000, 'Cash', NULL, NULL, NULL, 'RP-00210', NULL, NULL),
(214, 24, 2, 0, 50, 50000, 100000, 3, '2025-11-09 00:00:00', -50000, 'Cash', NULL, NULL, NULL, 'RP-00211', NULL, NULL),
(215, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-15 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-00212', NULL, NULL),
(216, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-15 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00213', NULL, NULL),
(217, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-15 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00214', NULL, NULL),
(218, 19, 2, 0, 20, 20000, 10000, 2, '2025-11-15 00:00:00', 10000, 'Customer file', 3, NULL, NULL, 'RP-00215', NULL, NULL),
(219, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-15 00:00:00', 40000, 'Customer file', 3, NULL, NULL, 'RP-00216', NULL, NULL),
(220, 21, 2, 0, 20, 300000, 240000, 2, '2025-11-15 00:00:00', 60000, 'Cash', NULL, NULL, NULL, 'RP-00217', NULL, NULL),
(221, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-15 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00218', NULL, NULL),
(222, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-15 00:00:00', 40000, 'Cash', NULL, NULL, NULL, 'RP-00219', NULL, NULL),
(223, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-15 00:00:00', 40000, 'Cash', NULL, NULL, NULL, 'RP-00220', NULL, NULL),
(224, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-15 00:00:00', 40000, 'Customer file', 3, NULL, NULL, 'RP-00221', NULL, NULL),
(225, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-15 00:00:00', 40000, 'Cash', NULL, NULL, NULL, 'RP-00222', NULL, NULL),
(226, 19, 2, 0, 10, 10000, 5000, 2, '2025-11-16 00:04:19', 5000, '', NULL, NULL, NULL, 'RP-00223', NULL, NULL),
(227, 19, 2, 0, 10, 10000, 5000, 2, '2025-11-16 00:22:06', 5000, '', NULL, NULL, NULL, 'RP-00224', NULL, NULL),
(228, 19, 2, 0, 1, 1000, 500, 2, '2025-11-16 00:22:48', 500, '', NULL, NULL, NULL, 'RP-00225', NULL, NULL),
(229, 17, 2, 0, 1, 12000, 10000, 2, '2025-11-16 00:22:48', 2000, '', NULL, NULL, NULL, 'RP-00226', NULL, NULL),
(230, 25, 2, 0, 1, 5000, 10000, 2, '2025-11-16 00:22:48', -5000, '', NULL, NULL, NULL, 'RP-00227', NULL, NULL),
(231, 19, 2, 0, 5, 5000, 2500, 2, '2025-11-16 00:27:19', 2500, '', NULL, NULL, NULL, 'RP-00228', NULL, NULL),
(232, 17, 2, 0, 8, 96000, 80000, 2, '2025-11-16 00:27:59', 16000, '', NULL, NULL, NULL, 'RP-00229', NULL, NULL),
(233, 19, 2, 0, 5, 5000, 2500, 2, '2025-11-16 00:35:30', 2500, '', NULL, NULL, NULL, 'RP-00230', NULL, NULL),
(234, 17, 2, 0, 20, 240000, 200000, 2, '2025-11-16 00:36:09', 40000, '', NULL, NULL, NULL, 'RP-00231', NULL, NULL),
(235, 25, 2, 0, 20, 100000, 200000, 2, '2025-11-16 00:50:13', -100000, '', NULL, NULL, NULL, 'RP-00232', NULL, NULL),
(236, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 01:01:12', 1000, '', NULL, NULL, NULL, 'RP-00233', NULL, NULL),
(237, 19, 2, 0, 5, 5000, 2500, 2, '2025-11-16 01:11:44', 2500, '', NULL, NULL, NULL, 'RP-00234', NULL, NULL),
(238, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 09:33:25', 1000, '', NULL, NULL, NULL, 'RP-00235', NULL, NULL),
(239, 21, 2, 0, 2, 30000, 24000, 2, '2025-11-16 00:00:00', 6000, 'Customer file', 5, NULL, NULL, 'RP-00236', NULL, NULL),
(240, 21, 2, 0, 2, 30000, 24000, 2, '2025-11-16 00:00:00', 6000, 'Customer file', 5, NULL, NULL, 'RP-00237', NULL, NULL),
(241, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-16 00:00:00', 4000, 'Customer file', 5, NULL, NULL, 'RP-00238', NULL, NULL),
(242, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 00:00:00', 1000, 'Customer file', 5, NULL, NULL, 'RP-9196', NULL, NULL),
(243, 0, 2, 0, 0, 375000, 0, 2, '2025-11-16 15:10:27', 0, 'Customer file', 6, NULL, NULL, '0', NULL, NULL),
(244, 0, 2, 0, 0, 20000, 0, 2, '2025-11-16 15:24:21', 0, 'Customer file', 6, NULL, NULL, '0', NULL, NULL),
(245, 0, 2, 0, 0, 2000, 0, 2, '2025-11-16 15:29:44', 0, 'Customer file', 6, NULL, NULL, '0', NULL, NULL),
(246, 0, 2, 0, 0, 240000, 0, 2, '2025-11-16 15:30:40', 0, 'Customer file', 6, NULL, NULL, '0', NULL, NULL),
(247, 0, 2, 0, 0, 96000, 0, 2, '2025-11-16 15:35:58', 0, 'Customer file', 6, NULL, NULL, '0', NULL, NULL),
(248, 0, 2, 0, 0, 41000, 0, 2, '2025-11-16 15:41:52', 0, 'Customer file', 6, NULL, NULL, '0', NULL, NULL),
(249, 0, 2, 0, 0, 10000, 0, 2, '2025-11-16 15:44:11', 0, 'Customer file', 3, NULL, NULL, '0', NULL, NULL),
(250, 17, 2, 0, 2, 24000, 0, 2, '2025-11-16 15:57:43', 0, 'Customer file', 3, NULL, NULL, 'RP-9456', NULL, NULL),
(251, 21, 2, 0, 2, 30000, 24000, 2, '2025-11-16 00:00:00', 6000, 'Customer file', 6, NULL, NULL, 'INV-1518', NULL, NULL),
(252, 21, 2, 0, 2, 30000, 0, 2, '2025-11-16 16:20:32', 0, 'Customer file', 6, NULL, NULL, 'RP-3650', NULL, NULL),
(253, 24, 2, 0, 1, 1000, 2000, 2, '2025-11-16 00:00:00', -1000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00239', NULL, NULL),
(254, 19, 2, 0, 1, 1000, 500, 2, '2025-11-16 14:22:50', 500, '', NULL, NULL, NULL, 'RP-00240', NULL, NULL),
(255, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 00:00:00', 1000, 'Customer file', 3, NULL, NULL, 'INV-4827', NULL, NULL),
(256, 0, 2, 0, 0, 24000, 0, 2, '2025-11-16 16:44:31', 0, 'MTN MoMo', 0, NULL, NULL, 'RP-6285', NULL, NULL),
(257, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-16 16:49:08', 4000, 'MTN MoMo', 0, NULL, NULL, 'RP-1459', NULL, NULL),
(258, 19, 2, 0, 1, 1000, 500, 2, '2025-11-16 16:52:41', 500, 'MTN MoMo', 0, NULL, NULL, 'RP-0575', NULL, NULL),
(259, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 00:00:00', 1000, 'Customer file', 6, NULL, NULL, 'INV-5681', NULL, NULL),
(260, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-16 00:00:00', 4000, 'Customer file', 6, NULL, NULL, 'INV-8262', NULL, NULL),
(261, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-16 15:07:38', 4000, '', NULL, NULL, NULL, 'RP-00241', NULL, NULL),
(262, 40, 2, 0, 10, 60000, 50000, 2, '2025-11-16 17:08:42', 10000, 'Cash', 0, NULL, NULL, 'RP-3957', NULL, NULL),
(263, 17, 2, 0, 2, 24000, 20000, 2, '2025-11-16 00:00:00', 4000, 'Customer file', 3, NULL, NULL, 'INV-2158', NULL, NULL),
(264, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 00:00:00', 1000, 'Customer file', 6, NULL, NULL, 'INV-2400', NULL, NULL),
(265, 17, 2, 0, 2, 24000, 0, 2, '2025-11-16 17:40:59', 0, 'Customer file', 6, NULL, NULL, 'RP-9394', NULL, NULL),
(266, 19, 2, 0, 2, 2000, 0, 2, '2025-11-16 17:41:55', 0, 'Customer file', 6, NULL, NULL, 'RP-4930', NULL, NULL),
(267, 17, 2, 0, 2, 24000, 0, 2, '2025-11-16 17:46:24', 0, 'Customer file', 6, NULL, NULL, 'RP-6871', NULL, NULL),
(268, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 00:00:00', 1000, 'Customer file', 5, NULL, NULL, 'RP-7257', NULL, NULL),
(269, 21, 2, 0, 2, 30000, 0, 2, '2025-11-16 19:18:25', 0, 'Customer file', 3, NULL, NULL, 'RP-6445', NULL, NULL),
(270, 19, 2, 0, 2, 2000, 1000, 2, '2025-11-16 00:00:00', 1000, 'Customer file', 5, NULL, NULL, 'RP-0508', NULL, NULL),
(271, 0, 2, 0, 13, 73000, 114600, 2, '2025-11-17 00:00:00', -41600, 'MTN MoMo', NULL, NULL, NULL, 'RP-00242', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":2},{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":2},{\"id\":\"25\",\"name\":\"mukene plus\",\"price\":5000,\"quantity\":9}]', NULL),
(272, 19, 2, 0, 1, 1000, 500, 2, '2025-11-17 08:26:52', 500, 'MTN MoMo', 0, NULL, NULL, 'RP-7405', NULL, NULL),
(273, 21, 2, 0, 1, 15000, 12000, 2, '2025-11-17 08:26:52', 3000, 'MTN MoMo', 0, NULL, NULL, 'RP-7405', NULL, NULL),
(274, 25, 2, 0, 1, 5000, 10000, 2, '2025-11-17 08:26:52', -5000, 'MTN MoMo', 0, NULL, NULL, 'RP-7405', NULL, NULL),
(275, 19, 2, 0, 3, 3000, 0, 2, '2025-11-17 08:28:46', 0, 'Customer file', 6, NULL, NULL, 'RP-3423', NULL, NULL),
(276, 28, 2, 0, 4, 8000, 0, 2, '2025-11-17 08:28:46', 0, 'Customer file', 6, NULL, NULL, 'RP-3423', NULL, NULL),
(277, 21, 2, 0, 2, 30000, 0, 2, '2025-11-17 08:36:04', 0, 'Customer file', 6, NULL, NULL, 'RP-1947', NULL, NULL),
(278, 24, 2, 0, 2, 2000, 0, 2, '2025-11-17 08:36:04', 0, 'Customer file', 6, NULL, NULL, 'RP-1947', NULL, NULL),
(279, 35, 2, 0, 2, 4000, 0, 2, '2025-11-17 08:36:04', 0, 'Customer file', 6, NULL, NULL, 'RP-1947', NULL, NULL),
(280, 21, 2, 0, 1, 15000, 12000, 2, '2025-11-17 08:44:55', 3000, 'Cash', 0, NULL, NULL, 'RP-8932', NULL, NULL),
(281, 24, 2, 0, 1, 1000, 2000, 2, '2025-11-17 08:44:55', -1000, 'Cash', 0, NULL, NULL, 'RP-8932', NULL, NULL),
(282, 40, 2, 0, 1, 6000, 5000, 2, '2025-11-17 08:44:55', 1000, 'Cash', 0, NULL, NULL, 'RP-8932', NULL, NULL),
(283, 0, 2, 0, 16, 82000, 91000, 2, '2025-11-17 07:16:17', -9000, 'Cash', NULL, NULL, NULL, 'RP-00243', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2},{\"id\":\"25\",\"name\":\"mukene plus\",\"price\":5000,\"quantity\":4},{\"id\":\"40\",\"name\":\"Vaseline\",\"price\":6000,\"quantity\":10}]', NULL),
(284, 21, 2, 0, 3, 45000, 0, 2, '2025-11-17 09:17:14', 0, 'Customer file', 3, NULL, NULL, 'RP-7032', NULL, NULL),
(285, 25, 2, 0, 2, 10000, 0, 2, '2025-11-17 09:17:14', 0, 'Customer file', 3, NULL, NULL, 'RP-7032', NULL, NULL),
(286, 0, 2, 0, 4, 6000, 0, 2, '2025-11-17 11:01:27', 0, 'Customer file', 6, NULL, NULL, 'RP-9332', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2},{\"id\":\"35\",\"name\":\"Files\",\"price\":2000,\"quantity\":2}]', NULL),
(287, 0, 2, 0, 2, 30000, 24000, 2, '2025-11-17 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-00244', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL),
(288, 0, 2, 0, 2, 4000, 6000, 2, '2025-11-17 00:00:00', -2000, 'Cash', NULL, NULL, NULL, NULL, '[{\"id\":\"35\",\"name\":\"Files\",\"price\":2000,\"quantity\":2}]', NULL),
(289, 0, 2, 0, 2, 2000, 4000, 2, '2025-11-17 00:00:00', -2000, 'Cash', NULL, NULL, NULL, NULL, '[{\"id\":\"24\",\"name\":\"mukene\",\"price\":1000,\"quantity\":2}]', NULL),
(290, 0, 2, 0, 2, 2000, 1000, 2, '2025-11-17 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-09460', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', NULL),
(291, 0, 2, 0, 2, 50000, 40000, 2, '2025-11-17 00:00:00', 10000, 'Cash', NULL, NULL, NULL, 'RP-09461', '[{\"id\":\"45\",\"name\":\"Nivana\",\"price\":25000,\"quantity\":2}]', NULL),
(292, 0, 2, 0, 2, 2000, 1000, 2, '2025-11-17 00:00:00', 1000, 'Customer file', 5, NULL, NULL, 'RP-09463', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', NULL),
(293, 0, 2, 0, 2, 2000, 1000, 2, '2025-11-17 00:00:00', 1000, 'Customer file', 5, NULL, NULL, 'RP-09464', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', NULL),
(294, 0, 2, 0, 5, 70000, 34000, 2, '2025-11-17 00:00:00', 36000, 'Customer file', 5, NULL, NULL, 'RP-09465', '[{\"id\":\"41\",\"name\":\"Vg kit\",\"price\":20000,\"quantity\":3},{\"id\":\"37\",\"name\":\"Jesa milk\",\"price\":5000,\"quantity\":2}]', NULL),
(295, 0, 2, 0, 2, 4000, 4600, 2, '2025-11-17 09:58:14', -600, 'Cash', NULL, NULL, NULL, 'RP-09482', '[{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":2}]', NULL),
(296, 0, 2, 0, 2, 30000, 24000, 2, '2025-11-17 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-09483', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL),
(297, 0, 2, 0, 4, 80000, 40000, 2, '2025-11-17 00:00:00', 40000, 'Cash', NULL, NULL, NULL, 'RP-09484', '[{\"id\":\"41\",\"name\":\"Vg kit\",\"price\":20000,\"quantity\":4}]', NULL),
(298, 0, 2, 0, 2, 100000, 50000, 2, '2025-11-17 09:59:41', 50000, 'Cash', NULL, NULL, NULL, 'RP-09485', '[{\"id\":\"39\",\"name\":\"Perfume\",\"price\":50000,\"quantity\":2}]', NULL),
(299, 0, 2, 0, 4, 42000, 0, 2, '2025-11-17 12:00:40', 0, 'Customer file', 3, NULL, NULL, 'RP-09486', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2},{\"id\":\"38\",\"name\":\"Sumz cookies\",\"price\":6000,\"quantity\":2}]', NULL),
(300, 0, 2, 0, 2, 30000, 24000, 2, '2025-11-17 10:29:32', 6000, 'Cash', NULL, NULL, NULL, 'RP-09487', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL),
(301, 0, 2, 0, 2, 10000, 20000, 2, '2025-11-17 11:07:43', -10000, 'Cash', NULL, NULL, NULL, 'RP-09488', '[{\"id\":\"25\",\"name\":\"mukene plus\",\"price\":5000,\"quantity\":2}]', NULL),
(302, 0, 2, 0, 2, 24000, 20000, 2, '2025-11-17 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-09489', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":2}]', NULL),
(303, 0, 2, 0, 2, 4000, 4600, 2, '2025-11-17 00:00:00', -600, 'Cash', NULL, NULL, NULL, 'RP-09490', '[{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":2}]', NULL),
(304, 0, 2, 0, 2, 24000, 20000, 2, '2025-11-17 00:00:00', 4000, 'Cash', NULL, NULL, NULL, 'RP-09491', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":2}]', NULL),
(305, 0, 2, 0, 2, 10000, 20000, 2, '2025-11-18 00:00:00', -10000, 'Cash', NULL, NULL, NULL, 'RP-09492', '[{\"id\":\"25\",\"name\":\"mukene plus\",\"price\":5000,\"quantity\":2}]', NULL),
(306, 0, 2, 0, 3, 3000, 6000, 2, '2025-11-18 00:00:00', -3000, 'Cash', NULL, NULL, NULL, 'RP-09493', '[{\"id\":\"24\",\"name\":\"mukene\",\"price\":1000,\"quantity\":3}]', NULL),
(307, 0, 2, 0, 3, 75000, 60000, 2, '2025-11-18 00:00:00', 15000, 'Cash', NULL, NULL, NULL, 'RP-09494', '[{\"id\":\"46\",\"name\":\"Eversest\",\"price\":25000,\"quantity\":3}]', NULL),
(308, 0, 2, 0, 5, 8000, 0, 2, '2025-11-18 20:22:38', 0, 'Customer file', 6, NULL, NULL, 'RP-09495', '[{\"id\":\"24\",\"name\":\"mukene\",\"price\":1000,\"quantity\":2},{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":3}]', NULL),
(309, 0, 2, 0, 2, 10000, 20000, 2, '2025-11-18 18:22:55', -10000, 'Airtel Money', NULL, NULL, NULL, 'RP-09496', '[{\"id\":\"25\",\"name\":\"mukene plus\",\"price\":5000,\"quantity\":2}]', NULL),
(310, 0, 2, 0, 2, 30000, 24000, 2, '2025-11-18 00:00:00', 6000, 'Cash', NULL, NULL, NULL, 'RP-09497', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL),
(311, 0, 2, 0, 1, 25000, 20000, 2, '2025-11-18 00:00:00', 5000, 'Cash', NULL, NULL, NULL, 'RP-09498', '[{\"id\":\"46\",\"name\":\"Eversest\",\"price\":25000,\"quantity\":1}]', NULL),
(312, 0, 2, 0, 1, 15000, 12000, 2, '2025-11-18 00:00:00', 3000, 'Cash', NULL, NULL, NULL, 'RP-09499', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL),
(313, 0, 2, 0, 1, 15000, 12000, 2, '2025-11-18 00:00:00', 3000, 'Cash', NULL, NULL, NULL, 'RP-09500', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL),
(314, 0, 2, 0, 1, 15000, 12000, 2, '2025-11-18 00:00:00', 3000, 'Cash', NULL, NULL, NULL, 'RP-09501', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL),
(315, 0, 2, 0, 1, 5000, 2000, 2, '2025-11-18 00:00:00', 3000, 'Customer file', 5, NULL, NULL, 'RP-09502', '[{\"id\":\"37\",\"name\":\"Jesa milk\",\"price\":5000,\"quantity\":1}]', NULL),
(316, 0, 2, 0, 1, 0, 12000, 2, '2025-11-19 07:58:30', 3000, 'Customer file', NULL, NULL, NULL, 'INV-00020', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":1}]', NULL),
(317, 0, 2, 0, 2, 0, 24000, 2, '2025-11-19 08:51:30', 6000, 'Customer file', NULL, NULL, NULL, 'INV-00021', '[{\"id\":\"21\",\"name\":\"gonja\",\"price\":15000,\"quantity\":2}]', NULL),
(318, 0, 2, 0, 2, 0, 4000, 2, '2025-11-19 09:07:59', -2000, 'Customer file', NULL, NULL, NULL, 'INV-00022', '[{\"id\":\"24\",\"name\":\"mukene\",\"price\":1000,\"quantity\":2}]', NULL),
(319, 0, 2, 0, 1, 20000, 15000, 2, '2025-11-19 00:00:00', 5000, 'Cash', NULL, NULL, NULL, 'RP-09503', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', NULL),
(320, 0, 2, 0, 1, 20000, 15000, 2, '2025-11-19 00:00:00', 5000, 'Customer file', 5, NULL, NULL, 'RP-09504', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', NULL),
(321, 0, 2, 0, 1, 0, 15000, 2, '2025-11-19 09:12:07', 5000, 'Customer file', NULL, NULL, NULL, 'INV-00024', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', NULL),
(322, 0, 2, 0, 1, 20000, 0, 2, '2025-11-19 11:25:24', 0, 'Customer file', 3, NULL, NULL, 'RP-09505', '[{\"id\":\"36\",\"name\":\"Loko eggs\",\"price\":20000,\"quantity\":1}]', NULL),
(323, 0, 2, 0, 2, 25000, 12000, 2, '2025-12-17 16:11:25', 13000, 'Cash', NULL, NULL, NULL, 'RC-00001', '[{\"id\":41,\"name\":\"Vg kit\",\"quantity\":1,\"price\":20000},{\"id\":47,\"name\":\"Water\",\"quantity\":1,\"price\":5000}]', NULL),
(324, 0, 2, 0, 2, 25000, 12000, 2, '2025-12-17 16:12:54', 13000, 'Cash', NULL, NULL, NULL, 'RC-00002', '[{\"id\":41,\"name\":\"Vg kit\",\"quantity\":1,\"price\":20000},{\"id\":47,\"name\":\"Water\",\"quantity\":1,\"price\":5000}]', NULL),
(325, 0, 2, 0, 2, 25000, 12000, 2, '2025-12-17 16:12:55', 13000, 'Cash', NULL, NULL, NULL, 'RC-00003', '[{\"id\":41,\"name\":\"Vg kit\",\"quantity\":1,\"price\":20000},{\"id\":47,\"name\":\"Water\",\"quantity\":1,\"price\":5000}]', NULL),
(326, 0, 2, 0, 3, 6000, 9000, 2, '2025-12-17 16:18:03', -3000, 'Cash', NULL, NULL, NULL, 'RC-251217-0001', '[{\"id\":35,\"name\":\"Files\",\"quantity\":3,\"price\":2000}]', NULL),
(327, 0, 2, 0, 3, 6000, 9000, 2, '2025-12-17 16:24:05', -3000, 'Cash', NULL, NULL, NULL, 'RC-00004', '[{\"id\":35,\"name\":\"Files\",\"quantity\":3,\"price\":2000}]', NULL),
(328, 0, 2, 0, 1, 2000, 3000, 2, '2025-12-17 20:08:16', -1000, 'Cash', NULL, NULL, NULL, 'RC-00005', '[{\"id\":35,\"name\":\"Files\",\"quantity\":1,\"price\":2000}]', NULL),
(329, 0, 2, 0, 1, 1000, 500, 2, '2025-12-17 00:00:00', 500, 'Cash', NULL, NULL, NULL, 'RP-09506', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', NULL),
(330, 0, 2, 0, 1, 12000, 10000, 2, '2025-12-17 00:00:00', 2000, 'Cash', NULL, NULL, NULL, 'RP-09507', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', NULL),
(331, 0, 2, 0, 1, 25000, 20000, 2, '2025-12-17 20:21:07', 5000, 'Cash', NULL, NULL, NULL, 'RP-09508', '[{\"id\":46,\"name\":\"Eversest\",\"quantity\":1,\"price\":25000}]', NULL),
(332, 0, 2, 0, 1, 25000, 20000, 2, '2025-12-17 20:27:12', 5000, 'Cash', NULL, NULL, NULL, 'RP-09509', '[{\"id\":46,\"name\":\"Eversest\",\"quantity\":1,\"price\":25000}]', NULL),
(333, 0, 2, 0, 1, 25000, 20000, 2, '2025-12-17 00:00:00', 5000, 'Cash', NULL, NULL, NULL, 'RP-09510', '[{\"id\":\"46\",\"name\":\"Eversest\",\"price\":25000,\"quantity\":1}]', NULL),
(334, 0, 2, 0, 1, 25000, 20000, 2, '2025-12-17 21:14:31', 5000, 'Cash', NULL, NULL, NULL, 'RP-09511', '[{\"id\":46,\"name\":\"Eversest\",\"quantity\":1,\"price\":25000}]', NULL),
(335, 0, 2, 0, 2, 27000, 22300, 2, '2025-12-17 21:34:12', 4700, 'Cash', NULL, NULL, NULL, 'RP-09512', '[{\"id\":28,\"name\":\"Biscuits\",\"quantity\":1,\"price\":2000},{\"id\":46,\"name\":\"Eversest\",\"quantity\":1,\"price\":25000}]', NULL),
(336, 0, 2, 0, 1, 15000, 12000, 2, '2025-12-17 23:02:57', 3000, 'MTN MoMo', NULL, NULL, NULL, 'RP-09513', '[{\"id\":21,\"name\":\"gonja\",\"quantity\":1,\"price\":15000}]', NULL),
(337, 0, 2, 0, 1, 15000, 12000, 2, '2025-12-18 00:41:22', 3000, 'MTN MoMo', NULL, NULL, NULL, 'RP-09514', '[{\"id\":21,\"name\":\"gonja\",\"quantity\":1,\"price\":15000}]', NULL),
(338, 0, 2, 0, 1, 2000, 3000, 2, '2025-12-18 01:02:42', -1000, 'Cash', NULL, NULL, NULL, 'RP-09515', '[{\"id\":35,\"name\":\"Files\",\"quantity\":1,\"price\":2000}]', NULL),
(339, 0, 2, 0, 70, 110000, 0, 2, '2025-12-18 12:48:36', 0, 'Customer file', 6, NULL, NULL, 'RP-09517', '[{\"id\":\"28\",\"name\":\"Biscuits\",\"price\":2000,\"quantity\":20},{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":30},{\"id\":\"35\",\"name\":\"Files\",\"price\":2000,\"quantity\":20}]', '2025-12-18'),
(340, 0, 2, 0, 2, 4000, 4600, 2, '2025-12-18 14:09:27', -600, 'Cash', NULL, NULL, NULL, 'RP-09539', '[{\"id\":28,\"name\":\"Biscuits\",\"quantity\":2,\"price\":2000}]', NULL),
(341, 0, 2, 0, 2, 2000, 1000, 2, '2025-12-19 17:48:22', 1000, 'Cash', NULL, NULL, NULL, 'RP-09541', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', '2025-12-19'),
(342, 0, 2, 0, 2, 2000, 0, 2, '2025-12-19 19:49:14', 0, 'Customer file', 6, NULL, NULL, 'RP-09542', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', '2025-12-19'),
(343, 0, 2, 0, 1, 12000, 0, 2, '2025-12-19 20:14:45', 0, '', NULL, NULL, NULL, 'RP-09545', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":1}]', '2025-12-19'),
(344, 0, 2, 0, 30000, 1500000000, 6000000, 2, '2025-12-19 00:00:00', 1494000000, 'Cash', NULL, NULL, NULL, 'RP-09546', '[{\"id\":\"48\",\"name\":\"benzen\",\"price\":50000,\"quantity\":30000}]', NULL),
(345, 0, 2, 0, 2, 2000, 0, 2, '2026-01-09 13:05:52', 0, '', NULL, NULL, NULL, 'RP-09547', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":2}]', '2026-01-09'),
(346, 0, 2, 0, 1, 1000, 500, 1, '2026-07-17 08:27:05', 500, 'Airtel Money', NULL, NULL, NULL, 'RP-09548', '[{\"id\":\"19\",\"name\":\"onions\",\"price\":1000,\"quantity\":1}]', '2025-12-19'),
(347, 0, 2, 0, 2, 24000, 20000, 1, '2026-07-17 08:27:29', 4000, 'Airtel Money', NULL, NULL, NULL, 'RP-09549', '[{\"id\":\"17\",\"name\":\"maize\",\"price\":12000,\"quantity\":2}]', '2025-12-19');

-- --------------------------------------------------------

--
-- Table structure for table `stock`
--

CREATE TABLE `stock` (
  `id` int(11) NOT NULL,
  `product-id` int(11) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `quantity-received` int(11) NOT NULL,
  `received-by` varchar(255) NOT NULL,
  `data` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_products`
--

CREATE TABLE `store_products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `selling-price` decimal(10,2) NOT NULL,
  `buying-price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `branch-id` int(11) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `store_products`
--

INSERT INTO `store_products` (`id`, `name`, `barcode`, `selling-price`, `buying-price`, `stock`, `branch-id`, `expiry_date`, `created_at`) VALUES
(1, 'Water', '6009622620027', 5000.00, 2000.00, 20, 2, '2025-12-20', '2025-12-16 20:01:56'),
(2, 'benzen', '5', 50000.00, 200.00, 40000, 2, '2025-12-30', '2025-12-19 18:07:24');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `contact` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `location`, `created_at`, `contact`, `email`) VALUES
(20, 'Omar Muammar', 'Kampala', '2025-10-30 20:52:57', '0771827046', 'katsomar60@gmail.com'),
(24, 'Victor Kiberu', 'Akright', '2025-10-30 23:06:36', '46561374', 'kiberu@gmail.com'),
(25, 'Omar Muammar', 'Kampala', '2025-11-07 18:06:48', '0771827046', '');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_products`
--

CREATE TABLE `supplier_products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_products`
--

INSERT INTO `supplier_products` (`id`, `supplier_id`, `product_name`, `unit_price`) VALUES
(2, 20, 'beans', 2000.00),
(3, 24, 'Vim', 9000.00),
(4, 24, 'Salt', 3000.00),
(5, 24, 'Single packs', 14500.00),
(6, 24, 'vaseline', 5000.00);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_transactions`
--

CREATE TABLE `supplier_transactions` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `date_time` datetime NOT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `products_supplied` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `amount_paid` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_transactions`
--

INSERT INTO `supplier_transactions` (`id`, `supplier_id`, `date_time`, `branch`, `products_supplied`, `quantity`, `unit_price`, `amount`, `payment_method`, `amount_paid`, `balance`) VALUES
(33, 24, '2025-11-07 19:01:43', 'mansa', 'Salt', 40, 3000.00, 120000.00, '', 0.00, 0.00),
(34, 24, '2025-11-07 19:01:43', 'mansa', 'Single packs', 20, 14500.00, 290000.00, '', 0.00, 0.00),
(35, 24, '2025-11-07 19:01:43', 'mansa', 'vaseline', 50, 5000.00, 250000.00, '', 0.00, 0.00),
(36, 24, '2025-11-07 19:01:43', 'mansa', 'Vim', 20, 9000.00, 180000.00, '', 0.00, 0.00),
(37, 24, '2025-11-07 19:02:02', NULL, 'Salt', 40, 3000.00, 120000.00, '', 120000.00, 0.00),
(38, 24, '2025-11-07 19:02:20', NULL, 'Single packs', 20, 14500.00, 290000.00, '', 100000.00, 190000.00),
(39, 24, '2025-11-07 19:02:40', NULL, 'Single packs', 20, 14500.00, 290000.00, '', 90000.00, 100000.00),
(40, 24, '2025-11-07 19:03:07', NULL, 'Single packs', 20, 14500.00, 290000.00, '', 100000.00, 0.00),
(41, 24, '2025-11-07 19:03:14', NULL, 'vaseline', 50, 5000.00, 250000.00, '', 250000.00, 0.00),
(42, 24, '2025-11-07 19:03:46', NULL, 'Vim', 20, 9000.00, 180000.00, '', 180000.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `tills`
--

CREATE TABLE `tills` (
  `id` int(11) NOT NULL,
  `creation_date` date NOT NULL,
  `name` varchar(255) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tills`
--

INSERT INTO `tills` (`id`, `creation_date`, `name`, `branch_id`, `staff_id`, `phone_number`) VALUES
(1, '2025-11-09', 'Till 1', 2, 2, '07718222154'),
(2, '2025-11-09', 'Till 2', 2, 3, '25463245435453'),
(3, '2025-11-13', 'Till 3', 1, 7, '326551656526');

-- --------------------------------------------------------

--
-- Table structure for table `till_removals`
--

CREATE TABLE `till_removals` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `till_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `till_removals`
--

INSERT INTO `till_removals` (`id`, `branch_id`, `till_id`, `amount`, `approved_by`, `balance_after`, `created_at`) VALUES
(1, 2, 2, 1000000.00, 5, 850000.00, '2025-11-13 23:06:35'),
(2, 2, 2, -500000.00, 1, 1350000.00, '2025-11-13 23:12:07'),
(3, 1, 3, 100000.00, 5, 650000.00, '2025-11-13 23:21:02'),
(4, 1, 3, -100000.00, 1, 750000.00, '2025-11-13 23:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `till_sales`
--

CREATE TABLE `till_sales` (
  `id` int(11) NOT NULL,
  `till_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `total_sales` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `till_transactions`
--

CREATE TABLE `till_transactions` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `till_id` int(11) NOT NULL,
  `event_type` enum('removal','sale') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `reference_removal_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `till_transactions`
--

INSERT INTO `till_transactions` (`id`, `branch_id`, `till_id`, `event_type`, `amount`, `balance_after`, `reference_removal_id`, `approved_by`, `created_at`) VALUES
(1, 2, 2, 'removal', 1000000.00, 850000.00, 1, 5, '2025-11-13 23:06:35'),
(2, 2, 2, '', 500000.00, 1350000.00, 2, 1, '2025-11-13 23:12:07'),
(3, 1, 3, 'removal', 100000.00, 650000.00, 3, 5, '2025-11-13 23:21:02'),
(4, 1, 3, '', 100000.00, 750000.00, 4, 1, '2025-11-13 23:22:07');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `debit_account_id` int(11) DEFAULT NULL,
  `credit_account_id` int(11) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `date`, `description`, `debit_account_id`, `credit_account_id`, `amount`) VALUES
(1, '2025-11-06', 'welfare', 1, 1, 1000000.00),
(2, '2025-11-06', 'utilities', 1, 1, 500000.00),
(3, '2025-11-06', 'Loan from the bank', 2, 2, 200000.00),
(4, '2025-11-06', 'Loan from the bank', 2, 1, 200000.00),
(5, '2025-11-06', 'buying furniture', 3, 5, 100000.00),
(6, '2025-11-06', 'incomes', 4, 1, 500000.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','manager','super') NOT NULL,
  `phone` int(11) NOT NULL,
  `branch-id` int(11) NOT NULL,
  `business_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `phone`, `branch-id`, `business_id`, `created_at`) VALUES
(1, 'Den', 'katsomar60@gmail.com', '$2y$10$clqqQDo.Dv/3I1k3IjPEV.qcE3zgkckMvdOw/NzXxoRheiBYHb6aW', 'admin', 771827046, 0, NULL, '2025-10-25 22:11:24'),
(2, 'Aisha', 'aisha@gmail.com', '$2y$10$tqmsCWGzhvja//bLWhTq5O7AME6mzNTQyMa3IMO2gL8mHTGA/XrJe', 'staff', 5478930, 2, NULL, '2025-10-25 22:11:24'),
(3, 'len', 'len@gmail.com', '$2y$10$SBsnz0sbtv48njlwGCpzAu.44NlMvB8av4Yu58N2v8Qd3OQN7nNn2', 'staff', 2147483647, 2, NULL, '2025-10-25 22:11:24'),
(4, 'luk', 'luk@gmail.com', '$2y$10$gsURMrUIL9ES1vggoa28ruRHAj3u4A2xmjba5rF5FthPfAqscaff2', 'staff', 57890876, 1, NULL, '2025-10-25 22:11:24'),
(5, 'fish', 'fish@gmail.com', '$2y$10$QqVdYI0ZPySYd3U9klKawu6ZM3TDJjVxT0894/SZyXjbf5XRXxrKS', 'manager', 57890876, 0, NULL, '2025-10-25 22:11:24'),
(6, 'gerald', 'gerald@gmail.com', '$2y$10$XdXhVz87lu8VoynnIz5eLe24FHxcz762FswArNT8Db/f6zfugKbq.', 'manager', 2147483647, 0, NULL, '2025-10-25 22:11:24'),
(7, 'Gen', 'gen@gmail.com', '$2y$10$1KY9J7xmdyXwmkxoxTeFquknkosQeq8aNyzc6Rc6kF.YNtyptvOXu', 'staff', 2147483647, 1, NULL, '2025-10-25 22:11:24'),
(8, 'Omar', 'katsomar@gmail.com', '12345', 'super', 4114184, 0, NULL, '2025-10-25 22:11:24'),
(9, 'denis', 'denis@gmail.com', '12345\r\n', '', 144489489, 0, NULL, '2025-10-25 22:11:24'),
(10, 'benis', 'benis@gmail.com', '$2y$10$qB64tc97/86iLO/kBifeK.FNyatH.EF25znwTmoFkzccMhLLu4FsW', 'super', 4824894, 0, NULL, '2025-10-25 22:11:24'),
(11, 'Daudi', 'daudi@gmail.com', '$2y$10$keSinAhmMk.pKm7.ot6pa.ans7pwHXZqj6y9nkFzyYOnv6dB12nc.', 'admin', 1255655, 0, 1, '2025-11-15 13:54:42'),
(12, 'kimk', 'kimk@gmail.com', '$2y$10$tGDWKPlH2mtudZZqTRsEK.gi6sRT32HGgKN/Yac8QopYoJ9aXOube', 'super', 769413480, 0, 2, '2025-11-28 18:51:48'),
(14, 'kimManager', 'kimmananger@gmail.com', '$2y$10$oosQZbyQxJGZ3UfwQY0aFeRA7z99ZYrHAfDiKd6HKFfdkTH9Bp8JO', 'manager', 712346578, 0, NULL, '2025-11-28 19:58:25'),
(15, 'kimk2', 'kimk2@gmail.com', '$2y$10$zafQromr8MmALvkOgbU/GuJigHskmT5Mq1j1vqR3.rz33YtQkihNS', 'admin', 712345678, 0, 4, '2025-11-28 20:30:21'),
(26, 'kimManager2', 'kimk22@gmail.com', '$2y$10$R1Rn/EE.pG/orm6Ny3m8N.IMN4L3dT.tuLetNgSyNwag5mN.ZFHEu', 'admin', 712345678, 0, 4, '2025-11-28 21:09:44'),
(27, 'kimManager3', 'kimkk@gmail.com', '$2y$10$YeVHJUBHKa3LnE6iiH4aYuZ3RyuuUs/PPeelLeO0nye0GLJXsV7ti', 'manager', 712345678, 0, 4, '2025-12-01 22:23:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch`
--
ALTER TABLE `branch`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_products`
--
ALTER TABLE `branch_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_branch_product` (`branch_id`,`product_id`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cash_book`
--
ALTER TABLE `cash_book`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_settings`
--
ALTER TABLE `company_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_transactions`
--
ALTER TABLE `customer_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `idx_branch_id` (`branch_id`);

--
-- Indexes for table `debtors`
--
ALTER TABLE `debtors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `branch-id` (`branch-id`),
  ADD KEY `product` (`product`),
  ADD KEY `spent-by` (`spent-by`);

--
-- Indexes for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_branch_id` (`branch_id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `petty_cash_balance`
--
ALTER TABLE `petty_cash_balance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `petty_cash_transactions`
--
ALTER TABLE `petty_cash_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_barcode_branch` (`barcode`,`branch-id`);

--
-- Indexes for table `profits`
--
ALTER TABLE `profits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receipt_counter`
--
ALTER TABLE `receipt_counter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prefix` (`prefix`);

--
-- Indexes for table `receipt_sequence`
--
ALTER TABLE `receipt_sequence`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_prefix_date` (`prefix`,`date`);

--
-- Indexes for table `remote_orders`
--
ALTER TABLE `remote_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_reference` (`order_reference`),
  ADD KEY `idx_order_reference` (`order_reference`),
  ADD KEY `idx_branch_id` (`branch_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `fk_remote_orders_processed_by` (`processed_by`);

--
-- Indexes for table `remote_order_audit_logs`
--
ALTER TABLE `remote_order_audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_action` (`action`);

--
-- Indexes for table `remote_order_items`
--
ALTER TABLE `remote_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `store_products`
--
ALTER TABLE `store_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `barcode` (`barcode`),
  ADD KEY `branch-id` (`branch-id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `tills`
--
ALTER TABLE `tills`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `till_removals`
--
ALTER TABLE `till_removals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `till_sales`
--
ALTER TABLE `till_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `till_id` (`till_id`);

--
-- Indexes for table `till_transactions`
--
ALTER TABLE `till_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branch_id` (`branch_id`),
  ADD KEY `till_id` (`till_id`),
  ADD KEY `event_type` (`event_type`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `debit_account_id` (`debit_account_id`),
  ADD KEY `credit_account_id` (`credit_account_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch`
--
ALTER TABLE `branch`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branch_products`
--
ALTER TABLE `branch_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cash_book`
--
ALTER TABLE `cash_book`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `company_settings`
--
ALTER TABLE `company_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customer_transactions`
--
ALTER TABLE `customer_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `debtors`
--
ALTER TABLE `debtors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `petty_cash_balance`
--
ALTER TABLE `petty_cash_balance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `petty_cash_transactions`
--
ALTER TABLE `petty_cash_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `profits`
--
ALTER TABLE `profits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `receipt_counter`
--
ALTER TABLE `receipt_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `receipt_sequence`
--
ALTER TABLE `receipt_sequence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remote_orders`
--
ALTER TABLE `remote_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `remote_order_audit_logs`
--
ALTER TABLE `remote_order_audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `remote_order_items`
--
ALTER TABLE `remote_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=348;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_products`
--
ALTER TABLE `store_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `supplier_products`
--
ALTER TABLE `supplier_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `tills`
--
ALTER TABLE `tills`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `till_removals`
--
ALTER TABLE `till_removals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `till_sales`
--
ALTER TABLE `till_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `till_transactions`
--
ALTER TABLE `till_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_transactions`
--
ALTER TABLE `customer_transactions`
  ADD CONSTRAINT `customer_transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_2` FOREIGN KEY (`branch-id`) REFERENCES `branch` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_3` FOREIGN KEY (`product`) REFERENCES `supplier_products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `expenses_ibfk_4` FOREIGN KEY (`spent-by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_proofs`
--
ALTER TABLE `payment_proofs`
  ADD CONSTRAINT `fk_payment_proof_order` FOREIGN KEY (`order_id`) REFERENCES `remote_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remote_orders`
--
ALTER TABLE `remote_orders`
  ADD CONSTRAINT `fk_remote_orders_processed_by` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `remote_order_items`
--
ALTER TABLE `remote_order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `remote_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_products`
--
ALTER TABLE `store_products`
  ADD CONSTRAINT `fk_store_products_branch` FOREIGN KEY (`branch-id`) REFERENCES `branch` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD CONSTRAINT `supplier_products_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_transactions`
--
ALTER TABLE `supplier_transactions`
  ADD CONSTRAINT `supplier_transactions_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tills`
--
ALTER TABLE `tills`
  ADD CONSTRAINT `tills_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tills_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `till_sales`
--
ALTER TABLE `till_sales`
  ADD CONSTRAINT `till_sales_ibfk_1` FOREIGN KEY (`till_id`) REFERENCES `tills` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`debit_account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`credit_account_id`) REFERENCES `accounts` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
