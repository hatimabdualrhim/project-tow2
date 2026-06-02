CREATE DATABASE IF NOT EXISTS `student_management`;
USE `student_management`;

CREATE TABLE IF NOT EXISTS `students` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `student_number` VARCHAR(100) NOT NULL UNIQUE,
    `year_of_study` INT NOT NULL,
    `batch_name` VARCHAR(100) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;