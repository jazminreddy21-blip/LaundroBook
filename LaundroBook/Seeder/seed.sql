-- laundrobook_seed_data.sql

-- Login credentials:
--   Username: admin
--   Password: Test@123
INSERT INTO system_manager (manager_username, password_hash) VALUES
('admin', '$2y$10$qmpdS.q9RvrDds9CQ/PjwO6N0JB7jnd0P9B2qGAUTRGIL5TpL21ie');

-- Machines
INSERT INTO machine (manager_id, machine_name, machine_status) VALUES
(1, 'Machine 1', 'available'),
(1, 'Machine 2', 'available'),
(1, 'Machine 3', 'available');

-- Slots
-- start_time/end_time are TIMESTAMP columns, so they need a full date-time,
-- not a bare time like '08:00:00' (a strict server rejects it, and XAMPP
-- stores it as 0000-00-00 and loses the time of day). Only the time of day
-- is used, so the date is arbitrary, but it must be the same for every slot.
INSERT INTO slot (manager_id, slot_label, start_time, end_time, is_active) VALUES
(1, '08:00 - 08:45', '2026-01-01 08:00:00', '2026-01-01 08:45:00', 1),
(1, '08:45 - 09:30', '2026-01-01 08:45:00', '2026-01-01 09:30:00', 1),
(1, '09:30 - 10:15', '2026-01-01 09:30:00', '2026-01-01 10:15:00', 1),
(1, '10:15 - 11:00', '2026-01-01 10:15:00', '2026-01-01 11:00:00', 1),
(1, '11:00 - 11:45', '2026-01-01 11:00:00', '2026-01-01 11:45:00', 1),
(1, '11:45 - 12:30', '2026-01-01 11:45:00', '2026-01-01 12:30:00', 1);

-- Services
INSERT INTO service (manager_id, wash_type, load_type, price, duration_minutes, duration_slots) VALUES
(1, 'quick',  'clothes',  30.00, 25, 1),
(1, 'quick',  'beddings', 40.00, 25, 1),
(1, 'quick',  'towels',   35.00, 25, 1),
(1, 'normal', 'clothes',  40.00, 35, 1),
(1, 'normal', 'beddings', 50.00, 35, 1),
(1, 'normal', 'towels',   45.00, 35, 1),
(1, 'heavy',  'clothes',  55.00, 65, 2),
(1, 'heavy',  'beddings', 70.00, 65, 2),
(1, 'heavy',  'towels',   65.00, 65, 2);

-- Seeds a few groundworker rows. Delivery/pickup bookings will fail
-- with "no groundworker available" until at least one row exists here.

INSERT INTO groundworker (groundworker_name, groundworker_phone, groundworker_role) VALUES
('John Smith', '0821234567', 'driver'),
('Sarah Johnson', '0839876543', 'driver'),
('Michael Brown', '0721112233', 'collector');