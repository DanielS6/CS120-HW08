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

function getGenreOpts( mysqli $db ): array {
	$res = $db->query( 'SELECT * FROM genres;' );
	$rows = $res->fetch_all( MYSQLI_ASSOC );
	return array_map(
		static fn ( $arr ) => $arr['genre_name'],
		$rows
	);
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
	$opts = getGenreOpts( $dbAccess );

	$formChecks = array_map(
		static fn ( $opt ) => "<input type='checkbox' name='genre[]' id='check-$opt' value='$opt'>"
			. "<label for='check-$opt'>$opt</label><br/>",
		$opts
	);
	$form = '<form method="POST" action="./db2.php">'
		. implode( '', $formChecks )
		. '<button>Submit</button>'
		. '</form>';
	return $form;
}


header( 'Content-Type: text/html; charset=utf-8' );
echo "<html><body>" . getBodyDisplay() . '</body></html>';