<?php
function init(){
	$xml=simplexml_load_file("C:\Users\Raphael\git\chefkochWrapperToRDF-remote\library\login.xml") or die("Error: Cannot create object");
	
$username = (string)$xml -> username[0];
$sharedSecret = (string)$xml -> key[0];
$clientNonceData = openssl_random_pseudo_bytes(16);
$loginInfo = array(
	'authType' => "DigestQuick",
	'username' => $username,
	'clientNonce' => base64_encode($clientNonceData),
	'deviceId' => "TEST_123456"
);

$opts = array(
	'http' => array(
		'method' => 'POST',
		'header' => "Content-Type: application/json; charset=utf-8\r\n",
		'content' => json_encode($loginInfo)
	)
);

$context = stream_context_create($opts);
$r = file_get_contents('http://www.codecheck.info/WebService/rest/session/auth', false, $context);
$r = json_decode($r);
$sessionInfo = $r->result;

$usernameData = $username;  // is already in UTF-8 as it's ASCII
$nonceData = base64_decode($sessionInfo->nonce);
$mergedBytes = $usernameData . $nonceData . $clientNonceData;
$encodedMac = base64_encode(hash_hmac('sha256', $mergedBytes, hex2bin($sharedSecret), true));
$authHeader = "DigestQuick nonce=\"" . $sessionInfo->nonce . "\",mac=\"" . $encodedMac . "\"";

// reuse $authHeader for all requests until session expires

// --- Sample request ---

$opts = array(
	'http' => array(
	'method' => "GET",
	'header' => "Authorization: " . $authHeader . "\r\n" .
		"Content-Type: application/json; charset=utf-8\r\n"
  )
);
$context = stream_context_create($opts);
return $context;
}

function getCodecheckProductByEanGeneral ($ean){
	$ean = preg_replace ( '/[^0-9]+/', '', $ean );
	$context = init();
	$r = file_get_contents('http://www.codecheck.info/WebService/rest/prod/ean2/0/1/'.$ean, false, $context);
	$r = json_decode($r);
	print_r($r);
	return $r->result;
}
function getCodecheckProductByEanAlergene ($ean){
	$ean = preg_replace ( '/[^0-9]+/', '', $ean );
	$context = init();
	$r = file_get_contents('http://www.codecheck.info/WebService/rest/prod/ean2/0/33554432/'.$ean, false, $context);
	$r = json_decode($r);
	return($r->result);
}
function getCodecheckProductBySearch ($suchbegriff){
	$ean = preg_replace ( '/[^a-zA-Z-0-9]+/', '', $ean );
	$context = init();
	$r = file_get_contents('http://www.codecheck.info/WebService/rest/prod/search/0/0/30/'.$suchbegriff, false, $context);
	$r = json_decode($r);
	return ($r->result);
}

