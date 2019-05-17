<?php
	
/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2018 Anton Reznichenko
 *

 *
 *  File: 			landing zone / redirect-demo / index.php
 *  Description:	Redirect Site Demo
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/	
	
// Put site configs from panel here
define( 'OFFER', '12' );
define( 'SITE', '345' );
define( 'SKEY', '12345678901234567890123456789012' );
require_once '../cms.php';	
	
/*
	
Configurations:
- URLBASE:	flow URL with SubID parameter - %s
- DEFLOW:	default flow ID for search traffic
- ACTKEY:	secret key for cross-site requests

PostBack URL examples:
- Created:	http://landing.ru/url/?action=n&key=secret&click={subid}
- Accept:	http://landing.ru/url/?action=a&key=secret&click={subid}
- Cancel:	http://landing.ru/url/?action=c&key=secret&click={subid}
- Trash:	http://landing.ru/url/?action=t&key=secret&click={subid}

Please read help documentation for futher details
	
*/
	
// Offer redirect configs
define( 'URLBASE',	'http://r.redirect.ru/flow/?subid=%s' );
define( 'DEFLOW',	12 );
define( 'ACTKEY',	'secret' );

// Actions	
$click	= isset( $_GET['click'] ) ? (int) $_GET['click'] : false;
$action	= isset( $_GET['action'] ) ? filter_var( $_GET['action'], FILTER_SANITIZE_STRING ) : false;	
$ak = isset( $_GET['key'] ) ? filter_var( $_GET['key'], FILTER_SANITIZE_STRING ) : false;
	
// Work with actions
if ( $action ) {
	
	// Check the key
	if ( $ak != ACTKEY ) die( 'auth-error' );
	
	// Choose the action URL
	switch ( $action ) {

		// Creating new lead
		case 'n': case 'new': case 'create': case 'created': case 'make':
		$url = BASEURL . 'api/site/click.json?token=' . SITE . '-' . SKEY . '&click=' . $click;
		break;			

		// Mark lead as accepted
		case 'a': case 'accept': case 'accepted': case 'approve': case 'approved': case 'confirm': case 'confirmed':
		$url = BASEURL . 'api/site/status.json?token=' . SITE . '-' . SKEY . '&status=accept&click=' . $click;
		break;		

		// Mark lead as declined
		case 'c': case 'd': case 'decline': case 'declined': case 'cancel': case 'cancelled': case 'canceled':
		$url = BASEURL . 'api/site/status.json?token=' . SITE . '-' . SKEY . '&status=cancel&click=' . $click;
		break;

		// Mark lead as trash
		case 't': case 'trash': case 'error': case 'wrong': case 'reject': case 'bad':
		$url = BASEURL . 'api/site/status.json?token=' . SITE . '-' . SKEY . '&status=trash&click=' . $click;
		break;
	
	}

	// Send the request
	$result = curl( $url );
	$result = json_decode( $result, true );
	echo $result['status'];
	die();	
	
}
	
// Check for click IDs
if ( ! $vid ) {
	if (!( $flow || $ext )) {
		$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$url .= strpos( $url, '?' ) ? '&' : '?';
		$url .= 'flow=' . DEFLOW;
	} else $url = false; // Click error
} else $url = sprintf( URLBASE, $vid );

// Redirect if we have URL
if ( $url ) {
	header( 'Location: ' . $url );
} else $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	
// Redirect (reload) page
?><!doctype html>
<html lang="ru">
<head>
	<meta charset="utf-8" />
	<title>Идёт перенаправление</title>
	<style type="text/css">
		html, body { width: 100%; height: 100%; padding: 0; margin: 0; font-family: -apple-system, "San Francisco", "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 14px; }	
		#block { padding: 4em 2em; text-align: center; margin: 0 auto; max-width: 400px; }
		h1 { font-weight: normal; font-size: 2em; margin: 0 0 0.5em 0; padding: 0; }
		p { margin: 0 0 1.5em 0; padding: 0; }
		a#redirect { display: inline-block; padding: 1.2em 2.8em; margin: 0; text-decoration: none; border: solid 1px #ddd; background: #fafafa; color: #000; }
		a#redirect:hover { border-color: #888; background: #eee; }
	</style>
</head>
<body>
	<div id="block">
		<h1>Переход на внешний сайт</h1>
		<p>Если браузер не перенаправляет Вас автоматически, нажмите эту ссылку:</p>
		<a id="redirect" href="<?=$url;?>">Перейти на сайт</a>
	</div>
</body>
</html>