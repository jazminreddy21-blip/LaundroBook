-- laundrobook_seed_data.sql

-- One manager
INSERT INTO system_manager (manager_username, password_hash) VALUES
('admin', '$2y$10$placeholderhashfortestingonly');

-- Machines
INSERT INTO machine (manager_id, machine_name, machine_status) VALUES
(1, 'Machine 1', 'available'),
(1, 'Machine 2', 'available'),
(1, 'Machine 3', 'available');

-- Slots
INSERT INTO slot (manager_id, slot_label, start_time, end_time, is_active) VALUES
(1, '08:00 - 08:45', '08:00:00', '08:45:00', 1),
(1, '08:45 - 09:30', '08:45:00', '09:30:00', 1),
(1, '09:30 - 10:15', '09:30:00', '10:15:00', 1);

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