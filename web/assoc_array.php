<?php

$hours = [
	'Sunday' => '11am - 2pm',
	'Monday' => '8am - 4pm',
	'Tuesday' => '8am - 4pm',
	'Wednesday' => '8am - 4pm',
	'Thursday' => '8am - 4pm',
	'Friday' => '8am - 2pm',
	'Saturday' => '10am - 2pm',
];

function getHourDisplay() {
	global $hours;
	$res = '';
	foreach ( $hours as $day => $times ) {
		$res .= "<span class='day'>$day</span><span class='times'>$times</span><br />";
	}
	return $res;
}

$style = <<<END
.day {
	display: inline-block;
	width: 80px;
}
.times {
	display: inline-block;
	width: 80px;
	text-align: end;
}
END;


header( 'Content-Type: text/html; charset=utf-8' );
echo "<html><head><style>$style</style></head><body>" . getHourDisplay() . '</body></html>';