<?php

header( 'Content-Type: text/javascript; charset=utf-8' );
header( 'Cache-Control: no-cache, no-store' );

require_once 'init.php';

$faction = 'horde';

$filter = array( 'value.date' => new MongoDate( strtotime( gmdate( 'Y-m-d', strtotime( 'now - 2 hours' ) ) ) ), 'value.count' => array( '$gt' => 100 ) );
function prev_filter( $current ) {
	return array(
		'value.date' => array( '$lt' => $current['value']['date'] ),
		'value.item' => $current['value']['item']
	);
}

$total = $db->{'daily_' . $faction}->count( $filter );
$start = mt_rand( 0, $total - 2 );

foreach ( $db->{'daily_' . $faction}->find( $filter )->skip( $start )->limit( 1 ) as $daily ) {
	foreach ( $db->{'daily_' . $faction}->find( prev_filter( $daily ) )->sort( array( 'value.date' => -1 ) )->limit( 1 ) as $prev );

	$data = array();

	$item = get_item( $daily['value']['item'] );

	$data['vals'] = array( $prev['value']['average'], $daily['value']['average'] );
	$data['name'] = $item['name'];

	echo 'tickerCB(', json_encode( $data ), ')';
}
