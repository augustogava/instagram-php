<?php

require '../instagram.class.php';

// Initialize class for public requests
$instagram = new Instagram('4f6256459c5c417dbe48b8609e1cc123');

$tag = 'veloster';

// Get recently tagged media
$media = $instagram->getTagMedia($tag);
print_r( $media );
// Display results
foreach ($media->data as $data) {
  echo "<img src=\"{$data->images->thumbnail->url}\">";
}

?>
