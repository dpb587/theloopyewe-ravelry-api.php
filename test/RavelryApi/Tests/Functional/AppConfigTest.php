<?php

namespace RavelryApi\Tests\Functional;

class AppConfigTest extends TestCase
{
    public function testBasicCrud()
    {
        $result = $this->client->app->config->set([
            'profile_badge' => '1',
        ]);

        $this->assertArrayHasKey('config', $result);

        $this->assertArrayHasKey('profile_badge', $result['config']);
        $this->assertEquals('1', $result['config']['profile_badge']);


        $result = $this->client->app->config->get([
            'keys' => [
                'profile_badge',
            ],
        ]);

        $this->assertArrayHasKey('config', $result);

        $this->assertArrayHasKey('profile_badge', $result['config']);
        $this->assertEquals('1', $result['config']['profile_badge']);


        $result = $this->client->app->config->delete([
            'keys' => [
                'profile_badge',
            ],
        ]);

        $this->assertArrayHasKey('config', $result);

        $this->assertArrayHasKey('profile_badge', $result['config']);
        $this->assertEquals('1', $result['config']['profile_badge']);


        $result = $this->client->app->config->get([
            'keys' => [
                'profile_badge',
            ],
        ]);

        $this->assertArrayHasKey('config', $result);

        $this->assertArrayNotHasKey('profile_badge', $result['config']);
    }
}
