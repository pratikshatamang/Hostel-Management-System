-- User Interest columns patch for userregistration table
-- Run this on the same database used by includes/config.php:
-- host=localhost, port=3307, database=hostel

USE hostel;

ALTER TABLE userregistration
  ADD COLUMN IF NOT EXISTS pref_seater INT DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS pref_attached_bathroom TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS pref_air_conditioner TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS pref_wifi TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS pref_balcony TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS pref_study_table TINYINT(1) NOT NULL DEFAULT 0;
