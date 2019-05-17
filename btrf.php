<?php

/**
 * Взаимодействие с сервисом BittrafCPA.
 * @param $API_ID Ваш ID в системе
 * @param $API_KEY Ваш API-ключ
 * @param $app Приложение для взаимодействия (wm, ext или comp)
 * @param $func Запрашиваемая функция
 * @param $data Массив параметров
 * @param $format Формат возвращаемого результата (serial, json, raw)
 * @return array Результат выполнения
 */

$API_ID = "3";
$API_KEY = "a1de2d741b2dc412df3b09d1f7c693cd";
$APP = 'wm'; // 'ext' for agency | 'wm' for web master
$FUNC = 'push'; // 'add' for agency | 'push' for web master
 //Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}
 
//Make sure that the content type of the POST request has been set to application/json
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if(strcasecmp($contentType, 'application/json') != 0){
    throw new Exception('Content type must be: application/json');
}
 
//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));
 
//Attempt to decode the incoming RAW post data from JSON.
$decoded = json_decode($content, true);
 
//If json_decode failed, the JSON is invalid.
if(!is_array($decoded)){
    throw new Exception('Received content contained invalid JSON!');
}

apirequest($API_ID, $API_KEY, $APP, $FUNC, $decoded, 'json');

function apirequest ( $id, $key, $app, $func, $data = array(), $format = 'serial' ) {

    $url = 'https://bittraf.com/api/' . $app . '/' . $func . '.' . $format . '?id=' . $id . '-' . $key;
    $curl = curl_init( $url );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $curl, CURLOPT_TIMEOUT, 30 );
    curl_setopt( $curl, CURLOPT_POST, 1 );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
    $result = curl_exec( $curl );
    curl_close( $curl );

    switch ( $format ) {
        case 'raw':     return $result;
        case 'json':    return json_decode( $result, true );
        case 'text':    parse_str( $result, $a ); return $a;
        default:        return unserialize( $result );
    }

}
