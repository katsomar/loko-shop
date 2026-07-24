-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Host: sql300.byetcluster.com
-- Generation Time: Jul 24, 2026 at 07:05 AM
-- Server version: 11.4.12-MariaDB
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `if0_42123248_shop_system`
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
(19, 'Annette Mbabazi', '0702326677', 'ann@gmail.com', '', '0.00', '0.00', '2026-07-19 13:13:06', '70000.00', '2026-07-19', 0),
(20, 'Hajjat Lucky Kitende', '0705469384', 'l@gmail.com', '', '0.00', '0.00', '2026-07-19 13:22:37', '0.00', '2026-07-19', 0),
(21, 'Grania Walnut', '0', 'g@gmail.com', '', '0.00', '0.00', '2026-07-19 13:23:45', '0.00', '2026-07-19', 0),
(22, 'Tr Racheal', '0779334298', 'r@gmail.com', '', '0.00', '0.00', '2026-07-19 14:34:38', '290000.00', '2026-07-19', 0),
(23, 'Mr Mbabazi Jackson', '0772486059', 'jac@gmail.com', '', '0.00', '0.00', '2026-07-21 12:35:07', '40000.00', '2026-07-21', 0),
(24, 'Akright Supermarket', '0761381704', 'a@gmail.com', '', '0.00', '0.00', '2026-07-21 17:11:27', '0.00', '2026-07-21', 0);

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
(44, 19, 7, '2026-07-19 16:45:35', '[{\"id\":\"165\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2}]', '0.00', '40000.00', 'Daudi', 'debtor', 'INV-00015', NULL, 0, 'Invoice'),
(45, 22, 7, '2026-07-19 18:01:05', '[{\"id\":\"163\",\"name\":\"Wholesale White\",\"price\":14000,\"quantity\":5}]', '0.00', '70000.00', 'Daudi', 'debtor', 'INV-00016', NULL, 0, 'Invoice'),
(46, 19, 7, '2026-07-20 16:34:32', '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2}]', '0.00', '30000.00', 'Daudi', 'debtor', 'INV-00017', NULL, 0, 'Invoice'),
(47, 23, 7, '2026-07-21 15:36:36', '[{\"id\":\"212\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2},{\"id\":\"225\",\"name\":\"Damage trays\",\"price\":10000,\"quantity\":1}]', '0.00', '40000.00', 'Daudi', 'debtor', 'INV-00018', NULL, 0, 'Invoice'),
(48, 24, 7, '2026-07-21 20:33:27', '[{\"id\":\"215\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":60,\"payment_method\":\"Cash\"},{\"id\":\"216\",\"name\":\"Wholesale White\",\"price\":14000,\"quantity\":15,\"payment_method\":\"Cash\"}]', '0.00', '930000.00', 'Daudi', 'paid', 'INV-00019', NULL, 0, 'Invoice'),
(49, 22, 7, '2026-07-23 14:14:12', '[{\"id\":\"274\",\"name\":\"Wholesale White\",\"price\":14000,\"quantity\":30,\"payment_method\":\"Cash\"}]', '0.00', '220000.00', 'Daudi', 'debtor', 'INV-00025', NULL, 0, 'Invoice'),
(50, 22, 7, '2026-07-23 14:29:23', 'partial payment receipt for invoice number INV-00025 on date 2026-07-23 for products Wholesale White x30', '200000.00', '0.00', 'Daudi', 'paid', 'RP-00113', NULL, 0, 'MTN MoMo'),
(51, 24, 7, '2026-07-23 20:50:11', 'receipt for invoice number INV-00019 on date 2026-07-21 for products Wholesale Brown x60, Wholesale White x15', '930000.00', '0.00', 'Daudi', 'paid', 'RP-00122', NULL, 0, 'MTN MoMo');

-- --------------------------------------------------------

--
-- Table structure for table `daily_banking`
--

CREATE TABLE `daily_banking` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `branch_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `due_date` date DEFAULT NULL,
  `payments_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `debtors`
--

INSERT INTO `debtors` (`id`, `date`, `time`, `debtor_name`, `debtor_contact`, `debtor_email`, `quantity_taken`, `payment_method`, `amount_paid`, `balance`, `is_paid`, `branch_id`, `item_taken`, `created_by`, `created_at`, `products_json`, `customer_id`, `invoice_no`, `receipt_no`, `due_date`, `payments_json`) VALUES
(108, '0000-00-00', '00:00:00', 'Professor Mugisha ( Penniner', '0772838000', 'penny@gmail.com', 2, NULL, '0.00', '40000.00', 0, 7, 'Dressed Birds x2', '31', '2026-07-22 20:32:10', '[{\"id\":\"251\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, 'INV-00020', NULL, NULL, NULL),
(109, '0000-00-00', '00:00:00', 'Hope Shanex salon Akright  Stage', '07', 'Hope@gmail.com', 1, NULL, '0.00', '15000.00', 0, 7, 'White single normal x1', '31', '2026-07-22 20:37:32', '[{\"id\":\"265\",\"name\":\"White single normal\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, 'INV-00021', NULL, NULL, NULL),
(111, '0000-00-00', '00:00:00', 'Gertrude Nagganyi', '0776817264', 'gert@gmail.com', 150, NULL, '1485000.00', '90000.00', 0, 7, 'Manure discounted x150', '31', '2026-07-22 20:43:40', '[{\"id\":\"269\",\"name\":\"Manure discounted\",\"price\":10500,\"quantity\":150,\"payment_method\":\"Cash\"}]', NULL, 'INV-00023', NULL, NULL, NULL),
(112, '0000-00-00', '00:00:00', 'HON Ruth Nankabira', '07', 'ruth@gmail.com', 2, NULL, '0.00', '30000.00', 0, 7, 'White trays x2', '31', '2026-07-22 20:44:56', '[{\"id\":\"245\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, 'INV-00024', NULL, NULL, NULL),
(113, '0000-00-00', '00:00:00', 'Ashok 2', '0772326677', 'Ash@gmail.com', 3, 'Debtor', '0.00', '50000.00', 0, 7, 'White trays x2, Dressed Birds x1', '31', '2026-07-24 13:29:16', '[{\"id\":\"295\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2,\"payment_method\":\"Cash\"},{\"id\":\"301\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, 'INV-00026', NULL, NULL, NULL);

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
(158, 'White trays', '1', NULL, '0', '15000', '5.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '3.00', '17.00', '15.00', '0.00', '2026-07-19'),
(159, 'Brown Trays', '2', NULL, '0', '13000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '2.00', '0.00', '2.00', '0.00', '2026-07-19'),
(160, 'Cream trays', '3', NULL, '0', '20000', '6.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '7.00', '1.00', '0.00', '2026-07-19'),
(162, 'Wholesale Brown', '4', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '23.00', '23.00', '0.00', '2026-07-19'),
(163, 'Wholesale White', '5', NULL, '0', '14000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '5.00', '5.00', '0.00', '2026-07-19'),
(164, 'Wholesale Cream', '6', NULL, '0', '19000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(165, 'Dressed Birds', '7', NULL, '0', '20000', '26.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '8.00', '25.00', '7.00', '0.00', '2026-07-19'),
(166, 'Live birds', '8', NULL, '0', '20000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '3.00', '3.00', '0.00', '2026-07-19'),
(167, 'Mortalities', '9', NULL, '0', '5000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(168, 'Wholesale Mortalities', '10', NULL, '0', '3000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(169, 'Manure', '11', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '6.00', '6.00', '0.00', '2026-07-19'),
(170, 'Wholesale Manure', '12', NULL, '0', '11500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(171, 'Damage eggs', '13', NULL, '0', '1000', '3.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '50.00', '47.00', '0.00', '2026-07-19'),
(172, 'Damage trays', '14', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(173, '15packs White', '15', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(174, '6pack White', '16', NULL, '0', '3500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(175, 'Singles white', '17', NULL, '0', '14500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(176, 'Brown eggs', '18', NULL, '0', '450', '16.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '16.00', '0.00', '0.00', '0.00', '2026-07-19'),
(177, 'White eggs', '19', NULL, '0', '500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-19'),
(178, 'Cream Eggs', '20', NULL, '0', '700', '47.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '26.00', '21.00', '0.00', '0.00', '2026-07-19'),
(179, 'White single normal', '21', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '1.00', '0.00', '2026-07-19'),
(180, 'White trays', '1', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '5.00', '2.00', '7.00', '0.00', '2026-07-20'),
(181, 'Brown Trays', '2', NULL, '0', '13000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-20'),
(182, 'Cream trays', '3', NULL, '0', '20000', '6.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '6.00', '0.00', '0.00', '0.00', '2026-07-20'),
(183, 'Wholesale Brown', '4', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '5.00', '5.00', '0.00', '2026-07-20'),
(184, 'Wholesale White', '5', NULL, '0', '14000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(185, 'Wholesale Cream', '6', NULL, '0', '19000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(186, 'Dressed Birds', '7', NULL, '0', '20000', '26.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '26.00', '0.00', '0.00', '0.00', '2026-07-20'),
(187, 'Live birds', '8', NULL, '0', '20000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(188, 'Mortalities', '9', NULL, '0', '5000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(189, 'Wholesale Mortalities', '10', NULL, '0', '3000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(190, 'Manure', '11', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '4.00', '4.00', '0.00', '2026-07-20'),
(191, 'Wholesale Manure', '12', NULL, '0', '11500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(192, 'Damage eggs', '13', NULL, '0', '1000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '3.00', '33.00', '35.00', '0.00', '2026-07-20'),
(193, 'Damage trays', '14', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(194, '15packs White', '15', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(195, '6pack White', '16', NULL, '0', '3500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(196, 'Singles white', '17', NULL, '0', '14500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(197, 'Brown eggs', '18', NULL, '0', '450', '16.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '16.00', '0.00', '0.00', '0.00', '2026-07-20'),
(198, 'White eggs', '19', NULL, '0', '500', '6.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '21.00', '15.00', '0.00', '2026-07-20'),
(199, 'Cream Eggs', '20', NULL, '0', '700', '47.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '47.00', '0.00', '0.00', '0.00', '2026-07-20'),
(200, 'White single normal', '21', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-20'),
(211, 'Brown trays Grania', '22', NULL, '0', '11500', '0.00', 7, 1, '2026-09-11', 0, 'shelf', NULL, 1, '0.00', '30.00', '30.00', '0.00', '2026-07-20'),
(212, 'White trays', '1', NULL, '0', '15000', '16.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '20.00', '4.00', '0.00', '2026-07-21'),
(213, 'Brown Trays', '2', NULL, '0', '13000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(214, 'Cream trays', '3', NULL, '0', '20000', '6.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '6.00', '2.00', '2.00', '0.00', '2026-07-21'),
(215, 'Wholesale Brown', '4', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '73.00', '73.00', '0.00', '2026-07-21'),
(216, 'Wholesale White', '5', NULL, '0', '14000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '15.00', '15.00', '0.00', '2026-07-21'),
(217, 'Wholesale Cream', '6', NULL, '0', '19000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(218, 'Dressed Birds', '7', NULL, '0', '20000', '19.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '26.00', '0.00', '7.00', '0.00', '2026-07-21'),
(219, 'Live birds', '8', NULL, '0', '20000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(220, 'Mortalities', '9', NULL, '0', '5000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(221, 'Wholesale Mortalities', '10', NULL, '0', '3000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(222, 'Manure', '11', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-21'),
(223, 'Wholesale Manure', '12', NULL, '0', '11500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(224, 'Damage eggs', '13', NULL, '0', '1000', '2.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '44.00', '43.00', '0.00', '2026-07-21'),
(225, 'Damage trays', '14', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-21'),
(226, '15packs White', '15', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(227, '6pack White', '16', NULL, '0', '3500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '0.00', '0.00', '2026-07-21'),
(228, 'Singles white', '17', NULL, '0', '14500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(229, 'Brown eggs', '18', NULL, '0', '450', '46.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '16.00', '30.00', '0.00', '0.00', '2026-07-21'),
(230, 'White eggs', '19', NULL, '0', '500', '5.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '6.00', '0.00', '1.00', '0.00', '2026-07-21'),
(231, 'Cream Eggs', '20', NULL, '0', '700', '47.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '47.00', '0.00', '0.00', '0.00', '2026-07-21'),
(232, 'White single normal', '21', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-21'),
(233, 'Brown trays Grania', '22', NULL, '0', '11500', '0.00', 7, 1, '2026-09-11', 0, 'shelf', NULL, 1, '0.00', '20.00', '20.00', '0.00', '2026-07-21'),
(243, 'White 6pac', '23', NULL, '0', '4000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-21'),
(244, 'Dressed bird', '24', NULL, '0', '10000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '2.00', '1.00', '0.00', '2026-07-21'),
(245, 'White trays', '1', NULL, '0', '15000', '11.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '16.00', '0.00', '5.00', '0.00', '2026-07-22'),
(246, 'Brown Trays', '2', NULL, '0', '13000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-22'),
(247, 'Cream trays', '3', NULL, '0', '20000', '6.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '6.00', '0.00', '0.00', '0.00', '2026-07-22'),
(248, 'Wholesale Brown', '4', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '15.00', '15.00', '0.00', '2026-07-22'),
(249, 'Wholesale White', '5', NULL, '0', '14000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(250, 'Wholesale Cream', '6', NULL, '0', '19000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(251, 'Dressed Birds', '7', NULL, '0', '20000', '15.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '19.00', '0.00', '4.00', '0.00', '2026-07-22'),
(252, 'Live birds', '8', NULL, '0', '20000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-22'),
(253, 'Mortalities', '9', NULL, '0', '5000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(254, 'Wholesale Mortalities', '10', NULL, '0', '3000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(255, 'Manure', '11', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(256, 'Wholesale Manure', '12', NULL, '0', '11500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(257, 'Damage eggs', '13', NULL, '0', '1000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '2.00', '5.00', '7.00', '0.00', '2026-07-22'),
(258, 'Damage trays', '14', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(259, '15packs White', '15', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(260, '6pack White', '16', NULL, '0', '3500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-22'),
(261, 'Singles white', '17', NULL, '0', '14500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '0.00', '0.00', '2026-07-22'),
(262, 'Brown eggs', '18', NULL, '0', '450', '46.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '46.00', '0.00', '0.00', '0.00', '2026-07-22'),
(263, 'White eggs', '19', NULL, '0', '500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '5.00', '5.00', '10.00', '0.00', '2026-07-22'),
(264, 'Cream Eggs', '20', NULL, '0', '700', '47.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '47.00', '0.00', '0.00', '0.00', '2026-07-22'),
(265, 'White single normal', '21', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-22'),
(266, 'Brown trays Grania', '22', NULL, '0', '11500', '0.00', 7, 1, '2026-09-11', 0, 'shelf', NULL, 1, '0.00', '70.00', '70.00', '0.00', '2026-07-22'),
(267, 'White 6pac', '23', NULL, '0', '4000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-22'),
(268, 'Dressed bird', '24', NULL, '0', '10000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-22'),
(269, 'Manure discounted', '25', NULL, '0', '10500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '150.00', '150.00', '0.00', '2026-07-22'),
(270, 'White trays', '1', NULL, '0', '15000', '4.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '11.00', '0.00', '7.00', '0.00', '2026-07-23'),
(271, 'Brown Trays', '2', NULL, '0', '13000', '8.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '10.00', '2.00', '0.00', '2026-07-23'),
(272, 'Cream trays', '3', NULL, '0', '20000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '6.00', '0.00', '5.00', '0.00', '2026-07-23'),
(273, 'Wholesale Brown', '4', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(274, 'Wholesale White', '5', NULL, '0', '14000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '30.00', '30.00', '0.00', '2026-07-23'),
(275, 'Wholesale Cream', '6', NULL, '0', '19000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(276, 'Dressed Birds', '7', NULL, '0', '20000', '14.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '15.00', '0.00', '1.00', '0.00', '2026-07-23'),
(277, 'Live birds', '8', NULL, '0', '20000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(278, 'Mortalities', '9', NULL, '0', '5000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(279, 'Wholesale Mortalities', '10', NULL, '0', '3000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(280, 'Manure', '11', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(281, 'Wholesale Manure', '12', NULL, '0', '11500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(282, 'Damage eggs', '13', NULL, '0', '1000', '4.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '20.00', '16.00', '0.00', '2026-07-23'),
(283, 'Damage trays', '14', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(284, '15packs White', '15', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(285, '6pack White', '16', NULL, '0', '3500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-23'),
(286, 'Singles white', '17', NULL, '0', '14500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-23'),
(287, 'Brown eggs', '18', NULL, '0', '450', '46.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '46.00', '0.00', '0.00', '0.00', '2026-07-23'),
(288, 'White eggs', '19', NULL, '0', '500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '10.00', '10.00', '0.00', '2026-07-23'),
(289, 'Cream Eggs', '20', NULL, '0', '700', '47.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '47.00', '0.00', '0.00', '0.00', '2026-07-23'),
(290, 'White single normal', '21', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(291, 'Brown trays Grania', '22', NULL, '0', '11500', '0.00', 7, 1, '2026-09-11', 0, 'shelf', NULL, 1, '0.00', '50.00', '50.00', '0.00', '2026-07-23'),
(292, 'White 6pac', '23', NULL, '0', '4000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(293, 'Dressed bird', '24', NULL, '0', '10000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-23'),
(294, 'Manure discounted', '25', NULL, '0', '10500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-23'),
(295, 'White trays', '1', NULL, '0', '15000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '4.00', '3.00', '6.00', '0.00', '2026-07-24'),
(296, 'Brown Trays', '2', NULL, '0', '13000', '8.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '8.00', '0.00', '0.00', '0.00', '2026-07-24'),
(297, 'Cream trays', '3', NULL, '0', '20000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '2.00', '2.00', '0.00', '2026-07-24'),
(298, 'Wholesale Brown', '4', NULL, '0', '12000', '36.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '71.00', '35.00', '0.00', '2026-07-24'),
(299, 'Wholesale White', '5', NULL, '0', '14000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(300, 'Wholesale Cream', '6', NULL, '0', '19000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(301, 'Dressed Birds', '7', NULL, '0', '20000', '11.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '14.00', '0.00', '3.00', '0.00', '2026-07-24'),
(302, 'Live birds', '8', NULL, '0', '20000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '2.00', '1.00', '0.00', '2026-07-24'),
(303, 'Mortalities', '9', NULL, '0', '5000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(304, 'Wholesale Mortalities', '10', NULL, '0', '3000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(305, 'Manure', '11', NULL, '0', '12000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '4.00', '4.00', '0.00', '2026-07-24'),
(306, 'Wholesale Manure', '12', NULL, '0', '11500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(307, 'Damage eggs', '13', NULL, '0', '1000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '4.00', '10.00', '14.00', '0.00', '2026-07-24'),
(308, 'Damage trays', '14', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(309, '15packs White', '15', NULL, '0', '10000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(310, '6pack White', '16', NULL, '0', '3500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-24'),
(311, 'Singles white', '17', NULL, '0', '14500', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-24'),
(312, 'Brown eggs', '18', NULL, '0', '450', '46.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '46.00', '0.00', '0.00', '0.00', '2026-07-24'),
(313, 'White eggs', '19', NULL, '0', '500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(314, 'Cream Eggs', '20', NULL, '0', '700', '47.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '47.00', '0.00', '0.00', '0.00', '2026-07-24'),
(315, 'White single normal', '21', NULL, '0', '15000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '1.00', '1.00', '0.00', '2026-07-24'),
(316, 'Brown trays Grania', '22', NULL, '0', '11500', '0.00', 7, 1, '2026-09-11', 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(317, 'White 6pac', '23', NULL, '0', '4000', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24'),
(318, 'Dressed bird', '24', NULL, '0', '10000', '1.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '1.00', '0.00', '0.00', '0.00', '2026-07-24'),
(319, 'Manure discounted', '25', NULL, '0', '10500', '0.00', 7, 1, NULL, 0, 'shelf', NULL, 1, '0.00', '0.00', '0.00', '0.00', '2026-07-24');

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
('2026-07-18', 32, 7, 0, '469000', '0', '469000'),
('2026-07-19', 33, 1, 0, '64920', '0', '64920'),
('2026-07-19', 34, 7, 0, '865000', '0', '865000'),
('2026-07-20', 35, 7, 0, '583500', '0', '583500'),
('2026-07-21', 36, 7, 0, '665500', '0', '665500'),
('2026-07-22', 37, 7, 0, '2432000', '0', '2432000'),
('2026-07-23', 38, 7, 0, '2157000', '0', '2157000'),
('2026-07-24', 39, 7, 0, '657000', '0', '657000');

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
(5, 137, 'RP', '2026-07-24 10:35:01'),
(6, 26, 'INV', '2026-07-24 10:29:16');

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
  `payment_method` varchar(255) NOT NULL DEFAULT 'Cash',
  `customer_id` int(11) DEFAULT NULL,
  `transaction_id` varchar(64) DEFAULT NULL,
  `invoice_no` varchar(32) DEFAULT NULL,
  `receipt_no` varchar(32) DEFAULT NULL,
  `products_json` text DEFAULT NULL,
  `original_debt_date` date DEFAULT NULL,
  `payments_json` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product-id`, `branch-id`, `business_id`, `quantity`, `amount`, `cost-price`, `sold-by`, `date`, `total_profits`, `payment_method`, `customer_id`, `transaction_id`, `invoice_no`, `receipt_no`, `products_json`, `original_debt_date`, `payments_json`) VALUES
(400, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-19 16:31:58', '20000', 'Cash', NULL, NULL, NULL, 'RP-00031', '[{\"id\":\"165\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(401, 0, 7, 0, '2.00', '40000', '0', 31, '2026-07-19 16:39:32', '40000', 'Cash', NULL, NULL, NULL, 'RP-00032', '[{\"id\":\"166\",\"name\":\"Live birds\",\"price\":20000,\"quantity\":2}]', NULL, NULL),
(402, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-19 16:39:51', '3000', 'Cash', NULL, NULL, NULL, 'RP-00033', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(403, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-19 16:40:13', '3000', 'Cash', NULL, NULL, NULL, 'RP-00034', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(404, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-19 16:40:52', '2000', 'Cash', NULL, NULL, NULL, 'RP-00035', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(405, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-19 16:41:03', '20000', 'Cash', NULL, NULL, NULL, 'RP-00036', '[{\"id\":\"165\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(406, 0, 7, 0, '2.00', '26000', '0', 31, '2026-07-19 16:41:22', '26000', 'Cash', NULL, NULL, NULL, 'RP-00037', '[{\"id\":\"159\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":2}]', NULL, NULL),
(407, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-19 16:41:33', '20000', 'Cash', NULL, NULL, NULL, 'RP-00038', '[{\"id\":\"166\",\"name\":\"Live birds\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(408, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-19 16:41:46', '20000', 'Cash', NULL, NULL, NULL, 'RP-00039', '[{\"id\":\"165\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(409, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-19 16:41:56', '3000', 'Cash', NULL, NULL, NULL, 'RP-00040', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(410, 0, 7, 0, '2.00', '0', '0', 31, '2026-07-19 16:42:26', '0', '', NULL, NULL, 'INV-00014', NULL, '[{\"id\":\"158\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2}]', NULL, NULL),
(411, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-19 16:43:25', '3000', 'Cash', NULL, NULL, NULL, 'RP-00041', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(412, 0, 7, 0, '12.00', '180000', '0', 31, '2026-07-19 16:43:43', '180000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00042', '[{\"id\":\"158\",\"name\":\"White trays\",\"price\":15000,\"quantity\":12}]', NULL, NULL),
(413, 0, 7, 0, '1.00', '1000', '0', 31, '2026-07-19 16:44:40', '1000', 'Cash', NULL, NULL, NULL, 'RP-00043', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":1}]', NULL, NULL),
(414, 0, 7, 0, '16.00', '192000', '0', 31, '2026-07-19 16:45:18', '192000', 'Cash', NULL, NULL, NULL, 'RP-00044', '[{\"id\":\"162\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":16}]', NULL, NULL),
(415, 0, 7, 0, '2.00', '0', '0', 31, '2026-07-19 16:45:35', '0', 'Customer file', NULL, NULL, 'INV-00015', NULL, '[{\"id\":\"165\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2}]', NULL, NULL),
(416, 0, 7, 0, '2.00', '40000', '0', 31, '2026-07-19 16:45:57', '40000', 'Cash', NULL, NULL, NULL, 'RP-00045', '[{\"id\":\"165\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2}]', NULL, NULL),
(417, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-19 16:46:10', '3000', 'Cash', NULL, NULL, NULL, 'RP-00046', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(418, 0, 7, 0, '2.00', '2000', '0', 32, '2026-07-19 17:07:48', '2000', 'Cash', NULL, NULL, NULL, 'RP-00047', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(419, 0, 7, 0, '2.00', '2000', '0', 32, '2026-07-19 17:11:44', '2000', 'Cash', NULL, NULL, NULL, 'RP-00048', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(420, 0, 7, 0, '2.00', '2000', '0', 32, '2026-07-19 17:16:03', '2000', 'Cash', NULL, NULL, NULL, 'RP-00049', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(421, 0, 7, 0, '4.00', '4000', '0', 32, '2026-07-19 17:17:39', '4000', 'Cash', NULL, NULL, NULL, 'RP-00050', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":4}]', NULL, NULL),
(422, 0, 7, 0, '7.00', '84000', '0', 32, '2026-07-19 17:18:23', '84000', 'Cash', NULL, NULL, NULL, 'RP-00051', '[{\"id\":\"162\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":7}]', NULL, NULL),
(423, 0, 7, 0, '2.00', '30000', '0', 32, '2026-07-19 17:27:12', '30000', 'Cash', NULL, NULL, NULL, 'RP-00052', '[{\"id\":\"158\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1},{\"id\":\"179\",\"name\":\"White single normal\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(424, 0, 7, 0, '5.00', '0', '0', 31, '2026-07-19 18:01:05', '0', 'Customer file', NULL, NULL, 'INV-00016', NULL, '[{\"id\":\"163\",\"name\":\"Wholesale White\",\"price\":14000,\"quantity\":5}]', NULL, NULL),
(425, 0, 7, 0, '2.00', '24000', '0', 31, '2026-07-19 18:23:03', '24000', 'Airtel Money', NULL, NULL, NULL, 'RP-00053', '[{\"id\":\"169\",\"name\":\"Manure\",\"price\":12000,\"quantity\":2}]', NULL, NULL),
(426, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-19 18:23:50', '2000', 'Cash', NULL, NULL, NULL, 'RP-00054', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(427, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-19 18:25:09', '2000', 'Cash', NULL, NULL, NULL, 'RP-00055', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(428, 0, 7, 0, '4.00', '48000', '0', 31, '2026-07-19 18:25:52', '48000', 'Cash', NULL, NULL, NULL, 'RP-00056', '[{\"id\":\"169\",\"name\":\"Manure\",\"price\":12000,\"quantity\":4}]', NULL, NULL),
(429, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-19 20:24:32', '20000', 'Cash', NULL, NULL, NULL, 'RP-00057', '[{\"id\":\"160\",\"name\":\"Cream trays\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(430, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-19 20:25:00', '3000', 'Cash', NULL, NULL, NULL, 'RP-00058', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(431, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-19 20:26:59', '10000', 'Cash', NULL, NULL, NULL, 'RP-00059', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10}]', NULL, NULL),
(432, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-19 20:27:19', '2000', 'Cash', NULL, NULL, NULL, 'RP-00060', '[{\"id\":\"171\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(433, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-20 12:15:46', '2000', 'Cash', NULL, NULL, NULL, 'RP-00061', '[{\"id\":\"192\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(434, 0, 7, 0, '1.00', '1000', '0', 31, '2026-07-20 12:16:48', '1000', 'Cash', NULL, NULL, NULL, 'RP-00062', '[{\"id\":\"192\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":1}]', NULL, NULL),
(435, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-20 12:46:34', '15000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00063', '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(436, 0, 7, 0, '30.00', '345000', '0', 31, '2026-07-20 13:05:35', '345000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00064', '[{\"id\":\"211\",\"name\":\"Brown trays Grania\",\"price\":11500,\"quantity\":30}]', NULL, NULL),
(437, 0, 7, 0, '1.00', '13000', '0', 31, '2026-07-20 13:18:54', '13000', 'Cash', NULL, NULL, NULL, 'RP-00065', '[{\"id\":\"181\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":1}]', NULL, NULL),
(438, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-20 16:18:04', '15000', 'Cash', NULL, NULL, NULL, 'RP-00066', '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(439, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-20 16:33:41', '15000', 'Cash', NULL, NULL, NULL, 'RP-00067', '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(440, 0, 7, 0, '2.00', '0', '0', 31, '2026-07-20 16:34:32', '0', 'Customer file', NULL, NULL, 'INV-00017', NULL, '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2}]', NULL, NULL),
(441, 0, 7, 0, '4.00', '48000', '0', 31, '2026-07-20 17:43:03', '48000', 'Cash', NULL, NULL, NULL, 'RP-00068', '[{\"id\":\"190\",\"name\":\"Manure\",\"price\":12000,\"quantity\":4}]', NULL, NULL),
(442, 0, 7, 0, '15.00', '7500', '0', 31, '2026-07-20 18:30:13', '7500', 'MTN MoMo', NULL, NULL, NULL, 'RP-00069', '[{\"id\":\"198\",\"name\":\"White eggs\",\"price\":500,\"quantity\":15}]', NULL, NULL),
(443, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-20 18:54:35', '10000', 'Cash', NULL, NULL, NULL, 'RP-00070', '[{\"id\":\"192\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10}]', NULL, NULL),
(444, 0, 7, 0, '5.00', '60000', '0', 31, '2026-07-20 19:51:42', '60000', 'Cash', NULL, NULL, NULL, 'RP-00071', '[{\"id\":\"183\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":5}]', NULL, NULL),
(445, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-20 20:26:37', '2000', 'Cash', NULL, NULL, NULL, 'RP-00072', '[{\"id\":\"192\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(446, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-20 20:28:24', '10000', 'Cash', NULL, NULL, NULL, 'RP-00073', '[{\"id\":\"192\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10}]', NULL, NULL),
(447, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-20 20:30:56', '15000', 'Cash', NULL, NULL, NULL, 'RP-00074', '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(448, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-20 20:35:34', '15000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00075', '[{\"id\":\"180\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(449, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-20 20:38:44', '10000', 'Cash', NULL, NULL, NULL, 'RP-00076', '[{\"id\":\"192\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10}]', NULL, NULL),
(450, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-21 11:39:59', '3000', 'Cash', NULL, NULL, NULL, 'RP-00077', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(451, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-21 11:47:53', '15000', 'Cash', NULL, NULL, NULL, 'RP-00078', '[{\"id\":\"212\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1}]', NULL, NULL),
(452, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-21 11:48:16', '20000', 'Cash', NULL, NULL, NULL, 'RP-00079', '[{\"id\":\"218\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(453, 0, 7, 0, '1.00', '4000', '0', 31, '2026-07-21 13:07:33', '4000', 'Cash', NULL, NULL, NULL, 'RP-00080', '[{\"id\":\"243\",\"name\":\"White 6pac\",\"price\":4000,\"quantity\":1}]', NULL, NULL),
(454, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-21 13:09:13', '2000', 'Cash', NULL, NULL, NULL, 'RP-00081', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(455, 0, 7, 0, '20.00', '230000', '0', 31, '2026-07-21 13:09:56', '230000', 'Cash', NULL, NULL, NULL, 'RP-00082', '[{\"id\":\"233\",\"name\":\"Brown trays Grania\",\"price\":11500,\"quantity\":20}]', NULL, NULL),
(456, 0, 7, 0, '4.00', '4000', '0', 31, '2026-07-21 13:38:34', '4000', 'Cash', NULL, NULL, NULL, 'RP-00083', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":4}]', NULL, NULL),
(457, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-21 13:39:13', '2000', 'Cash', NULL, NULL, NULL, 'RP-00084', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(458, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-21 13:39:51', '20000', 'Cash', NULL, NULL, NULL, 'RP-00085', '[{\"id\":\"218\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1}]', NULL, NULL),
(459, 0, 7, 0, '1.00', '10000', '0', 31, '2026-07-21 13:42:16', '10000', 'Cash', NULL, NULL, NULL, 'RP-00086', '[{\"id\":\"244\",\"name\":\"Dressed bird\",\"price\":10000,\"quantity\":1}]', NULL, NULL),
(460, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-21 13:42:44', '2000', 'Cash', NULL, NULL, NULL, 'RP-00087', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(461, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-21 13:43:05', '10000', 'Cash', NULL, NULL, NULL, 'RP-00088', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10}]', NULL, NULL),
(462, 0, 7, 0, '1.00', '12000', '0', 31, '2026-07-21 13:43:40', '12000', 'Cash', NULL, NULL, NULL, 'RP-00089', '[{\"id\":\"222\",\"name\":\"Manure\",\"price\":12000,\"quantity\":1}]', NULL, NULL),
(463, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-21 13:53:20', '3000', 'Cash', NULL, NULL, NULL, 'RP-00090', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3}]', NULL, NULL),
(464, 0, 7, 0, '3.00', '0', '0', 31, '2026-07-21 15:36:36', '0', 'Customer file', NULL, NULL, 'INV-00018', NULL, '[{\"id\":\"212\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2},{\"id\":\"225\",\"name\":\"Damage trays\",\"price\":10000,\"quantity\":1}]', NULL, NULL),
(465, 0, 7, 0, '5.00', '100000', '0', 31, '2026-07-21 15:37:01', '100000', 'Cash', NULL, NULL, NULL, 'RP-00091', '[{\"id\":\"218\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":5}]', NULL, NULL),
(466, 0, 7, 0, '5.00', '60000', '0', 31, '2026-07-21 15:37:21', '60000', 'Cash', NULL, NULL, NULL, 'RP-00092', '[{\"id\":\"215\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":5}]', NULL, NULL),
(467, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-21 15:37:48', '2000', 'Cash', NULL, NULL, NULL, 'RP-00093', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2}]', NULL, NULL),
(468, 0, 7, 0, '8.00', '96000', '0', 31, '2026-07-21 16:24:01', '96000', 'Cash', NULL, NULL, NULL, 'RP-00094', '[{\"id\":\"215\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":8}]', NULL, NULL),
(469, 0, 7, 0, '2.00', '40000', '0', 31, '2026-07-21 16:29:29', '40000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00095', '[{\"id\":\"214\",\"name\":\"Cream trays\",\"price\":20000,\"quantity\":2}]', NULL, NULL),
(470, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-21 17:31:42', '3000', 'Cash', NULL, NULL, NULL, 'RP-00096', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3,\"payment_method\":\"Cash\"}]', NULL, NULL),
(471, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-21 18:11:17', '10000', 'Cash', NULL, NULL, NULL, 'RP-00097', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10,\"payment_method\":\"Cash\"}]', NULL, NULL),
(472, 0, 7, 0, '75.00', '0', '0', 31, '2026-07-21 20:33:27', '0', 'Customer File', NULL, NULL, 'INV-00019', NULL, '[{\"id\":\"215\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":60,\"payment_method\":\"Cash\"},{\"id\":\"216\",\"name\":\"Wholesale White\",\"price\":14000,\"quantity\":15,\"payment_method\":\"Cash\"}]', NULL, NULL),
(473, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-21 21:01:28', '15000', 'Cash', NULL, NULL, NULL, 'RP-00098', '[{\"id\":\"212\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(474, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-21 21:11:36', '2000', 'Cash', NULL, NULL, NULL, 'RP-00099', '[{\"id\":\"224\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(475, 0, 7, 0, '1.00', '500', '0', 31, '2026-07-21 21:12:57', '500', 'Cash', NULL, NULL, NULL, 'RP-00100', '[{\"id\":\"230\",\"name\":\"White eggs\",\"price\":500,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(476, 0, 7, 0, '2.00', '30000', '0', 31, '2026-07-22 13:26:29', '30000', 'Cash', NULL, NULL, NULL, 'RP-00101', '[{\"id\":\"245\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(477, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-22 13:27:06', '15000', 'Cash', NULL, NULL, NULL, 'RP-00102', '[{\"id\":\"245\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(478, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-22 14:33:46', '20000', 'Cash', NULL, NULL, NULL, 'RP-00105', '[{\"id\":\"252\",\"name\":\"Live birds\",\"price\":20000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(479, 0, 7, 0, '10.00', '5000', '0', 31, '2026-07-22 15:34:33', '5000', 'Cash', NULL, NULL, NULL, 'RP-00106', '[{\"id\":\"263\",\"name\":\"White eggs\",\"price\":500,\"quantity\":10,\"payment_method\":\"Cash\"}]', NULL, NULL),
(480, 0, 7, 0, '0.00', '30000', '0', 31, '2026-07-22 16:06:38', '30000', 'Airtel Money', NULL, NULL, NULL, 'RP-00107', 'receipt for invoice number INV-00014 on date 1970-01-01 for products White trays x2', '1970-01-01', NULL),
(481, 0, 7, 0, '70.00', '805000', '0', 31, '2026-07-22 16:20:56', '805000', 'Split (Cash: 690,000, MTN MoMo: 115,000)', NULL, NULL, NULL, 'RP-00108', '[{\"id\":\"266\",\"name\":\"Brown trays Grania\",\"price\":11500,\"quantity\":70,\"payment_method\":\"Cash\"}]', NULL, '[{\"method\":\"Cash\",\"amount\":690000},{\"method\":\"MTN MoMo\",\"amount\":115000}]'),
(482, 0, 7, 0, '2.00', '0', '0', 31, '2026-07-22 20:32:10', '0', 'Debtor', NULL, NULL, 'INV-00020', NULL, '[{\"id\":\"251\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(483, 0, 7, 0, '1.00', '0', '0', 31, '2026-07-22 20:37:32', '0', 'Debtor', NULL, NULL, 'INV-00021', NULL, '[{\"id\":\"265\",\"name\":\"White single normal\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(484, 0, 7, 0, '15.00', '0', '0', 31, '2026-07-22 20:39:53', '0', 'Debtor', NULL, NULL, 'INV-00022', NULL, '[{\"id\":\"248\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":15,\"payment_method\":\"Cash\"}]', NULL, NULL),
(485, 0, 7, 0, '150.00', '1485000', '0', 31, '2026-07-22 20:43:40', '1485000', 'Debtor', NULL, NULL, 'INV-00023', NULL, '[{\"id\":\"269\",\"name\":\"Manure discounted\",\"price\":10500,\"quantity\":150,\"payment_method\":\"Cash\"}]', NULL, NULL),
(486, 0, 7, 0, '2.00', '0', '0', 31, '2026-07-22 20:44:56', '0', 'Debtor', NULL, NULL, 'INV-00024', NULL, '[{\"id\":\"245\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(487, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-22 20:48:17', '2000', 'Cash', NULL, NULL, NULL, 'RP-00109', '[{\"id\":\"257\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(488, 0, 7, 0, '2.00', '40000', '0', 31, '2026-07-22 21:21:15', '40000', 'Cash', NULL, NULL, NULL, 'RP-00110', '[{\"id\":\"251\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(489, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-23 11:35:05', '15000', 'Cash', NULL, NULL, NULL, 'RP-00111', '[{\"id\":\"270\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(490, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-23 11:46:06', '15000', 'Cash', NULL, NULL, NULL, 'RP-00112', '[{\"id\":\"270\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(491, 0, 7, 0, '30.00', '0', '0', 31, '2026-07-23 14:14:12', '0', 'Customer File', NULL, NULL, 'INV-00025', NULL, '[{\"id\":\"274\",\"name\":\"Wholesale White\",\"price\":14000,\"quantity\":30,\"payment_method\":\"Cash\"}]', NULL, NULL),
(492, 0, 7, 0, '0.00', '200000', '0', 31, '2026-07-23 14:29:23', '200000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00113', 'partial payment receipt for invoice number INV-00025 on date 2026-07-23 for products Wholesale White x30', '2026-07-23', NULL);
INSERT INTO `sales` (`id`, `product-id`, `branch-id`, `business_id`, `quantity`, `amount`, `cost-price`, `sold-by`, `date`, `total_profits`, `payment_method`, `customer_id`, `transaction_id`, `invoice_no`, `receipt_no`, `products_json`, `original_debt_date`, `payments_json`) VALUES
(493, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-23 14:32:35', '20000', 'Cash', NULL, NULL, NULL, 'RP-00114', '[{\"id\":\"272\",\"name\":\"Cream trays\",\"price\":20000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(494, 0, 7, 0, '10.00', '5000', '0', 31, '2026-07-23 14:59:52', '5000', 'Cash', NULL, NULL, NULL, 'RP-00115', '[{\"id\":\"288\",\"name\":\"White eggs\",\"price\":500,\"quantity\":10,\"payment_method\":\"Cash\"}]', NULL, NULL),
(495, 0, 7, 0, '4.00', '80000', '0', 31, '2026-07-23 20:34:05', '80000', 'Cash', NULL, NULL, NULL, 'RP-00116', '[{\"id\":\"272\",\"name\":\"Cream trays\",\"price\":20000,\"quantity\":4,\"payment_method\":\"Cash\"}]', NULL, NULL),
(496, 0, 7, 0, '1.00', '13000', '0', 31, '2026-07-23 20:35:42', '13000', 'Cash', NULL, NULL, NULL, 'RP-00117', '[{\"id\":\"271\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(497, 0, 7, 0, '1.00', '13000', '0', 31, '2026-07-23 20:36:01', '13000', 'Cash', NULL, NULL, NULL, 'RP-00118', '[{\"id\":\"271\",\"name\":\"Brown Trays\",\"price\":13000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(498, 0, 7, 0, '50.00', '575000', '0', 31, '2026-07-23 20:37:15', '575000', 'Split (Cash: 145,000, MTN MoMo: 430,000)', NULL, NULL, NULL, 'RP-00119', '[{\"id\":\"291\",\"name\":\"Brown trays Grania\",\"price\":11500,\"quantity\":50,\"payment_method\":\"Cash\"}]', NULL, '[{\"method\":\"Cash\",\"amount\":145000},{\"method\":\"MTN MoMo\",\"amount\":430000}]'),
(499, 0, 7, 0, '4.00', '60000', '0', 31, '2026-07-23 20:41:05', '60000', 'Cash', NULL, NULL, NULL, 'RP-00120', '[{\"id\":\"270\",\"name\":\"White trays\",\"price\":15000,\"quantity\":4,\"payment_method\":\"Cash\"}]', NULL, NULL),
(500, 0, 7, 0, '0.00', '180000', '0', 31, '2026-07-23 20:48:31', '180000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00121', 'receipt for invoice number INV-00022 on date 1970-01-01 for products Wholesale Brown x15', '1970-01-01', NULL),
(501, 0, 7, 0, '0.00', '930000', '0', 31, '2026-07-23 20:50:11', '930000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00122', 'receipt for invoice number INV-00019 on date 2026-07-21 for products Wholesale Brown x60, Wholesale White x15', '2026-07-21', NULL),
(502, 0, 7, 0, '10.00', '10000', '0', 31, '2026-07-23 20:54:15', '10000', 'Cash', NULL, NULL, NULL, 'RP-00123', '[{\"id\":\"282\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":10,\"payment_method\":\"Cash\"}]', NULL, NULL),
(503, 0, 7, 0, '6.00', '6000', '0', 31, '2026-07-23 20:54:42', '6000', 'Cash', NULL, NULL, NULL, 'RP-00124', '[{\"id\":\"282\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":6,\"payment_method\":\"Cash\"}]', NULL, NULL),
(504, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-23 20:57:21', '20000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00125', '[{\"id\":\"276\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(505, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-23 20:57:41', '15000', 'Cash', NULL, NULL, NULL, 'RP-00126', '[{\"id\":\"270\",\"name\":\"White trays\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(506, 0, 7, 0, '5.00', '5000', '0', 31, '2026-07-24 11:45:36', '5000', 'Cash', NULL, NULL, NULL, 'RP-00127', '[{\"id\":\"307\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":5,\"payment_method\":\"Cash\"}]', NULL, NULL),
(507, 0, 7, 0, '6.00', '72000', '0', 31, '2026-07-24 11:52:52', '72000', 'Cash', NULL, NULL, NULL, 'RP-00128', '[{\"id\":\"298\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":6,\"payment_method\":\"Cash\"}]', NULL, NULL),
(508, 0, 7, 0, '3.00', '3000', '0', 31, '2026-07-24 11:54:32', '3000', 'Cash', NULL, NULL, NULL, 'RP-00129', '[{\"id\":\"307\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":3,\"payment_method\":\"Cash\"}]', NULL, NULL),
(509, 0, 7, 0, '6.00', '100000', '0', 31, '2026-07-24 12:54:09', '100000', 'Cash', NULL, NULL, NULL, 'RP-00130', '[{\"id\":\"301\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":2,\"payment_method\":\"Cash\"},{\"id\":\"295\",\"name\":\"White trays\",\"price\":15000,\"quantity\":4,\"payment_method\":\"Cash\"}]', NULL, NULL),
(510, 0, 7, 0, '4.00', '4000', '0', 31, '2026-07-24 12:54:34', '4000', 'Cash', NULL, NULL, NULL, 'RP-00131', '[{\"id\":\"307\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":4,\"payment_method\":\"Cash\"}]', NULL, NULL),
(511, 0, 7, 0, '2.00', '2000', '0', 31, '2026-07-24 12:54:51', '2000', 'Cash', NULL, NULL, NULL, 'RP-00132', '[{\"id\":\"307\",\"name\":\"Damage eggs\",\"price\":1000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(512, 0, 7, 0, '2.00', '40000', '0', 31, '2026-07-24 12:56:54', '40000', 'MTN MoMo', NULL, NULL, NULL, 'RP-00133', '[{\"id\":\"297\",\"name\":\"Cream trays\",\"price\":20000,\"quantity\":2,\"payment_method\":\"Cash\"}]', NULL, NULL),
(513, 0, 7, 0, '1.00', '20000', '0', 31, '2026-07-24 12:58:39', '20000', 'Cash', NULL, NULL, NULL, 'RP-00134', '[{\"id\":\"302\",\"name\":\"Live birds\",\"price\":20000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(514, 0, 7, 0, '4.00', '48000', '0', 31, '2026-07-24 13:18:19', '48000', 'Cash', NULL, NULL, NULL, 'RP-00135', '[{\"id\":\"305\",\"name\":\"Manure\",\"price\":12000,\"quantity\":4,\"payment_method\":\"Cash\"}]', NULL, NULL),
(515, 0, 7, 0, '29.00', '348000', '0', 31, '2026-07-24 13:28:10', '348000', 'Cash', NULL, NULL, NULL, 'RP-00136', '[{\"id\":\"298\",\"name\":\"Wholesale Brown\",\"price\":12000,\"quantity\":29,\"payment_method\":\"Cash\"}]', NULL, NULL),
(516, 0, 7, 0, '3.00', '0', '0', 31, '2026-07-24 13:29:16', '0', 'Debtor', NULL, NULL, 'INV-00026', NULL, '[{\"id\":\"295\",\"name\":\"White trays\",\"price\":15000,\"quantity\":2,\"payment_method\":\"Cash\"},{\"id\":\"301\",\"name\":\"Dressed Birds\",\"price\":20000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL),
(517, 0, 7, 0, '1.00', '15000', '0', 31, '2026-07-24 13:35:01', '15000', 'Cash', NULL, NULL, NULL, 'RP-00137', '[{\"id\":\"315\",\"name\":\"White single normal\",\"price\":15000,\"quantity\":1,\"payment_method\":\"Cash\"}]', NULL, NULL);

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
(8, 'super', 'super@gmail.com', '12345', 'super', 4114184, 0, NULL, '2025-10-25 22:11:24', 'active'),
(31, 'Daudi', 'daudi@gmail.com', '$2y$10$1DNsniJOiFWLBpZJ3qgfu.2/HsGw0iQORF67H5HYwQZ3ifDDu.mS2', 'staff', 712345678, 7, 1, '2026-07-18 05:21:56', 'active'),
(32, 'Johnson', 'j@gmail.com', '$2y$10$sV24UbrXNiF3bg4OfDrvLepwOX5ZvH7sA7QTnh.S9u5sYXh3AX56C', 'staff', 783590362, 7, 1, '2026-07-19 06:58:22', 'active'),
(33, 'Omar', 'o@gmail.com', '$2y$10$YuW4xV3.TleJPjKi9Mww5uvHZrRaHJZS64a38wT9eBBpP93DCOW2G', 'admin', 71234567, 0, 1, '2026-07-24 01:54:04', 'active'),
(34, 'Ann', 'ann@gmail.com', '$2y$10$R4rZMfi1VQQoDVoAQ626AuIgXgzIp5N1uxRsPkbL4HkYo0mDrN5s.', 'admin', 712345678, 0, 1, '2026-07-24 01:55:37', 'active');

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
-- Indexes for table `daily_banking`
--
ALTER TABLE `daily_banking`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_date_branch` (`date`,`branch_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `customer_transactions`
--
ALTER TABLE `customer_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `daily_banking`
--
ALTER TABLE `daily_banking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `debtors`
--
ALTER TABLE `debtors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=320;

--
-- AUTO_INCREMENT for table `profits`
--
ALTER TABLE `profits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=518;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

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
