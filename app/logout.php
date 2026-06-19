<?php
ob_start();
include realpath(__DIR__ . '/../') . '/_init.php';

// Redirect, If user is not logged in
if (!is_loggedin()) {
  redirect(root_url() . '/index.php');
}

$url = SSOURL . "/logout.php?token=" . $session->data['token'];
$data = ["token" => $session->data['token']];
$response = json_decode(make_request($url, $data));

if ($response->code == 200) {
  unset($session->data['token']);
  session_destroy();
  header('Location: ' . str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), SSOURL . "/login.php?auth=" . APPID), true, 302);
} else {
  print_r($response);
}

?>
