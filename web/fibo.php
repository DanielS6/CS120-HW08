<?php

function getSequence( int $length ): array {
	// Special cases for special lengths
	if ( $length === 0 ) {
		return [];
	} elseif ( $length === 1 ) {
		return [ 0 ];
	}

	$res = [ 0, 1 ];
	$last = 1;
	$prior = 0;
	for ( $iii = 2; $iii < $length; $iii++ ) {
		$new = $last + $prior;
		$prior = $last;
		$last = $new;
		$res[] = $new;
	}
	return $res;
}

function getResult() {
	if ( !isset( $_GET['n'] ) ) {
		return [
			'error' => 'Missing query parameter `n`',
		];
	}
	$length = $_GET['n'];
	if ( !is_numeric( $length ) ) {
		return [
			'error' => "`$length` is not a numeric string",
		];
	}
	$length = (int)$length;
	if ( $length < 0 ) {
		return [
			'error' => "`$length` should not be negative",
		];
	}
	return [
		'length' => $length,
		'fibSequence' => getSequence( $length ),
	];
}

header( 'Content-Type: application/json' );
echo json_encode( getResult(), JSON_PRETTY_PRINT );
