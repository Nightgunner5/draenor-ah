<?php

date_default_timezone_set( 'UTC' );
ignore_user_abort( true );
set_time_limit( 0 );

require_once 'init.php';

$last_fetch = $db->vars->findOne( array( 'name' => 'last_fetch' ) );
if ( $last_fetch['value'] > time() - 1800 )
	exit;

$last_fetch['name'] = 'last_fetch';
$last_fetch['value'] = time();
$db->vars->save( $last_fetch );

$info_info = json_decode( http_get( 'https://eu.battle.net/api/wow/auction/data/draenor' ) );
$last_modified = $db->vars->findOne( array( 'name' => 'last_modified' ) );
if ( $last_modified['value'] >= $info_info->files[0]->lastModified ) {
	exit;
}

$last_modified['name'] = 'last_modified';
$last_modified['value'] = $info_info->files[0]->lastModified;
$db->vars->save( $last_modified );

$insert_id = new MongoDate( floor( time() / 3600 ) * 3600 );

$auction_data = json_decode( http_get( $info_info->files[0]->url ) );
foreach ( array( 'alliance', 'horde', 'neutral' ) as $faction ) {
	$db->{'auctions_' . $faction}->ensureIndex( 'auc', array( 'unique' => true ) );
	$db->{'auctions_' . $faction}->ensureIndex( 'item' );
	$db->{'auctions_' . $faction}->ensureIndex( 'inserted' );

	// echo count( $auction_data->$faction->auctions ), ' auctions for ', ucfirst( $faction ), "\n";

	foreach ( $auction_data->$faction->auctions as $auction ) {
		$auction = (array) $auction;
		$auction['inserted'] = $insert_id;
		$db->{'auctions_' . $faction}->save( $auction );
	}

	$db->{'count_' . $faction}->ensureIndex( 'value' );
	$db->command( array(
		'mapReduce' => 'auctions_' . $faction,
		'map' => new MongoCode( 'function () {
			emit(this.item, this.quantity);
		}' ),
		'reduce' => new MongoCode( 'function (key, values) {
			var result = 0;
			values.forEach(function (value) {
				result += value;
			});
			return result;
		}' ),
		'out' => array(
			'replace' => 'count_' . $faction
		)
	), array(
		'timeout' => PHP_INT_MAX
	) );

	$db->{'daily_' . $faction}->ensureIndex( 'value.date' );
	$db->{'daily_' . $faction}->ensureIndex( 'value.item' );
	$db->command( array(
		'mapReduce' => 'auctions_' . $faction,
		'map' => new MongoCode( 'function() {
			var pad = function(a){return a < 10 ? "0" + a : a};
			var date = this.inserted.getFullYear() + "-" + pad( 1 + this.inserted.getMonth() ) + "-" + pad( this.inserted.getDate() );
			emit( date + "_" + this.item, {
				count: this.quantity,
				sum: this.buyout ? this.buyout : this.bid,
				diff: 0,
				item: this.item,
				date: new Date( this.inserted - ( this.inserted % 86400000 ) )
			} );
		}' ),
		'reduce' => new MongoCode( 'function( key, values ) {
			var result = {item: values[0].item, date: values[0].date, count: 0, sum: 0, diff: 0};
			values.forEach( function( value ) {
				var delta = result.sum / Math.max( result.count, 1 ) - value.sum / value.count;
				var weight = ( result.count * value.count ) / ( result.count + value.count );

				result.diff += value.diff + delta * delta * weight;
				result.sum += value.sum;
				result.count += value.count;
			} );
			return result;
		}' ),
		'finalize' => new MongoCode( 'function( key, value ) {
			value.average = value.sum / value.count;
			value.variance = value.diff / value.count;
			value.stddev = Math.sqrt( value.variance );
			return value;
		}' ),
		'out' => array(
			'replace' => 'daily_' . $faction
		)
	), array( 'timeout' => PHP_INT_MAX ) );
}
