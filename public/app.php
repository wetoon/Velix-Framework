<?php

require realpath( __DIR__ . "/../Velix.php" );

$app = new Velix();

$app->get('/api/users/{name}', function($req, $res, $name) {
    $res->json([
        'message' => 'สวัสดี ' . urldecode($name)
    ]);
});

$app->dispatch();

?>