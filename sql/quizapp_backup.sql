-- phpMyAdmin SQL Dump
-- version 4.9.5deb2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 03, 2021 at 05:35 PM
-- Server version: 8.0.27-0ubuntu0.20.04.1
-- PHP Version: 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quizapp`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetScoreLessThan40Percent` ()  BEGIN
    SELECT user.firstname, user.lastname, attempt.quiz_id, attempt.score, quiz_score.total_score FROM attempt
        INNER JOIN student ON student.id = attempt.stu_id
        INNER JOIN user ON user.username = student.username
        INNER JOIN (
            SELECT quiz_id, SUM(score) AS total_score FROM question GROUP BY quiz_id
        ) AS quiz_score ON quiz_score.quiz_id = attempt.quiz_id
        WHERE attempt.score < quiz_score.total_score * 0.4;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `attempt`
--

CREATE TABLE `attempt` (
  `quiz_id` int UNSIGNED NOT NULL,
  `stu_id` int UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `score` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `attempt`
--

INSERT INTO `attempt` (`quiz_id`, `stu_id`, `date`, `score`) VALUES
(2, 1, '2021-12-03', 10),
(4, 1, '2021-12-03', 0);

-- --------------------------------------------------------

--
-- Table structure for table `option`
--

CREATE TABLE `option` (
  `quiz_id` int UNSIGNED NOT NULL,
  `ques_id` int UNSIGNED NOT NULL,
  `opt_id` int UNSIGNED NOT NULL,
  `name` varchar(1024) NOT NULL,
  `is_correct` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `option`
--

INSERT INTO `option` (`quiz_id`, `ques_id`, `opt_id`, `name`, `is_correct`) VALUES
(2, 1, 1, 'Option 1', 1),
(2, 1, 2, 'Option 2', 0),
(2, 1, 3, 'Option 3', 0),
(2, 1, 4, 'Option 4', 0),
(2, 2, 1, 'Option 1', 0),
(2, 2, 2, 'Option 2', 1),
(3, 1, 1, 'Option 1', 0),
(3, 1, 2, 'Option 2', 1),
(4, 1, 1, 'Option 1', 0),
(4, 1, 2, 'Option 2', 0),
(4, 1, 3, 'Option 3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `quiz_id` int UNSIGNED NOT NULL,
  `ques_id` int UNSIGNED NOT NULL,
  `name` varchar(1024) NOT NULL,
  `score` int UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`quiz_id`, `ques_id`, `name`, `score`) VALUES
(2, 1, 'This is question 1', 10),
(2, 2, 'This is question 2', 10),
(3, 1, 'This is question 1', 10),
(4, 1, 'Question 1', 10);

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(1024) NOT NULL,
  `author_id` int UNSIGNED NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '0',
  `duration` int UNSIGNED NOT NULL
) ;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`id`, `name`, `author_id`, `is_available`, `duration`) VALUES
(2, 'Quiz 1', 1, 1, 60),
(3, 'Quiz 2', 1, 0, 60),
(4, 'Quiz 3', 2, 1, 60);

--
-- Triggers `quiz`
--
DELIMITER $$
CREATE TRIGGER `AfterQuizDelete` AFTER DELETE ON `quiz` FOR EACH ROW BEGIN
    INSERT INTO quizDeleteLog (staff_id, quiz_id, date_time)
    VALUES (OLD.author_id, OLD.id, NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `quizDeleteLog`
--

CREATE TABLE `quizDeleteLog` (
  `id` int NOT NULL,
  `staff_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `date_time` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `quizDeleteLog`
--

INSERT INTO `quizDeleteLog` (`id`, `staff_id`, `quiz_id`, `date_time`) VALUES
(1, 2, 5, '2021-12-03 17:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `username`) VALUES
(1, 'staff1'),
(2, 'staff2');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int UNSIGNED NOT NULL,
  `username` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `username`) VALUES
(1, 'student1');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `is_staff` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`username`, `password`, `firstname`, `lastname`, `is_staff`) VALUES
('staff1', '$2y$10$0Segt79BaX00e11TXyYUmetbHyZXbvOkfmr6iGhC6BqStCBer849G', 'Duncan', 'Hull', 1),
('staff2', '$2y$10$noABfEr9LLuae7Ji2pBI8uu9nuuE3qEnpQhP//eUihaPm8STkcr7i', 'Stewart', 'Blakeway', 1),
('student1', '$2y$10$JESaymndCmIJ4to3jLRzlOrbP7UnVHilSKXFQ8Is75ANbVlqPbS6.', 'Yanze', 'Xin', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attempt`
--
ALTER TABLE `attempt`
  ADD PRIMARY KEY (`quiz_id`,`stu_id`),
  ADD KEY `stu_id` (`stu_id`);

--
-- Indexes for table `option`
--
ALTER TABLE `option`
  ADD PRIMARY KEY (`quiz_id`,`ques_id`,`opt_id`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`quiz_id`,`ques_id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `quizDeleteLog`
--
ALTER TABLE `quizDeleteLog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quizDeleteLog`
--
ALTER TABLE `quizDeleteLog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attempt`
--
ALTER TABLE `attempt`
  ADD CONSTRAINT `attempt_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attempt_ibfk_2` FOREIGN KEY (`stu_id`) REFERENCES `student` (`id`);

--
-- Constraints for table `option`
--
ALTER TABLE `option`
  ADD CONSTRAINT `option_ibfk_1` FOREIGN KEY (`quiz_id`,`ques_id`) REFERENCES `question` (`quiz_id`, `ques_id`) ON DELETE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `staff` (`id`);

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `staff_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`username`) REFERENCES `user` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
