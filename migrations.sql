CREATE DATABASE QUIZZITY;
USE QUIZZITY;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user'
);

ALTER TABLE users
ADD COLUMN role ENUM('user', 'admin') NOT NULL DEFAULT 'user';


DROP TABLE IF EXISTS quizzes;

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
(1, 1, 'From which year is this meme?'),
(1, 2, 'Who is the person in this meme?'),
(1, 3, 'What does this meme mean?'),
(1, 4, 'What should the person in this meme say?'),
(1, 5, 'What is the name of this meme?'),
(1, 6, 'Which meme is shown in this picture?'),
(1, 7, 'Who is the character shown in this meme?'),
(1, 8, 'What movie or show is this meme from?'),
(1, 9, 'What is the original context of this meme?'),
(1, 10, 'Which phrase is commonly associated with this meme?'),
(1, 11, 'Who is the original person shown in this meme?'),
(1, 12, 'Which event made this image become a popular meme?'),
(1, 13, 'What is this meme usually used to express?'),
(1, 14, 'Which year did this meme become popular?'),
(1, 15, 'What is the name of this popular meme?'),   

(2, 1, 'Who made Minecraft movie?'),
(2, 2, 'Which movie stars Leonardo DiCaprio as Jack Dawson?'),
(2, 3, 'Who played Iron Man in the Marvel Cinematic Universe?'),
(2, 4, 'In which year was the first Spider-Man movie released?'),
(2, 5, 'Who is the main villain in Avengers: Infinity War?'),
(2, 6, 'Which movie has won the most Academy Awards?'),
(2, 7, 'Which company created the Pixar animated movies?'),
(2, 8, 'Who played Jack Sparrow in Pirates of the Caribbean?'),
(2, 9, 'What is the name of the kingdom in Frozen?'),
(2, 10, 'Which movie features the quote "I''ll be back"?'),
(2, 11, 'Who directed Jurassic Park?'),
(2, 12, 'Which movie won the Academy Award for Best Picture in 1998?'),
(2, 13, 'What is the name of the blue alien species in Avatar?'),
(2, 14, 'Who played Wednesday Addams in the 2022 Netflix series Wednesday?'),
(2, 15, 'Which movie follows a young wizard named Harry Potter?'),

(3, 1, 'What does CPU stand for?'),
(3, 2, 'What does RAM stand for?'),
(3, 3, 'Which programming language is mainly used for web page structure?'),
(3, 4, 'What does HTML stand for?'),
(3, 5, 'What does CSS stand for?'),
(3, 6, 'Which language is commonly used to add interactivity to websites?'),
(3, 7, 'What does SQL stand for?'),
(3, 8, 'Which company developed the Windows operating system?'),
(3, 9, 'What does GPU stand for?'),
(3, 10, 'Which device is used to connect a computer to a network?'),
(3, 11, 'What is the main purpose of an operating system?'),
(3, 12, 'Which of these is a programming language?'),
(3, 13, 'What does URL stand for?'),
(3, 14, 'Which company created the Android operating system?'),
(3, 15, 'What does PHP mainly run on?'),

(4, 1, 'Which company created Minecraft?'),
(4, 2, 'Which game features the character Mario?'),
(4, 3, 'What is the name of the main character in The Legend of Zelda?'),
(4, 4, 'Which game takes place in the fictional world of Los Santos?'),
(4, 5, 'What is the main objective in Minecraft Survival Mode?'),
(4, 6, 'Which game features the character Kratos?'),
(4, 7, 'What is the name of the main character in Red Dead Redemption 2?'),
(4, 8, 'Which game series is known for the character Master Chief?'),
(4, 9, 'Which company created the PlayStation?'),
(4, 10, 'Which game is famous for the battle royale mode called Fortnite Battle Royale?'),
(4, 11, 'What is the name of the city in Grand Theft Auto V?'),
(4, 12, 'Which game features characters called Creepers?'),
(4, 13, 'Which company created the Xbox?'),
(4, 14, 'Which game series includes the character Geralt of Rivia?'),
(4, 15, 'Which game is known for the phrase "Finish Him!"?'),

(5, 1, 'Which singer is known as the King of Pop?'),
(5, 2, 'Which band performed the song "Bohemian Rhapsody"?'),
(5, 3, 'Who played Jack Sparrow in Pirates of the Caribbean?'),
(5, 4, 'Which TV series features the characters Walter White and Jesse Pinkman?'),
(5, 5, 'Who played Wednesday Addams in the Netflix series Wednesday?'),
(5, 6, 'Which singer released the album "Thriller"?'),
(5, 7, 'Which band is known for the song "Smells Like Teen Spirit"?'),
(5, 8, 'Who played Harry Potter in the Harry Potter film series?'),
(5, 9, 'Which TV series is set in the fictional town of Hawkins?'),
(5, 10, 'Who played the character Neo in The Matrix?'),
(5, 11, 'Which singer is known for the song "Bad Guy"?'),
(5, 12, 'Which animated series features Homer, Marge, Bart, Lisa and Maggie?'),
(5, 13, 'Who played the Joker in The Dark Knight?'),
(5, 14, 'Which streaming service produced the series Stranger Things?'),
(5, 15, 'Which artist is known for the song "Shape of You"?');

CREATE TEMPORARY TABLE answer_data ( 
	quiz_id INT,
	question_number INT, 
	correct INT, 
	a1 VARCHAR(255), 
	a2 VARCHAR(255), 
	a3 VARCHAR(255), 
	a4 VARCHAR(255) 
);

INSERT INTO answer_data 
VALUES
(1,1,1,'2010','2005','2015','2020'), 
(1,2,1,'Drake','Ryan Reynolds','Tom Holland','Keanu Reeves'), 
(1,3,1,'It is used to express an idea or reaction humorously','It is a type of computer virus','It is a movie title','It is a programming language'), 
(1,4,1,'A humorous or relatable response','A mathematical equation','A movie review','A computer command'), 
(1,5,1,'Drake Hotline Bling','Distracted Boyfriend','Doge','Success Kid'), 
(1,6,1,'Distracted Boyfriend','Pepe the Frog','Doge','Rickroll'), 
(1,7,1,'Pepe the Frog','Mario','Sonic','Shrek'), 
(1,8,1,'The Office','Friends','Breaking Bad','Stranger Things'), 
(1,9,1,'A funny or unexpected situation','A scientific experiment','A political speech','A sports match'), 
(1,10,1,'This is fine','May the Force be with you','I am Groot','Why so serious?'), 
(1,11,1,'The person shown in the original photograph','The meme creator','A fictional character','A cartoon character'), 
(1,12,1,'A memorable or humorous event','A software update','A video game release','A weather event'), 
(1,13,1,'Humor or a reaction','A password','A recipe','A computer command'), 
(1,14,1,'2010s','1980s','1990s','2020s'), 
(1,15,1,'A popular internet meme','A programming language','A movie genre','A music album'),

(2,1,1,'Jared Hess','Christopher Nolan','James Cameron','Steven Spielberg'), 
(2,2,1,'Titanic','Inception','The Great Gatsby','Avatar'), 
(2,3,1,'Robert Downey Jr.','Chris Evans','Chris Hemsworth','Mark Ruffalo'), 
(2,4,1,'2002','2000','2004','2005'), 
(2,5,1,'Thanos','Loki','Ultron','Kang'), 
(2,6,1,'Ben-Hur, Titanic, and The Lord of the Rings: The Return of the King','Avatar','The Godfather','Gladiator'), 
(2,7,1,'Pixar','DreamWorks','Warner Bros.','Universal'), 
(2,8,1,'Johnny Depp','Orlando Bloom','Brad Pitt','Tom Cruise'), 
(2,9,1,'Arendelle','Agrabah','Wakanda','Asgard'), 
(2,10,1,'The Terminator','Predator','RoboCop','Die Hard'), 
(2,11,1,'Steven Spielberg','James Cameron','George Lucas','Christopher Nolan'), 
(2,12,1,'Titanic','Good Will Hunting','Saving Private Ryan','The Truman Show'), 
(2,13,1,'Na''vi','Kryptonians','Wookies','Vulcans'), 
(2,14,1,'Jenna Ortega','Emma Stone','Millie Bobby Brown','Anya Taylor-Joy'), 
(2,15,1,'Harry Potter','Percy Jackson','The Lord of the Rings','The Chronicles of Narnia');
