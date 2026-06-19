<?php
include("_init.php");

// Set Current By Query String
if (isset($request->get['token'])) {
  try {
      
    // Set not logged in by default
    $token = $request->get['token'];
    $sig = $request->get['sig'];

    // See if they have the right signature
    if (hash_equals(hash_hmac('sha256', $token, APPID), $sig)) {
      $session->data['token'] = $token;
    }else{
      unset($session->data['token']);
      throw new Exception('Signature Not Match..');
    }

    header('Content-Type: application/json');
    echo json_encode(array('msg' => 'Redirecting to ' . APPNAME));
    exit();

  } catch (Exception $e) { 
    header('HTTP/1.1 422 Unprocessable Entity');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode(array('errorMsg' => $e->getMessage()));
    exit();
  }
}

if(isset($session->data['token'])) {
  redirect(APPDIRNAME."/dashboard.php");
}else{
  // Send the user for authenticate to the sso
  redirect(SSOURL . "/login.php?auth=".APPID."");
}

?>