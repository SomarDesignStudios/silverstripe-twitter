<?php

$path = explode(DIRECTORY_SEPARATOR, dirname(__FILE__));
$dir = $path[count($path) - 1];

define('SILVERSTRIPE_TWITTER_DIR', $dir);
