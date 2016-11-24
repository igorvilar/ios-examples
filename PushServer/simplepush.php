<?php

// Put your device token here (without spaces):
$deviceToken = 'eada5820da66f33b10ea6b7dc599965519fe3c17443b750595ba66a9396624b1';

// Put your private key's passphrase here:
$passphrase = 'pushchat';

// Put your alert message here:
$message = 'Minha primeira notificação push!';

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

//Cip - I needed these too when uploading to a PHP server instead of trying this PHP from OSX's terminal
//stream_context_set_option($ctx, 'ssl', 'verify_peer', 'false');
//stream_context_set_option($ctx, 'ssl', 'allow_self_signed', 'true');
//stream_context_set_option($ctx, 'ssl', 'cafile', 'entrust_2048_ca.cer');

// Open a connection to the APNS server
$fp = stream_socket_client(
	'ssl://gateway.sandbox.push.apple.com:2195', $err,
	$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

// Create the payload body
$body['aps'] = array(
	'alert' => $message,
	'sound' => 'default'
	);

// Encode the payload as JSON
$payload = json_encode($body);

// Build the binary notification
//Cip - this doesn't work anymore
//$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
$msgInner =  chr(1) . pack('n', 32)  . pack('H*', $deviceToken) .
	     chr(2) . pack('n', strlen($payload)) . $payload;
	       //there's 4 bytes for $msgInner's length, might need to modify this if making larger messages
$msg = chr(2) . chr(0) . chr(0). pack('n', strlen($msgInner)) . $msgInner ;

// Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered' . PHP_EOL;
else
	echo 'Message successfully delivered' . PHP_EOL;

// Close the connection to the server
fclose($fp);
