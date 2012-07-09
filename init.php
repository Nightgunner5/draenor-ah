<?php

date_default_timezone_set( 'UTC' );

function http_get( $url ) {
	static $ch = null;
	if ( $ch == null ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_USERAGENT, 'DraenorAH/0.1 (+http://nightgunner5.is-a-geek.net:1337/draenor-ah/)' );
	}
	curl_setopt( $ch, CURLOPT_URL, $url );
	return curl_exec( $ch );
}

$mongo = new Mongo;
$db = $mongo->selectDB( 'draenor-ah' );

$db->vars->ensureIndex( 'name', array( 'unique' => true ) );
$db->items->ensureIndex( 'id', array( 'unique' => true ) );
$db->items->ensureIndex( array( 'itemClass' => 1, 'itemSubClass' => 1 ) );

function get_item( $id ) {
	global $db;
	if ( $item = $db->items->findOne( array( 'id' => $id ) ) )
		return $item;
        sleep( 20 ); // 20 seconds between requests - otherwise, Blizzard might get mad and stop letting us use the service.
	$item = json_decode( http_get( 'https://eu.battle.net/api/wow/item/' . $id ) );
	$db->items->save( $item );
	return $db->items->findOne( array( 'id' => $id ) ); // Re-fetch so it's an array and not a stdObject
}
