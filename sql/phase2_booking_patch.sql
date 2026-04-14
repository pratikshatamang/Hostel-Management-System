-- Phase 2 booking logic patch
-- Run this after backing up your database.
-- If you already have duplicate rows in registration.emailid or rooms.room_no,
-- clean them first or the UNIQUE KEY statements will fail.

START TRANSACTION;

-- Helpful checks before applying unique constraints.
-- Duplicate room numbers:
-- SELECT room_no, COUNT(*) AS total_rows FROM rooms GROUP BY room_no HAVING COUNT(*) > 1;

-- Duplicate active bookings per user:
-- SELECT emailid, COUNT(*) AS total_rows FROM registration GROUP BY emailid HAVING COUNT(*) > 1;

ALTER TABLE rooms
  ADD UNIQUE KEY uq_rooms_room_no (room_no);

ALTER TABLE registration
  ADD INDEX idx_registration_roomno (roomno),
  ADD UNIQUE KEY uq_registration_emailid (emailid);

COMMIT;
