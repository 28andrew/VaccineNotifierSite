-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 05, 2021 at 02:29 AM
-- Server version: 10.3.22-MariaDB-1ubuntu1
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vaccine`
--

-- --------------------------------------------------------

--
-- Table structure for table `last_notification`
--

CREATE TABLE `last_notification` (
                                     `id` int(11) NOT NULL,
                                     `location_id` int(11) NOT NULL,
                                     `timestamp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `push_notifications`
--

CREATE TABLE `push_notifications` (
                                      `id` int(11) NOT NULL,
                                      `endpoint` varchar(256) NOT NULL,
                                      `public_key` varchar(256) NOT NULL,
                                      `auth_token` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pw_tokens`
--

CREATE TABLE `pw_tokens` (
                             `id` int(11) NOT NULL,
                             `token` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
                         `id` int(11) NOT NULL,
                         `email` varchar(128) NOT NULL,
                         `password` varchar(512) NOT NULL,
                         `state` varchar(32) NOT NULL,
                         `register_date` int(11) NOT NULL,
                         `signup_ip` varchar(256) NOT NULL,
                         `verified` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
                                    `id` int(11) NOT NULL,
                                    `latitude` decimal(30,20) NOT NULL,
                                    `longitude` decimal(30,20) NOT NULL,
                                    `radius_miles` decimal(30,20) NOT NULL,
                                    `vaccine_type` varchar(32) NOT NULL,
                                    `enabled` tinyint(1) NOT NULL DEFAULT 0,
                                    `carrier` varchar(64) NOT NULL,
                                    `phone` varchar(32) NOT NULL,
                                    `repeat_seconds` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_tokens`
--

CREATE TABLE `user_tokens` (
                               `id` int(11) NOT NULL,
                               `token` varchar(128) NOT NULL,
                               `ip` varchar(256) NOT NULL,
                               `creation_date` int(11) NOT NULL,
                               `user_agent` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_verify`
--

CREATE TABLE `user_verify` (
                               `id` int(11) NOT NULL,
                               `token` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `last_notification`
--
ALTER TABLE `last_notification`
    ADD PRIMARY KEY (`id`,`location_id`);

--
-- Indexes for table `push_notifications`
--
ALTER TABLE `push_notifications`
    ADD PRIMARY KEY (`id`,`endpoint`);

--
-- Indexes for table `pw_tokens`
--
ALTER TABLE `pw_tokens`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_tokens`
--
ALTER TABLE `user_tokens`
    ADD PRIMARY KEY (`id`,`token`);

--
-- Indexes for table `user_verify`
--
ALTER TABLE `user_verify`
    ADD PRIMARY KEY (`id`,`token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `last_notification`
--
ALTER TABLE `last_notification`
    ADD CONSTRAINT `last_notification_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `push_notifications`
--
ALTER TABLE `push_notifications`
    ADD CONSTRAINT `push_notifications_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pw_tokens`
--
ALTER TABLE `pw_tokens`
    ADD CONSTRAINT `pw_tokens_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
    ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_tokens`
--
ALTER TABLE `user_tokens`
    ADD CONSTRAINT `user_tokens_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_verify`
--
ALTER TABLE `user_verify`
    ADD CONSTRAINT `user_verify_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
