-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 10, 2025 at 08:31 PM
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
-- Database: `interlinkdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USER_ID` text NOT NULL,
  `USER_EMAIL` text NOT NULL,
  `USER_TYPE` text NOT NULL,
  `USER_FSTNAME` text NOT NULL,
  `USER_LSTNAME` text NOT NULL,
  `USER_BIRTHDAY` date NOT NULL,
  `USER_CONTACT` varchar(11) NOT NULL,
  `USER_PASSWORD` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `USER_EMAIL`, `USER_TYPE`, `USER_FSTNAME`, `USER_LSTNAME`, `USER_BIRTHDAY`, `USER_CONTACT`, `USER_PASSWORD`) VALUES
('CL250000', 'joshua.ranin@wvsu.edu.ph', 'Client', 'JOSHUA', 'RANIN', '2005-05-15', '09777152764', '$2y$10$ALb9aGZjZfmi52P559UY9eHV3ne.xnojXzsGuos9/FgK8x6YE4Ycu'),
('CL250001', 'ianharvey@gmail.com', 'Client', 'IAN', 'HARVEY', '2005-09-21', '09776337374', '$2y$10$9XfanKK5s7O1MuZmMzmM2.UUkEya3Q0dKvIeQNz0eixChHnWvGfVy'),
('FR250000', 'nat@gmail.com', 'Freelancer', 'NATASHA', 'BINAL', '2005-10-11', '123456789', '$2y$10$3DH.ta6yHbe.s/5LV1rF2uYj8nXEgZbx0F5fwtSWgBNKplGCBI4SS'),
('FR250001', 'jor@gmail.com', 'Freelancer', 'Jorich', 'Dohinog', '2005-04-11', '987654321', '$2y$10$mXgVhLoUDxg2FrWZVz9AqeqIxaRJai21jBpgNtvU4g0NyDzh7qhbO'),
('FR250002', 'marvin@gmail.com', 'Freelancer', 'IAN', 'MARVIN', '2003-03-06', '142536457', '$2y$10$RYsW43DBdTguT4fJbMXh2.IZJR4KPPzHa3rtY4GG/C.zPceSKP3XK');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD UNIQUE KEY `USER_ID` (`USER_ID`) USING HASH;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
