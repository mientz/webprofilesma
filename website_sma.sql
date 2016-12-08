-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Dec 08, 2016 at 06:02 AM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `website_sma`
--

-- --------------------------------------------------------

--
-- Table structure for table `albums`
--

CREATE TABLE `albums` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `created` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

CREATE TABLE `gallery` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `album_id` int(11) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  `description` longtext,
  `file` text,
  `meta` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `name_url` text NOT NULL,
  `header_image` text NOT NULL,
  `deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `name_url`, `header_image`, `deleted`) VALUES
(1, 'main', 'main', '13385771_255950824767690_1897064539_n.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `title` varchar(45) DEFAULT NULL,
  `parent` int(11) DEFAULT NULL,
  `meta` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `author` text NOT NULL,
  `title` text NOT NULL,
  `content` longtext,
  `description` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

CREATE TABLE `post` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `author` longtext NOT NULL,
  `date` datetime NOT NULL,
  `title` text NOT NULL,
  `title_url` text NOT NULL,
  `content` longtext,
  `visibility` tinyint(1) NOT NULL DEFAULT '1',
  `password` varchar(40) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `header_image` text,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `post`
--

INSERT INTO `post` (`id`, `category_id`, `author`, `date`, `title`, `title_url`, `content`, `visibility`, `password`, `status`, `header_image`, `deleted`) VALUES
(1, 1, '[{"author":"2","date":"2016-11-04 03:02:57"}]', '2016-11-04 03:02:57', 'adadadasd', 'adadadasd', '<p>adadadadsa</p>', 0, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(22, 1, '[{"author":"1","date":"2016-12-04 03:13:10"}]', '2016-12-04 03:13:10', 'adadadada', 'adadadada', '<p>adasdadadad</p>', 2, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(23, 1, '[{"author":"1","date":"2016-12-04 03:13:16"}]', '2016-12-04 03:13:16', 'adadadada', 'adadadada', '<p>adasdadadad</p>', 1, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(24, 1, '[{"author":"1","date":"2016-12-04 03:26:41"},{"author":"1","date":"2016-12-04 03:26:42"},{"author":"1","date":"2016-12-04 03:26:43"},{"author":"1","date":"2016-12-04 03:26:44"},{"author":"1","date":"2016-12-04 03:26:45"},{"author":"1","date":"2016-12-04 03:26:46"},{"author":"1","date":"2016-12-04 03:26:47"},{"author":"1","date":"2016-12-04 03:26:48"},{"author":"1","date":"2016-12-04 03:26:49"},{"author":"1","date":"2016-12-04 03:26:50"},{"author":"1","date":"2016-12-04 03:26:51"},{"author":"1","date":"2016-12-04 03:26:52"},{"author":"1","date":"2016-12-04 03:26:53"},{"author":"1","date":"2016-12-04 03:26:54"},{"author":"1","date":"2016-12-04 03:26:55"},{"author":"1","date":"2016-12-04 03:26:56"},{"author":"1","date":"2016-12-04 03:26:57"},{"author":"1","date":"2016-12-04 03:26:58"},{"author":"1","date":"2016-12-04 03:26:59"},{"author":"1","date":"2016-12-04 03:27:00"},{"author":"1","date":"2016-12-04 03:27:01"},{"author":"1","date":"2016-12-04 03:27:02"},{"author":"1","date":"2016-12-04 03:27:03"},{"author":"1","date":"2016-12-04 03:27:04"},{"author":"1","date":"2016-12-04 03:27:05"},{"author":"1","date":"2016-12-04 03:27:06"},{"author":"1","date":"2016-12-04 03:27:07"},{"author":"1","date":"2016-12-04 03:27:08"},{"author":"1","date":"2016-12-04 03:27:09"},{"author":"1","date":"2016-12-04 03:27:10"},{"author":"1","date":"2016-12-07 19:19:37"}]', '2016-12-07 19:19:37', 'Olympiade Sains Kabupaten 2015', 'olympiade-sains-kabupaten-2015', '<p style="margin-bottom: 6pt; padding: 0px; border: 0px; font-size: 10pt; font-family: &quot;Trebuchet MS&quot;, Tahoma, &quot;Lucida Grande&quot;, Verdana, Arial, Helvetica, sans-serif; vertical-align: baseline; text-align: justify; text-indent: 36pt; line-height: 26.6667px;"><img src="public/content/post-media-2016-12-04-02-22-50-29943.jpg" data-filename="post-media-2016-12-04-02-22-50-29943.jpg" style="width: 25%; float: left;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Syukur Alhamdulillah kami curahkan kepada Allah SWT yang telah memberikan rahmat dan hidayah kepada kita semua. 11 Februari 2015 merupakan waktu yang sangat berarti bagi Smansaba karena lewat perwakilannya, Smansaba dapat memborong 4 Emas, 4 Perak dan 2 Perunggu pada Olimpiade Sains Kab.Bangkalan (OSK). Tak lupa kami sampaikan banyak terimakasih atas dukungan, bantuan dan doa dari segala pihak yang terlibat.&nbsp;<span style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-family: inherit; vertical-align: baseline;">&nbsp;</span>Berikut hasil Olimpiade Sains Kabupaten (OSK) yang dilaksanakan oleh Dinas Pendidikan Kabupaten Bangkalan</span></p><table width="354" height="214" cellspacing="1" cellpadding="1" border="1" align="center" style="margin: 0px; padding: 0px; font-size: 13px; font-family: &quot;Trebuchet MS&quot;, Tahoma, &quot;Lucida Grande&quot;, Verdana, Arial, Helvetica, sans-serif; border-collapse: separate; text-align: justify; background-color: rgb(255, 255, 255);"><tbody style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;">Nama Peserta</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Hasil</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Mata Pelajaran</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">I Made Bayu Dimaswara P.</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Juara 1</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Fisika</span></td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Rizki Amaliya</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 3</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Fisika</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Andy Nur Rosihun Al Fajri</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 3</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Kimia</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Riski Sriwijayati</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 1</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Komputer</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Robiatul Fadhila</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 2</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Astronomi</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Aditya Tri Ananda</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 3</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Astronomi</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Mutiara Balqis S</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 2</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Kebumian</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Ratih Bahari</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 1</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Ekonomi</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Firman Assiddiqi</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 1</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Geografi</td></tr><tr style="margin: 0px; padding: 0px; font-weight: inherit; font-style: inherit; font-family: inherit;"><td style="margin: 0px; font-style: inherit; font-family: inherit;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">M. Rizky Firdaus</span></td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Juara 2</td><td style="margin: 0px; font-style: inherit; font-family: inherit;">Geografi</td></tr></tbody></table><p style="margin-bottom: 6pt; padding: 0px; border: 0px; font-size: 10pt; font-family: &quot;Trebuchet MS&quot;, Tahoma, &quot;Lucida Grande&quot;, Verdana, Arial, Helvetica, sans-serif; vertical-align: baseline; text-align: justify; text-indent: 36pt; line-height: 26.6667px;"></p><p style="margin-bottom: 6pt; padding: 0px; border: 0px; font-size: 10pt; font-family: &quot;Trebuchet MS&quot;, Tahoma, &quot;Lucida Grande&quot;, Verdana, Arial, Helvetica, sans-serif; vertical-align: baseline; text-align: justify; text-indent: 36pt; line-height: 26.6667px;"><span times="" new="" style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-size: 12pt; font-family: inherit; vertical-align: baseline; line-height: 18.4px;">Selamat kepada para pemenang !. Kami<span style="margin: 0px; padding: 0px; border: 0px; font-weight: inherit; font-style: inherit; font-family: inherit; vertical-align: baseline;">&nbsp;&nbsp;</span>berharap semoga Smansaba selalu meraih prestasi yang gemilang di tahun-tahun yang akan datang. Amin.</span></p>', 1, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(25, 2, '[{"author":"1","date":"2016-12-04 03:26:41"},{"author":"1","date":"2016-12-04 03:26:42"},{"author":"1","date":"2016-12-04 03:26:43"},{"author":"1","date":"2016-12-04 03:26:44"},{"author":"1","date":"2016-12-04 03:26:45"},{"author":"1","date":"2016-12-04 03:26:46"},{"author":"1","date":"2016-12-04 03:26:47"},{"author":"1","date":"2016-12-04 03:26:48"},{"author":"1","date":"2016-12-04 03:26:49"},{"author":"1","date":"2016-12-04 03:26:50"},{"author":"1","date":"2016-12-04 03:26:51"},{"author":"1","date":"2016-12-04 03:26:52"},{"author":"1","date":"2016-12-04 03:26:53"},{"author":"1","date":"2016-12-04 03:26:54"},{"author":"1","date":"2016-12-04 03:26:55"},{"author":"1","date":"2016-12-04 03:26:56"},{"author":"1","date":"2016-12-04 03:26:57"},{"author":"1","date":"2016-12-04 03:26:58"},{"author":"1","date":"2016-12-04 03:26:59"},{"author":"1","date":"2016-12-04 03:27:00"},{"author":"1","date":"2016-12-04 03:27:01"},{"author":"1","date":"2016-12-04 03:27:02"},{"author":"1","date":"2016-12-04 03:27:03"},{"author":"1","date":"2016-12-04 03:27:04"},{"author":"1","date":"2016-12-04 03:27:05"},{"author":"1","date":"2016-12-04 03:27:06"},{"author":"1","date":"2016-12-04 03:27:07"},{"author":"1","date":"2016-12-04 03:27:08"},{"author":"1","date":"2016-12-04 03:27:09"},{"author":"1","date":"2016-12-04 03:27:10"},{"author":"1","date":"2016-12-07 15:49:50"}]', '2016-12-07 15:49:50', 'Coba ah', 'coba-ah', '<p>lala</p>', 1, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(26, 1, '[{"author":"1","date":"2016-12-06 19:24:23"}]', '2016-12-06 19:24:23', 'coba lagi', 'coba-lagi', '<p>asdaddada</p>', 1, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(27, 1, '[{"author":"1","date":"2016-12-06 19:25:58"}]', '2016-12-06 19:25:58', 'coba lagi lagi', 'coba-lagi-lagi', '<p>adadadad</p>', 1, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(28, 1, '[{"author":"1","date":"2016-12-06 19:28:03"}]', '2016-12-06 19:28:03', 'adasdads', 'adasdads', '<p>adsadadada</p>', 1, 'd41d8cd98f00b204e9800998ecf8427e', 1, '', 0),
(29, 2, '[{"author":"1","date":"2016-12-04 03:26:41"},{"author":"1","date":"2016-12-04 03:26:42"},{"author":"1","date":"2016-12-04 03:26:43"},{"author":"1","date":"2016-12-04 03:26:44"},{"author":"1","date":"2016-12-04 03:26:45"},{"author":"1","date":"2016-12-04 03:26:46"},{"author":"1","date":"2016-12-04 03:26:47"},{"author":"1","date":"2016-12-04 03:26:48"},{"author":"1","date":"2016-12-04 03:26:49"},{"author":"1","date":"2016-12-04 03:26:50"},{"author":"1","date":"2016-12-04 03:26:51"},{"author":"1","date":"2016-12-04 03:26:52"},{"author":"1","date":"2016-12-04 03:26:53"},{"author":"1","date":"2016-12-04 03:26:54"},{"author":"1","date":"2016-12-04 03:26:55"},{"author":"1","date":"2016-12-04 03:26:56"},{"author":"1","date":"2016-12-04 03:26:57"},{"author":"1","date":"2016-12-04 03:26:58"},{"author":"1","date":"2016-12-04 03:26:59"},{"author":"1","date":"2016-12-04 03:27:00"},{"author":"1","date":"2016-12-04 03:27:01"},{"author":"1","date":"2016-12-04 03:27:02"},{"author":"1","date":"2016-12-04 03:27:03"},{"author":"1","date":"2016-12-04 03:27:04"},{"author":"1","date":"2016-12-04 03:27:05"},{"author":"1","date":"2016-12-04 03:27:06"},{"author":"1","date":"2016-12-04 03:27:07"},{"author":"1","date":"2016-12-04 03:27:08"},{"author":"1","date":"2016-12-04 03:27:09"},{"author":"1","date":"2016-12-04 03:27:10"},{"author":"1","date":"2016-12-07 16:39:20"}]', '2016-12-07 16:39:20', 'lkkjiuhkl', 'lkkjiuhkl', '<p>adadadad</p>', 2, '2e3817293fc275dbee74bd71ce6eb056', 1, '', 2);

-- --------------------------------------------------------

--
-- Table structure for table `post_category`
--

CREATE TABLE `post_category` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `name_url` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `post_category`
--

INSERT INTO `post_category` (`id`, `group_id`, `name`, `name_url`, `deleted`) VALUES
(1, 1, 'Tidak Berkategori', 'tidak_berkategori', 0),
(2, 1, 'Berita', 'berita', 0);

-- --------------------------------------------------------

--
-- Table structure for table `post_tags`
--

CREATE TABLE `post_tags` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `post_tags`
--

INSERT INTO `post_tags` (`id`, `post_id`, `tag_id`) VALUES
(20, 26, 5),
(21, 27, 5),
(22, 28, 5),
(28, 25, 3),
(29, 25, 4),
(33, 29, 5),
(34, 24, 6);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `name_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`, `name_url`) VALUES
(3, 'alamat', 'alamat'),
(4, 'dan', 'dan'),
(5, '', ''),
(6, 'lala', 'lala');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` text NOT NULL,
  `field` text,
  `number` text,
  `quote` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL DEFAULT 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
  `password` varchar(40) NOT NULL DEFAULT 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
  `email` varchar(100) NOT NULL DEFAULT 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
  `nickname` varchar(100) DEFAULT 'da39a3ee5e6b4b0d3255bfef95601890afd80709',
  `image` text NOT NULL,
  `registered` datetime NOT NULL,
  `status` text NOT NULL,
  `privilege` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=FIXED;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `nickname`, `image`, `registered`, `status`, `privilege`, `deleted`) VALUES
(1, 'admin', 'd033e22ae348aeb5660fc2140aec35850c4da997', 'drak_nes@yahoo.com', 'Saya Admin', 'no-photo.png', '2016-11-13 00:00:00', '{\n  "status": "active",\n  "token": "",\n  "login": "2016-11-13 00:00:00"\n}', '{"post_management":true,"register_management":true,"user_management":true,"web_management":true}', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--

CREATE TABLE `user_group` (
  `id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_group`
--

INSERT INTO `user_group` (`id`, `group_id`, `user_id`) VALUES
(1, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `albums`
--
ALTER TABLE `albums`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_gallery_Groups1_idx` (`group_id`),
  ADD KEY `fk_gallery_albums1_idx` (`album_id`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_menus_Groups1_idx` (`group_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pages_Groups1_idx` (`group_id`);

--
-- Indexes for table `post`
--
ALTER TABLE `post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_post_post_category1_idx` (`category_id`);

--
-- Indexes for table `post_category`
--
ALTER TABLE `post_category`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_post_category_Groups1_idx` (`group_id`);

--
-- Indexes for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_Post_tags_post1_idx` (`post_id`),
  ADD KEY `fk_Post_tags_tags1_idx` (`tag_id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_group`
--
ALTER TABLE `user_group`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_group_Users_idx` (`user_id`),
  ADD KEY `fk_user_group_Groups1_idx` (`group_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `albums`
--
ALTER TABLE `albums`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `post`
--
ALTER TABLE `post`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;
--
-- AUTO_INCREMENT for table `post_category`
--
ALTER TABLE `post_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `post_tags`
--
ALTER TABLE `post_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;
--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_group`
--
ALTER TABLE `user_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `gallery`
--
ALTER TABLE `gallery`
  ADD CONSTRAINT `fk_gallery_Groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_gallery_albums1` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `fk_menus_Groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `fk_pages_Groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `fk_post_post_category1` FOREIGN KEY (`category_id`) REFERENCES `post_category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `post_category`
--
ALTER TABLE `post_category`
  ADD CONSTRAINT `fk_post_category_Groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `post_tags`
--
ALTER TABLE `post_tags`
  ADD CONSTRAINT `fk_Post_tags_post1` FOREIGN KEY (`post_id`) REFERENCES `post` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_Post_tags_tags1` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `user_group`
--
ALTER TABLE `user_group`
  ADD CONSTRAINT `fk_user_group_Groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_user_group_Users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
