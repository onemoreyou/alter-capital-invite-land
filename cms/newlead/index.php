<?php


function sendPUT($url, $data){
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  $response = json_decode(curl_exec($ch));
  curl_close($ch);
  return $response;
};
    
function sendLead($key, $site, $user_id, $board_id, $name, $phone, $email, $pid, $ip, $timezone ='',$country = ''){

  $url_API = "https://api.monday.com/v1/";
  $url_create = $url_API."boards/$board_id/pulses.json?api_key=$key";

  $data = json_encode(array(
      "user_id" => $user_id,
      "pulse" => ["name" => $name]
  ));

  $ch = curl_init($url_create);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

  $response = json_decode(curl_exec($ch));
  //var_dump($response);
  $pulse_id = $response->pulse->id;


  foreach ($response->column_values as $column){
    $cid = $column->cid;
    if($column->title == "Site"){
      $type = "text";
      //print $cid;
      $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
      $data = json_encode(array("pulse_id" => $pulse_id,"text" => $site));

      sendPUT($url_update,$data);
      continue;
    }

    if($column->title == "Pid"){
      $type = "text";
      //print $cid;
      $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
      $data = json_encode(array("pulse_id" => $pulse_id,"text" => $pid));

      sendPUT($url_update,$data);
      continue;
    }

    if($column->title == "Phone"){
      $type = "text";
      //print $cid;
      $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
      $data = json_encode(array("pulse_id" => $pulse_id,"text" => $phone));

      sendPUT($url_update,$data);
      continue;
    }

    if($column->title == "Email"){
      $type = "text";
      $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
      $data = json_encode(array("pulse_id" => $pulse_id,"text" => $email));

      sendPUT($url_update,$data);
      continue;
    }

    if($column->title == "IP"){
      $type = "text";
      //print $cid;
      $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
      $data = json_encode(array("pulse_id" => $pulse_id,"text" => "https://www.geoiptool.com/en/?ip=".$ip));

      sendPUT($url_update,$data);
      continue;
    }
    /*
    if($column->title == "World Clock"){
        $type = "text";
        $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
        break;
    }
    if($column->title == "Pid"){
        $type = "text";
        $url_update = $url_API."boards/$board_id/columns/$cid/$type.json?api_key=$key";
        break;
    }
    */
  }
  curl_close($ch);
}

// Write data to file
try {
  $data = json_decode( file_get_contents( 'php://input' ), true );
  $inp = file_get_contents('leads.json');
  $tempArray = json_decode($inp);
  array_push($tempArray, $data);
  $jsonData = json_encode($tempArray);
  file_put_contents('leads.json', $jsonData);
  http_response_code(200);

} catch (Exception $e) {
  $today = date('d.m.y H:m:s'); 
  $fpe = fopen('log.txt', 'a');
  fwrite($fpe, $today.' ');
  fwrite($fpe, $e->getMessage() . PHP_EOL . PHP_EOL);
  fwrite($fpe, json_encode($data) . PHP_EOL);
  fclose($fpe);
}

// Array to Object
$data = (object)$data;

var_dump($data);

// Read JSON request data
$name   = isset($data->firstName)   ? $data->firstName : isset($data->name)   ? $data->name : "no";
$phone  = isset($data->phone)       ? $data->phone : "";
$email  = isset($data->email)       ? $data->email : "";
$pid    = isset($data->pid)         ? $data->pid : "";
$ip     = isset($data->ip)          ? $data->ip : "";
//$timezone = "???";
$country = isset($data->country) ? $data->country : "";


// Read JSON config file
$config = json_decode(file_get_contents("config165461365468461348.json"));
$key = $config->key;
$site = $config->site;
$user_id = $config->user_id;
$board_id = $config->board_id;


/*
echo $name;
echo $phone;
echo $email;
echo $pid;
echo $ip;
//$timezone = "???";
//$country = "UY";



// Read JSON config file
var_dump($config);
echo $key;
echo $site;
echo $user_id;
echo $board_id;
*/





sendLead($key, $site,$user_id,$board_id, $name, $phone, $email, $pid, $ip, $timezone, $country);



