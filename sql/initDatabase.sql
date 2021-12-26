DROP DATABASE IF EXISTS quizapp;

-- create database
CREATE DATABASE IF NOT EXISTS quizapp;
USE quizapp;

-- create tables
CREATE TABLE IF NOT EXISTS user (
    username VARCHAR(255),
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(255) NOT NULL,
    lastname VARCHAR(255) NOT NULL,
    is_staff BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (username)
);

CREATE TABLE IF NOT EXISTS student (
    id INT UNSIGNED AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (username) REFERENCES user (username) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS staff (
    id INT UNSIGNED AUTO_INCREMENT,
    username VARCHAR(255) UNIQUE NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (username) REFERENCES user (username) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz (
    id INT UNSIGNED AUTO_INCREMENT,
    name VARCHAR(1024) NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    is_available BOOLEAN NOT NULL DEFAULT FALSE,
    duration INT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (author_id) REFERENCES staff (id),
    CHECK (duration > 0)
);

CREATE TABLE IF NOT EXISTS attempt (
    quiz_id INT UNSIGNED,
    stu_id INT UNSIGNED,
    date DATE NOT NULL,
    score INT UNSIGNED NOT NULL,
    PRIMARY KEY (quiz_id, stu_id),
    FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE,
    FOREIGN KEY (stu_id) REFERENCES student (id)
);

CREATE TABLE IF NOT EXISTS question (
    quiz_id INT UNSIGNED,
    ques_id INT UNSIGNED,
    name VARCHAR(1024) NOT NULL,
    score INT UNSIGNED NOT NULL DEFAULT 1,
    PRIMARY KEY (quiz_id, ques_id),
    FOREIGN KEY (quiz_id) REFERENCES quiz (id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `option` (
    quiz_id INT UNSIGNED,
    ques_id INT UNSIGNED,
    opt_id INT UNSIGNED,
    name VARCHAR(1024) NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    PRIMARY KEY (quiz_id, ques_id, opt_id),
    FOREIGN KEY (quiz_id, ques_id) REFERENCES question (quiz_id, ques_id) ON DELETE CASCADE
);
