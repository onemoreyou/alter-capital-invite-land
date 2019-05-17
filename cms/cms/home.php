<?php

/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2017 Anton Reznichenko
 *

 *
 *  File: 			landing zone / cms / home.php
 *  Description:	Landing Zone Homepage
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/

if (!defined( 'PATHDB' )) define( 'PATHDB', PATH . 'cms/db/' );
if (!defined( 'PATHOF' )) define( 'PATHOF', PATH . 'cms/offer/' );

// Generic CURL request
function curl( $url ) {
	$curl = curl_init( $url );
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt( $curl, CURLOPT_FOLLOWLOCATION, 1 );
	curl_setopt( $curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0' );
	$result = curl_exec( $curl );
	curl_close( $curl );
	return $result;
}

// Remote IP address
function ip() {
	static $ip;
	if (isset( $ip )) return $ip;
	if ( $_SERVER['HTTP_X_FORWARDED_FOR'] ) {
		if (strpos( $_SERVER['HTTP_X_FORWARDED_FOR'], ',' ) !== false ) {
			$xffd = explode( ',', $_SERVER['HTTP_X_FORWARDED_FOR'] );
			foreach ( $xffd as $xff ) {
				$xff = trim( $xff );
				$ip = filter_var( $xff, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
				if ( $ip ) break;
			}
		} else $ip = filter_var( $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	}
	if ( ! $ip ) $ip = filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );
	if ( ! $ip ) $ip = $_SERVER['REMOTE_ADDR'];
	return $ip;
}

// Likes and orders
function lo ( $oid ) {
	$base = abs(crc32(md5( $oid . date('dmY') )));
	$order = $base % 5312;
	$likes = $order + $base % 3210; 
	return array( $likes, $order );	
}

// User GEO data
function geo() {

	static $geo;
	if (isset( $geo )) return $geo;

	if (isset( $_GET['geo'] )) {
		$geo = substr( $_GET['geo'], 0, 2 );
		$geo = preg_replace( '/([^a-z]+)/i', '', $geo );
		$geo = strtolower( $geo );
		return $geo;
	}

	$ipt = ip();
	$ipp = explode( '.', $ipt );
	$ipp0 = min( max( 0, (int) $ipp[0] ), 255 );
	$ipp1 = min( max( 0, (int) $ipp[1] ), 255 );
	$ipp2 = min( max( 0, (int) $ipp[2] ), 255 );
	$ip = ( $ipp0 << 17 ) + ( $ipp1 << 9 ) + ( $ipp2 << 1 );
	$ip -= 262144;

	$geo = false;
	$ipf = fopen( PATH . 'geocode.txt', 'r' );
	fseek( $ipf, $ip );
	$geo = fread( $ipf, 2 );
	fclose( $ipf );
	if ( $geo == '--' ) $geo = false;
	return $geo;

}

// Preparing offers list
if ( @filemtime( PATHDB . 'offers.txt' ) < time() - 3600 ) {
	$data = curl( BASEURL . 'api/wm/pub.json' );
	if ( $data ) {
		$offers = json_decode( $data, true );
		file_put_contents( PATHDB . 'offers.txt', $data );
	} else $offers = json_decode( file_get_contents( PATHDB . 'offers.txt' ), true );
} else $offers = json_decode( file_get_contents( PATHDB . 'offers.txt' ), true );

// Preparing offer pictures
foreach ( $offers as $i => $o ) if ( @filemtime( PATHOF . 'pic'.$i.'.jpg' ) < $o['imgt'] )  {
	$picdata = curl( $o['image'] );
	file_put_contents( PATHOF . 'pic'.$i.'.jpg', $picdata );
	unset ( $picdata );
}

shuffle( $offers );
