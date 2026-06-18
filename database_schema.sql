-- Database schema for AMI CodeIgniter 3

CREATE DATABASE IF NOT EXISTS `ami` CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `ami`;

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin', 'auditor', 'auditee') NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`nama`, `email`, `password`, `role`) VALUES
('Admin Super', 'admin@ami.test', '$2y$10$6kPpWBhWJ.gZoZHQQCHvQOnIqrxVnbXp33jjgXogQaHUBkHg6g2Pi', 'super_admin'),
('Auditor Demo', 'auditor@ami.test', '$2y$10$9pN0XAS.o0wk1tYv34nklu3Gej..LJCrBT92HD28ndnjkaM0GF4ni', 'auditor'),
('Auditee Demo', 'auditee@ami.test', '$2y$10$gH/cx/G53aHvut33k.hi.ORdA1pm82WM2MjQqCUZ/cSgQd35Z/MRi', 'auditee');
