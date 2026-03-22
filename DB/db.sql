-- Adminer 5.4.1 MySQL 8.4.3 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `announcements`;
CREATE TABLE `announcements` (
  `announcement_id` int unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned NOT NULL,
  `author_id` int unsigned NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `audience` enum('all','players','parents','staff') NOT NULL DEFAULT 'all',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcement_id`),
  KEY `idx_team_created` (`team_id`,`created_at` DESC),
  KEY `fk_ann_author` (`author_id`),
  CONSTRAINT `fk_ann_author` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ann_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `announcements` (`announcement_id`, `team_id`, `author_id`, `title`, `body`, `audience`, `is_pinned`, `created_at`) VALUES
(1,	1,	20,	'Saturday Match Logistics',	'Please arrive 15 minutes early. Bring both kits (blue/white), shin guards, and water. Parking at east lot entrance.',	'all',	1,	'2026-03-04 09:05:00'),
(2,	1,	20,	'Training Moved Indoors',	'Thursday training will be in the Indoor Dome due to weather. Same time, same check-in process.',	'players',	0,	'2026-03-03 17:13:00'),
(3,	1,	21,	'Welcome to Spring Season!',	'Excited to kick off Spring 2026. First practice is this Tuesday. Please make sure all registration fees are paid.',	'all',	0,	'2026-03-01 08:00:00'),
(4,	1,	20,	'System check',	'Announcement insert path is valid.',	'all',	0,	'2026-03-04 15:48:35'),
(5,	1,	20,	'Test',	'Test test Test',	'all',	0,	'2026-03-04 19:24:22'),
(12,	8,	56,	'Test',	'testetsttestetsttestetsttestetst',	'all',	0,	'2026-03-05 03:46:10'),
(13,	9,	58,	'Practice 15th March, 11am',	'bring water',	'all',	0,	'2026-03-05 14:13:41');

DROP TABLE IF EXISTS `direct_messages`;
CREATE TABLE `direct_messages` (
  `message_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned DEFAULT NULL,
  `sender_user_id` int unsigned NOT NULL,
  `recipient_user_id` int unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `idx_dm_pair_time` (`sender_user_id`,`recipient_user_id`,`created_at`),
  KEY `idx_dm_recipient_time` (`recipient_user_id`,`created_at`),
  KEY `idx_dm_team` (`team_id`),
  CONSTRAINT `fk_dm_recipient` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dm_sender` FOREIGN KEY (`sender_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_dm_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `direct_messages` (`message_id`, `team_id`, `sender_user_id`, `recipient_user_id`, `body`, `is_read`, `read_at`, `created_at`) VALUES
(1,	1,	20,	18,	'Welcome. Use this chat for training and match updates.',	1,	'2026-03-04 16:09:27',	'2026-03-04 15:47:12'),
(2,	1,	20,	33,	'Hi',	0,	NULL,	'2026-03-04 16:07:08'),
(3,	NULL,	18,	20,	'Hello trainer',	1,	'2026-03-04 17:54:07',	'2026-03-04 17:53:42'),
(4,	1,	20,	18,	'Test',	1,	'2026-03-04 19:25:11',	'2026-03-04 17:54:16'),
(25,	7,	55,	53,	'Your join request was approved. Welcome to the team.',	1,	'2026-03-05 03:06:36',	'2026-03-05 03:06:19'),
(26,	NULL,	53,	55,	'Hi coach how are you?',	1,	'2026-03-05 03:06:49',	'2026-03-05 03:06:43'),
(27,	7,	55,	53,	'Im good what about you?',	1,	'2026-03-05 03:09:23',	'2026-03-05 03:09:20'),
(28,	8,	56,	57,	'Your join request was approved. Welcome to the team.',	1,	'2026-03-05 03:44:50',	'2026-03-05 03:44:10'),
(29,	8,	56,	57,	'HI welcome on board',	1,	'2026-03-05 03:44:50',	'2026-03-05 03:44:39'),
(30,	NULL,	57,	56,	'hi',	1,	'2026-03-05 03:45:19',	'2026-03-05 03:45:19'),
(31,	9,	58,	59,	'Your join request was approved. Welcome to the team.',	1,	'2026-03-05 14:12:07',	'2026-03-05 14:11:12'),
(32,	9,	58,	59,	'Hi welcome',	1,	'2026-03-05 14:12:07',	'2026-03-05 14:12:00'),
(33,	NULL,	59,	58,	'Hi',	1,	'2026-03-05 14:12:26',	'2026-03-05 14:12:25'),
(34,	NULL,	59,	58,	'Test',	1,	'2026-03-05 14:12:30',	'2026-03-05 14:12:28');

DROP TABLE IF EXISTS `leagues`;
CREATE TABLE `leagues` (
  `league_id` int unsigned NOT NULL AUTO_INCREMENT,
  `season` varchar(50) NOT NULL,
  `sport` varchar(50) NOT NULL,
  `name` varchar(50) NOT NULL,
  `age_min` tinyint unsigned DEFAULT NULL,
  `age_max` tinyint unsigned DEFAULT NULL,
  `fee_cents` int unsigned NOT NULL DEFAULT '0',
  `roster_cap` smallint unsigned NOT NULL DEFAULT '0',
  `reg_open` date DEFAULT NULL,
  `reg_close` date DEFAULT NULL,
  `status` enum('draft','open','closed','archived') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`league_id`),
  UNIQUE KEY `uniq_league` (`season`,`sport`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `leagues` (`league_id`, `season`, `sport`, `name`, `age_min`, `age_max`, `fee_cents`, `roster_cap`, `reg_open`, `reg_close`, `status`, `created_at`) VALUES
(1,	'2026 Fall',	'Baseball',	'u18',	18,	22,	15000,	18,	'2026-01-20',	'2026-07-15',	'open',	'2026-03-03 01:41:51'),
(2,	'2015 fall',	'Baseball',	'u13',	3,	30,	18000,	17,	'2014-02-02',	'2014-03-03',	'open',	'2026-03-03 01:46:35'),
(3,	'2026 Spring',	'Soccer',	'Youth League',	NULL,	NULL,	0,	0,	NULL,	NULL,	'open',	'2026-03-03 01:53:56');

DROP TABLE IF EXISTS `matches`;
CREATE TABLE `matches` (
  `match_id` int unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int unsigned NOT NULL,
  `home_team_id` int unsigned NOT NULL,
  `away_team_id` int unsigned NOT NULL,
  `match_datetime` datetime NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `status` enum('scheduled','completed','canceled') NOT NULL DEFAULT 'scheduled',
  `home_score` tinyint unsigned DEFAULT NULL,
  `away_score` tinyint unsigned DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`match_id`),
  KEY `idx_league_datetime` (`league_id`,`match_datetime`),
  KEY `idx_home` (`home_team_id`),
  KEY `idx_away` (`away_team_id`),
  CONSTRAINT `fk_matches_away` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_home` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_matches_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `matches` (`match_id`, `league_id`, `home_team_id`, `away_team_id`, `match_datetime`, `location`, `status`, `home_score`, `away_score`, `notes`, `created_at`) VALUES
(1,	1,	1,	3,	'2026-12-12 23:11:00',	'US',	'completed',	1,	1,	NULL,	'2026-03-03 02:38:27'),
(2,	1,	1,	2,	'2026-03-06 19:00:00',	'RIT Stadium',	'scheduled',	NULL,	NULL,	NULL,	'2026-03-03 03:44:16'),
(3,	1,	2,	3,	'2026-03-09 18:30:00',	'Downtown Field',	'scheduled',	NULL,	NULL,	NULL,	'2026-03-03 03:44:16'),
(4,	1,	1,	2,	'2026-03-10 02:51:09',	'RIT Turf Field 2',	'scheduled',	NULL,	NULL,	NULL,	'2026-03-04 01:51:09'),
(5,	3,	5,	6,	'2026-09-13 21:00:00',	'Roc',	'completed',	1,	20,	NULL,	'2026-03-05 02:25:18'),
(6,	3,	6,	5,	'2025-12-09 21:00:00',	'Ritter',	'completed',	1,	1,	NULL,	'2026-03-05 02:31:41'),
(7,	3,	10,	9,	'2025-08-13 21:00:00',	'Ritter arena',	'completed',	1,	1,	NULL,	'2026-03-05 14:31:10');

DROP TABLE IF EXISTS `parent_players`;
CREATE TABLE `parent_players` (
  `parent_id` int unsigned NOT NULL,
  `player_id` int unsigned NOT NULL,
  `relation` enum('parent','guardian','other') NOT NULL DEFAULT 'parent',
  PRIMARY KEY (`parent_id`,`player_id`),
  KEY `fk_pp_player` (`player_id`),
  CONSTRAINT `fk_pp_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pp_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `parent_players` (`parent_id`, `player_id`, `relation`) VALUES
(1,	1,	'parent'),
(1,	3,	'parent'),
(1,	4,	'parent'),
(2,	2,	'parent'),
(7,	16,	'parent');

DROP TABLE IF EXISTS `parents`;
CREATE TABLE `parents` (
  `parent_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `preferred_contact` enum('email','sms','phone') NOT NULL DEFAULT 'email',
  `balance_cents` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`parent_id`),
  UNIQUE KEY `uniq_parent_user` (`user_id`),
  CONSTRAINT `fk_parents_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `parents` (`parent_id`, `user_id`, `phone`, `preferred_contact`, `balance_cents`, `is_active`, `created_at`) VALUES
(1,	17,	'+38269231464',	'phone',	122000,	1,	'2026-03-03 02:13:10'),
(2,	22,	'585-555-2001',	'email',	0,	1,	'2026-03-04 01:51:09'),
(7,	54,	NULL,	'email',	0,	1,	'2026-03-05 02:57:39');

DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `payment_id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned NOT NULL,
  `player_id` int unsigned DEFAULT NULL,
  `league_id` int unsigned DEFAULT NULL,
  `item` varchar(200) NOT NULL,
  `amount_cents` int unsigned NOT NULL DEFAULT '0',
  `status` enum('paid','unpaid','pending','refunded') NOT NULL DEFAULT 'unpaid',
  `method` varchar(50) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `idx_parent` (`parent_id`),
  KEY `idx_player` (`player_id`),
  CONSTRAINT `fk_pay_parent` FOREIGN KEY (`parent_id`) REFERENCES `parents` (`parent_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_pay_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `payments` (`payment_id`, `parent_id`, `player_id`, `league_id`, `item`, `amount_cents`, `status`, `method`, `due_date`, `paid_at`, `created_at`) VALUES
(1,	1,	1,	1,	'Spring 2026 Season Fee',	18000,	'paid',	'stripe',	'2026-02-10',	'2026-02-10 13:22:00',	'2026-01-15 09:00:00'),
(2,	1,	1,	1,	'Uniform Kit',	4500,	'unpaid',	NULL,	'2026-03-15',	NULL,	'2026-02-01 09:00:00'),
(3,	1,	1,	1,	'Tournament Fee (Optional)',	3500,	'pending',	NULL,	'2026-03-12',	NULL,	'2026-02-20 09:00:00'),
(4,	2,	2,	1,	'Spring 2026 Season Fee',	18000,	'paid',	'paypal',	'2026-02-10',	'2026-02-08 10:30:00',	'2026-01-15 09:00:00'),
(5,	2,	2,	1,	'Uniform Kit',	4500,	'paid',	'stripe',	'2026-03-01',	'2026-02-28 08:15:00',	'2026-02-01 09:00:00');

DROP TABLE IF EXISTS `players`;
CREATE TABLE `players` (
  `player_id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `team_id` int unsigned NOT NULL,
  `jersey_number` smallint unsigned DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `height_cm` smallint unsigned DEFAULT NULL,
  `weight_kg` smallint unsigned DEFAULT NULL,
  `guardian_name` varchar(120) DEFAULT NULL,
  `guardian_phone` varchar(30) DEFAULT NULL,
  `medical_notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`player_id`),
  UNIQUE KEY `uniq_player_user` (`user_id`),
  KEY `idx_team` (`team_id`),
  CONSTRAINT `fk_players_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_players_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `players` (`player_id`, `user_id`, `team_id`, `jersey_number`, `position`, `dob`, `height_cm`, `weight_kg`, `guardian_name`, `guardian_phone`, `medical_notes`, `created_at`) VALUES
(1,	18,	1,	10,	'Mid',	'2003-08-13',	NULL,	NULL,	'Bojana',	'585-202-9226',	NULL,	'2026-03-03 02:02:07'),
(2,	23,	1,	8,	'CM',	NULL,	NULL,	NULL,	'Ana Petrovic',	'585-555-2001',	NULL,	'2026-03-04 02:23:33'),
(3,	27,	1,	7,	'Forward',	'2006-03-12',	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 02:46:35'),
(4,	28,	1,	11,	'Midfielder',	'2005-08-21',	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 02:46:35'),
(5,	29,	1,	4,	'Defender',	'2006-11-05',	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 02:46:35'),
(6,	30,	1,	7,	'CM',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 03:00:48'),
(7,	31,	1,	10,	'ST',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 03:00:48'),
(8,	32,	1,	4,	'CB',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 03:00:48'),
(9,	33,	1,	1,	'GK',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 03:00:48'),
(10,	34,	1,	11,	'RW',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 03:00:48'),
(11,	35,	1,	8,	'CM',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-04 03:00:48'),
(16,	53,	7,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-05 03:06:19'),
(17,	57,	8,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-05 03:44:10'),
(18,	59,	9,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'2026-03-05 14:11:12'),
(19,	54,	10,	10,	'mid',	'2003-08-13',	NULL,	NULL,	'Test',	'5852029226',	NULL,	'2026-03-05 14:26:11');

DROP TABLE IF EXISTS `team_coaches`;
CREATE TABLE `team_coaches` (
  `team_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `role` enum('head','assistant') NOT NULL DEFAULT 'head',
  PRIMARY KEY (`team_id`,`user_id`),
  KEY `idx_tc_user` (`user_id`),
  CONSTRAINT `fk_tc_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tc_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `team_coaches` (`team_id`, `user_id`, `role`) VALUES
(1,	20,	'head'),
(1,	21,	'head'),
(7,	55,	'head'),
(8,	56,	'head'),
(9,	58,	'head');

DROP TABLE IF EXISTS `team_join_requests`;
CREATE TABLE `team_join_requests` (
  `request_id` int unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned NOT NULL,
  `player_user_id` int unsigned NOT NULL,
  `message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reviewed_by` int unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  KEY `idx_tjr_team_status` (`team_id`,`status`,`created_at`),
  KEY `idx_tjr_player` (`player_user_id`,`created_at`),
  KEY `idx_tjr_reviewed_by` (`reviewed_by`),
  CONSTRAINT `fk_tjr_player_user` FOREIGN KEY (`player_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_tjr_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_tjr_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `team_join_requests` (`request_id`, `team_id`, `player_user_id`, `message`, `status`, `reviewed_by`, `reviewed_at`, `created_at`) VALUES
(3,	7,	53,	'Let me in!',	'approved',	55,	'2026-03-05 03:06:19',	'2026-03-05 03:06:04'),
(4,	8,	57,	'Hi please let me in',	'approved',	56,	'2026-03-05 03:44:10',	'2026-03-05 03:43:50'),
(5,	9,	59,	'Hi i woild like to joiin you team',	'approved',	58,	'2026-03-05 14:11:12',	'2026-03-05 14:10:43');

DROP TABLE IF EXISTS `teams`;
CREATE TABLE `teams` (
  `team_id` int unsigned NOT NULL AUTO_INCREMENT,
  `league_id` int unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `season` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sport` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`team_id`),
  KEY `fk_teams_league` (`league_id`),
  CONSTRAINT `fk_teams_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `teams` (`team_id`, `league_id`, `name`, `city`, `is_active`, `created_at`, `season`, `sport`) VALUES
(1,	1,	'RIT Tigers U14',	NULL,	1,	'2026-03-03 01:54:45',	'2026 Spring',	'Soccer'),
(2,	1,	'Rochester Falcons U16',	NULL,	1,	'2026-03-03 01:54:45',	'2026 Spring',	'Soccer'),
(3,	1,	'Lake Ontario Sharks U12',	NULL,	1,	'2026-03-03 01:54:45',	'2026 Spring',	'Soccer'),
(4,	2,	'Maxim Gorki',	'NYC',	1,	'2026-03-03 02:26:30',	NULL,	'Baseball'),
(5,	3,	'Test Team',	'Zagreb',	1,	'2026-03-05 02:03:59',	'2026 Spring',	'Soccer'),
(6,	3,	'Team Du',	NULL,	1,	'2026-03-05 02:24:47',	NULL,	NULL),
(7,	3,	'Test ROC',	'Rochester',	1,	'2026-03-05 03:03:13',	'2026 Spring',	'Soccer'),
(8,	3,	'RochesterTest',	'Rochester',	1,	'2026-03-05 03:40:39',	'2026 Spring',	'Soccer'),
(9,	3,	'Rochester Tigers',	'Rochester',	1,	'2026-03-05 14:07:59',	'2026 Spring',	'Soccer'),
(10,	3,	'RIT DUbrovnik',	'Dubrovnik',	1,	'2026-03-05 14:24:00',	NULL,	'Baseball');

DROP TABLE IF EXISTS `training_attendance`;
CREATE TABLE `training_attendance` (
  `attendance_id` int unsigned NOT NULL AUTO_INCREMENT,
  `training_id` int unsigned NOT NULL,
  `player_id` int unsigned NOT NULL,
  `status` enum('present','late','absent','excused') NOT NULL DEFAULT 'present',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`attendance_id`),
  UNIQUE KEY `uniq_training_player` (`training_id`,`player_id`),
  KEY `fk_ta_player` (`player_id`),
  CONSTRAINT `fk_ta_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`player_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ta_training` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`training_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


DROP TABLE IF EXISTS `trainings`;
CREATE TABLE `trainings` (
  `training_id` int unsigned NOT NULL AUTO_INCREMENT,
  `team_id` int unsigned NOT NULL,
  `league_id` int unsigned NOT NULL,
  `training_datetime` datetime NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `duration_minutes` smallint unsigned DEFAULT NULL,
  `intensity` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('scheduled','completed','canceled') DEFAULT 'scheduled',
  `notes` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`training_id`),
  KEY `idx_team_datetime` (`team_id`,`training_datetime`),
  KEY `fk_training_league` (`league_id`),
  CONSTRAINT `fk_training_league` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`league_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_training_team` FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `trainings` (`training_id`, `team_id`, `league_id`, `training_datetime`, `location`, `duration_minutes`, `intensity`, `status`, `notes`, `created_at`) VALUES
(1,	1,	1,	'2026-03-05 17:30:00',	'RIT Turf Field',	90,	'high',	'scheduled',	'Tactical drills + finishing',	'2026-03-03 03:29:30'),
(2,	1,	1,	'2026-03-08 18:00:00',	'RIT Indoor Hall',	75,	'medium',	'scheduled',	'Passing and possession',	'2026-03-03 03:29:30'),
(3,	2,	1,	'2026-03-06 16:30:00',	'Downtown Field',	90,	'high',	'scheduled',	'Conditioning + sprint work',	'2026-03-03 03:29:30'),
(4,	3,	1,	'2026-03-07 17:00:00',	'Lakeview Complex',	60,	'low',	'scheduled',	'Recovery session',	'2026-03-03 03:29:30'),
(5,	1,	1,	'2026-03-04 18:00:00',	'RIT Turf Field',	90,	'high',	'scheduled',	NULL,	'2026-03-03 03:44:16'),
(6,	2,	1,	'2026-03-05 17:00:00',	'Indoor Hall',	75,	'medium',	'scheduled',	NULL,	'2026-03-03 03:44:16'),
(7,	3,	1,	'2026-03-15 09:00:00',	'US',	30,	'high',	'scheduled',	NULL,	'2026-03-03 03:57:03'),
(8,	1,	1,	'2026-03-06 02:51:09',	'RIT Turf Field',	90,	'high',	'scheduled',	'Technical Session',	'2026-03-04 01:51:09'),
(9,	1,	1,	'2026-03-14 21:00:00',	'Roc',	90,	'low',	'scheduled',	NULL,	'2026-03-04 04:43:59'),
(10,	10,	3,	'2026-03-16 21:00:00',	'Ritter arena',	90,	'medium',	'scheduled',	NULL,	'2026-03-05 14:32:42');

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `profile_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `is_coach` tinyint(1) NOT NULL DEFAULT '0',
  `provider` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `users_chk_1` CHECK (json_valid(`metadata`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `users` (`user_id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `profile_image`, `created_at`, `ip_address`, `metadata`, `is_active`, `is_admin`, `is_coach`, `provider`) VALUES
(1,	'djole786@gmail.com',	'$2y$10$XuPOgTgAAAorjYS.1.NXTe2tIYgFmVNIK.JXEwL96Zdp6iZSyXShu',	'Devil',	'',	'0953628294',	NULL,	'2025-12-05 00:04:47',	'::1',	NULL,	1,	1,	0,	'local'),
(12,	'admin@test.com',	'$2y$10$jMmE6411dyBSX0Wg3ddxtOF/2tE02/54PYlHqLdEwx3VGbLrYTrGi',	'Admin',	'Test',	'0000000000',	NULL,	'2026-02-14 17:48:00',	'127.0.0.1',	NULL,	1,	1,	0,	'local'),
(17,	'parent@sportsplay.test',	'$2y$10$jMmE6411dyBSX0Wg3ddxtOF/2tE02/54PYlHqLdEwx3VGbLrYTrGi',	'Parent',	'Maria',	NULL,	NULL,	'2026-03-02 00:04:24',	NULL,	'{\"role\":\"parent\",\"children\":[{\"first_name\":\"Luka\",\"last_name\":\"Maria\"}]}',	1,	0,	0,	'local'),
(18,	'player@sportsplay.test',	'$2y$10$jMmE6411dyBSX0Wg3ddxtOF/2tE02/54PYlHqLdEwx3VGbLrYTrGi',	'Player',	'Aleksa',	NULL,	'assets/uploads/profiles/profile_18_1772653665.jpg',	'2026-03-02 00:04:24',	NULL,	'{\"role\":\"player\",\"position\":\"CM\",\"number\":8}',	1,	0,	0,	'local'),
(19,	'coach@sportsplay.com',	'$2y$10$jMmE6411dyBSX0Wg3ddxtOF/2tE02/54PYlHqLdEwx3VGbLrYTrGi	',	'John',	'Coach',	'123456789',	NULL,	'2026-03-04 01:10:57',	NULL,	NULL,	1,	0,	1,	'local'),
(20,	'coach@sportsplay.test',	'$2y$10$jMmE6411dyBSX0Wg3ddxtOF/2tE02/54PYlHqLdEwx3VGbLrYTrGi',	'John',	'Ivan',	'123456789',	NULL,	'2026-03-04 01:12:06',	NULL,	NULL,	1,	0,	1,	'local'),
(21,	'coach.demo@sportsplay.com',	'$2y$12$oj7tqEJZUwa7l6NlG1a3q./PX5Txg.JxyUs0xJtGWzVdN8cSFx7Mm',	'Mia',	'Peterson',	'585-555-1001',	NULL,	'2026-03-04 01:51:09',	NULL,	'{\"app_role\": \"coach\"}',	1,	0,	1,	'local'),
(22,	'parent.demo@sportsplay.com',	'$2y$12$oj7tqEJZUwa7l6NlG1a3q./PX5Txg.JxyUs0xJtGWzVdN8cSFx7Mm',	'Ana',	'Petrovic',	'585-555-2001',	NULL,	'2026-03-04 01:51:09',	NULL,	'{\"app_role\": \"parent\"}',	1,	0,	0,	'local'),
(23,	'player.demo@sportsplay.com',	'$2y$12$oj7tqEJZUwa7l6NlG1a3q./PX5Txg.JxyUs0xJtGWzVdN8cSFx7Mm',	'Luka',	'Petrovic',	'585-555-3001',	NULL,	'2026-03-04 01:51:09',	NULL,	'{\"number\": 8, \"app_role\": \"player\", \"position\": \"CM\"}',	1,	0,	0,	'local'),
(27,	'player1@sportsplay.test',	'$2y$10$testhash',	'Luka',	'Ivanovic',	NULL,	NULL,	'2026-03-04 02:46:16',	NULL,	NULL,	1,	0,	0,	'local'),
(28,	'player2@sportsplay.test',	'$2y$10$testhash',	'Marko',	'Petrovic',	NULL,	NULL,	'2026-03-04 02:46:16',	NULL,	NULL,	1,	0,	0,	'local'),
(29,	'player3@sportsplay.test',	'$2y$10$testhash',	'Stefan',	'Kovacevic',	NULL,	NULL,	'2026-03-04 02:46:16',	NULL,	NULL,	1,	0,	0,	'local'),
(30,	'luka@sportsplay.test',	'test',	'Luka',	'Petrovic',	NULL,	NULL,	'2026-03-04 03:00:35',	NULL,	NULL,	1,	0,	0,	'local'),
(31,	'noah@sportsplay.test',	'test',	'Noah',	'Williams',	NULL,	NULL,	'2026-03-04 03:00:35',	NULL,	NULL,	1,	0,	0,	'local'),
(32,	'mila@sportsplay.test',	'test',	'Mila',	'Johnson',	NULL,	NULL,	'2026-03-04 03:00:35',	NULL,	NULL,	1,	0,	0,	'local'),
(33,	'ethan@sportsplay.test',	'test',	'Ethan',	'Brown',	NULL,	NULL,	'2026-03-04 03:00:35',	NULL,	NULL,	1,	0,	0,	'local'),
(34,	'sofia@sportsplay.test',	'test',	'Sofia',	'Ramirez',	NULL,	NULL,	'2026-03-04 03:00:35',	NULL,	NULL,	1,	0,	0,	'local'),
(35,	'alex@sportsplay.test',	'test',	'Alex',	'Chen',	NULL,	NULL,	'2026-03-04 03:00:35',	NULL,	NULL,	1,	0,	0,	'local'),
(53,	'aleksapl@sport.com',	'$2y$10$m6iC9HsCPGo9y46XSsjJq.XVNRWQoSCVLntqLsnMG7RQBg1vWTKLS',	'Aleksa',	'Player',	NULL,	NULL,	'2026-03-05 02:57:05',	'::1',	'{\"app_role\":\"player\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	0,	'local'),
(54,	'aleksapa@sport.com',	'$2y$10$3wQ3DeI/Cq9ixZvzJN87UujI8uTDkDvt46n6WXaU7DS4iHytwYebC',	'AleksaPa',	'',	NULL,	NULL,	'2026-03-05 02:57:39',	'::1',	'{\"app_role\":\"parent\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	0,	'local'),
(55,	'aleksac@sport.com',	'$2y$10$4TU0vm8kICgszb9kQq45tuuPZ/iH79KG0pdBzbsYDBwph/MVOc8Xm',	'AleksaCo',	'',	NULL,	NULL,	'2026-03-05 03:02:07',	'::1',	'{\"app_role\":\"coach\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	1,	'local'),
(56,	'basket@sport.com',	'$2y$10$zA0hKM4Td732H6CvECCmS.faghnvNO1QJP20AZ.THxeJxjzGmjgna',	'Basket',	'',	NULL,	'assets/uploads/profiles/profile_56_1772682429.jpg',	'2026-03-05 03:37:21',	'::1',	'{\"app_role\":\"coach\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	1,	'local'),
(57,	'tstp@sport.com',	'$2y$10$Gdg6yGdNHtXFhwi4yGnll.UIwSSA/rGK.S.t95dC87uL1CaSURNJe',	'Player111',	'',	NULL,	NULL,	'2026-03-05 03:42:27',	'::1',	'{\"app_role\":\"player\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	0,	'local'),
(58,	'testcoach1@sportsplay.com',	'$2y$10$RuS048cgc9XzS52mLqNPCOMcXd/lv/GePM9F7g8BgsIbF6anSDRia',	'Test',	'Acc',	NULL,	NULL,	'2026-03-05 14:06:35',	'::1',	'{\"app_role\":\"coach\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	1,	'local'),
(59,	'player12@sportsplay.test',	'$2y$10$5Cj/NdtgiEY5O6zkRp5C5e6kmPZPuYry24yXpiVAmKAGAUq5po7LS',	'player1122',	'',	NULL,	'assets/uploads/profiles/profile_59_1772720295.jpg',	'2026-03-05 14:09:01',	'::1',	'{\"app_role\":\"player\",\"signup_source\":\"sportsplay_auth\"}',	1,	0,	0,	'local');

-- 2026-03-22 19:41:10 UTC
