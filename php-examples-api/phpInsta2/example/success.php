<?php

require '../instagram.class.php';

// Initialize class
$instagram = new Instagram(array(
  'apiKey'      => '63239a4c8214a85b9153d00c1a2a00f',
  'apiSecret'   => '4f6256459c5c417dbe48b8609e1cc123',
  'apiCallback' => 'http://localhost/ws/Works/instagram/phpInsta2/example/success.php'
));

// Receive OAuth code parameter
$code = $_GET['code'];

// Check whether the user has granted access
if (true === isset($code)) {

  // Receive OAuth token object
  $data = $instagram->getOAuthToken($code);
  echo 'Your username is: '.$data->user->username;

  // Store user access token
  $instagram->setAccessToken($data);

  // Now you can call all authenticated user methods
  // Get all user likes
  $likes = $instagram->getUserLikes();

  // Display all user likes
  foreach ($likes->data as $entry) {
    echo "<img src=\"{$entry->images->thumbnail->url}\">";
  }

} else {

  // Check whether an error occurred
  if (true === isset($_GET['error'])) {
    echo 'An error occurred: '.$_GET['error_description'];
  }

}

?>
