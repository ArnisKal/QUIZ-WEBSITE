CREATE DATABASE QUIZZITY;
USE QUIZZITY;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE quizzes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    image VARCHAR(255) NULL,
    type ENUM('official','created') NOT NULL DEFAULT 'official',
    created_by INT UNSIGNED NOT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE questions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id INT UNSIGNED NOT NULL,
    question_number INT UNSIGNED NOT NULL,
    text VARCHAR(500) NOT NULL,

    FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    UNIQUE (quiz_id, question_number)
);

CREATE TABLE answers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id INT UNSIGNED NOT NULL,
    text VARCHAR(255) NOT NULL,
    is_correct TINYINT(1) NOT NULL DEFAULT 0,

    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

INSERT INTO quizzes (title, type, created_by)
VALUES
('Memes', 'official', 1),
('Movies', 'official', 1),
('Games', 'official', 1);

INSERT INTO questions (quiz_id, question_number, text)
VALUES
(1, 0, 'From which year is this meme?'),
(1, 1, 'Who is the person in this meme?'),
(1, 2, 'What does this meme mean?'),
(1, 3, 'What should the person in this meme say?'),
(1, 4, 'What is the name of this meme?'),
(1, 5, 'Which meme is shown in this picture?'),
(1, 6, 'Who is the character shown in this meme?'),
(1, 7, 'What movie or show is this meme from?'),
(1, 8, 'What is the original context of this meme?'),
(1, 9, 'Which phrase is commonly associated with this meme?'),
(1, 10, 'Who is the original person shown in this meme?'),
(1, 11, 'Which event made this image become a popular meme?'),
(1, 12, 'What is this meme usually used to express?'),
(1, 13, 'Which year did this meme become popular?'),
(1, 14, 'What is the name of this popular meme?');

(2, 0, 'Who directed the Minecraft Movie?'),
(2, 1, 'Which movie stars Leonardo DiCaprio as Jack Dawson?'),
(2, 2, 'Who played Iron Man in the Marvel Cinematic Universe?'),
(2, 3, 'In which year was the first Spider-Man movie released?'),
(2, 4, 'Who is the main villain in Avengers: Infinity War?'),
(2, 5, 'Which movie has won the most Academy Awards?'),
(2, 6, 'Which company created the Pixar animated movies?'),
(2, 7, 'Who played Jack Sparrow in Pirates of the Caribbean?'),
(2, 8, 'What is the name of the kingdom in Frozen?'),
(2, 9, 'Which movie features the quote "I''ll be back"?'),
(2, 10, 'Who directed Jurassic Park?'),
(2, 11, 'Which movie won the Academy Award for Best Picture in 1998?'),
(2, 12, 'What is the name of the blue alien species in Avatar?'),
(2, 13, 'Who played Wednesday Addams in the 2022 Netflix series Wednesday?'),
(2, 14, 'Which movie follows a young wizard named Harry Potter?');