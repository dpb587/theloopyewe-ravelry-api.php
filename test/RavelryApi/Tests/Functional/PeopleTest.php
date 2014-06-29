<?php

namespace RavelryApi\Tests\Functional;

class PeopleTest extends TestCase
{
    public function testShow()
    {
        $result = $this->client->people->show([
            'id' => 'TheLoopyEwe',
        ]);

        $this->assertInternalType('integer', $result['user']['id']);
        $this->assertArrayHasKey('large_photo_url', $result['user']);
        $this->assertArrayHasKey('photo_url', $result['user']);
        $this->assertArrayHasKey('small_photo_url', $result['user']);
        $this->assertArrayHasKey('tiny_photo_url', $result['user']);
        $this->assertArrayHasKey('username', $result['user']);
    }
}
