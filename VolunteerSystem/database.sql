-- Volunteer registration schema; safe to import repeatedly.
CREATE DATABASE IF NOT EXISTS volunteer_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE volunteer_db;
CREATE TABLE IF NOT EXISTS volunteers (
 volunteer_id INT AUTO_INCREMENT PRIMARY KEY,
 volunteer_name VARCHAR(100) NOT NULL,
 ic_number VARCHAR(20) NOT NULL,
 contact_number VARCHAR(20) NOT NULL,
 email VARCHAR(100) NOT NULL,
 service_category VARCHAR(100) NOT NULL,
 available_date DATE NOT NULL
) ENGINE=InnoDB;
