<?php

require realpath( __DIR__ . "/../Velix.php" );

$app = new Velix();

$app->get('/api/users/{name}', function( Response $res, string $name ) {
    $res->json([
        'message' => 'Hello ' . $name
    ]);
});

$app->dispatch();

?>
