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

CREATE TABLE site_texts(
	text_key VARCHAR(50) PRIMARY KEY,
	text_value TEXT NOT NULL

);

CREATE TABLE info_blocks (
	id INT AUTO_INCREMENT PRIMARY KEY,
	image VARCHAR(255) NULL,
	title VARCHAR(100) NOT NULL,
	body TEXT NOT NULL,
	sort_order INT NOT NULL DEFAULT 0
);

CREATE TABLE faq_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    question   VARCHAR(255) NOT NULL,
    answer     TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0
);


INSERT INTO site_texts 
(text_key, text_value) 
VALUES
('hero_title', 'Welcome to QUIZZITY!'),
('hero_text',  'The place where you can be yourself!');

INSERT INTO info_blocks 
(image, title, body, sort_order) 
VALUES
('images/craft.jpg','Create Quizzes','Build your own quizzes in minutes and share them with the world.', 1),
(NULL,'Play & Learn','Try quizzes made by the community on any topic.', 2),
('images/leader.jpeg', 'Climb the Leaderboard', 'Score points, beat other players and become number one.', 3),
(NULL,'Join the QUIZZITY','Join our team, to be yourself!', 4);

INSERT INTO faq_items 
(question, answer, sort_order) 
VALUES
('What is Quizzity?', 'Quizzity is an online quiz platform where you can play quizzes made by us and compete on the leaderboards.', 1),
('How can I create my own quiz?', 'For now you can''t, only admins can create quizzes!', 2),
('Do I need to register an account to play Quizzity?', 'Yes, you do. You don''t have to register using Google.', 3);



ALTER TABLE users ADD COLUMN country CHAR(2) NULL;

ALTER TABLE questions ADD COLUMN image VARCHAR(255) NULL;
ALTER TABLE questions ADD COLUMN time_limit SMALLINT UNSIGNED NOT NULL DEFAULT 30;
ALTER TABLE quizzes   ADD COLUMN status ENUM('public', 'development') NOT NULL DEFAULT 'public';
ALTER TABLE users ADD COLUMN theme VARCHAR(10) NOT NULL DEFAULT 'light';

ALTER TABLE site_texts  ADD COLUMN text_value_lv TEXT NULL;
ALTER TABLE info_blocks ADD COLUMN title_lv VARCHAR(100) NULL, ADD COLUMN body_lv TEXT NULL;
ALTER TABLE faq_items   ADD COLUMN question_lv VARCHAR(255) NULL, ADD COLUMN answer_lv TEXT NULL;

UPDATE site_texts SET text_value_lv = 'Laipni lūdzam QUIZZITY!'      WHERE text_key = 'hero_title';
UPDATE site_texts SET text_value_lv = 'Vieta, kur vari būt pats!'    WHERE text_key = 'hero_text';

UPDATE info_blocks SET title_lv = 'Izveido viktorīnas',
    body_lv = 'Izveido savas viktorīnas dažās minūtēs un dalies ar tām ar pasauli.' WHERE sort_order = 1;
UPDATE info_blocks SET title_lv = 'Spēlē un mācies',
    body_lv = 'Izmēģini kopienas veidotās viktorīnas par jebkuru tēmu.'              WHERE sort_order = 2;
UPDATE info_blocks SET title_lv = 'Kāp līderu sarakstā',
    body_lv = 'Pelni punktus, uzvari citus spēlētājus un kļūsti par pirmo.'          WHERE sort_order = 3;
UPDATE info_blocks SET title_lv = 'Pievienojies QUIZZITY',
    body_lv = 'Pievienojies mūsu komandai, lai būtu pats!'                           WHERE sort_order = 4;

UPDATE faq_items SET question_lv = 'Kas ir Quizzity?',
    answer_lv = 'Quizzity ir tiešsaistes viktorīnu platforma, kurā vari spēlēt mūsu veidotās viktorīnas un sacensties līderu sarakstos.' WHERE sort_order = 1;
UPDATE faq_items SET question_lv = 'Kā es varu izveidot savu viktorīnu?',
    answer_lv = 'Pagaidām nevari, viktorīnas var veidot tikai administratori!' WHERE sort_order = 2;
UPDATE faq_items SET question_lv = 'Vai man jāreģistrē konts, lai spēlētu Quizzity?',
    answer_lv = 'Jā, jāreģistrē. Reģistrēties ar Google nav obligāti.'        WHERE sort_order = 3;