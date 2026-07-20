-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2026 at 11:10 AM
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
-- Database: `shop_system`
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
(7, 'Akright Shop', 'Bwebajja', 72625266, 'default', 1);

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
(1, 'default', 'Loko harvest', NULL, NULL, '0000000000', 'Default Address', '2026-07-17 08:14:10', 'active', NULL, NULL, 'pending');

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
(8, 'Annette Mehangye', '0702326677', 'ann@gmail.com', 'MTN MoMo', 0.00, 0.00, '2026-07-18 13:56:08', 0.00, '2026-07-18', 0),
(9, 'Grania walnut', '', 'grania@gmail.com', 'Customer File', 0.00, 0.00, '2026-07-18 14:04:36', 280000.00, '2026-07-18', 0),
(10, 'FIFO Test Customer', '123456', 'fifo@test.com', NULL, 0.00, 0.00, '2026-07-19 10:00:17', 25000.00, '2026-07-19', 0),
(11, 'FIFO Test Customer', '123456', 'fifo@test.com', NULL, 0.00, 0.00, '2026-07-19 10:00:29', 25000.00, '2026-07-19', 0),
(13, 'FIFO Test Customer', '123456', 'fifo@test.com', NULL, 0.00, 0.00, '2026-07-19 10:00:51', 10000.00, '2026-07-19', 0),
(16, 'test 1', '10895782606', 't@gmail.com', '', 0.00, 0.00, '2026-07-19 10:02:25', 37000.00, '2026-07-19', 0);

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
  `business_id` int(11) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_transactions`
--

INSERT INTO `customer_transactions` (`id`, `customer_id`, `branch_id`, `date_time`, `products_bought`, `amount_paid`, `amount_credited`, `sold_by`, `status`, `invoice_receipt_no`, `due_date`, `business_id`, `payment_method`) VALUES
(18, 8, 7, '2026-07-18 09:58:04', '[{\"id\":\"53\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":1}]', 0.00, 13000.00, 'Daudi', 'paid', 'INV-00001', NULL, 0, NULL),
(19, 9, 7, '2026-07-18 10:06:14', '[{\"id\":\"57\",\"name\":\"White singles\",\"price\":14500,\"quantity\":10},{\"id\":\"58\",\"name\":\"White 15 pack\",\"price\":10000,\"quantity\":10},{\"id\":\"59\",\"name\":\"white 6 pack\",\"price\":3500,\"quantity\":10}]', 0.00, 280000.00, 'Daudi', 'debtor', 'INV-00002', NULL, 0, NULL),
(20, 10, 1, '2026-07-19 13:00:17', '[{\"name\":\"Item A\",\"qty\":2,\"price\":5000}]', 0.00, 10000.00, 'staff', 'debtor', 'INV-FIFO1', NULL, 0, 'Invoice'),
(21, 10, 1, '2026-07-19 13:00:22', '[{\"name\":\"Item B\",\"qty\":1,\"price\":15000}]', 0.00, 15000.00, 'staff', 'debtor', 'INV-FIFO2', NULL, 0, 'Invoice'),
(22, 11, 1, '2026-07-19 13:00:29', '[{\"name\":\"Item A\",\"qty\":2,\"price\":5000}]', 0.00, 10000.00, 'staff', 'debtor', 'INV-FIFO1', NULL, 0, 'Invoice'),
(23, 11, 1, '2026-07-19 13:00:34', '[{\"name\":\"Item B\",\"qty\":1,\"price\":15000}]', 0.00, 15000.00, 'staff', 'debtor', 'INV-FIFO2', NULL, 0, 'Invoice'),
(26, 13, 1, '2026-07-19 13:00:51', '[{\"name\":\"Item A\",\"qty\":2,\"price\":5000}]', 0.00, 10000.00, 'staff', 'paid', 'INV-FIFO1', NULL, 0, 'Invoice'),
(27, 13, 1, '2026-07-19 13:00:56', '[{\"name\":\"Item B\",\"qty\":1,\"price\":15000}]', 0.00, 10000.00, 'staff', 'debtor', 'INV-FIFO2', NULL, 0, 'Invoice'),
(28, 13, 1, '2026-07-19 13:00:51', 'receipt for invoice number INV-FIFO1 on date 2026-07-19 for products Item A x2', 10000.00, 0.00, 'test_admin', 'paid', 'RP-20260719130051', NULL, 0, 'Airtel Money'),
(29, 13, 1, '2026-07-19 13:00:51', 'partial payment receipt for invoice number INV-FIFO2 on date 2026-07-19 for products Item B x1', 5000.00, 0.00, 'test_admin', 'paid', 'RP-20260719130051', NULL, 0, 'Airtel Money'),
(38, 16, 7, '2026-07-19 13:03:31', '[{\"id\":\"138\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', 0.00, 11000.00, 'Daudi', 'debtor', 'INV-00009', NULL, 0, 'Invoice'),
(39, 16, 7, '2026-07-19 13:10:35', 'partial payment receipt for invoice number INV-00009 on date 2026-07-19 for products Brown Trays x2', 15000.00, 0.00, 'Daudi', 'paid', 'RP-00026', NULL, 0, 'MTN MoMo'),
(40, 8, 7, '2026-07-19 13:12:42', 'receipt for invoice number INV-00001 on date 2026-07-18 for products Brown Trays x1', 13000.00, 0.00, 'Daudi', 'paid', 'RP-00027', NULL, 0, 'MTN MoMo'),
(42, 16, 7, '2026-07-19 13:24:38', '[{\"id\":\"138\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', 0.00, 26000.00, 'Daudi', 'debtor', 'INV-00013', NULL, 0, 'Invoice'),
(43, 16, 7, '2026-07-19 13:25:18', '[{\"id\":\"138\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', 26000.00, 0.00, 'Daudi', 'paid', 'RP-00030', NULL, 0, 'Airtel Money');

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
  `stock` decimal(12,2) DEFAULT 0.00,
  `branch-id` int(11) NOT NULL,
  `business_id` int(11) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `sms_sent` tinyint(1) DEFAULT 0,
  `location` varchar(20) DEFAULT 'shelf',
  `image_path` varchar(255) DEFAULT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `opening_stock` decimal(12,2) DEFAULT 0.00,
  `incoming_stock` decimal(12,2) DEFAULT 0.00,
  `outgoing` decimal(12,2) DEFAULT 0.00,
  `damages` decimal(12,2) DEFAULT 0.00,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `barcode`, `category`, `buying-price`, `selling-price`, `stock`, `branch-id`, `business_id`, `expiry_date`, `sms_sent`, `location`, `image_path`, `visible`, `opening_stock`, `incoming_stock`, `outgoing`, `damages`, `date`) VALUES
(51, 'White trays', '0', NULL, 0, 15000, 7.00, 7, 1, '2026-09-18', 0, 'shelf', NULL, 1, 11.00, 0.00, 4.00, 0.00, '2026-07-18'),
(53, 'Brown Trays', '2', NULL, 0, 13000, 16.00, 7, 1, '2027-06-18', 0, 'shelf', NULL, 1, 3.00, 20.00, 7.00, 0.00, '2026-07-18'),
(54, 'white Whole sale', '4', NULL, 0, 14000, 0.00, 7, 1, '2027-05-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(56, 'Dressed birds', '7', NULL, 0, 20000, 5.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 9.00, 0.00, 4.00, 0.00, '2026-07-18'),
(57, 'White singles', '8', NULL, 0, 14500, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 10.00, 0.00, 10.00, 0.00, '2026-07-18'),
(58, 'White 15 pack', '9', NULL, 0, 10000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 10.00, 0.00, 10.00, 0.00, '2026-07-18'),
(59, 'white 6 pack', '10', NULL, 0, 3500, 0.00, 7, 1, '2027-01-18', 0, 'shelf', NULL, 1, 10.00, 0.00, 10.00, 0.00, '2026-07-18'),
(60, 'Live birds', '11', NULL, 0, 20000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(61, 'Mortalities', '12', NULL, 0, 5000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(62, 'Mortalitie whole sale', '13', NULL, 0, 3000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(63, 'Manure', '14', NULL, 0, 12000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(64, 'Manure whole sale', '15', NULL, 0, 11500, 0.00, 7, 1, '2027-05-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(65, 'Damage Tray', '16', NULL, 0, 10000, 0.00, 7, 1, '2027-10-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(66, 'Damage eggs', '17', NULL, 0, 1000, 16.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 20.00, 0.00, 4.00, 0.00, '2026-07-18'),
(67, 'whole sale brown', '18', NULL, 0, 12000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-18'),
(68, 'Cream trays', '19', NULL, 0, 20000, 2.00, 7, 1, '2027-10-18', 0, 'shelf', NULL, 1, 2.00, 0.00, 0.00, 0.00, '2026-07-18'),
(69, 'Cream whole sale', '20', NULL, 0, 19000, 8.00, 7, 1, '2027-06-18', 0, 'shelf', NULL, 1, 5.00, 16.00, 13.00, 0.00, '2026-07-18'),
(314, 'White trays', '0', NULL, 0, 15000, 7.00, 7, 1, '2026-09-18', 0, 'shelf', NULL, 1, 7.00, 0.00, 0.00, 0.00, '2026-07-19'),
(315, 'Brown Trays', '2', NULL, 0, 13000, 16.00, 7, 1, '2027-06-18', 0, 'shelf', NULL, 1, 16.00, 0.00, 0.00, 0.00, '2026-07-19'),
(316, 'white Whole sale', '4', NULL, 0, 14000, 0.00, 7, 1, '2027-05-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(317, 'Dressed birds', '7', NULL, 0, 20000, 5.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 5.00, 0.00, 0.00, 0.00, '2026-07-19'),
(318, 'White singles', '8', NULL, 0, 14500, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(319, 'White 15 pack', '9', NULL, 0, 10000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(320, 'white 6 pack', '10', NULL, 0, 3500, 0.00, 7, 1, '2027-01-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(321, 'Live birds', '11', NULL, 0, 20000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(322, 'Mortalities', '12', NULL, 0, 5000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(323, 'Mortalitie whole sale', '13', NULL, 0, 3000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(324, 'Manure', '14', NULL, 0, 12000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(325, 'Manure whole sale', '15', NULL, 0, 11500, 0.00, 7, 1, '2027-05-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(326, 'Damage Tray', '16', NULL, 0, 10000, 0.00, 7, 1, '2027-10-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(327, 'Damage eggs', '17', NULL, 0, 1000, 16.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 16.00, 0.00, 0.00, 0.00, '2026-07-19'),
(328, 'whole sale brown', '18', NULL, 0, 12000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(329, 'Cream trays', '19', NULL, 0, 20000, 2.00, 7, 1, '2027-10-18', 0, 'shelf', NULL, 1, 2.00, 0.00, 0.00, 0.00, '2026-07-19'),
(330, 'Cream whole sale', '20', NULL, 0, 2000, 8.00, 7, 1, NULL, 0, 'shelf', NULL, 1, 8.00, 0.00, 0.00, 0.00, '2026-07-19'),
(345, 'zom', '22', NULL, 0, 2000, 0.00, 7, 1, NULL, 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(347, 'bongs', '23', NULL, 0, 12000, 0.00, 7, 1, NULL, 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-19'),
(348, 'Sams', '24', NULL, 0, 12000, 0.90, 7, 1, NULL, 0, 'shelf', NULL, 1, 0.90, 0.00, 0.00, 0.00, '2026-07-19'),
(350, 'White trays', '0', NULL, 0, 15000, 7.00, 7, 1, '2026-09-18', 0, 'shelf', NULL, 1, 7.00, 0.00, 0.00, 0.00, '2026-07-20'),
(351, 'Brown Trays', '2', NULL, 0, 13000, 16.00, 7, 1, '2027-06-18', 0, 'shelf', NULL, 1, 16.00, 0.00, 0.00, 0.00, '2026-07-20'),
(352, 'white Whole sale', '4', NULL, 0, 14000, 0.00, 7, 1, '2027-05-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(353, 'Dressed birds', '7', NULL, 0, 20000, 5.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 5.00, 0.00, 0.00, 0.00, '2026-07-20'),
(354, 'White singles', '8', NULL, 0, 14500, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(355, 'White 15 pack', '9', NULL, 0, 10000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(356, 'white 6 pack', '10', NULL, 0, 3500, 0.00, 7, 1, '2027-01-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(357, 'Live birds', '11', NULL, 0, 20000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(358, 'Mortalities', '12', NULL, 0, 5000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(359, 'Mortalitie whole sale', '13', NULL, 0, 3000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(360, 'Manure', '14', NULL, 0, 12000, 0.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(361, 'Manure whole sale', '15', NULL, 0, 11500, 0.00, 7, 1, '2027-05-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(362, 'Damage Tray', '16', NULL, 0, 10000, 0.00, 7, 1, '2027-10-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(363, 'Damage eggs', '17', NULL, 0, 1000, 16.00, 7, 1, '2027-09-18', 0, 'shelf', NULL, 1, 16.00, 0.00, 0.00, 0.00, '2026-07-20'),
(364, 'whole sale brown', '18', NULL, 0, 12000, 0.00, 7, 1, '2027-11-18', 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(365, 'Cream trays', '19', NULL, 0, 20000, 2.00, 7, 1, '2027-10-18', 0, 'shelf', NULL, 1, 2.00, 0.00, 0.00, 0.00, '2026-07-20'),
(366, 'Cream whole sale', '20', NULL, 0, 2000, 8.00, 7, 1, NULL, 0, 'shelf', NULL, 1, 8.00, 0.00, 0.00, 0.00, '2026-07-20'),
(367, 'zom', '22', NULL, 0, 2000, 0.00, 7, 1, NULL, 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(368, 'bongs', '23', NULL, 0, 12000, 0.00, 7, 1, NULL, 0, 'shelf', NULL, 1, 0.00, 0.00, 0.00, 0.00, '2026-07-20'),
(369, 'Sams', '24', NULL, 0, 12000, 0.90, 7, 1, NULL, 0, 'shelf', NULL, 1, 0.90, 0.00, 0.00, 0.00, '2026-07-20');

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
('2026-07-18', 32, 7, 0, 469000, 0, 469000),
('2026-07-19', 33, 1, 0, 64920, 0, 64920),
('2026-07-19', 34, 7, 0, 54000, 0, 54000);

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
(5, 30, 'RP', '2026-07-19 10:25:18'),
(6, 13, 'INV', '2026-07-19 10:24:38');

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
  `quantity` decimal(12,2) DEFAULT 0.00,
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
(349, 0, 7, 0, 3.00, 3000, 0, 31, '2026-07-18 00:00:00', 3000, 'Cash', NULL, NULL, NULL, 'RP-00001', '[{\"id\":\"66\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL),
(350, 0, 7, 0, 1.00, 20000, 0, 31, '2026-07-18 00:00:00', 20000, 'Cash', NULL, NULL, NULL, 'RP-00002', '[{\"id\":\"56\",\"name\":\"Dressed birds\",\"price\":20000,\"quantity\":1}]', NULL),
(351, 0, 7, 0, 1.00, 13000, 0, 31, '2026-07-18 00:00:00', 13000, 'Cash', NULL, NULL, NULL, 'RP-00003', '[{\"id\":\"53\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":1}]', NULL),
(352, 0, 7, 0, 2.00, 40000, 0, 31, '2026-07-18 00:00:00', 40000, 'Cash', NULL, NULL, NULL, 'RP-00004', '[{\"id\":\"56\",\"name\":\"Dressed birds\",\"price\":20000,\"quantity\":2}]', NULL),
(353, 0, 7, 0, 13.00, 247000, 0, 31, '2026-07-18 00:00:00', 247000, 'Cash', NULL, NULL, NULL, 'RP-00005', '[{\"id\":\"69\",\"name\":\"Cream whole sale\",\"price\":19000,\"quantity\":13}]', NULL),
(354, 0, 7, 0, 2.00, 30000, 0, 31, '2026-07-18 00:00:00', 30000, 'Cash', NULL, NULL, NULL, 'RP-00006', '[{\"id\":\"51\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2}]', NULL),
(355, 0, 7, 0, 1.00, 20000, 0, 31, '2026-07-18 00:00:00', 20000, 'Cash', NULL, NULL, NULL, 'RP-00007', '[{\"id\":\"56\",\"name\":\"Dressed birds\",\"price\":20000,\"quantity\":1}]', NULL),
(356, 0, 7, 0, 1.00, 1000, 0, 31, '2026-07-18 00:00:00', 1000, 'Cash', NULL, NULL, NULL, 'RP-00008', '[{\"id\":\"66\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":1}]', NULL),
(357, 0, 7, 0, 3.00, 39000, 0, 31, '2026-07-18 00:00:00', 39000, 'Cash', NULL, NULL, NULL, 'RP-00009', '[{\"id\":\"53\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":3}]', NULL),
(358, 0, 7, 0, 2.00, 30000, 0, 31, '2026-07-18 00:00:00', 30000, 'Cash', NULL, NULL, NULL, 'RP-00010', '[{\"id\":\"51\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2}]', NULL),
(359, 0, 7, 0, 2.00, 26000, 0, 31, '2026-07-18 00:00:00', 26000, 'Cash', NULL, NULL, NULL, 'RP-00011', '[{\"id\":\"53\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', NULL),
(366, 0, 7, 0, 1.00, 0, 0, 31, '2026-07-18 09:58:04', 0, 'Customer file', NULL, NULL, 'INV-00001', NULL, '[{\"id\":\"53\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":1}]', NULL),
(367, 0, 7, 0, 30.00, 0, 0, 31, '2026-07-18 10:06:14', 0, 'Customer file', NULL, NULL, 'INV-00002', NULL, '[{\"id\":\"57\",\"name\":\"White singles\",\"price\":14500,\"quantity\":10},{\"id\":\"58\",\"name\":\"White 15 pack\",\"price\":10000,\"quantity\":10},{\"id\":\"59\",\"name\":\"white 6 pack\",\"price\":3500,\"quantity\":10}]', NULL),
(380, 0, 1, 0, 2.00, 0, 0, 1, '2026-07-19 13:00:17', 0, 'Customer file', NULL, NULL, 'INV-FIFO1', NULL, '[{\"name\":\"Item A\",\"qty\":2,\"price\":5000}]', NULL),
(381, 0, 1, 0, 1.00, 0, 0, 1, '2026-07-19 13:00:22', 0, 'Customer file', NULL, NULL, 'INV-FIFO2', NULL, '[{\"name\":\"Item B\",\"qty\":1,\"price\":15000}]', NULL),
(392, 0, 7, 0, 0.00, 15000, 0, 31, '2026-07-19 13:10:35', 15000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00026', 'partial payment receipt for invoice number INV-00009 on date 2026-07-19 for products Brown Trays x2', '2026-07-19'),
(393, 0, 7, 0, 0.00, 13000, 0, 31, '2026-07-19 13:12:42', 13000, 'MTN MoMo', NULL, NULL, NULL, 'RP-00027', 'receipt for invoice number INV-00001 on date 2026-07-18 for products Brown Trays x1', '2026-07-18'),
(398, 0, 7, 0, 2.00, 0, 0, 31, '2026-07-19 13:24:38', 0, 'Customer file', NULL, NULL, 'INV-00013', NULL, '[{\"id\":\"138\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', NULL),
(399, 0, 7, 0, 2.00, 26000, 0, 31, '2026-07-19 13:25:18', 26000, 'Airtel Money', 16, NULL, NULL, 'RP-00030', '[{\"id\":\"138\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', NULL),
(401, 0, 7, 0, 2.00, 0, 0, 31, '2026-07-19 13:03:31', 0, 'Customer file', NULL, NULL, 'INV-00009', NULL, '[{\"id\":\"138\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', NULL);

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
  `created_at` datetime DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `phone`, `branch-id`, `business_id`, `created_at`, `status`) VALUES
(1, 'Den', 'katsomar60@gmail.com', '$2y$10$clqqQDo.Dv/3I1k3IjPEV.qcE3zgkckMvdOw/NzXxoRheiBYHb6aW', 'admin', 771827046, 0, 1, '2025-10-25 22:11:24', 'active'),
(8, 'Omar', 'katsomar@gmail.com', '12345', 'super', 4114184, 0, NULL, '2025-10-25 22:11:24', 'active'),
(29, 'omar', 'o@gmail.com', '$2y$10$.7FUR3iGR8fdXvl651dgJu./mXQpBRQhppZKzMKPkMxPnR9XW0dUO', 'staff', 746154625, 0, NULL, '2026-07-17 07:51:49', 'pending'),
(30, 'omar2', 'o1@gmail.com', '$2y$10$CvEgyktj9OSKi2Recvv25.5lk1bIj8MpuotfBmnv8R2j4Z8IYElX.', 'staff', 2147483647, 7, 1, '2026-07-17 08:15:15', 'active'),
(31, 'Daudi', 'daudi@gmail.com', '$2y$10$1DNsniJOiFWLBpZJ3qgfu.2/HsGw0iQORF67H5HYwQZ3ifDDu.mS2', 'staff', 712345678, 7, 1, '2026-07-18 05:21:56', 'active');

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
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `branch-id` (`branch-id`),
  ADD KEY `product` (`product`),
  ADD KEY `spent-by` (`spent-by`);

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
  ADD UNIQUE KEY `unique_barcode_branch_date` (`barcode`,`branch-id`,`date`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1000;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `customer_transactions`
--
ALTER TABLE `customer_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `debtors`
--
ALTER TABLE `debtors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=381;

--
-- AUTO_INCREMENT for table `profits`
--
ALTER TABLE `profits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `receipt_counter`
--
ALTER TABLE `receipt_counter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `receipt_sequence`
--
ALTER TABLE `receipt_sequence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=402;

--
-- AUTO_INCREMENT for table `stock`
--
ALTER TABLE `stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
