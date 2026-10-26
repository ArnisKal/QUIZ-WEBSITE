CREATE DATABASE IF NOT EXISTS QUIZZITY
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE QUIZZITY;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
);

CREATE TABLE IF NOT EXISTS quizzes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255) NULL,
    type ENUM('official', 'created') NOT NULL DEFAULT 'official',
    created_by INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    question_number INT UNSIGNED NOT NULL DEFAULT 0,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    answer_text VARCHAR(255) NOT NULL,
    is_correct BOOLEAN NOT NULL DEFAULT FALSE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS favourites (
    user_id INT UNSIGNED NOT NULL,
    quiz_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, quiz_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS faq_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
) CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS info_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(120) NOT NULL,
    description VARCHAR(500) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT NOT NULL DEFAULT 0
) CHARACTER SET utf8mb4;

INSERT INTO faq_items (question, answer, sort_order) VALUES
('What is Quizzity?', 'Quizzity is an online quiz platform where you can play quizzes made by us and compete on the leaderboards.', 1),
('How I can create my own quizz?', 'For now you can''t, only admin can create our quizzes!', 2),
('Do I need to register my account to play Quizzity?', 'Yes, you do. You don''t have necessarily have to register using Google.', 3);

INSERT INTO info_blocks (title, description, image, sort_order) VALUES
('Create Quizzes', 'Build your own quizzes in minutes and share them with the world.', 'images/craft.jpg', 1),
('Play & Learn', 'Try quizzes made by the community on any topic.', NULL, 2),
('Climb the Leaderboard', 'Score points, beat other players and become number one.', 'images/leader.jpeg', 3),
('Join the QUIZZITY', 'Join our team, to be yourself!', NULL, 4);


ALTER TABLE users ADD COLUMN country CHAR(2) NULL;

ALTER TABLE questions ADD COLUMN image VARCHAR(255) NULL;
ALTER TABLE questions ADD COLUMN time_limit SMALLINT UNSIGNED NOT NULL DEFAULT 30;
ALTER TABLE quizzes   ADD COLUMN status ENUM('public', 'development') NOT NULL DEFAULT 'public';