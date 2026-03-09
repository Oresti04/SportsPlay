USE sportsplay;

DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `google_tokens`;

DROP TABLE IF EXISTS `registrations`;

DROP TABLE IF EXISTS `team_posts`;
DROP TABLE IF EXISTS `league_announcements`;

DROP TABLE IF EXISTS `practices`;
DROP TABLE IF EXISTS `game_results`;

DROP TABLE IF EXISTS `games`;
DROP TABLE IF EXISTS `team_players`;
DROP TABLE IF EXISTS `teams`;

DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `logs`;
DROP TABLE IF EXISTS `news_items`;

DROP TABLE IF EXISTS `players`;
DROP TABLE IF EXISTS `parents`;
DROP TABLE IF EXISTS `coaches`;
DROP TABLE IF EXISTS `accounts`;

DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `roles`;

DROP TABLE IF EXISTS `leagues`;
DROP TABLE IF EXISTS `sports`;

DROP TABLE IF EXISTS `venues`;

DROP TABLE IF EXISTS `users`;

-- =========================
-- CREATE TABLES (FK-safe order)
-- =========================

CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(50) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_roles` (
  `user_role_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`user_role_id`),
  KEY `idx_user_roles_user_id` (`user_id`),
  KEY `idx_user_roles_role_id` (`role_id`),
  UNIQUE KEY `uniq_user_role` (`user_id`, `role_id`),
  CONSTRAINT `fk_user_roles_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_user_roles_role`
    FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `parents` (
  `parent_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`parent_id`),
  KEY `idx_parents_user_id` (`user_id`),
  CONSTRAINT `fk_parents_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coaches` (
  `coach_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `bio` text DEFAULT NULL,
  PRIMARY KEY (`coach_id`),
  KEY `idx_coaches_user_id` (`user_id`),
  CONSTRAINT `fk_coaches_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `accounts` (
  `account_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `can_manage_schedules` tinyint(1) NOT NULL DEFAULT 0,
  `can_post_results` tinyint(1) NOT NULL DEFAULT 0,
  `can_edit_team_pages` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_league_pages` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`account_id`),
  KEY `idx_accounts_user_id` (`user_id`),
  CONSTRAINT `fk_accounts_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `players` (
  `player_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `jersey_no` int(10) unsigned DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`player_id`),
  KEY `idx_players_parent_id` (`parent_id`),
  CONSTRAINT `fk_players_parent`
    FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sports` (
  `sport_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`sport_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `leagues` (
  `league_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sport_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `age_min` int(3) unsigned NOT NULL,
  `age_max` int(3) unsigned NOT NULL,
  `season_year` int(4) unsigned NOT NULL,
  PRIMARY KEY (`league_id`),
  KEY `idx_leagues_sport_id` (`sport_id`),
  UNIQUE KEY `uniq_league_per_season` (`sport_id`, `name`, `season_year`),
  CONSTRAINT `fk_leagues_sport`
    FOREIGN KEY (`sport_id`) REFERENCES `sports` (`sport_id`)
    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `teams` (
  `team_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `coach_id` int(10) unsigned DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`team_id`),
  KEY `idx_teams_league_id` (`league_id`),
  KEY `idx_teams_coach_id` (`coach_id`),
  UNIQUE KEY `uniq_team_name_per_league` (`league_id`, `name`),
  CONSTRAINT `fk_teams_league`
    FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_teams_coach`
    FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`coach_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `team_players` (
  `team_player_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `player_id` int(10) unsigned NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`team_player_id`),
  KEY `idx_team_players_team_id` (`team_id`),
  KEY `idx_team_players_player_id` (`player_id`),
  UNIQUE KEY `uniq_team_player` (`team_id`, `player_id`),
  CONSTRAINT `fk_team_players_team`
    FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_team_players_player`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `venues` (
  `venue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`venue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `news_items` (
  `news_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `week_start` date DEFAULT NULL,
  `week_end` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`news_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `channel` varchar(50) NOT NULL,
  `target_address` varchar(255) NOT NULL,
  `is_subscribed` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`notification_id`),
  KEY `idx_notifications_user_id` (`user_id`),
  CONSTRAINT `fk_notifications_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `logs` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `level` varchar(20) NOT NULL,
  `action` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `idx_logs_user_id` (`user_id`),
  KEY `idx_logs_level` (`level`),
  KEY `idx_logs_created_at` (`created_at`),
  CONSTRAINT `fk_logs_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `games` (
  `game_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int(10) unsigned NOT NULL,
  `home_team_id` int(10) unsigned NOT NULL,
  `away_team_id` int(10) unsigned NOT NULL,
  `venue_id` int(10) unsigned DEFAULT NULL,
  `scheduled_start` timestamp NOT NULL,
  `stage` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`game_id`),
  KEY `idx_games_league_id` (`league_id`),
  KEY `idx_games_home_team_id` (`home_team_id`),
  KEY `idx_games_away_team_id` (`away_team_id`),
  KEY `idx_games_venue_id` (`venue_id`),
  CONSTRAINT `fk_games_league`
    FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_games_home_team`
    FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`team_id`)
    ON DELETE RESTRICT,
  CONSTRAINT `fk_games_away_team`
    FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`team_id`)
    ON DELETE RESTRICT,
  CONSTRAINT `fk_games_venue`
    FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`)
    ON DELETE SET NULL,
  CONSTRAINT `chk_games_different_teams`
    CHECK (`home_team_id` <> `away_team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `game_results` (
  `result_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `game_id` int(10) unsigned NOT NULL,
  `home_score` int(10) unsigned NOT NULL DEFAULT 0,
  `away_score` int(10) unsigned NOT NULL DEFAULT 0,
  `recorded_by` int(10) unsigned DEFAULT NULL,
  `recorded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`result_id`),
  UNIQUE KEY `uniq_game_result` (`game_id`),
  KEY `idx_game_results_recorded_by` (`recorded_by`),
  CONSTRAINT `fk_game_results_game`
    FOREIGN KEY (`game_id`) REFERENCES `games` (`game_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_game_results_recorded_by`
    FOREIGN KEY (`recorded_by`) REFERENCES `users` (`user_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `practices` (
  `practice_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `venue_id` int(10) unsigned DEFAULT NULL,
  `start_time` timestamp NOT NULL,
  `end_time` timestamp DEFAULT NULL,
  PRIMARY KEY (`practice_id`),
  KEY `idx_practices_team_id` (`team_id`),
  KEY `idx_practices_venue_id` (`venue_id`),
  CONSTRAINT `fk_practices_team`
    FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_practices_venue`
    FOREIGN KEY (`venue_id`) REFERENCES `venues` (`venue_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE `league_announcements` (
  `announcement_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`announcement_id`),
  KEY `idx_league_announcements_league_id` (`league_id`),
  KEY `idx_league_announcements_author_id` (`author_id`),
  CONSTRAINT `fk_league_announcements_league`
    FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_league_announcements_author`
    FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `team_posts` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int(10) unsigned NOT NULL,
  `author_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`post_id`),
  KEY `idx_team_posts_team_id` (`team_id`),
  KEY `idx_team_posts_author_id` (`author_id`),
  CONSTRAINT `fk_team_posts_team`
    FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_team_posts_author`
    FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `registrations` (
  `registration_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) unsigned NOT NULL,
  `league_id` int(10) unsigned NOT NULL,
  `season_year` int(4) unsigned NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`registration_id`),
  KEY `idx_registrations_player_id` (`player_id`),
  KEY `idx_registrations_league_id` (`league_id`),
  CONSTRAINT `fk_registrations_player`
    FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`)
    ON DELETE CASCADE,
  CONSTRAINT `fk_registrations_league`
    FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `payment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `registration_id` int(10) unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `payment_method` varchar(50) NOT NULL,
  `provider_transaction_id` varchar(255) DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payments_registration_id` (`registration_id`),
  CONSTRAINT `fk_payments_registration`
    FOREIGN KEY (`registration_id`) REFERENCES `registrations` (`registration_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `google_tokens` (
  `token_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `provider` varchar(50) NOT NULL,
  `google_user_id` varchar(255) DEFAULT NULL,
  `access_token` text NOT NULL,
  `refresh_token` text DEFAULT NULL,
  `scope` text DEFAULT NULL,
  `id_token` text DEFAULT NULL,
  `token_type` varchar(50) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`token_id`),
  KEY `idx_google_tokens_user_id` (`user_id`),
  CONSTRAINT `fk_google_tokens_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
INSERT INTO `users`
  (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `created_at`, `is_active`)
VALUES
  (1, 'admin@test.com',  '$2a$12$I/xGB5YlWIMULDaGmbQdyOUZ8jO/1EXNXsvUuFfHFo9eHydzVNFsO',  'Admin',  'User', '0000000001', '2026-02-22 20:29:39', 1),
  (2, 'coach@test.com',  '$2a$12$I/xGB5YlWIMULDaGmbQdyOUZ8jO/1EXNXsvUuFfHFo9eHydzVNFsO',  'Coach',  'User', '0000000002', '2026-02-22 20:29:39', 1),
  (3, 'parent@test.com', '$2a$12$I/xGB5YlWIMULDaGmbQdyOUZ8jO/1EXNXsvUuFfHFo9eHydzVNFsO',  'Parent', 'User', '0000000003', '2026-02-22 20:29:39', 1);

INSERT INTO `roles` (`role_id`, `role_name`) VALUES
  (1, 'admin'),
  (2, 'coach'),
  (3, 'user');

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
  (1, 1),
  (2, 2),
  (3, 3);
