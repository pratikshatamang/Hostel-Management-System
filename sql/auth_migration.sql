CREATE TABLE IF NOT EXISTS `users` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @db_name := DATABASE();

SET @has_regDate := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'userregistration' AND COLUMN_NAME = 'regDate'
);
SET @sql := IF(@has_regDate = 0,
  'ALTER TABLE `userregistration` ADD COLUMN `regDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_updationDate := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'userregistration' AND COLUMN_NAME = 'updationDate'
);
SET @sql := IF(@has_updationDate = 0,
  'ALTER TABLE `userregistration` ADD COLUMN `updationDate` varchar(255) NOT NULL DEFAULT ''''',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @has_passUdateDate := (
  SELECT COUNT(*)
  FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db_name AND TABLE_NAME = 'userregistration' AND COLUMN_NAME = 'passUdateDate'
);
SET @sql := IF(@has_passUdateDate = 0,
  'ALTER TABLE `userregistration` ADD COLUMN `passUdateDate` varchar(255) NOT NULL DEFAULT ''''',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO `users` (`full_name`, `email`, `username`, `phone`, `password`, `role`, `legacy_id`, `created_at`, `updated_at`)
SELECT
  'Administrator',
  a.email,
  a.username,
  NULL,
  a.password,
  'admin',
  a.id,
  a.reg_date,
  COALESCE(NULLIF(a.updation_date, '0000-00-00'), a.reg_date)
FROM `admin` a
LEFT JOIN `users` u ON u.role = 'admin' AND u.legacy_id = a.id
WHERE u.id IS NULL;

INSERT INTO `users` (`full_name`, `email`, `username`, `phone`, `password`, `role`, `legacy_id`, `created_at`, `updated_at`)
SELECT
  TRIM(CONCAT(ur.firstName, ' ', ur.middleName, ' ', ur.lastName)),
  ur.email,
  CONCAT('user', ur.id),
  ur.contactNo,
  ur.password,
  'user',
  ur.id,
  COALESCE(ur.regDate, NOW()),
  NOW()
FROM `userregistration` ur
LEFT JOIN `users` u ON u.role = 'user' AND u.legacy_id = ur.id
WHERE u.id IS NULL;
