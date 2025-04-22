-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 20, 2025 at 01:54 AM
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
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `AD_ID` varchar(15) NOT NULL,
  `USER_ID` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `CL_ID` varchar(15) NOT NULL,
  `USER_ID` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `client`
--

INSERT INTO `client` (`CL_ID`, `USER_ID`) VALUES
('CL25-00001', '25-INTL-00004');

-- --------------------------------------------------------

--
-- Table structure for table `freelancer`
--

CREATE TABLE `freelancer` (
  `FR_ID` varchar(15) NOT NULL,
  `USER_ID` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer`
--

INSERT INTO `freelancer` (`FR_ID`, `USER_ID`) VALUES
('FR25-00001', '25-INTL-00001'),
('FR25-00002', '25-INTL-00003');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `USER_ID` varchar(15) NOT NULL,
  `USER_EMAIL` varchar(255) NOT NULL,
  `USER_TYPE` varchar(255) NOT NULL,
  `USER_FSTNAME` varchar(255) NOT NULL,
  `USER_LSTNAME` varchar(255) NOT NULL,
  `USER_BIRTHDAY` date NOT NULL,
  `USER_CONTACT` varchar(11) NOT NULL,
  `USER_PASSWORD` varchar(255) NOT NULL,
  `USER_NAME` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`USER_ID`, `USER_EMAIL`, `USER_TYPE`, `USER_FSTNAME`, `USER_LSTNAME`, `USER_BIRTHDAY`, `USER_CONTACT`, `USER_PASSWORD`, `USER_NAME`) VALUES
('25-INTL-00001', 'joshua.ranin@wvsu.edu.ph', 'Freelancer', 'JOSHUA DAVID', 'RANIN', '2005-05-15', '09777152764', 'Josh12345!', 'JoshDavid'),
('25-INTL-00003', 'ianharvey@gmail.com', 'Freelancer', 'IAN HARVEY', 'YAP', '2005-09-21', '09876543219', 'Harvs45678!', 'ThisisHarv'),
('25-INTL-00004', 'nat@gmail.com', 'Client', 'NATASHA', 'BINAL', '2025-04-02', '09876543219', 'Nats45678!', 'Binals');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`AD_ID`,`USER_ID`),
  ADD KEY `USER_ID` (`USER_ID`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`CL_ID`,`USER_ID`),
  ADD KEY `USER_ID` (`USER_ID`);

--
-- Indexes for table `freelancer`
--
ALTER TABLE `freelancer`
  ADD PRIMARY KEY (`FR_ID`,`USER_ID`),
  ADD KEY `USER_ID` (`USER_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`USER_ID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`);

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`);

--
-- Constraints for table `freelancer`
--
ALTER TABLE `freelancer`
  ADD CONSTRAINT `freelancer_ibfk_1` FOREIGN KEY (`USER_ID`) REFERENCES `user` (`USER_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
