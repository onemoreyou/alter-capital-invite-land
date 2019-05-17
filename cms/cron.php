<?php

/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2017 Anton Reznichenko
 *

 *
 *  File: 			landing zone / cron.php
 *  Description:	Lost clicks and orders processor
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/

// Loading configuration
define( 'PATH', dirname(__FILE__) . '/' );
require_once PATH . 'config.php';

// Generic CURL request
function curl( $url, $post = false ) {
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 );
	curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 );
	curl_setopt( $curl, CURLOPT_FAILONERROR, false );
	curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:63.0) Gecko/20100101 Firefox/63.0' );
	if ( $post ) {
		curl_setopt( $curl, CURLOPT_POST, 1 );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $post );
	}
	$result = curl_exec( $curl );
	curl_close( $curl );
	return $result;
}

// CLickServer request
function click( $data ) {
	
	// Small hack
	foreach ( $data as &$d ) $d = (string) $d;
	$data['key'] = defined( 'CLICKSRV_KEY' ) ? CLICKSRV_KEY : 'secret';
	$data = json_encode( $data );
	
	// Connecting
	$host = defined( 'CLICKSRV_HOST' ) ? CLICKSRV_HOST : 'localhost';
	$port = defined( 'CLICKSRV_PORT' ) ? CLICKSRV_PORT : 1862;
	$socket = socket_create( AF_INET, SOCK_STREAM, SOL_TCP );
	if ( ! $socket ) return false;
	$conn = socket_connect( $socket, $host, $port );	
	if ( ! $conn ) return false;
	
	// Get the result
	socket_write( $socket, $data, strlen( $data ) );		
	$result = socket_read( $socket, 100 );
	socket_close( $socket );	
	
	// Return the result
	if ( $result ) {
		$result = json_decode( $result, true );
		if ( $result['status'] == 'ok' ) {
			return isset( $result['id'] ) ? $result['id'] : true;
		} else return -1; 		
	} else return false;
	
}

// Process the lost clicks
define( 'PATH', dirname(__FILE__) . '/' );
if (file_exists( PATH . 'click.txt' )) {
	rename( PATH . 'click.txt', PATH . 'click-work.txt' );
	$clicks = file( PATH . 'click-work.txt' );
	$badclick = array();
	foreach ( $clicks as &$c ) if ( $req = trim( $c ) ) {
		if (defined( 'CLICKSERVER' )) {
			$res = click(json_decode( $req, true ));
			if ( $res === false ) $badclick[] = $req;
		} else {
			$res = curl( BASEURL . CC . '?' . $req );
			$res = explode( ':', trim( $res ), 2 );
			if (!( $res[0] == 'ok' || $res[0] == 'e' )) $badclick[] = $req;
		}
	} unset ( $c, $clicks );
	if ( $badclick ) file_put_contents( PATH . 'click.txt', implode( "\r\n", $badclick ) . "\r\n", FILE_APPEND | LOCK_EX  );
	unlink( PATH . 'click-work.txt' );
}

// Process the lost orders
if (file_exists( PATH . 'query.txt' )) {
	rename( PATH . 'query.txt', PATH . 'query-work.txt' );
	$query = file( PATH . 'query-work.txt' );
	$badquery = array();
	foreach ( $query as &$qu ) if ( $post = trim( $qu ) ) {
		list( $skey, $request ) = unserialize( $post );
		$result = curl( BASEURL . 'api/site/add.json?token=' . $skey, $request );
		$result = $result ? json_decode( $result, true ) : [];
		if (!isset( $result['status'] )) $badquery[] = $post;
	} unset ( $qu, $query );
	if ( $badquery ) file_put_contents( PATH .  'query.txt', implode( "\r\n", $badquery ) . "\r\n", FILE_APPEND | LOCK_EX  );
	unlink( PATH . 'query-work.txt' );
}