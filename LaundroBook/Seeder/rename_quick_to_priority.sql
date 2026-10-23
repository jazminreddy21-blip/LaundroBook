-- Renames the "quick" wash type to "priority" and sets the new prices.
-- Run once on an existing database (phpMyAdmin > laundrobook > SQL).
-- Existing bookings keep working: the service rows are updated in
-- place, so their service_id (and every booking's stored total) stays.
-- Safe to run again.

USE laundrobook;

-- The CHECK constraint must go first, it does not allow 'priority' yet.
-- The name differs between our schema versions, so drop both.
ALTER TABLE service DROP CONSTRAINT IF EXISTS check_wash_type;
ALTER TABLE service DROP CONSTRAINT IF EXISTS chk_wash_type;

UPDATE service SET wash_type = 'priority', price = 50.00 WHERE wash_type IN ('quick','priority') AND load_type = 'clothes';
UPDATE service SET wash_type = 'priority', price = 65.00 WHERE wash_type IN ('quick','priority') AND load_type = 'beddings';
UPDATE service SET wash_type = 'priority', price = 55.00 WHERE wash_type IN ('quick','priority') AND load_type = 'towels';

ALTER TABLE service
  ADD CONSTRAINT check_wash_type CHECK (wash_type IN ('priority', 'normal', 'heavy'));