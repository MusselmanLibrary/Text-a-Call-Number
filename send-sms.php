<?php
	session_start();
	// Require the bundled autoload file - the path may need to change
	// based on where you downloaded and unzipped the SDK
	require __DIR__ . '/vendor/autoload.php';
	use Twilio\Rest\Client;

	$sid    = "TWILIOACCOUNTSID"; 
	$token  = "TWILIOAUTHTOKEN"; 
	$client = new Client($sid, $token);
	$smsNumber = $_GET['number'];
	$itemTitle = $_GET['title'];
	$itemInfo = $_GET['holdings'];
	$smsMessage = "MUSCAT PLUS CALL NUMBER"."\n".$itemTitle.".\n".$itemInfo;
	$referer = $_GET[$referer];

	//echo $smsMessage;


	// Use the client to do fun stuff like send text messages!
	$client->messages->create(
	    // the number you'd like to send the message to
	    $smsNumber,
	    array(
	        // A Twilio phone number you purchased at twilio.com/console
	        'from' => '+1TWILIOSMSNUMBER',
	        // the body of the text message you'd like to send
	        'body' => $smsMessage
	    )
	);
	echo 'echo "<script>window.close();</script>"';
	echo '<p>Message sent. <a href="JavaScript:window.close()">Close</a>';
?>