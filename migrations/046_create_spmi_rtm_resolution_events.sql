CREATE TABLE IF NOT EXISTS `spmi_rtm_resolution_events` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `meeting_id` INT NOT NULL,
    `action` ENUM('resolve','unresolve') NOT NULL,
    `actor_user_id` INT NOT NULL,
    `reason` TEXT NULL,
    `status_from` ENUM('draft','resolved') NOT NULL,
    `status_to` ENUM('draft','resolved') NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_spmi_rtm_resolution_events_chronological` (`meeting_id`, `created_at`, `id`),
    KEY `idx_spmi_rtm_resolution_events_actor` (`actor_user_id`, `created_at`),
    CONSTRAINT `fk_spmi_rtm_resolution_events_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `spmi_rtm_meetings` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
    CONSTRAINT `fk_spmi_rtm_resolution_events_actor` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
