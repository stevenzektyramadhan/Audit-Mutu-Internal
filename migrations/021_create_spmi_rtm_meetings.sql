CREATE TABLE IF NOT EXISTS `spmi_rtm_meetings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_code` VARCHAR(128) NOT NULL,
    `meeting_title` VARCHAR(200) NOT NULL,
    `meeting_date` DATE NOT NULL,
    `location` VARCHAR(200) NOT NULL,
    `status` ENUM('draft','resolved') NOT NULL DEFAULT 'draft',
    `created_by` INT NOT NULL,
    `resolved_by` INT NULL,
    `resolved_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_meetings_code` (`meeting_code`),
    CONSTRAINT `fk_spmi_rtm_meetings_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_meetings_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_meeting_reports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `report_id` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_meeting_reports_report` (`meeting_id`, `report_id`),
    CONSTRAINT `fk_spmi_rtm_meeting_reports_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_meeting_reports_report` FOREIGN KEY (`report_id`) REFERENCES `spmi_reports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_participants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `name_snapshot` VARCHAR(100) NOT NULL,
    `email_snapshot` VARCHAR(100) NOT NULL,
    `role_snapshot` VARCHAR(32) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_participants_user` (`meeting_id`, `user_id`),
    CONSTRAINT `fk_spmi_rtm_participants_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_participants_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spmi_rtm_decisions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `display_order` INT UNSIGNED NOT NULL,
    `decision_text` TEXT NOT NULL,
    `action_text` TEXT NOT NULL,
    `report_id` INT NULL,
    `report_item_id` INT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_decisions_order` (`meeting_id`, `display_order`),
    CONSTRAINT `fk_spmi_rtm_decisions_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_decisions_report` FOREIGN KEY (`report_id`) REFERENCES `spmi_reports` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_decisions_report_item` FOREIGN KEY (`report_item_id`) REFERENCES `spmi_report_items` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
