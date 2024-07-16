<?php

/**
 * DATABASE SETUP
 *
 * To allow both local development with docker and working on siteground, with
 * needing to change the code that interacts with the database, define global
 * constants for mysqli connection.
 */
if ( getenv( 'SONGS_DOCKER' ) !== false ) {
    define( 'SONGS_DB_HOST', 'db' );
    define( 'SONGS_DB_USER', 'root' );
    define( 'SONGS_DB_PASS', 'root' );
    define( 'SONGS_DB_NAME', 'songs_db' );
} else {
    define( 'SONGS_DB_HOST', 'localhost' );
    define( 'SONGS_DB_USER', 'u5ibud7ary6e1' );
    define( 'SONGS_DB_PASS', 'u8rxgbugnixy' );
    define( 'SONGS_DB_NAME', 'db1omb2fkpnbkl' );
}

function getDatabase(): mysqli {
	$dbCreator = new mysqli( SONGS_DB_HOST, SONGS_DB_USER, SONGS_DB_PASS );
	$dbCreator->query( 'CREATE DATABASE IF NOT EXISTS ' . SONGS_DB_NAME );
	$dbCreator->close();

	$db = new mysqli( SONGS_DB_HOST, SONGS_DB_USER, SONGS_DB_PASS, SONGS_DB_NAME );
	foreach ( [ 'songs', 'genres', 'genre_membership' ] as $table ) {
		if ( $db->query( "SHOW TABLES LIKE '$table';" )->num_rows === 0 ) {
			$db->query( file_get_contents( "./table-$table.sql" ) );
		}
	}
	// Values all use INSERT IGNORE, can be run multiple times each, but need
	// their own statements
	$valueStatements = explode( "\n", file_get_contents( './values.sql' ) );
	foreach ( $valueStatements as $statement ) {
		$db->query( $statement );
	}
	return $db;
}

function getSongsForGenres( mysqli $db ): array {
	$genres = $_REQUEST['genre'] ?? [];
	if ( $genres === [] ) {
		// None selected, no need for a DB query
		return [];
	}
	$genreInfo = $db->query( 'SELECT * FROM genres;' );
	$genreIds = array_map(
		static fn ( $arr ) => (int)$arr['genre_id'],
		array_filter(
			$genreInfo->fetch_all( MYSQLI_ASSOC ),
			static fn ( $arr ) => in_array( $arr['genre_name'], $genres )
		)
	);

	// We want something like
	// SELECT * FROM songs WHERE EXISTS (SELECT 1 FROM genre_membership
	// WHERE gm_song = song_id AND gm_genre IN (?, ?, ?) )
	// WHERE those ?s are replaced with the values of $genreIds
	// since we ***KNOW*** that $genreIds only contains integers (see the
	// (int) cast above) no need to do complicated parameter binding, can just
	// directly construct the statement with the values without a risk of SQL
	// injection
	return $db->query(
		'SELECT song_title, song_artist FROM songs WHERE EXISTS ' .
			'(SELECT 1 FROM genre_membership WHERE gm_song = song_id AND ' .
			'gm_genre IN (' . implode( ', ', $genreIds ) . '))'
	)->fetch_all( MYSQLI_ASSOC );
}

$dbAccess = getDatabase();
register_shutdown_function( function () {
	global $dbAccess;
	if ( $dbAccess ) {
		$dbAccess->close();
		$dbAccess = null;
	}
} );

function getBodyDisplay(): string {
	global $dbAccess;
	$songs = getSongsForGenres( $dbAccess );

	$songLis = array_map(
		static fn ( $song ) => '<li><span class="song">' . $song['song_title']
			. '</span> by <span class="artist">' . $song['song_artist'] . '</span></li>',
		$songs
	);

	if ( $songLis === [] ) {
		$list = '(none)';
	} else {
		$list = '<ul>' . implode( '', $songLis ) . '</ul>';
	}
	return '<h1>Matching songs</h1>' . $list;
}

// "Use css for an aesthetic display."
$style = <<<END
body {
	font-size: 20px;
}
li {
	padding-bottom: 10px;
	width: fit-content;
}
li:hover {
	font-size: 30px;
}
.song {
	color: red;
	font-weight: bold;
}
.artist {
	color: limegreen;
	font-weight: bold;
}
END;


header( 'Content-Type: text/html; charset=utf-8' );
echo "<html><head><style>$style</style></head><body>" . getBodyDisplay() . '</body></html>';