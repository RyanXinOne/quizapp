-- Create a log table
DROP TABLE IF EXISTS quizDeleteLog;
CREATE TABLE IF NOT EXISTS quizDeleteLog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    staff_id INT NOT NULL,
    quiz_id INT NOT NULL,
    date_time DATETIME NOT NULL
);

-- Create trigger
DROP TRIGGER IF EXISTS AfterQuizDelete;
DELIMITER //
CREATE TRIGGER AfterQuizDelete
AFTER DELETE ON quiz FOR EACH ROW
BEGIN
    INSERT INTO quizDeleteLog (staff_id, quiz_id, date_time)
    VALUES (OLD.author_id, OLD.id, NOW());
END //
DELIMITER ;
