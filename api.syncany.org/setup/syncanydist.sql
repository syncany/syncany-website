-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 26, 2015 at 10:47 PM
-- Server version: 5.5.41-0ubuntu0.14.04.1
-- PHP Version: 5.5.9-1ubuntu4.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `syncanydist`
--
CREATE DATABASE IF NOT EXISTS `syncanydist` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `syncanydist`;

-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pluginId` varchar(255) NOT NULL,
  `pluginName` varchar(255) NOT NULL,
  `pluginVersion` varchar(255) NOT NULL,
  `pluginOperatingSystem` varchar(255) NOT NULL DEFAULT 'all',
  `pluginArchitecture` varchar(255) NOT NULL DEFAULT 'all',
  `pluginDate` varchar(255) NOT NULL,
  `pluginAppMinVersion` varchar(255) NOT NULL,
  `pluginRelease` int(1) NOT NULL DEFAULT '0',
  `pluginConflictsWith` varchar(255) DEFAULT NULL,
  `sha256sum` varchar(255) NOT NULL,
  `filenameBasename` varchar(255) NOT NULL,
  `filenameFull` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pluginId_2` (`pluginId`,`pluginVersion`,`pluginOperatingSystem`,`pluginArchitecture`),
  KEY `pluginId` (`pluginId`),
  KEY `pluginDate` (`pluginDate`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=872 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
