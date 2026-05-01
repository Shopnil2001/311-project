USE digital_constituency;

-- Clear existing data to avoid duplicates if any
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM sectors;
DELETE FROM constituencies;
SET FOREIGN_KEY_CHECKS = 1;

-- Insert Constituencies
INSERT INTO constituencies (id, name) VALUES
(1, 'Dhaka-10'),
(2, 'Dhaka-8'),
(3, 'Chattogram-9'),
(4, 'Sylhet-1'),
(5, 'Rajshahi-2');

-- Insert Sectors
INSERT INTO sectors (name, constituency_id) VALUES
('Dhanmondi', 1),
('New Market', 1),
('Hazaribagh', 1),
('Ramna', 2),
('Shahbagh', 2),
('Paltan', 2),
('Kotwali (CTG)', 3),
('Bakalia', 3),
('Chawkbazar (CTG)', 3),
('Zindabazar', 4),
('Amberkhana', 4),
('Motihar', 5),
('Boalia', 5);
