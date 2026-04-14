-- Room feature and suggestion patch
-- Run this on the same database used by includes/config.php:
-- host=localhost, port=3307, database=hostel

USE hostel;

ALTER TABLE rooms
  ADD COLUMN IF NOT EXISTS attached_bathroom TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS air_conditioner TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS wifi TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS balcony TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS study_table TINYINT(1) NOT NULL DEFAULT 0,
  ADD COLUMN IF NOT EXISTS description VARCHAR(500) NOT NULL DEFAULT '',
  ADD COLUMN IF NOT EXISTS room_status VARCHAR(50) NOT NULL DEFAULT 'available';

CREATE INDEX IF NOT EXISTS idx_rooms_room_status ON rooms (room_status);
CREATE INDEX IF NOT EXISTS idx_rooms_seater ON rooms (seater);
CREATE INDEX IF NOT EXISTS idx_rooms_fees ON rooms (fees);
