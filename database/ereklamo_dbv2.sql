-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 20, 2025 at 02:26 AM
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
-- Database: `ereklamo_dbv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `complaint`
--

CREATE TABLE `complaint` (
  `Complaint_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `Complaint_Location_ID` int(11) NOT NULL,
  `Complaint_Category` varchar(255) NOT NULL,
  `Complaint_SubCategory` varchar(255) NOT NULL,
  `Complaint_Description` text NOT NULL,
  `Complaint_TrackingNumber` varchar(255) NOT NULL,
  `Complaint_Status` varchar(255) NOT NULL,
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `Progress_Date` date NOT NULL,
  `Resolved_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_location`
--

CREATE TABLE `complaint_location` (
  `Complaint_Location_ID` int(11) NOT NULL,
  `Complaint_Region` varchar(255) NOT NULL,
  `Complaint_Province` varchar(255) NOT NULL,
  `Complaint_City` varchar(255) NOT NULL,
  `Complaint_Barangay` varchar(255) NOT NULL,
  `Complaint_Street` varchar(255) NOT NULL,
  `Complaint_Landmark` varchar(255) NOT NULL,
  `Complaint_ZIP` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaint_media`
--

CREATE TABLE `complaint_media` (
  `Complaint_Media_ID` int(11) NOT NULL,
  `Complaint_ID` int(11) NOT NULL,
  `File_Path` varchar(255) NOT NULL,
  `File_Type` varchar(255) NOT NULL,
  `Upload_Date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `User_ID` int(11) NOT NULL,
  `User_FirstName` varchar(255) NOT NULL,
  `User_LastName` varchar(255) NOT NULL,
  `User_Email` varchar(255) NOT NULL,
  `User_PhoneNumber` varchar(255) NOT NULL,
  `User_Password` varchar(255) NOT NULL,
  `User_Type` varchar(1) NOT NULL,
  `AccountCreated_At` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`User_ID`, `User_FirstName`, `User_LastName`, `User_Email`, `User_PhoneNumber`, `User_Password`, `User_Type`, `AccountCreated_At`) VALUES
(1, '', '', 'e.reklamo.alerts@gmail.com', '09288247445', '$2y$10$CuVVv6OvRRUedR6oxX.oWOjRb7Rrj4TmZYzbglkldnxbDjwomdTTm', '1', '2025-10-20 00:12:57');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `User_Address_ID` int(11) NOT NULL,
  `User_ID` int(11) NOT NULL,
  `User_Region` varchar(255) NOT NULL,
  `User_Province` varchar(255) NOT NULL,
  `User_City` varchar(255) NOT NULL,
  `User_Barangay` varchar(255) NOT NULL,
  `User_Street` varchar(255) NOT NULL,
  `User_HouseNo` varchar(255) NOT NULL,
  `User_ZIP` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`User_Address_ID`, `User_ID`, `User_Region`, `User_Province`, `User_City`, `User_Barangay`, `User_Street`, `User_HouseNo`, `User_ZIP`) VALUES
(1, 1, 'Region IV-A (CALABARZON)', 'Batangas', 'Batangas City (Capital)', 'Balagtas', 'Diversion Road', '', '4200');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `complaint`
--
ALTER TABLE `complaint`
  ADD PRIMARY KEY (`Complaint_ID`),
  ADD KEY `Complaint_Location_ID` (`Complaint_Location_ID`),
  ADD KEY `User_ID` (`User_ID`);

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
  ADD PRIMARY KEY (`User_ID`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`User_Address_ID`),
  ADD KEY `User_ID` (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `complaint`
--
ALTER TABLE `complaint`
  MODIFY `Complaint_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_location`
--
ALTER TABLE `complaint_location`
  MODIFY `Complaint_Location_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaint_media`
--
ALTER TABLE `complaint_media`
  MODIFY `Complaint_Media_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `User_Address_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaint`
--
ALTER TABLE `complaint`
  ADD CONSTRAINT `complaint_ibfk_1` FOREIGN KEY (`Complaint_Location_ID`) REFERENCES `complaint_location` (`Complaint_Location_ID`),
  ADD CONSTRAINT `complaint_ibfk_2` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);

--
-- Constraints for table `complaint_media`
--
ALTER TABLE `complaint_media`
  ADD CONSTRAINT `complaint_media_ibfk_1` FOREIGN KEY (`Complaint_ID`) REFERENCES `complaint` (`Complaint_ID`);

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`User_ID`) REFERENCES `user` (`User_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
