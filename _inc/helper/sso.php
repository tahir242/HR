<?php

function requestData($url, $method = "GET", $postdata = null) {
	// create curl resource 
	$ch = curl_init($url); 
	
	$headers = array(
	  'Accept: application/json',
	);

	if ($method == "POST") {
		curl_setopt_array($ch, array(
			CURLOPT_POST            => 1,
			CURLOPT_HTTPHEADER      => $headers,
			CURLOPT_USERAGENT       => "spider",
			CURLOPT_POSTFIELDS      => $postdata,
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_VERBOSE         => 1
		));
	}else{
		curl_setopt_array($ch, array(
			CURLOPT_HTTPGET         => 1,
			CURLOPT_HTTPHEADER      => $headers,
			CURLOPT_USERAGENT       => "spider",
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_VERBOSE         => 1
		));
	}

	// $output contains the output string
	$output = curl_exec($ch);
	// close curl resource to free up system resources 
	curl_close($ch);
	return $output;
}

function make_request($url, $data){
    
    $headers = array('Content-Type' => 'application/x-www-form-urlencoded');
    $options = array('verify' => false);
    $response = WpOrg\Requests\Requests::post($url, $headers, $data, $options);
    return $response->body;
	
}
