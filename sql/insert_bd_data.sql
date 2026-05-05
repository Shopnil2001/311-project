USE digital_constituency;

-- Clear existing data to avoid duplicates if any
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM sectors;
DELETE FROM mps;
DELETE FROM admins;
DELETE FROM constituencies;
SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO complaint_categories (name) VALUES
('Roads and Infrastructure'),
('Water Supply and Sanitation'),
('Electricity and Power'),
('Education and Schools'),
('Healthcare and Hospitals'),
('Public Safety and Security'),
('Environmental Issues'),
('Public Transportation'),
('Corruption and Governance'),
('Other'); 

insert INTO complaint_status (name) VALUES
('Pending'),
('In Progress'),
('Resolved'),
('Rejected');
 insert into crime_categories (name) values
 ('Theft'),
 ('Assault'),
 ('Vandalism'),
 ('Fraud'),
 ('Cybercrime'),
 ('Domestic Violence'),
 ('Drug Offenses'),
 ('Traffic Violations'),
 ('Homicide'),
 ('Other');
-- Insert Constituencies
INSERT INTO constituencies (id, name) VALUES
(1, 'Dhaka-10'),
(2, 'Dhaka-8'),
(3, 'Chattogram-9'),
(4, 'Sylhet-1'),
(5, 'Rajshahi-2');

-- Insert demo admin and MP user accounts
INSERT INTO admins (name, email, password) VALUES
('Demo Admin', 'admin@example.com', '$2y$10$KQbUJK3wQ7eYKhnPczwGWeGP5CI.kvnkFsj3BSBUuwKQhNm2FkUTi');

INSERT INTO mps (name, email, password, constituency_id, phone, is_approved) VALUES
('Demo MP', 'mp@example.com', '$2y$10$NYsKPNN7udMC7PBaSd8z4utPuRbV3zMuIraoEK1Noyx/dkXcgmLKS', 1, '+8801712345678', 1);

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

-- Assign the demo MP to their constituency sectors
UPDATE sectors SET mp_id = 1 WHERE constituency_id = 1;
