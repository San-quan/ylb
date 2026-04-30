<?php
$url = $_GET['url'];
$img = file_get_contents($url);
header('Content-Type: image/jpeg');
echo $img;
