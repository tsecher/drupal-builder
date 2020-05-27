<?php

include_once(__DIR__ . '/custom_autoload.php');

use BimRunner\Application\RunnerApplication;

$app = new RunnerApplication('Build drupal', __DIR__, 'App', 'Actions');
$app->run();
