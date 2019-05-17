<?php
	
/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2018 Anton Reznichenko
 *

 *
 *  File: 			landing zone / multiplex-demo / index.php
 *  Description:	Redirect Site Multiplexor
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/	

// Load the core tools	
require_once '../tools.php';	

// Get current request info
$geo = geo();
$mobile = is_mobile();
$ios = is_ios();
$android = $mobile && !$ios;

// Go to location
switch ( $geo )  {
	
	case 'us':
	if ( $mobile ) {
		if ( $ios ) {
			go( 'https://ios.offer/' );
		} else go( 'http://android.offer/' );
	} else go( 'http://desktop.offer/' );

	case 'ru': case 'ua': case 'kz': case 'by':
	go( 'https://yet.another.offer/' );
	
	default:
	go( 'http://default.offer/' );
	
}