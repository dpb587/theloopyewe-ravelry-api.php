#!/usr/bin/env php
<?php

require_once __DIR__ . '/bootstrap.php';

$ravelry = RavelryApi\Tests\Functional\TestCase::createClient();

fwrite(STDOUT, '> whoami...');

$whoami = $ravelry->currentUser()['user'];

fwrite(STDOUT, $whoami['username'] . "\n");


fwrite(STDOUT, '> cleaning favorites:list ("apitest")...');

$result = $ravelry->favorites->list([
    'username' => $whoami['username'],
    'query' => 'apitest',
]);

if (0 < count($result['favorites'])) {
    fwrite(STDOUT, "\n");

    foreach ($result['favorites'] as $favorite) {
        fwrite(STDOUT, '  > deleting ' . $favorite['id'] . '...');

        $ravelry->favorites->delete([
            'username' => $whoami['username'],
            'id' => $favorite['id'],
        ]);

        fwrite(STDOUT, 'done' . "\n");
    }
} else {
    fwrite(STDOUT, 'done' . "\n");
}