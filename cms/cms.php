<?php

/*******************************************************************************

 *
 * 	AlterVision CPA platform
 * 	Created by AlterVision - altervision.me
 *  Copyright (c) 2014-2018 Anton Reznichenko
 *

 *
 *  File: 			landing zone / cms.php
 *  Description:	Landing site simple CMS
 *  Author:			Anton 'AlterVision' Reznichenko - altervision13@gmail.com
 *

*******************************************************************************/

// Loading configuration
define( 'PATH', dirname(__FILE__) . '/' );
require_once PATH . 'config.php';

// Redefinitions
if (!defined( 'CC' )) define ( 'CC', 'cc.php' );

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

// API request
function api( $method, $data ) {
	$url = BASEURL . 'api/site/' . $method . '.json?token=' . SITE . '-' . SKEY;
	$data = curl( $url, $data );
	if ( $data ) {
		return json_decode( $data, true );
	} else return false;
}

// ClickServer request
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
		} else return false;
	} else return false;

}

// Add 5-second period
if (isset( $_GET['good'] )) {
	$gid = (int) $_GET['good'];
	if ( $gid ) {
		if (defined('CLICKSERVER')) {
			click(array( 'type' => 'good', 'id' => $gid ));
		} else curl( BASEURL . CC . '?g=' . $gid );
	}
	die();
}

// Check for site download
if ( isset( $_GET['load'] ) && defined('CONTROL') && $_GET['load'] == CONTROL ) {

	function files( &$data, $path ) {

		$d = opendir( './'.$path );
		while ( $f = readdir( $d ) ) {
			if ( $f == '.' || $f == '..' ) continue;
			$fn = $path ? $path . '/' . $f : $f;
			if ( ! is_dir( $fn )) {
				$data[$fn] = base64_encode(file_get_contents( './'.$fn ));
			} else files( $data, $fn );
		} closedir( $d );

	}

	$data = array();
	files( $data, '' );

	if ( isset( $_GET['format'] ) && $_GET['format'] == 'zip' ) {
		$fn = tempnam( dirname(__FILE__), 'zip' );
		$zip = new ZipArchive;
		if ( $zip->open( $fn, ZipArchive::CREATE ) ) {
			foreach ( $data as $n => &$d ) $zip->addFromString( $n, base64_decode($d) );
			$zip->close();
			header ( 'Content-type: application/zip' );
			header ( 'Content-disposition: attachment; filename=export.zip' );
			readfile( $fn );
			unlink( $fn );
			die();
		}
		unlink( $fn );
	}

	header ( 'Content-type: application/json' );
	header ( 'Content-disposition: attachment; filename=export.json' );
	echo json_encode( $data );
	die();

}

// Let's start!
header( 'Content-type: text/html; charset=UTF-8' );
$now = time();

// Showing the message
if ( $_GET['done'] == 'success' ) :
	if (!defined('THANKSPAGE')) :
?><html>
<head>
	<title>Ваш заказ принят!</title>
	<meta charset="utf-8" />
	<style type="text/css">body,h1,h2,p,div{font:normal 12px OpenSans,Segoe UI,Tahoma,sans-serif}body{padding:40px 10px;text-align:center}h1{font-size:34px;padding:0;margin:0 0 20px;color:#292}h2{font-size:20px;padding:0;margin:0 0 20px;color:#111}p{font-size:11px;padding:0;margin:0;color:#777}div{font-size:16px;padding:2px;margin:0;color:#822}</style>
</head>
<body>
	<h1>Ваш заказ принят! Спасибо!</h1>
	<h2>Менеджер перезвонит Вам для уточнения деталей в течение часа</h2>
<?php

	// Metrika
	if ( $_GET['mtrk'] && $mtrk = (int) $_GET['mtrk'] ) {
		setcookie( 'mtrk', $mtrk, $now + 300, '/' );
	} else $mtrk = $_COOKIE['mtrk'] ? (int) $_COOKIE['mtrk'] : false;

	// Google Analytics
	if ( $_GET['ga'] && $ga = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['ga'] ) ) {
		setcookie( 'ga', $ga, $now + 300, '/' );
	} else $ga = $_COOKIE['ga'] ? preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_COOKIE['ga'] ) : false;

	// Facebook
	if ( $_GET['fb'] && $fb = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['fb'] ) ) {
		setcookie( 'fb', $fb, $now + 300, '/' );
	} else $fb = $_COOKIE['fb'] ? preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_COOKIE['fb'] ) : false;

	// VK
	if ( $_GET['vk'] && $vk = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['vk'] ) ) {
		setcookie( 'vk', $vk, $now + 300, '/' );
	} else $vk = $_COOKIE['vk'] ? preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_COOKIE['vk'] ) : false;

	if ( $mtrk ) : ?><!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter<?=$mtrk;?> = new Ya.Metrika({ id:<?=$mtrk;?>, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/<?=$mtrk;?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter --><?
	elseif (defined( 'METRIKA' )) : ?><!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter<?=METRIKA;?> = new Ya.Metrika({ id:<?=METRIKA;?>, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/<?=METRIKA;?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter --><? endif;

	if ( $ga ) : ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?=$ga;?>"></script><script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments)}; gtag('js', new Date()); gtag('config', '<?=$ga;?>'); </script><?
	elseif (defined( 'GA' )) : ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?=GA;?>"></script><script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments)}; gtag('js', new Date()); gtag('config', '<?=GA;?>'); </script><? endif;

	if ( $fb ) : ?><script>!function(f,b,e,v,n,t,s) {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)}; if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0'; n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '<?=$fb;?>'); fbq('track', 'PageView'); fbq('track', 'Lead'); </script><noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=$fb;?>&ev=Lead&noscript=1" /></noscript><?
	elseif (defined( 'FB' )) : ?><script>!function(f,b,e,v,n,t,s) {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)}; if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0'; n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '<?=FB;?>'); fbq('track', 'PageView'); fbq('track', 'Lead'); </script><noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=FB;?>&ev=Lead&noscript=1" /></noscript><? endif;

	if ( $vk ) : ?><script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = 'https://vk.com/rtrg?p=<?=$vk;?>&event=lead';</script><?
	elseif (defined( 'VK' )) : ?><script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = 'https://vk.com/rtrg?p=<?=VK;?>&event=lead';</script><? endif;

?>
</body>
</html><?php
	else :
		require_once THANKSPAGE;
	endif;
	die();
elseif ( $_SERVER['QUERY_STRING'] == 'privacypolicy' ) :
?><!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
	<title>Политика конфиденциальности</title>
	<style type="text/css">body,html{min-height:100%;margin:0;padding:0;background:#eee}body{padding-top:40px}.block_more_info{width:800px;margin:0 auto 20px;background:#fff;font-family:Arial;padding:20px 40px 40px;border:1px solid #DADADA;line-height:20px}.block_more_info h1{color:#3B6A7C;margin-bottom:30px;text-align:center}.s1{font-style:italic;text-align:center;margin:40px 0 0;font-weight:700}h2{font-size:16px;margin-top:26px}</style>
</head>
<body>
	<div class="block_more_info">
		<h1>Политика конфиденциальности</h1>
		<h2>Защита личных данных</h2>
		<p>Для защиты ваших личных данных у нас внедрен ряд средств защиты, которые действуют при введении, передаче или работе с вашими личными данными.</p>
		<h2>Разглашение личных сведений и передача этих сведений третьим лицам</h2>
		<p>Ваши личные сведения могут быть разглашены нами  только в том случае это необходимо для: (а) обеспечения соответствия предписаниям закона или требованиям судебного процесса в нашем отношении ; (б) защиты наших прав или собственности (в) принятия срочных мер по обеспечению личной безопасности наших сотрудников или потребителей предоставляемых им услуг, а также обеспечению общественной безопасности. Личные сведения, полученные в наше распоряжение при регистрации, могут передаваться третьим организациям и лицам, состоящим с нами в партнерских отношениях для улучшения качества оказываемых услуг.  Эти сведения не будут использоваться в каких-либо иных целях, кроме перечисленных выше.   Адрес электронной почты, предоставленный вами при регистрации может использоваться для отправки вам сообщений или уведомлений об изменениях, связанных с вашей заявкой, а также  рассылки сообщений о происходящих в компании событиях и изменениях, важной информации о новых товарах и услугах и т.д.  Предусмотрена возможность отказа от подписки на эти почтовые сообщения.</p>
		<h2>Использование файлов «cookie»</h2>
		<p>Когда пользователь посещает веб-узел, на его компьютер записывается файл «cookie» (если пользователь разрешает прием таких файлов). Если же пользователь уже посещал данный веб-узел, файл «cookie» считывается с компьютера. Одно из направлений использования файлов «cookie» связано с тем, что с их помощью облегчается сбор статистики посещения. Эти сведения помогают определять, какая информация, отправляемая заказчикам, может представлять для них наибольший интерес. Сбор этих данных осуществляется в обобщенном виде и никогда не соотносится с личными сведениями пользователей.</p>
		<p>Третьи стороны, включая компании Google, показывают объявления нашей компании на страницах сайтов в Интернете. Третьи стороны, включая компанию  Google, используют cookie, чтобы показывать объявления, основанные на предыдущих посещениях пользователем наших вебсайтов и интересах в веб-браузерах. Пользователи могут запретить компаниям Google использовать cookie. Для этого необходимо посетить специальную страницу компании Google по этому адресу: http://www.google.com/privacy/ads/</p>
		<h2>Изменения в заявлении о соблюдении конфиденциальности</h2>
		<p>Заявление о соблюдении конфиденциальности предполагается периодически обновлять. При этом будет изменяться дата предыдущего обновления, указанная в начале документа. Сообщения об изменениях в данном заявлении будут размещаться на видном месте наших веб-узлов</p>
		<p class="s1">Благодарим Вас за проявленный интерес к нашей системе!</p>
	</div>
</body>
</html><?php
	die();
endif;

// Getting new flow ID
if ( $_GET['flow'] && $f = (int) $_GET['flow'] ) {
	$newflow = $f;
} elseif ( preg_match( "#^([0-9]+)#i", $_SERVER['QUERY_STRING'], $mf ) ) {
	$newflow = (int) $mf[0];
} else $newflow = false;

// Processing current flow ID
if ( $newflow ) {
	$flow = $newflow;
	setcookie( 'flow', $newflow, $now + 2592000, '/' );
} else $flow = (int) $_COOKIE['flow'];
$unique = ( $newflow && $newflow != $_COOKIE['flow'] ) ? true : false;

// Getting promo code
if ( $_GET['promo'] ) {
	$promo = preg_replace( '#[^0-9]+#i', '', $_GET['promo'] );
	setcookie( 'promo', $promo, $now + 2592000, '/' );
} elseif ( $_COOKIE['promo'] ) {
	$promo = preg_replace( '#[^0-9]+#i', '', $_COOKIE['promo'] );
} else $promo = false;

// Processing external ID from function
if (function_exists( 'ext' )) {
	$ee = ext();
	$exti = $ee['i'] ? $ee['i'] : 0;
	$extu = $ee['u'] ? $ee['u'] : '';
	$exts = $ee['s'] ? $ee['s'] : '';
} else {
	$exti = 0;
	$extu = $exts = '';
}

// Processing external ID by direct
if ( $newexti = (int) $_GET['exti'] ) {
	$exti = $newexti;
	$extu = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['extu'] );
	$exts = preg_replace( '#[^0-9]+#i', '', $_GET['exts'] );
}

// Set up EXT cookie
if ( $exti ) {
	if ( $_COOKIE['extd'] ) {
		$extd = explode( ':', $_COOKIE['extd'] );
		$unique = ( $extd[0] == $exti ) ? false : true;
	} else $unique = true;
	setcookie( 'extd', "$exti:$extu:$exts", $now + 86400, '/' );
} else list( $exti, $extu, $exts ) = $_COOKIE['extd'] ? explode( ':', $_COOKIE['extd'] ) : array( 0, '', '' );

// Processing Space Source
if ( $_GET['sp'] && $from = (int) $_GET['sp'] ) {
	setcookie( 'fromspace', $from, $now + 300, '/' );
} else $from = $_COOKIE['fromspace'] ? (int) $_COOKIE['fromspace'] : false;

// Test ID
if ( isset( $_GET['t'] ) && $test = (int) $_GET['t'] ) {
	setcookie( 'testid', $test, $now + 300, '/' );
} else $test = isset( $_COOKIE['testid'] ) ? (int) $_COOKIE['testid'] : false;

// Metrika
if ( $_GET['mtrk'] && $mtrk = (int) $_GET['mtrk'] ) {
	setcookie( 'mtrk', $mtrk, $now + 300, '/' );
} else $mtrk = $_COOKIE['mtrk'] ? (int) $_COOKIE['mtrk'] : false;

// Google Analytics
if ( $_GET['ga'] && $ga = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['ga'] ) ) {
	setcookie( 'ga', $ga, $now + 300, '/' );
} else $ga = $_COOKIE['ga'] ? preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_COOKIE['ga'] ): false;

// Facebook
if ( $_GET['fb'] && $fb = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['fb'] ) ) {
	setcookie( 'fb', $fb, $now + 300, '/' );
} else $fb = $_COOKIE['fb'] ? preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_COOKIE['fb'] ) : false;

// VK
if ( $_GET['vk'] && $vk = preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_GET['vk'] ) ) {
	setcookie( 'vk', $vk, $now + 300, '/' );
} else $vk = $_COOKIE['vk'] ? preg_replace( '#[^0-9A-Za-z\_\-]+#i', '', $_COOKIE['vk'] ) : false;

//
// Generic UTM analysis
//

// Get new UTM
$hasutm = false;
if ( isset( $_GET['utm_source'] ) && $_GET['utm_source'] ) $hasutm = $us = mb_substr(filter_var( $_GET['utm_source'], FILTER_SANITIZE_STRING ), 0, 250 );
if ( isset( $_GET['utm_campaign'] ) && $_GET['utm_campaign'] ) $hasutm = $uc = mb_substr(filter_var( $_GET['utm_campaign'], FILTER_SANITIZE_STRING ), 0, 250 );
if ( isset( $_GET['utm_content'] ) && $_GET['utm_content'] ) $hasutm = $un = mb_substr(filter_var( $_GET['utm_content'], FILTER_SANITIZE_STRING ), 0, 250 );
if ( isset( $_GET['utm_term'] ) && $_GET['utm_term'] ) $hasutm = $ut = mb_substr(filter_var( $_GET['utm_term'], FILTER_SANITIZE_STRING ), 0, 250 );
if ( isset( $_GET['utm_medium'] ) && $_GET['utm_medium'] ) $hasutm = $um = mb_substr(filter_var( $_GET['utm_medium'], FILTER_SANITIZE_STRING ), 0, 250 );

// Reset UTM or get the old ones
$ass = '(!)'; // hope nobody uses it in UTM ...
if ( $hasutm === false ) {
	if ( $_COOKIE['utm'] ) list( $us, $uc, $un, $ut, $um ) = explode( $ass, $_COOKIE['utm'] );
} else setcookie( 'utm', $us.$ass.$uc.$ass.$un.$ass.$ut.$ass.$um, $now + 86400, '/' );

// New Flow vs. ExtID
if ( $newflow && $exti ) {
	unset( $exti, $extd, $extu, $exts );
	setcookie( 'extd', '', $now - 2592000, '/' );
} elseif ( $exti && $flow ) {
	unset( $flow );
	setcookie( 'flow', '', $now - 2592000, '/' );
}

// Checking for the post requests
$error = $request = false;
if ( $_POST['task'] == 'process' ) {

	// Check mobile
	$mobile = preg_match( '/mobile|ip(hone|od|ad)|android|blackberry|iemobile|kindle|netfront|(hpw|web)os|fennec|minimo|opera m(obi|ini)|blazer|dolfin|dolphin|skyfire|zune|tablet|silk|playbook/i', $_SERVER['HTTP_USER_AGENT'] ) ? 1 : 0;

	// Creating post request array
	$request = array(
		'offer'		=> OFFER,
		'site'		=> SITE,
		'flow'		=> (int) $_POST['flow'],
		'from'		=> (int) $_POST['from'],
		'test'		=> (int) $_POST['test'],
		'us'		=> text2line( $_POST['us'] ),
		'uc'		=> text2line( $_POST['uc'] ),
		'un'		=> text2line( $_POST['un'] ),
		'ut'		=> text2line( $_POST['ut'] ),
		'um'		=> text2line( $_POST['um'] ),
		'exti'		=> (int) $_POST['exti'],
		'extu'		=> text2link( $_POST['extu'] ),
		'exts'		=> text2link( $_POST['exts'] ),
		'ip'		=> ip(),
		'ua'		=> text2line( $_SERVER['HTTP_USER_AGENT'] ),
		'name'		=> text2anum( $_POST['name'] ),
		'email'		=> text2mail( $_POST['email'] ),
		'phone'		=> text2num( $_POST['phone'] ),
		'comm'		=> text2line( $_POST['comment'] ),
		'country'	=> $_POST['country'] ? strtolower(substr( text2link( $_POST['country'] ), 0, 2 )) : '',
		'count'		=> defined( 'COUNT' ) ? COUNT : 1,
		'discount'	=> defined( 'DSCNT' ) ? DSCNT : 0,
		'more'		=> defined( 'MORE' ) ? MORE : 0,
		'mobile'	=> $mobile ? 1 : 0,
	);

	// Set currency only if required
	if (isset( $_POST['curr'] )) {
		$request['currency'] = (int) $_POST['curr'];
	} elseif (defined( 'DEFCUR' )) $request['currency'] = DEFCUR;

	// Additional fields
	if (isset( $_POST['amount'] ) ) {
		$ccc = (int) $_POST['amount'];
		if ( $ccc > 0 ) $request['count'] = $ccc;
	}
	if (isset( $_POST['address'] )) {
		$request['addr'] = text2anum( $_POST['address'] );
	} elseif (defined( 'ADDR' )) $request['addr'] = ADDR;
	if (isset( $_POST['index'] )) $request['index'] = text2anum( $_POST['index'] );
	if (isset( $_POST['area'] )) $request['area'] = text2anum( $_POST['area'] );
	if (isset( $_POST['city'] )) $request['city'] = text2anum( $_POST['city'] );
	if (isset( $_POST['street'] )) $request['street'] = text2anum( $_POST['street'] );
	if (isset( $_POST['promo'] )) $request['promo'] = text2num( $_POST['promo'] );

	// Request post processing
	checkxss( $request );
	if (function_exists( 'requestprocess' )) $request = requestprocess( $request );

	// Checkign for errors or success
	$ajax = isset( $_GET['ajax'] ) ? 1 : 0;
	$result = api( 'add', $request );
	if ( ! $result ) file_put_contents( PATH.'query.txt', serialize(array( SITE.'-'.SKEY, $request )) . "\r\n", FILE_APPEND | LOCK_EX  );

	// Choose action to display
	if ( $result && !$result['id'] ) {

		// Simple error info
		switch ( $result['error'] ) {
         	case 'data':	$error = 'Ошибка заполнения формы!'; break;
         	case 'key':		$error = 'Ошибка сервера: поставщик неизвестен ...'; break;
         	case 'site':	$error = 'Ошибка сервера: сайт неизвестен ...'; break;
         	case 'offer':	$error = 'Ошибка сервера: товар неизвестен ...'; break;
         	case 'db':		$error = 'Внутренняя ошибка сервера ...'; break;
         	case 'ban':		$error = 'Вы занесены в чёрный список!';	break;
         	case 'security':$error = 'Заказ отклонён службой безопасности системы!';	break;
         	case 'traffic':	$error = 'Ошибка: товар закончился :(';	break;
         	default:		$error = 'Произошла неизвестная ошибка сервера ...';
		}

		// Give the AJAX result
		if ( $ajax ) {
			$info = json_encode(array( 'status' => 'error', 'code' => $result['error'], 'error' => $error ));
			echo json_encode( $info , JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
			die();
		}

	} else {

		// Redirect URL
		$redurl = defined( 'DONEURL' ) ? DONEURL : SHOPURL.'?done=success&id=' . $result['id'];
		$redurl = str_replace( '%id', $result['id'], $redurl );

		// Pin code
		if (isset( $result['pin'] )) {
			$pin = hash_hmac( 'sha256', sprintf( '%s-%s', $result['id'], $result['pin'] ), SKEY );
			if ( $dmn = parse_url( $redurl, PHP_URL_HOST ) ) {
				setcookie( 'pin'.$result['id'], $pin, $now+2592000, '/', $dmn );
			} else setcookie( 'pin'.$result['id'], $pin, $now+2592000, '/' );
		}

		// Redirect user to success page
		if ( $ajax ) {
			$result['url'] = $redurl;
			echo json_encode( $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
		} else header( 'Location: '. $redurl );
		die();

	}

} elseif ( $flow || $exti ) {

	if (defined( 'CLICKSERVER' )) {

		$req = array(
			'type'		=> 'click',
			'offer'		=> OFFER,
			'site'		=> SITE,
			'sib'		=> $from ? $from : "0",
			'space'		=> "0",
			'unique'	=> $unique ? 1 : 0,
			'ip'		=> sprintf( "%u", ip2long( ip() ) ),
			'flow'		=> (int) $flow,
			'test'		=> (int) $test,
			'exti'		=> (int) $exti,
			'extu'		=> $extu ? $extu : "",
			'exts'		=> $exts ? $exts : "",
			'tm'		=> time(),
			'us'		=> $us ? $us : "",
			'uc'		=> $uc ? $uc : "",
			'un'		=> $un ? $un : "",
			'ut'		=> $ut ? $ut : "",
			'um'		=> $um ? $um : "",
		);

		$vid = click( $req );
		if ( $vid === false ) file_put_contents( PATH.'click.txt', json_encode( $req )."\r\n", FILE_APPEND | LOCK_EX  );

	} else {

		$req = 'o='.OFFER.'&s=' . SITE . '&ip=' . sprintf( "%u", ip2long( ip() ) );
		if ( $from ) $req .= '&b=' . $from;
		if ( $flow ) $req .= '&f=' . $flow;
		if ( $test ) $req .= '&t=' . $test;
		if ( $exti ) $req .= '&ei=' . $exti;
		if ( $extu ) $req .= '&eu=' . $extu;
		if ( $exts ) $req .= '&es=' . $exts;
		if ( $unique ) $req .= '&u=1';
		if ( $us ) $req .= '&us='.rawurlencode($us);
		if ( $uc ) $req .= '&uc='.rawurlencode($uc);
		if ( $un ) $req .= '&un='.rawurlencode($un);
		if ( $ut ) $req .= '&ut='.rawurlencode($ut);
		if ( $um ) $req .= '&um='.rawurlencode($um);
		$res = curl( BASEURL . CC . '?' . $req );
		$res = explode( ':', $res, 2 );
		if (!( $res[0] == 'ok' || $res[0] == 'e' )) file_put_contents( PATH.'click.txt', $req . "&tm=".time()."\r\n", FILE_APPEND | LOCK_EX  );
		$vid = $res[1];

	}

}

// Show only the code
if ( isset( $_GET['only'] ) && $_GET['only'] == 'code' ) die( 'ok' );

// Params for the form
$params = '<input type="hidden" name="task" value="process" />'."\n";
if ( $flow ) $params .= '<input type="hidden" name="flow" value="'.$flow.'" />'."\n";
if ( $from ) $params .= '<input type="hidden" name="from" value="'.$from.'" />'."\n";
if ( $test ) $params .= '<input type="hidden" name="test" value="'.$test.'" />'."\n";
if ( $exti ) $params .= '<input type="hidden" name="exti" value="'.$exti.'" />'."\n";
if ( $extu ) $params .= '<input type="hidden" name="extu" value="'.$extu.'" />'."\n";
if ( $exts ) $params .= '<input type="hidden" name="exts" value="'.$exts.'" />'."\n";
if ( $promo ) $params .= '<input type="hidden" name="promo" value="'.$promo.'" />'."\n";
if ( $us ) $params .= '<input type="hidden" name="us" value="'.str_replace( '"', '&quot;', $us ).'" />'."\n";
if ( $uc ) $params .= '<input type="hidden" name="uc" value="'.str_replace( '"', '&quot;', $uc ).'" />'."\n";
if ( $un ) $params .= '<input type="hidden" name="un" value="'.str_replace( '"', '&quot;', $un ).'" />'."\n";
if ( $ut ) $params .= '<input type="hidden" name="ut" value="'.str_replace( '"', '&quot;', $ut ).'" />'."\n";
if ( $um ) $params .= '<input type="hidden" name="um" value="'.str_replace( '"', '&quot;', $um ).'" />'."\n";
if (defined( 'SETGEO' )) $params .= '<input type="hidden" name="country" value="'.SETGEO.'" />'."\n";
if (defined( 'SETCUR' )) $params .= '<input type="hidden" name="curr" value="'.SETCUR.'" />'."\n";

// Params array and JSON
$pa = array( 'task' => 'process' );
if ( $flow ) $pa['flow'] = $flow;
if ( $from ) $pa['from'] = $from;
if ( $test ) $pa['test'] = $test;
if ( $exti ) $pa['exti'] = $exti;
if ( $extu ) $pa['extu'] = $extu;
if ( $exts ) $pa['exts'] = $exts;
if ( $promo ) $pa['promo'] = $promo;
if ( $us ) $pa['us'] = $us;
if ( $uc ) $pa['uc'] = $uc;
if ( $un ) $pa['un'] = $un;
if ( $ut ) $pa['ut'] = $ut;
if ( $um ) $pa['um'] = $um;
$pj = json_encode( $pa );

if (!function_exists('checkpromo')) {
	function checkpromo() {
		$promo = (string) $_POST['promo'];
		return ( strlen( $promo ) == 10 && $promo{0} == '2' ) ? true : false;
	}
}

//
// Data sanitize functions
//

// Remote IP address
function ip() {
	static $ip;
	if (isset( $ip )) return $ip;
	$ip = remoteip( $_SERVER );
	return $ip;
}

// Get remote IP address
function remoteip ( $server ) {
	if ( goodip ( $server['HTTP_CF_CONNECTING_IP'] ) && !privateip( $server['HTTP_CF_CONNECTING_IP'] )) return $server['HTTP_CF_CONNECTING_IP'];
	if ( goodip ( $server['HTTP_CLIENT_IP'] ) && !privateip( $server['HTTP_CLIENT_IP'] )) return $server['HTTP_CLIENT_IP'];
	if ( goodip ( $server['HTTP_X_FORWARDED_FOR'] ) && !privateip( $server['HTTP_X_FORWARDED_FOR'] )) return $server['HTTP_X_FORWARDED_FOR'];
	if ( goodip ( $server['HTTP_X_REAL_IP'] ) && !privateip( $server['HTTP_X_REAL_IP'] )) return $server['HTTP_X_REAL_IP'];
	return $server['REMOTE_ADDR'];
}

// Check IP to be valid
function goodip ( $ip ) {
	return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ? 1 : 0;
}

// Check IP to be public
function publicip ( $ip ) {
	return filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ? 1 : 0;
}

// Check IP to be private
function privateip ( $ip ) {
	return publicip( $ip ) ? 0 : 1;
}

// Cleanup text to aplha-numeric
function text2anum ( $text ) {
	$text = filter_var( $text, FILTER_SANITIZE_STRING );
	$text = trim ( $text );
	$text = preg_replace( '#[^\w\_\-\.]+#iu', ' ', $text );
	$text = preg_replace( '#([ ]+)#', ' ', $text );
	$text = stripslashes( $text );
	$text = htmlspecialchars ( $text );
	return $text ? $text : '';
}

// Cleanup text to numeric
function text2num ( $text ) {
	$text = filter_var( $text, FILTER_SANITIZE_STRING );
	$text = preg_replace( '#[^0-9]+#iu', '', $text );
	return $text ? $text : 0;
}

// Cleanup text to line
function text2line ( $text ) {
	$text = filter_var( $text, FILTER_SANITIZE_STRING );
	$text = stripslashes( $text );
	$text = mb_substr( $text, 0, 250 );
	return $text ? $text : '';
}

// Cleanup text to link or ID
function text2link ( $text ) {
	$text = filter_var( $text, FILTER_SANITIZE_STRING );
	$text = stripslashes( $text );
	$text = preg_replace( '#[^0-9A-Za-z\-\_\.]+#i', '', $text );
	return $text ? $text : '';
}

// Cleanup text to email
function text2mail ( $email ) {
	$email = filter_var( $email, FILTER_SANITIZE_EMAIL );
	return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? $email : '';
}

// Checking value for hacking attempt
function checkxss ( &$z ) {

	if (isset( $_SERVER['HTTP_X_ARACHNI_SCAN_SEED'] )) xssdie();
	if ( $z['phone'] == '1' ) xssdie();

	foreach ( $z as $x ) {
		if ( stripos( $x, 'arachni') !== false ) xssdie();
		if ( stripos( $x, 'sleep ') !== false ) xssdie();
		if ( stripos( $x, 'select convert') !== false ) xssdie();
		if ( stripos( $x, 'win.ini') !== false ) xssdie();
		if ( stripos( $x, 'boot.ini') !== false ) xssdie();
		if ( stripos( $x, 'proc version') !== false ) xssdie();
		if ( stripos( $x, 'time.sleep') !== false ) xssdie();
		if ( stripos( $x, 'vunlweb.com') !== false ) xssdie();
		if ( stripos( $x, 'x-crlf-safe') !== false ) xssdie();
		if ( stripos( $x, 'pg_sleep') !== false ) xssdie();
		if ( stripos( $x, 'passwd') !== false ) xssdie();
		if ( stripos( $x, 'web-inf') !== false ) xssdie();
		if ( stripos( $x, 'web.xml') !== false ) xssdie();
		if ( stripos( $x, 'self environ') !== false ) xssdie();
		if ( stripos( $x, 'waitfor') !== false ) xssdie();
		if ( stripos( $x, 'response.write') !== false ) xssdie();
		if ( stripos( $x, 'print ') !== false ) xssdie();
		if ( stripos( $x, ' or ') !== false ) xssdie();
		if ( stripos( $x, 'inexistent') !== false ) xssdie();
		if ( stripos( $x, 'nslookup') !== false ) xssdie();
	}

}

// Die of the XSS atack
function xssdie() {

	header( 'Content-type: text/plain' );
	header( 'X-Sorry: no hacking on this site please' );
	header( 'X-Even-More-Sorry: dont be upset' );
	header( 'X-Cute: here is a kitten for you ^^' );
?>

Простите, на этом сайте пока не поддерживаются XSS-атаки :(
Не расстраивайтесь! Вот вам миленького котика ;)

           .               ,.
          T."-._..---.._,-"/|
          l|"-.  _.v._   (" |
          [l /.'_ \; _~"-.`-t
          Y " _(o} _{o)._ ^.|
          j  T  ,--.  T  ]
          \  l ( /-^-\ ) !  !
           \. \.  "~"  ./  /c-..,__
             ^r- .._ .- .-"  `- .  ~"--.
              > \.                      \
              ]   ^.                     \
              3  .  ">            .       Y
 ,.__.--._   _j   \ ~   .         ;       |
(    ~"-._~"^._\   ^.    ^._      I     . l
 "-._ ___ ~"-,_7    .Z-._   7"   Y      ;  \        _
    /"   "~-(r r  _/_--._~-/    /      /,.--^-._   / Y
    "-._    '"~~~>-._~]>--^---./____,.^~        ^.^  !
        ~--._    '   Y---.                        \./
             ~~--._  l_   )                        \
                   ~-._~~~---._,____..---           \
                       ~----"~       \
                                      \

We are very sorry but XSS-attacks are not supported yet :(
Dont be upset, here is a cure kitten for you ;)

<?

	$info = '------ '.date( 'Y-m-d H:i:s').' ------';
	$info .= "\nServer: ".var_export( $_SERVER, true );
	if ( $_POST ) $info .= "\nPOST: ".var_export( $_POST, true );
	$info .= "\n\n";
	file_put_contents( dirname(__FILE__) . '/xss-log.txt', $info, FILE_APPEND | LOCK_EX );

	die();

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

	if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) && $_SERVER['HTTP_CF_IPCOUNTRY'] ) {
		$geo = strtolower( $_SERVER['HTTP_CF_IPCOUNTRY'] );
		return $geo;
	}

	$ipt = ip();
	$ipp = explode( '.', $ipt );
	$ipp0 = min( max( 0, (int) $ipp[0] ), 255 );
	$ipp1 = min( max( 0, (int) $ipp[1] ), 255 );
	$ipp2 = min( max( 0, (int) $ipp[2] ), 255 );
	$ip = ( $ipp0 << 17 ) + ( $ipp1 << 9 ) + ( $ipp2 << 1 );

	$geo = false;
	$ipf = fopen( PATH . 'geocode.txt', 'r' );
	fseek( $ipf, $ip );
	$geo = fread( $ipf, 2 );
	fclose( $ipf );
	if ( $geo == 'zz' ) $geo = false;
	return $geo;

}

// Page footer
function footer() {

	global $error, $vid, $mtrk, $ga, $fb, $vk;  // I know I'll burn in hell for this ...
	if ( $error ) echo '<script type="text/javascript">alert("Невозможно выполнить заказ.\\n'.$error.'");</script>';

	if ( $vid ) : ?><script type="text/javascript">
function noregret(){var xx = new XMLHttpRequest();xx.open("GET","?good=<?=$vid;?>&z="+Math.random(),true);xx.send(null);setTimeout("noregret()",5000);}function trytosee(){isd&&(setTimeout("noregret()",5000),isd=!1)}var isd=!0;window.onload=function(){document.hidden||document.msHidden||document.webkitHidden||document.mozHidden?window.onfocus=function(){trytosee(),window.onfocus=null}:trytosee()};
</script><? endif;

	if ( $mtrk ) : ?><!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter<?=$mtrk;?> = new Ya.Metrika({ id:<?=$mtrk;?>, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/<?=$mtrk;?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter --><?
	elseif (defined( 'METRIKA' )) : ?><!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter<?=METRIKA;?> = new Ya.Metrika({ id:<?=METRIKA;?>, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/<?=METRIKA;?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter --><? endif;

	if ( $ga ) : ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?=$ga;?>"></script><script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments)}; gtag('js', new Date()); gtag('config', '<?=$ga;?>'); </script><?
	elseif (defined( 'GA' )) : ?><script async src="https://www.googletagmanager.com/gtag/js?id=<?=GA;?>"></script><script> window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments)}; gtag('js', new Date()); gtag('config', '<?=GA;?>'); </script><? endif;

	if ( $fb ) : ?><script>!function(f,b,e,v,n,t,s) {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)}; if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0'; n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '<?=$fb;?>'); fbq('track', 'PageView'); setTimeout( fbq, 30000, 'track', 'ViewContent' );</script><noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=$fb;?>&ev=PageView&noscript=1" /></noscript><?
	elseif (defined( 'FB' )) : ?><script>!function(f,b,e,v,n,t,s) {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)}; if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0'; n.queue=[];t=b.createElement(e);t.async=!0; t.src=v;s=b.getElementsByTagName(e)[0]; s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js'); fbq('init', '<?=FB;?>'); fbq('track', 'PageView'); setTimeout( fbq, 30000, 'track', 'ViewContent' );</script><noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=FB;?>&ev=PageView&noscript=1" /></noscript><? endif;

	if ( $vk ) : ?><script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = 'https://vk.com/rtrg?p=<?=$vk;?>&event=visit';</script><?
	elseif (defined( 'VK' )) : ?><script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = 'https://vk.com/rtrg?p=<?=VK;?>&event=visit';</script><? endif;

	if (function_exists('customfooter')) customfooter();

}

// end. =)