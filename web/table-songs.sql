CREATE TABLE songs (
	song_id INT UNSIGNED AUTO_INCREMENT NOT NULL,
	song_title VARCHAR(255) DEFAULT '' NOT NULL,
	song_artist VARCHAR(255) DEFAULT '' NOT NULL,
	song_year INT UNSIGNED NOT NULL,
	PRIMARY KEY(song_id),
	UNIQUE INDEX song_all(song_title, song_artist, song_year) -- no duplicates
);