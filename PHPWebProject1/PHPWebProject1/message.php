<?php
date_default_timezone_set('Europe/Stockholm');

// API access key from Google API's Console
define( 'API_ACCESS_KEY', 'AIzaSyDrNX2VxJ-DSeS8dzDu6_yKitRakAdJQa4' );


$registrationIds = array( $_GET['id'] );
$registrationIds = array("eX2JNijFYQs:APA91bEzZxBfaFfBNT76NylQwH2anXdH1QGiGqyScnV7wlXg4aQzXuZL31Por2DNGTO_Cn46ooqN1-zfEWDsSXKpL2Jq2b3lcTj_diwUg17_2xYHI1AVEq3KVTEDGzgkn5-cszI9yJ_w");//API_ACCESS_KEY;

// prep the bundle
$msg = array
(
	'message' 	=> 'here is a message. message',
	'title'		=> 'This is a title. title',
	'subtitle'	=> 'This is a subtitle. subtitle',
	'tickerText'	=> 'Ticker text here...Ticker text here...Ticker text here',
	'vibrate'	=> 1,
	'sound'		=> 1,
	'largeIcon'	=> 'large_icon',
	'smallIcon'	=> 'small_icon'
);

$fields = array
(
	'registration_ids' 	=> $registrationIds,
	'data'			=> $msg
);
 
$headers = array
(
	'Authorization: key=' . API_ACCESS_KEY,
	'Content-Type: application/json'
);
 
$ch = curl_init();
curl_setopt( $ch,CURLOPT_URL, 'https://android.googleapis.com/gcm/send' );
curl_setopt( $ch,CURLOPT_POST, true );
curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
$result = curl_exec($ch );
curl_close( $ch );

echo $result;
