DROP PROCEDURE IF EXISTS GetScoreLessThan40Percent;

DELIMITER //
CREATE PROCEDURE GetScoreLessThan40Percent()
BEGIN
    SELECT user.firstname, user.lastname, attempt.quiz_id, attempt.score, quiz_score.total_score FROM attempt
        INNER JOIN student ON student.id = attempt.stu_id
        INNER JOIN user ON user.username = student.username
        INNER JOIN (
            SELECT quiz_id, SUM(score) AS total_score FROM question GROUP BY quiz_id
        ) AS quiz_score ON quiz_score.quiz_id = attempt.quiz_id
        WHERE attempt.score < quiz_score.total_score * 0.4;
END //
DELIMITER ;

CALL GetScoreLessThan40Percent();
