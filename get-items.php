<?php

require_once 'init.php';

if ( isset( $_SERVER['REQUEST_URI'] ) )
	exit;

$items = array();
foreach ( array( 'alliance', 'neutral', 'horde' ) as $faction ) {
	foreach ( $db->{'auctions_' . $faction}->find( array(), array( 'item' => true ) ) as $item ) {
		$items[] = $item['item'];
	}
}

$items = array_filter( array_unique( $items ), function( $item ) {
	global $db;
	return $db->items->findOne( array( 'id' => $item ) ) == null;
} );
sort( $items );

$total = count( $items );
$i = 0;

foreach ( $items as $item ) {
	$item_data = get_item( $item );
	echo ++$i, '/', $total, ' - ', $item, ' = ', $item_data['name'], "\n";
}
