-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2025 at 07:15 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eraklamo_dbv1`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `Complaint_ID` int(11) NOT NULL,
  `User_ID` int(11) DEFAULT NULL,
  `Complaint_Location_ID` int(11) DEFAULT NULL,
  `Complaint_Category` varchar(100) DEFAULT NULL,
  `Complaint_SubCategory` varchar(100) DEFAULT NULL,
  `Complaint_Description` text DEFAULT NULL,
  `Complaint_TrackingNumber` varchar(50) DEFAULT NULL,
  `Complaint_Status` varchar(50) DEFAULT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Resolved_At` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_location`
--

CREATE TABLE `complaint_location` (
  `Complaint_Location_ID` int(11) NOT NULL,
  `Complaint_Region` varchar(100) DEFAULT NULL,
  `Complaint_Province` varchar(100) DEFAULT NULL,
  `Complaint_City` varchar(100) DEFAULT NULL,
  `Complaint_Barangay` varchar(100) DEFAULT NULL,
  `Complaint_Street` varchar(100) DEFAULT NULL,
  `Complaint_Landmark` varchar(255) DEFAULT NULL,
  `Complaint_ZIP` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_media`
--

CREATE TABLE `complaint_media` (
  `Complaint_Media_ID` int(11) NOT NULL,
  `Complaint_ID` int(11) DEFAULT NULL,
  `File_Path` varchar(255) DEFAULT NULL,
  `File_Type` varchar(50) DEFAULT NULL,
  `Upload_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(11) NOT NULL,
  `User_Address_ID` int(11) DEFAULT NULL,
  `User_FirstName` varchar(100) DEFAULT NULL,
  `User_LastName` varchar(100) DEFAULT NULL,
  `User_Email` varchar(100) DEFAULT NULL,
  `User_PhoneNumber` varchar(15) DEFAULT NULL,
  `User_Password` varchar(255) DEFAULT NULL,
  `User_Type` varchar(50) DEFAULT NULL,
  `AccountCreated_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `User_Address_ID` int(11) NOT NULL,
  `User_Region` varchar(100) DEFAULT NULL,
  `User_Province` varchar(100) DEFAULT NULL,
  `User_City` varchar(100) DEFAULT NULL,
  `User_Barangay` varchar(100) DEFAULT NULL,
  `User_Street` varchar(100) DEFAULT NULL,
  `User_HouseNo` varchar(50) DEFAULT NULL,
  `User_ZIP` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`Complaint_ID`),
  ADD KEY `User_ID` (`User_ID`),
  ADD KEY `Complaint_Location_ID` (`Complaint_Location_ID`);

--
-- Indexes for table `complaint_location`
--
ALTER TABLE `complaint_location`
  ADD PRIMARY KEY (`Complaint_Location_ID`);

--
-- Indexes for table `complaint_media`
--
ALTER TABLE `complaint_media`
  ADD PRIMARY KEY (`Complaint_Media_ID`),
  ADD KEY `Complaint_ID` (`Complaint_ID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`User_ID`),
  ADD KEY `User_Address_ID` (`User_Address_ID`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`User_Address_ID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `complaint_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`),
  ADD CONSTRAINT `complaint_ibfk_2` FOREIGN KEY (`Complaint_Location_ID`) REFERENCES `complaint_location` (`Complaint_Location_ID`);

--
-- Constraints for table `complaint_media`
--
ALTER TABLE `complaint_media`
  ADD CONSTRAINT `complaint_media_ibfk_1` FOREIGN KEY (`Complaint_ID`) REFERENCES `complaint` (`Complaint_ID`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`User_Address_ID`) REFERENCES `user_address` (`User_Address_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
