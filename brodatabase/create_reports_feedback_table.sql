-- Create reports_feedback table for ISUtoLearn system
-- This table stores reports and feedback submitted by learners

USE `ttest2`;

CREATE TABLE IF NOT EXISTS `reports_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `learner_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `type` enum('Report','Feedback') NOT NULL DEFAULT 'Report',
  `message` text NOT NULL,
  `status` enum('Pending','In Review','Resolved','Closed') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `learner_id` (`learner_id`),
  KEY `course_id` (`course_id`),
  KEY `status` (`status`),
  KEY `type` (`type`),
  CONSTRAINT `reports_feedback_ibfk_1` FOREIGN KEY (`learner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_feedback_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert sample data (optional)
INSERT INTO `reports_feedback` (`learner_id`, `course_id`, `title`, `category`, `type`, `message`, `status`) VALUES
(1, 1, 'Issue with Module 2', 'Technical', 'Report', 'I am experiencing issues accessing Module 2 content. The page keeps loading indefinitely.', 'Pending'),
(3, 1, 'Great Course!', 'General', 'Feedback', 'This course has been incredibly helpful. The content is well-structured and easy to follow.', 'Resolved');
