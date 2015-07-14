<?php
 
require 'instagram.class.php';
 
$instagram = new Instagram(array(
  'apiKey'      => '563239a4c8214a85b9153d00c1a2a00f',
  'apiSecret'   => '4f6256459c5c417dbe48b8609e1cc123',
  'apiCallback' => 'http://localhost/ws/Works/instagram/phpInsta/'
));
 
$token = 'USER_ACCESS_TOKEN';
$instagram->setAccessToken($token);
 
$id = 'MEDIA_ID';
$result = $instagram->likeMedia($id);
 
if ($result->meta->code === 200) {
  echo 'Success! The image was added to your likes.';
} else {
  echo 'Something went wrong :(';
}

?>
