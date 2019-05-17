<?php

/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2018 Anton Reznichenko
 *

 *
 *  File: 			landing zone / tools.php
 *  Description:	Landing site CMS tools
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/

// Loading configuration
define( 'PATH', dirname(__FILE__) . '/' );
require_once PATH . 'config.php';

// Redirect to site
function go ( $url ) {
	
	if ( $_SERVER['QUERY_STRING'] ) {
		$url .= ( strpos( $url, '?' ) === false ) ? '?' : '&';
		$url .= $_SERVER['QUERY_STRING'];
	}
	
	header( 'Location: ' . $url );
	die();
	
}

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

// Check mobile
function is_mobile() {
	return preg_match( '/mobile|ip(hone|od|ad)|android|blackberry|iemobile|kindle|netfront|(hpw|web)os|fennec|minimo|opera m(obi|ini)|blazer|dolfin|dolphin|skyfire|zune|tablet|silk|playbook/i', $_SERVER['HTTP_USER_AGENT'] ) ? 1 : 0;	
}

// Check iOS	
function is_ios() {
	return preg_match( '/ip(hone|od|ad)/i', $_SERVER['HTTP_USER_AGENT'] ) ? 1 : 0;	
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

// end. =)