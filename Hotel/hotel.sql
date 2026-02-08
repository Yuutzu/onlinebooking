-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Apr 06, 2025 at 06:47 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hotel`
--

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(255) NOT NULL,
  `client_id` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `client_presented_id` varchar(255) NOT NULL,
  `client_id_picture` varchar(255) NOT NULL,
  `client_id_number` varchar(255) NOT NULL,
  `client_phone` varchar(255) NOT NULL,
  `client_email` varchar(255) NOT NULL,
  `client_password` varchar(255) NOT NULL,
  `client_status` varchar(255) NOT NULL,
  `client_picture` varchar(255) NOT NULL,
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed_attempt` timestamp NULL DEFAULT NULL,
  `role` varchar(45) DEFAULT 'User'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_id`, `client_name`, `client_presented_id`, `client_id_picture`, `client_id_number`, `client_phone`, `client_email`, `client_password`, `client_status`, `client_picture`, `failed_attempts`, `last_failed_attempt`, `role`) VALUES
(0, 'ADMIN-2025-4182AS42', 'Kensang Omandam', 'Social Security ID', 'profile2.jpg', '123456', '09519237937', 'admin@gmail.com', 'admin123', 'Activated', 'profile2.jpg', 0, '0000-00-00 00:00:00', 'Admin'),
(37, 'LUX-2025-4ABA7528C9', 'Arturo Yparraguirre', 'Social Security', 'profile1.jpg', '531297', '09099366481', 'arturoyparraguirre01@gmail.com', '1234', 'Activated', 'profile1.jpg', 0, NULL, 'User');

-- --------------------------------------------------------

--
-- Table structure for table `inquiry`
--

CREATE TABLE `inquiry` (
  `inquiry_id` int(11) NOT NULL,
  `date` varchar(45) DEFAULT NULL,
  `time` varchar(45) DEFAULT NULL,
  `name` varchar(45) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `inquiry` varchar(500) DEFAULT NULL,
  `remarks` varchar(500) DEFAULT NULL,
  `status` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_category` varchar(45) DEFAULT NULL,
  `product_name` varchar(45) DEFAULT NULL,
  `product_description` varchar(500) DEFAULT NULL,
  `product_price` varchar(45) DEFAULT NULL,
  `product_image` varchar(500) DEFAULT NULL,
  `product_status` enum('Available','Unavailable','Out of stock') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_category`, `product_name`, `product_description`, `product_price`, `product_image`, `product_status`) VALUES
(190907, 'Beverages', 'Coca Cola', 'Sparkling soft drinks!', '65', 'images.jpg', 'Available'),
(255702, 'Food', 'Spaghetti', 'Delicious', '185', 'spaghetti-with-white-background-high-quality-ultra-free-photo.jpg', 'Available'),
(396968, 'Food', 'Iced Coffee', 'So refreshing!', '75', 'download (1).jpg', 'Available'),
(418757, 'Food', 'French Fries', 'Delicious Snacks!', '55', 'download (2).jpg', 'Available'),
(547123, 'Food', 'Fried Chicken', 'Crispy Chicken!', '85', '1000_F_491337687_PrvRcxwYnKtgmxoERK3i9TW02tcDE9cz.jpg', 'Available'),
(628351, 'Beverages', 'Red Wine', 'Makes your night romantic!', '95', 'download.jpg', 'Available'),
(852013, 'Food', 'Meal', 'Delicious Meal', '250', 'pngtree-fast-foods-png-image_10320972.png', 'Available'),
(906841, 'Beverages', 'Orange Juice', 'So Refreshing!', '45', 'images (1).jpg', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `request_id` int(11) NOT NULL,
  `reservation_id` varchar(45) DEFAULT NULL,
  `products` varchar(500) DEFAULT NULL,
  `services` varchar(500) DEFAULT NULL,
  `total_price` varchar(45) DEFAULT NULL,
  `status` enum('confirmed','pending','cancelled') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`request_id`, `reservation_id`, `products`, `services`, `total_price`, `status`) VALUES
(351337, '67', '[\"Spaghetti (₱185 x 2)\",\"Iced Coffee (₱75 x 3)\",\"French Fries (₱55 x 1)\",\"Meal (₱250 x 1)\",\"Coca Cola (₱65 x 1)\",\"Red Wine (₱95 x 1)\",\"Orange Juice (₱45 x 2)\"]', '[\"Swimming Pool (₱200)\",\"Tennis Court (₱280)\"]', '1630', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservation_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `check_in` date DEFAULT NULL,
  `check_out` date DEFAULT NULL,
  `payment_method` varchar(45) DEFAULT 'Gcash',
  `gcash_name` varchar(45) DEFAULT NULL,
  `gcash_number` varchar(45) DEFAULT NULL,
  `gcash_ref` varchar(45) DEFAULT NULL,
  `gcash_screenshot` varchar(500) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `type` varchar(45) DEFAULT 'Online',
  `reservation_status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservation_id`, `client_id`, `room_id`, `check_in`, `check_out`, `payment_method`, `gcash_name`, `gcash_number`, `gcash_ref`, `gcash_screenshot`, `total_price`, `type`, `reservation_status`, `created_at`) VALUES
(67, 37, 10, '2025-03-29', '2025-03-30', 'Gcash', 'Arturo Yparraguirre Jr.', '09099366481', '1234567890123', 'During (14).png', 2500.00, 'Online', 'confirmed', '2025-03-28 06:11:40'),
(68, 37, 30, '2025-03-29', '2025-03-31', 'Gcash', 'Arturo Yparraguirre Jr.', '09099366481', '1234567890123', 'During (15).png', 2000.00, 'Online', 'pending', '2025-03-28 06:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(255) NOT NULL,
  `room_name` varchar(255) NOT NULL,
  `room_number` varchar(255) NOT NULL,
  `room_description` text NOT NULL,
  `room_adult` int(255) NOT NULL,
  `room_child` int(255) NOT NULL,
  `room_category` varchar(255) NOT NULL,
  `room_price` float NOT NULL,
  `room_status` varchar(255) NOT NULL,
  `room_picture` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`, `room_number`, `room_description`, `room_adult`, `room_child`, `room_category`, `room_price`, `room_status`, `room_picture`, `created_at`) VALUES
(30, 'Room 101', '1234', 'Lorem ipsum dolor sit amet consectetur adipisicing elit. Ea dolore quibusdam dolores itaque! Magnam corporis inventore nihil dolorum quidem placeat. Eaque sunt voluptatibus officiis, sapiente enim deleniti est aliquam qui!\r\n', 2, 3, 'Presidential Suite Room', 1000, 'Booked', 'double.jpg', '2025-03-28 14:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `room_category`
--

CREATE TABLE `room_category` (
  `id` int(255) NOT NULL,
  `category_id` varchar(255) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_description` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_category`
--

INSERT INTO `room_category` (`id`, `category_id`, `category_name`, `category_description`, `created_at`) VALUES
(15, 'CAT-2B1B', 'Deluxe Room', 'Deluxe Where Comforts maximized.', '2025-03-20 19:52:54'),
(19, 'CAT-A5C0', 'Standard Room', 'Standard rooms, cheaper', '2025-03-24 01:23:14'),
(31, 'CAT-0A21', 'Presidential Suite Room', 'Elegant Rooms', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `room_services`
--

CREATE TABLE `room_services` (
  `id` int(255) NOT NULL,
  `service_id` varchar(255) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `service_description` varchar(255) NOT NULL,
  `service_price` varchar(45) DEFAULT NULL,
  `service_picture` varchar(255) NOT NULL,
  `service_status` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_services`
--

INSERT INTO `room_services` (`id`, `service_id`, `service_name`, `service_description`, `service_price`, `service_picture`, `service_status`, `created_at`) VALUES
(11, 'SER-A682', 'Swimming Pool', 'Refreshing Sensation!', '200', 'Familyswimmingpool-GettyImages-155377305-599caa23d088c00010862071.jpg', 'Available', '2025-03-20 19:54:51'),
(15, 'SER-4B1A', 'Tennis Court', 'Have fun with your family and Friends!', '280', 'Ban-sao-cua-Tennis-1.jpg', 'Available', '2025-03-24 23:08:56'),
(18, 'SER-7A5B', 'Basket Ball Court', 'Have fun with your friends and family!', '280', 'basketball-court.jpg', 'Available', '2025-03-27 05:20:43');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `site_name` varchar(45) DEFAULT NULL,
  `carousel1` varchar(500) DEFAULT NULL,
  `carousel2` varchar(500) DEFAULT NULL,
  `carousel3` varchar(500) DEFAULT NULL,
  `site_shortname` varchar(45) DEFAULT NULL,
  `site_welcome_text` varchar(500) DEFAULT NULL,
  `site_about_text1` varchar(500) DEFAULT NULL,
  `site_about_text2` varchar(500) DEFAULT NULL,
  `site_about_text3` varchar(500) DEFAULT NULL,
  `site_about_title1` varchar(500) DEFAULT NULL,
  `site_about_title2` varchar(500) DEFAULT NULL,
  `site_about_title3` varchar(500) DEFAULT NULL,
  `site_favicon` varchar(500) DEFAULT NULL,
  `site_logo` varchar(500) DEFAULT NULL,
  `site_iframe_address` varchar(500) DEFAULT NULL,
  `site_email` varchar(45) DEFAULT NULL,
  `site_contact` varchar(45) DEFAULT NULL,
  `site_bg_color` varchar(45) DEFAULT NULL,
  `site_primary_color` varchar(45) DEFAULT NULL,
  `site_hover_color` varchar(45) DEFAULT NULL,
  `site_about_image1` varchar(500) DEFAULT NULL,
  `site_about_image2` varchar(500) DEFAULT NULL,
  `site_about_image3` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`id`, `site_name`, `carousel1`, `carousel2`, `carousel3`, `site_shortname`, `site_welcome_text`, `site_about_text1`, `site_about_text2`, `site_about_text3`, `site_about_title1`, `site_about_title2`, `site_about_title3`, `site_favicon`, `site_logo`, `site_iframe_address`, `site_email`, `site_contact`, `site_bg_color`, `site_primary_color`, `site_hover_color`, `site_about_image1`, `site_about_image2`, `site_about_image3`) VALUES
(0, 'LUXE', '67e645f2ba277_home.jpeg', '67e645f2bb152_images.jpg', 'banner3.jpg', 'lux', 'Welcome to Luxe Haven Hotel – Where Elegance Meets Comfort. Located in the heart of your dream destination, our opulent suites, world-class dining, and exclusive amenities are crafted for relaxation and sophistication. Whether for leisure or business, enjoy personalized service and exquisite surroundings for an unforgettable stay.', 'Luxe Haven started as a small boutique hotel catering to discerning guests seeking an intimate and personalized stay. Over the years, our commitment to excellence earned us a reputation as one of the most sought-after destinations for both leisure and business travelers.', 'As our guests’ trust and loyalty grew, so did our vision. Luxe Haven expanded its offerings, introducing state-of-the-art facilities, luxurious suites, fine dining restaurants, and wellness amenities. By 2015, we proudly became a premier destination for international travelers, hosting memorable weddings, corporate events, and once-in-a-lifetime celebrations.', 'At Luxe Haven, we believe in creating moments that matter. We combine modern elegance with touches of traditional Filipino culture, showcasing the beauty of the Philippines while offering unparalleled comfort. We strive to provide not just a stay, but an experience. Whether you’re here for a relaxing escape, a business trip, or a special occasion, Luxe Haven Hotel PH promises exceptional service, luxurious accommodations, and unforgettable memories.', 'Our Beginnings', 'Our Growth and Achievements', 'Our Philosophy and Promise', '67e64645572bc_png-transparent-marriott-logo-marriott-international-hotel-logo-company-accommodation-hotel-text-business-resort-removebg-preview.png', '67e6464557ad0_png-transparent-marriott-logo-marriott-international-hotel-logo-company-accommodation-hotel-text-business-resort-removebg-preview.png', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d35556.54193436231!2d125.51167304768366!3d7.049211260849978!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f90d7f845ec3f1%3A0x1de046a8bdd1abc3!2sBaliok%20Barangay%20Hall!5e0!3m2!1sen!2sph!4v1743144346975!5m2!1sen!2sph\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'luxehotelsss@gmail.com', '09099366481', '#ffffff', '#a23939', '#d2a7a7', 'about1.jpg', 'about2.jpg', 'about3.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `walkin_reservation`
--

CREATE TABLE `walkin_reservation` (
  `reservation_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `client_name` varchar(500) DEFAULT NULL,
  `client_email` varchar(500) DEFAULT NULL,
  `client_contact` varchar(11) DEFAULT NULL,
  `client_address` varchar(500) DEFAULT NULL,
  `client_id_type` varchar(45) DEFAULT NULL,
  `client_id_image` varchar(500) DEFAULT NULL,
  `check_in_date` date DEFAULT NULL,
  `check_out_date` date DEFAULT NULL,
  `payment_method` varchar(45) DEFAULT NULL,
  `client_gcash_name` varchar(500) DEFAULT NULL,
  `client_gcash_number` varchar(11) DEFAULT NULL,
  `client_gcash_ref` varchar(500) DEFAULT NULL,
  `client_gcash_ref_image` varchar(500) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT NULL,
  `payment_remarks` varchar(45) DEFAULT NULL,
  `reservation_type` varchar(45) DEFAULT 'Walk-in',
  `reservation_status` enum('pending','confirmed','cancelled') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `walkin_reservation`
--

INSERT INTO `walkin_reservation` (`reservation_id`, `room_id`, `client_name`, `client_email`, `client_contact`, `client_address`, `client_id_type`, `client_id_image`, `check_in_date`, `check_out_date`, `payment_method`, `client_gcash_name`, `client_gcash_number`, `client_gcash_ref`, `client_gcash_ref_image`, `total_price`, `amount_paid`, `balance`, `payment_remarks`, `reservation_type`, `reservation_status`) VALUES
(684474, 30, 'Arturo Yparraguirre', 'arturoyparraguirre01@gmail.com', '09099366481', 'Bangkal', 'Driver\'s License', '1743143635_67e642d383acb_rooms.jpg', '2025-03-29', '2025-04-03', 'gcash', 'Arturo Yparraguirre Jr.', '09099366481', '1234567890123', '', 5000.00, 2000.00, 0.00, 'Fully Paid', 'Walk-in', 'confirmed');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inquiry`
--
ALTER TABLE `inquiry`
  ADD PRIMARY KEY (`inquiry_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`request_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `room_category`
--
ALTER TABLE `room_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `room_services`
--
ALTER TABLE `room_services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `walkin_reservation`
--
ALTER TABLE `walkin_reservation`
  ADD PRIMARY KEY (`reservation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `room_category`
--
ALTER TABLE `room_category`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `room_services`
--
ALTER TABLE `room_services`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
