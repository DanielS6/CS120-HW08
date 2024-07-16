CREATE TABLE genres (
	genre_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	genre_name VARCHAR(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (genre_id),
	UNIQUE INDEX genre_name(genre_name) -- no duplicates
);