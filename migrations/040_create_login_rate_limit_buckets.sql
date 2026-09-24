CREATE TABLE IF NOT EXISTS `login_rate_limit_buckets` (
    `scope` ENUM('identity','ip') NOT NULL,
    `key_hash` CHAR(64) NOT NULL,
    `window_started_at` DATETIME NOT NULL,
    `failure_count` SMALLINT UNSIGNED NOT NULL,
    `blocked_until` DATETIME NULL DEFAULT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`scope`, `key_hash`),
    KEY `idx_login_rate_limit_buckets_window` (`window_started_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
