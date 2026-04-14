-- Hostel Management System complete database rebuild
-- Generated from the current project schema on 2026-04-14.
-- Default admin login after import:
--   username: admin
--   email: admin@hostel.local
--   password: Admin@123

CREATE DATABASE IF NOT EXISTS `hostel`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `hostel`;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `email_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `userlog`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `registration`;
DROP TABLE IF EXISTS `userregistration`;
DROP TABLE IF EXISTS `adminlog`;
DROP TABLE IF EXISTS `rooms`;
DROP TABLE IF EXISTS `courses`;
DROP TABLE IF EXISTS `states`;
DROP TABLE IF EXISTS `admin`;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(300) NOT NULL,
  `reg_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updation_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_admin_username` (`username`),
  UNIQUE KEY `uniq_admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `adminlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `adminid` int(11) NOT NULL,
  `ip` varbinary(16) NOT NULL,
  `logintime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_adminlog_adminid` (`adminid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `courses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_code` varchar(255) NOT NULL,
  `course_sn` varchar(255) NOT NULL,
  `course_fn` varchar(255) NOT NULL,
  `posting_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_courses_code` (`course_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seater` int(11) NOT NULL,
  `room_no` int(11) NOT NULL,
  `fees` int(11) NOT NULL,
  `posting_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attached_bathroom` tinyint(1) NOT NULL DEFAULT 0,
  `air_conditioner` tinyint(1) NOT NULL DEFAULT 0,
  `wifi` tinyint(1) NOT NULL DEFAULT 0,
  `balcony` tinyint(1) NOT NULL DEFAULT 0,
  `study_table` tinyint(1) NOT NULL DEFAULT 0,
  `description` text NOT NULL,
  `room_status` enum('available','full','maintenance') NOT NULL DEFAULT 'available',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rooms_room_no` (`room_no`),
  KEY `idx_rooms_room_status` (`room_status`),
  KEY `idx_rooms_seater` (`seater`),
  KEY `idx_rooms_fees` (`fees`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `State` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `userregistration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `regNo` varchar(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `middleName` varchar(255) NOT NULL DEFAULT '',
  `lastName` varchar(255) NOT NULL,
  `gender` varchar(255) NOT NULL,
  `contactNo` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `regDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updationDate` varchar(255) NOT NULL DEFAULT '',
  `passUdateDate` varchar(255) NOT NULL DEFAULT '',
  `pref_seater` int(11) DEFAULT NULL,
  `pref_attached_bathroom` tinyint(1) NOT NULL DEFAULT 0,
  `pref_air_conditioner` tinyint(1) NOT NULL DEFAULT 0,
  `pref_wifi` tinyint(1) NOT NULL DEFAULT 0,
  `pref_balcony` tinyint(1) NOT NULL DEFAULT 0,
  `pref_study_table` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_userregistration_regno` (`regNo`),
  UNIQUE KEY `uniq_userregistration_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `legacy_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_users_email` (`email`),
  UNIQUE KEY `uniq_users_username` (`username`),
  UNIQUE KEY `uniq_users_role_legacy` (`role`,`legacy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `userlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `userEmail` varchar(255) NOT NULL,
  `userIp` varbinary(16) NOT NULL,
  `city` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `loginTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_userlog_userid` (`userId`),
  KEY `idx_userlog_email` (`userEmail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roomno` int(11) NOT NULL,
  `seater` int(11) NOT NULL,
  `feespm` int(11) NOT NULL,
  `foodstatus` int(11) NOT NULL,
  `stayfrom` date NOT NULL,
  `duration` int(11) NOT NULL,
  `course` varchar(500) NOT NULL,
  `regno` int(11) NOT NULL,
  `firstName` varchar(500) NOT NULL,
  `middleName` varchar(500) NOT NULL DEFAULT '',
  `lastName` varchar(500) NOT NULL,
  `gender` varchar(250) NOT NULL,
  `contactno` bigint(20) NOT NULL,
  `emailid` varchar(500) NOT NULL,
  `egycontactno` bigint(20) NOT NULL,
  `guardianName` varchar(500) NOT NULL,
  `guardianRelation` varchar(500) NOT NULL,
  `guardianContactno` bigint(20) NOT NULL,
  `corresAddress` varchar(500) NOT NULL,
  `corresCIty` varchar(500) NOT NULL,
  `corresState` varchar(500) NOT NULL,
  `corresPincode` int(11) NOT NULL,
  `pmntAddress` varchar(500) NOT NULL,
  `pmntCity` varchar(500) NOT NULL,
  `pmnatetState` varchar(500) NOT NULL,
  `pmntPincode` int(11) NOT NULL,
  `postingDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updationDate` varchar(500) NOT NULL DEFAULT '',
  `payment_method` varchar(50) NOT NULL DEFAULT 'cash',
  `payment_status` varchar(50) NOT NULL DEFAULT 'pending',
  `transaction_id` varchar(255) DEFAULT NULL,
  `renewal_count` int(11) NOT NULL DEFAULT 0,
  `checkout_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_registration_emailid` (`emailid`),
  KEY `idx_registration_roomno` (`roomno`),
  KEY `idx_registration_payment_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receiver_email` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_receiver_email` (`receiver_email`),
  KEY `idx_notifications_is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `email_to` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'general',
  `status` enum('sent','failed') NOT NULL DEFAULT 'sent',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_email_logs_user_id` (`user_id`),
  KEY `idx_email_logs_status` (`status`),
  KEY `idx_email_logs_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin` (`id`, `username`, `email`, `password`, `reg_date`, `updation_date`) VALUES
(1, 'admin', 'admin@hostel.local', '$2y$10$847ajbIOoS3UHKnST8r1C.SC0islERqrcTHjEQ27JmsMcEXRCuPRq', CURRENT_TIMESTAMP, CURRENT_DATE);

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `phone`, `password`, `role`, `legacy_id`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin@hostel.local', 'admin', NULL, '$2y$10$847ajbIOoS3UHKnST8r1C.SC0islERqrcTHjEQ27JmsMcEXRCuPRq', 'admin', 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);

INSERT INTO `courses` (`id`, `course_code`, `course_sn`, `course_fn`) VALUES
(1, 'B10992', 'B.Tech', 'Bachelor of Technology'),
(2, 'BCOM1453', 'B.Com', 'Bachelor of Commerce'),
(3, 'BSC12', 'BSC', 'Bachelor of Science'),
(4, 'BC36356', 'BCA', 'Bachelor of Computer Application'),
(5, 'MCA565', 'MCA', 'Master of Computer Application'),
(6, 'MBA75', 'MBA', 'Master of Business Administration'),
(7, 'BE765', 'BE', 'Bachelor of Engineering');

INSERT INTO `rooms` (`id`, `seater`, `room_no`, `fees`, `attached_bathroom`, `air_conditioner`, `wifi`, `balcony`, `study_table`, `description`, `room_status`) VALUES
(1, 5, 100, 8000, 1, 0, 1, 0, 1, 'Shared room with Wi-Fi and study table.', 'available'),
(2, 2, 201, 6000, 1, 0, 1, 1, 1, 'Two-seater room with balcony.', 'available'),
(3, 2, 200, 6000, 1, 1, 1, 0, 1, 'Two-seater AC room.', 'available'),
(4, 3, 112, 4000, 0, 0, 1, 0, 1, 'Budget-friendly room with Wi-Fi.', 'available'),
(5, 5, 132, 2000, 0, 0, 0, 0, 0, 'Basic economy room.', 'available');

INSERT INTO `states` (`id`, `State`) VALUES
(1, 'Andaman and Nicobar Island (UT)'),
(2, 'Andhra Pradesh'),
(3, 'Arunachal Pradesh'),
(4, 'Assam'),
(5, 'Bihar'),
(6, 'Chandigarh (UT)'),
(7, 'Chhattisgarh'),
(8, 'Dadra and Nagar Haveli (UT)'),
(9, 'Daman and Diu (UT)'),
(10, 'Delhi (NCT)'),
(11, 'Goa'),
(12, 'Gujarat'),
(13, 'Haryana'),
(14, 'Himachal Pradesh'),
(15, 'Jammu and Kashmir'),
(16, 'Jharkhand'),
(17, 'Karnataka'),
(18, 'Kerala'),
(19, 'Lakshadweep (UT)'),
(20, 'Madhya Pradesh'),
(21, 'Maharashtra'),
(22, 'Manipur'),
(23, 'Meghalaya'),
(24, 'Mizoram'),
(25, 'Nagaland'),
(26, 'Odisha'),
(27, 'Puducherry (UT)'),
(28, 'Punjab'),
(29, 'Rajasthan'),
(30, 'Sikkim'),
(31, 'Tamil Nadu'),
(32, 'Telangana'),
(33, 'Tripura'),
(34, 'Uttarakhand'),
(35, 'Uttar Pradesh'),
(36, 'West Bengal');

ALTER TABLE `admin` AUTO_INCREMENT = 2;
ALTER TABLE `courses` AUTO_INCREMENT = 8;
ALTER TABLE `rooms` AUTO_INCREMENT = 6;
ALTER TABLE `states` AUTO_INCREMENT = 37;
ALTER TABLE `users` AUTO_INCREMENT = 2;

