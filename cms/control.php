<?php

/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2017 Anton Reznichenko
 *

 *
 *  File: 			spacing zone / control.php
 *  Description:	Spacing Control File
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/

// Loading configuration
define( 'PATH', dirname(__FILE__) . '/' );
require_once PATH . 'config.php';
if (!defined( 'CONTROL' )) die('nocontrol');
if ( $_POST['key'] != CONTROL ) die('nokey');

//
// Site control procedures
//

// Create new site
function createsite() {

	$site = checkpath( $_POST['site'] );
	$force = isset( $_POST['force'] ) ? (int) $_POST['force'] : 0;
	$cando = (!file_exists( BASEDIR . $site )) || $force;
	if ( $site && $cando ) {
		@mkdir( BASEDIR.$site );
		unpackit( BASEDIR.$site );
		die( 'ok' );
	} else die( 'error' );

}

// Update existing site
function updatesite() {

	$site = checkpath( $_POST['site'] );
	if ( $site && file_exists( BASEDIR . $site ) ) {
		cleandir( BASEDIR.$site );
		unpackit( BASEDIR.$site );
		die( 'ok' );
	} else die( 'error' );

}

// Rename site
function renamesite() {

	$from = checkpath( $_POST['from'] );
	$to = checkpath( $_POST['to'] );
	if ( $from && $to && file_exists( BASEDIR . $from ) ) {
		@rename( BASEDIR.$from, BASEDIR.$to );
		die( 'ok' );
	} else die( 'error' );

}

// Remove site
function removesite() {

	$site = checkpath( $_POST['site'] );
	if ( $site && file_exists( BASEDIR . $site ) ) {
		cleandir( BASEDIR.$site );
		rmdir( BASEDIR.$site );
		die( 'ok' );
	} else die( 'error' );

}

//
// Routines
//

// Unpack the data
function unpackit ( $path ) {
	$data = json_decode( stripslashes( $_POST['data'] ), true );
	if ( ! $data ) return false;
	$path = rtrim( $path, '/' ) . '/';
	foreach ( $data as $p => &$d ) {
		$ff = $path . checkpath( $p );
		$dn = dirname( $ff );
		if (!file_exists( $dn )) mkdir( $dn, 0755, true );
		file_put_contents( $ff, base64_decode( $d ) );
	}
	return true;
}

// Check the site path
function checkpath ( $path ) {
	$path = filter_var( $path, FILTER_SANITIZE_STRING );
	$path = preg_replace( '/([\.]+)/', '.', $path );
	$path = trim( $path, '/' );
	return $path;
}

// Cleanup directory from files
function cleandir ( $path ) {

	if ( substr( $path, -1 ) != '/' ) $path .= '/';
	$d = opendir( $path );
	while ( ( $f = readdir( $d ) ) !== false ) {
		if ( $f == '.' || $f == '..' ) continue;
		if (is_dir( $path . $f )) {
			cleandir( $path . $f . '/' );
			@rmdir( $path . $f );
		} else @unlink( $path . $f );
	} closedir( $d );

}

//
// Actions
//

// Check the action and work
$action = filter_var( $_POST['action'], FILTER_SANITIZE_STRING );
switch ( $action ) {
	case 'create':	createsite();
	case 'update':	updatesite();
	case 'rename':	renamesite();
	case 'remove':	removesite();
	default:		die('error');
}

// end. =)