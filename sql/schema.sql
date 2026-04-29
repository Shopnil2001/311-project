CREATE DATABASE IF NOT EXISTS digital_constituency CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE digital_constituency;

CREATE TABLE IF NOT EXISTS constituencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS mps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    email VARCHAR(128) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    constituency_id INT DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (constituency_id) REFERENCES constituencies(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS sectors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    constituency_id INT NOT NULL,
    mp_id INT DEFAULT NULL,
    FOREIGN KEY (constituency_id) REFERENCES constituencies(id) ON DELETE CASCADE,
    FOREIGN KEY (mp_id) REFERENCES mps(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS citizens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    email VARCHAR(128) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    sector_id INT DEFAULT NULL, -- Changed from NOT NULL
    registered_at DATETIME NOT NULL,
    FOREIGN KEY (sector_id) REFERENCES sectors(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaint_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS complaint_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL
);

CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    category_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status_id INT NOT NULL DEFAULT 1,
    response TEXT DEFAULT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (citizen_id) REFERENCES citizens(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES complaint_categories(id),
    FOREIGN KEY (status_id) REFERENCES complaint_status(id)
);

CREATE TABLE IF NOT EXISTS crime_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL
);

CREATE TABLE IF NOT EXISTS crime_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    category_id INT NOT NULL,
    description TEXT NOT NULL,
    is_anonymous TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (citizen_id) REFERENCES citizens(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES crime_categories(id)
);

CREATE TABLE IF NOT EXISTS suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    votes INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (citizen_id) REFERENCES citizens(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mp_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (mp_id) REFERENCES mps(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS mp_projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mp_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status VARCHAR(64) NOT NULL DEFAULT 'Planned',
    created_at DATETIME NOT NULL,
    FOREIGN KEY (mp_id) REFERENCES mps(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS project_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    update_text TEXT NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (project_id) REFERENCES mp_projects(id) ON DELETE CASCADE
);

INSERT IGNORE INTO constituencies (id, name) VALUES
(1, 'North Constituency'),
(2, 'East Constituency');

INSERT IGNORE INTO mps (id, name, email, password, constituency_id, phone) VALUES
(1, 'Hon. Jane Smith', 'mp@example.com', '$2y$10$1ukjT5Zr0Q5qPRMpRWYpyOiQAqY1oFk48TY7VAxOElnjK6Lp3yPnG', 1, '070-123-4567');

INSERT IGNORE INTO sectors (id, name, constituency_id, mp_id) VALUES
(1, 'Sector A', 1, 1),
(2, 'Sector B', 1, 1),
(3, 'Sector C', 2, NULL);

INSERT IGNORE INTO complaint_categories (id, name) VALUES
(1, 'Public Services'),
(2, 'Infrastructure'),
(3, 'Safety'),
(4, 'Education');

INSERT IGNORE INTO complaint_status (id, name) VALUES
(1, 'New'),
(2, 'In progress'),
(3, 'Resolved');

INSERT IGNORE INTO crime_categories (id, name) VALUES
(1, 'Theft'),
(2, 'Assault'),
(3, 'Vandalism');

INSERT IGNORE INTO announcements (mp_id, title, message, created_at) VALUES
(1, 'Road repair update', 'We have approved road repairs across Sector A this month.', NOW());

INSERT IGNORE INTO mp_projects (mp_id, title, description, status, created_at) VALUES
(1, 'Community water pipeline', 'Installing pipeline connections to improve water access.', 'In progress', NOW());
