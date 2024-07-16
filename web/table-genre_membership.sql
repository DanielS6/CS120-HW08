CREATE TABLE genre_membership (
	gm_song INT UNSIGNED NOT NULL, -- key to song_id
	gm_genre INT UNSIGNED NOT NULL, -- key to genre_id
	PRIMARY KEY (gm_song, gm_genre)
);