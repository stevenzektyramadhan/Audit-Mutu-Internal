CREATE TABLE IF NOT EXISTS `spmi_rtm_follow_ups` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `follow_up_code` VARCHAR(128) NOT NULL,
    `decision_id` INT NOT NULL,
    `decision_text_snapshot` TEXT NOT NULL,
    `action_text_snapshot` TEXT NOT NULL,
    `responsible_user_id` INT NOT NULL,
    `responsible_name_snapshot` VARCHAR(100) NOT NULL,
    `responsible_email_snapshot` VARCHAR(100) NOT NULL,
    `responsible_role_snapshot` VARCHAR(32) NOT NULL,
    `due_date` DATE NULL,
    `follow_up_note` TEXT NULL,
    `status` ENUM('open','in_progress','completed') NOT NULL DEFAULT 'open',
    `started_by` INT NULL,
    `started_at` DATETIME NULL,
    `completion_note` TEXT NULL,
    `completed_by` INT NULL,
    `completed_at` DATETIME NULL,
    `created_by` INT NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_spmi_rtm_follow_ups_code` (`follow_up_code`),
    UNIQUE KEY `uq_spmi_rtm_follow_ups_decision` (`decision_id`),
    CONSTRAINT `fk_spmi_rtm_follow_ups_decision` FOREIGN KEY (`decision_id`) REFERENCES `spmi_rtm_decisions` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_responsible` FOREIGN KEY (`responsible_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_started_by` FOREIGN KEY (`started_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_follow_ups_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_rtm_follow_ups' AND COLUMN_NAME = 'follow_up_code');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE `spmi_rtm_follow_ups` ADD COLUMN `follow_up_code` VARCHAR(128) NULL AFTER `id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
UPDATE `spmi_rtm_follow_ups` SET `follow_up_code` = CONCAT('RTM-FU-', `decision_id`) WHERE `follow_up_code` IS NULL;
ALTER TABLE `spmi_rtm_follow_ups` MODIFY COLUMN `follow_up_code` VARCHAR(128) NOT NULL;
SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_rtm_follow_ups' AND COLUMN_NAME = 'started_by');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE `spmi_rtm_follow_ups` ADD COLUMN `started_by` INT NULL AFTER `status`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @column_exists = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_rtm_follow_ups' AND COLUMN_NAME = 'started_at');
SET @sql = IF(@column_exists = 0, 'ALTER TABLE `spmi_rtm_follow_ups` ADD COLUMN `started_at` DATETIME NULL AFTER `started_by`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @index_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_rtm_follow_ups' AND INDEX_NAME = 'uq_spmi_rtm_follow_ups_code');
SET @sql = IF(@index_exists = 0, 'ALTER TABLE `spmi_rtm_follow_ups` ADD UNIQUE KEY `uq_spmi_rtm_follow_ups_code` (`follow_up_code`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @fk_exists = (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'spmi_rtm_follow_ups' AND CONSTRAINT_NAME = 'fk_spmi_rtm_follow_ups_started_by');
SET @sql = IF(@fk_exists = 0, 'ALTER TABLE `spmi_rtm_follow_ups` ADD CONSTRAINT `fk_spmi_rtm_follow_ups_started_by` FOREIGN KEY (`started_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
