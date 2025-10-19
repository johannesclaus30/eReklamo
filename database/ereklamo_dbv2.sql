-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2025 at 09:32 AM
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
  `Pending_Date` date NOT NULL,
  `Progress_Date` date NOT NULL,
  `Resolved_Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaint`
--

INSERT INTO `complaint` (`Complaint_ID`, `User_ID`, `Complaint_Location_ID`, `Complaint_Category`, `Complaint_SubCategory`, `Complaint_Description`, `Complaint_TrackingNumber`, `Complaint_Status`, `Created_At`, `Pending_Date`, `Progress_Date`, `Resolved_Date`) VALUES
(1, 1, 1, 'Environment', 'Air and Water Pollution', 'SDADADSAD', 'ERK-1234567890', 'pending', '2025-10-19 06:56:44', '0000-00-00', '0000-00-00', '0000-00-00'),
(2, 1, 2, 'Infrastructure', 'Damaged Road', '123123', 'ERK-454E8C3776', 'pending', '2025-10-19 07:16:57', '0000-00-00', '0000-00-00', '0000-00-00'),
(3, 1, 3, 'Infrastructure', 'Sewer Blockage', 'asfsafagsag', 'ERK-4A85F193AC', 'pending', '2025-10-19 07:17:55', '0000-00-00', '0000-00-00', '0000-00-00'),
(4, 1, 4, 'Infrastructure', 'Weak Streetlights', 'NATUMBA', 'ERK-1BA949F2BA', 'pending', '2025-10-19 07:19:07', '0000-00-00', '0000-00-00', '0000-00-00'),
(5, 1, 5, 'Infrastructure', 'Blocked Footpaths/Sidewalks', 'gg', 'ERK-9DFF697222', 'pending', '2025-10-19 07:19:44', '0000-00-00', '0000-00-00', '0000-00-00'),
(6, 1, 6, 'Environment', 'Littering', 'safasfas', 'ERK-9DE0C500BF', 'pending', '2025-10-19 07:21:23', '0000-00-00', '0000-00-00', '0000-00-00'),
(7, 1, 7, 'Others', 'ARIANA GRANDE', 'AHHAHAA', 'ERK-CF9510647E', 'pending', '2025-10-19 07:25:35', '0000-00-00', '0000-00-00', '0000-00-00'),
(8, 1, 8, 'Infrastructure', 'Sewer Blockage', 'AGASGASGASG', 'ERK-F14C7772F4', 'pending', '2025-10-19 07:27:22', '0000-00-00', '0000-00-00', '0000-00-00'),
(9, 1, 9, 'Environment', 'Flooding', 'HSDHSHS', 'ERK-62997E04EC', 'pending', '2025-10-19 07:27:52', '0000-00-00', '0000-00-00', '0000-00-00'),
(10, 1, 10, 'Infrastructure', 'Sewer Blockage', 'hlkrkr', 'ERK-C037195C75', 'pending', '2025-10-19 07:28:54', '0000-00-00', '0000-00-00', '0000-00-00');

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

--
-- Dumping data for table `complaint_location`
--

INSERT INTO `complaint_location` (`Complaint_Location_ID`, `Complaint_Region`, `Complaint_Province`, `Complaint_City`, `Complaint_Barangay`, `Complaint_Street`, `Complaint_Landmark`, `Complaint_ZIP`) VALUES
(1, 'Region XI (Davao Region)', 'Davao Del Norte', 'Island Garden City Of Samal', 'Aumbay', 'Rizal Street', '', '4200'),
(2, 'National Capital Region (NCR)', 'Ncr, Fourth District', 'Pasay City', 'Barangay 1', 'Rizal Street', 'eee', '1244'),
(3, 'MIMAROPA', 'Occidental Mindoro', 'Rizal', 'Magsikap', 'Rizal Street', '', ''),
(4, 'Autonomous Region in Muslim Mindanao (ARMM)', 'Lanao Del Sur', 'Balabagan', 'Budas', 'MountView Park', '', ''),
(5, 'Region IV-A (CALABARZON)', 'Laguna', 'Mabitac', 'Libis ng Nayon (Pob.)', 'Sto. Tomas, Batangas', '', ''),
(6, 'National Capital Region (NCR)', 'Ncr, Fourth District', 'City Of Parañaque', 'San Dionisio', 'sgasgaga', '', ''),
(7, 'Region II (Cagayan Valley)', 'Isabela', 'Aurora', 'Esperanza East', 'sgasgaga', '', ''),
(8, 'Region XI (Davao Region)', 'Davao Del Norte', 'New Corella', 'Cabidianan', 'sgasgaga', '', ''),
(9, 'Autonomous Region in Muslim Mindanao (ARMM)', 'Basilan', 'Tipo-tipo', 'Limbo-Upas', 'Rizal Street', 'eee', '1244'),
(10, 'Region II (Cagayan Valley)', 'Batanes', 'Ivana', 'Salagao', 'Rizal Street', 'eee', '1244');

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

--
-- Dumping data for table `complaint_media`
--

INSERT INTO `complaint_media` (`Complaint_Media_ID`, `Complaint_ID`, `File_Path`, `File_Type`, `Upload_Date`) VALUES
(1, 2, 'post_videos/video_68f4906960d935.49383651.mp4', 'mp4', '2025-10-19 07:16:57'),
(2, 3, 'post_videos/video_68f490a35cf396.73202304.mp4', 'mp4', '2025-10-19 07:17:55'),
(3, 4, 'post_videos/video_68f490eb4bf791.11027364.mp4', 'mp4', '2025-10-19 07:19:07'),
(4, 6, 'post_photos/photo_68f49173248fa1.17161138.jpg', 'jpg', '2025-10-19 07:21:23'),
(5, 6, 'post_photos/photo_68f49173252ad5.79112420.jpg', 'jpg', '2025-10-19 07:21:23'),
(6, 7, 'post_photos/photo_68f4926f1aa4d1.22499473.png', 'png', '2025-10-19 07:25:35'),
(7, 8, 'post_photos/photo_68f492dabf7c97.18549764.jpg', 'jpg', '2025-10-19 07:27:22'),
(8, 8, 'post_photos/photo_68f492dac03533.02180132.jpg', 'jpg', '2025-10-19 07:27:22');

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
(1, 'Admin', '', 'admin@e-reklamo.org', '', 'admin123', '1', '2025-10-15 05:18:56'),
(2, 'Jade', 'CP', 'jadecp@nu-lipa.edu.ph', '+63 492 449 9573', '$2y$10$FfnaNma4C4uYGxfurxbBFuoxxxEYqTAZZij4i.BACShQkahltqILW', '2', '2025-10-15 05:00:21'),
(3, 'Timohtyyy', 'Asada', 'tjasada@national-u.edu.ph', '9242959503', '$2y$10$iPVY2f9E8OoJU/f9zA9P0eHloQZZAAv7FIcHqfyxUMmIrjjiHFndS', '2', '2025-10-19 03:54:58'),
(4, 'TJ', 'Asada Hehe', 'tjasada@feu.edu.ph', '+639157792517', '$2y$10$JM7r3OeAyP1XOpJQInSnDOdEAYoo/G9MJie5Hs2UQMeiJPLkiWyLO', '2', '2025-10-19 06:09:53');

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
(1, 1, '', '', '', '', '', '', ''),
(2, 2, '10', '1013', '101312', '101312051', 'Bayanihan Road 3', '452', '4200'),
(3, 3, 'National Capital Region (NCR)', 'City Of Manila', 'Santa Ana', 'Barangay 757', 'Santa Rosa', '242', '1255'),
(4, 4, 'National Capital Region (NCR)', 'City Of Manila', 'San Miguel', 'Barangay 639', 'agag', '452', '4200');

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
  MODIFY `Complaint_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `complaint_location`
--
ALTER TABLE `complaint_location`
  MODIFY `Complaint_Location_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `complaint_media`
--
ALTER TABLE `complaint_media`
  MODIFY `Complaint_Media_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `User_Address_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
