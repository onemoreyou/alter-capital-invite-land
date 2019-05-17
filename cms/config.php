<?php

/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2017 Anton Reznichenko
 *

 *
 *  File: 			landing zone / config.php
 *  Description:	Landing Zone Configuration
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/

// The base URL where "click.php" and "neworder" can be found
// Something like: http://bittraf.com/
define ( 'BASEURL', 'https://bittraf.com/' );

// The shop URL where the landings are
// Something like: http://shop.cpa/
//define ( 'SHOPURL', '' ); // Keep on the landing page
define ( 'SHOPURL', '' ); // Redirect to one index page

// Name of counting script
// Default: cc.php
define ( 'CC', 'cc.php' );

// Yandex Metrika ID
//define ( 'METRIKA', '' );

// Site Contol Key and Directory
//define ( 'CONTROL', 'yoursecretkey' );
//define ( 'BASEDIR', dirname(__FILE__).'/' );

// ClickServer settings
//define ( 'CLICKSERVER', true );			// Enable the ClickServer
//define ( 'CLICKSRV_HOST', 'localhost' );	// ClickServer bind address
//define ( 'CLICKSRV_PORT', 1862 );			// ClickServer bind port
//define ( 'CLICKSRV_KEY', 'secret' );		// ClickServer secret key (change required!)

// Additional configs
//define ( 'DEFCUR', 0 ); // Default currency ID for all sites

// External processing
// Must return array( 'i' => exti, 'u' => extu, 's' => exts )
function ext () {

    if ( $_GET['myext'] && is_numeric( $_GET['myext'] ) ) {
        return array( 'i' => 13, 'u' => preg_replace( '#[^0-9]+#i', '', $_GET['myext'] ), 's' => (int) $_GET['myextsrc'] );
    } elseif (isset( $_GET['click_id'] )) {
        if ( isset( $_GET['utm_source'] ) && $_GET['utm_source'] == 'biggico') {
            return array( 'i' => 2, 'u' => $_GET['click_id'], 's' => '1' );
        } elseif ( isset( $_GET['utm_campaign'] ) && $_GET['utm_campaign'] == 'mobytize') {
             return array( 'i' => 4, 'u' => $_GET['click_id'], 's' => '1' );
        } elseif (isset( $_GET['utm_source'] ) && $_GET['utm_source'] == 'leaddealer') {
             return array( 'i' => 8, 'u' => $_GET['click_id'], 's' => '1' );
//            utm_source=leaddealer&click_id={click_id}&utm_campaign={sub_id}&utm_content={sub_id2}
        } elseif (isset( $_GET['utm_source'] ) && $_GET['utm_source'] == 'cpaelectro') {
            return array( 'i' => 9, 'u' => $_GET['click_id'], 's' => '1' );
        } elseif (isset( $_GET['utm_source'] ) && $_GET['utm_source'] == '3snet') {
			return array( 'i' => 11, 'u' => $_GET['click_id'], 's' => '1' );
		} else {
            return array();
        }
        
    } elseif (isset( $_GET['utm_source'] )) {
		if ($_GET['utm_source'] == 'bearprofit') {
			return array( 'i' => 10, 'u' => $_GET['clickid'], 's' => '1' );
		} elseif ($_GET['utm_source'] == 'alfaleads') {
			return array( 'i' => 12, 'u' => $_GET['clickid'], 's' => '1' );
        } elseif ($_GET['utm_source'] == 'c3pa') {
            return array( 'i' => 14, 'u' => $_GET['clickid'], 's' => '1' );
        } elseif ($_GET['utm_source'] == 'cpatoday') {
            return array( 'i' => 15, 'u' => $_GET['clickid'], 's' => '1' );
        } elseif ($_GET['utm_source'] == 'adbid') {
            return array( 'i' => 16, 'u' => $_GET['clickid'], 's' => '1' );
        } elseif ($_GET['utm_source'] == 'olimob') {
            return array( 'i' => 18, 'u' => $_GET['clickid'], 's' => $_GET['utm_campaign'] );
        } elseif ($_GET['utm_source'] == 'clicklead') {
            return array( 'i' => 19, 'u' => $_GET['clickid'], 's' => '1' );
        } else {
			return array();
		}
		
	} elseif ( isset( $_GET['fin_id'] ) ) {
         return array( 'i' => 6, 'u' => $_GET['fin_id'], 's' => '1' );
    } elseif ( isset( $_GET['track_id'] ) ) {
        return array( 'i' => 3, 'u' => $_GET['track_id'], 's' => '2' );
    } else return array();

}

// That's all, folks
