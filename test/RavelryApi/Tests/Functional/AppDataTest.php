<?php

namespace RavelryApi\Tests\Functional;

class AppDataTest extends TestCase
{
    public function testBasicCrud()
    {
        $now = date('c');

        $result = $this->client->app->data->set([
            'test/sync/favorites' => $now,
        ]);

        $this->assertArrayHasKey('data', $result);

        $this->assertArrayHasKey('test/sync/favorites', $result['data']);
        $this->assertEquals($now, $result['data']['test/sync/favorites']);


        $result = $this->client->app->data->get([
            'keys' => [
                'test/sync/favorites',
            ],
        ]);

        $this->assertArrayHasKey('data', $result);

        $this->assertArrayHasKey('test/sync/favorites', $result['data']);
        $this->assertEquals($now, $result['data']['test/sync/favorites']);


        // @todo
        $this->markTestIncomplete('The app/data/delete call seems to be breaking with a 500 Internal Server Error');

        $result = $this->client->app->data->delete([
            'keys' => [
                'test/sync/favorites',
            ],
        ]);

        $this->assertArrayHasKey('data', $result);

        $this->assertArrayHasKey('test/sync/favorites', $result['data']);
        $this->assertEquals($now, $result['data']['test/sync/favorites']);


        $result = $this->client->app->data->get([
            'keys' => [
                'test/sync/favorites',
            ],
        ]);

        $this->assertArrayHasKey('data', $result);

        $this->assertArrayNotHasKey('test/sync/favorites', $result['data']);
    }
}
