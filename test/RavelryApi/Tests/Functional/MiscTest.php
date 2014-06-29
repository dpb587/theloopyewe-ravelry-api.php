<?php

namespace RavelryApi\Tests\Functional;

class MiscTest extends TestCase
{
    public function testColorFamilies()
    {
        $result = $this->client->colorFamilies();

        $this->assertArrayHasKey('permalink', $result['color_families'][0]);
        $this->assertArrayHasKey('color', $result['color_families'][0]);
        $this->assertArrayHasKey('name', $result['color_families'][0]);
        $this->assertArrayHasKey('spectrum_order', $result['color_families'][0]);
        $this->assertInternalType('integer', $result['color_families'][0]['id']);
    }

    public function testCurrentUser()
    {
        $result = $this->client->currentUser();

        $this->assertInternalType('integer', $result['user']['id']);
        $this->assertArrayHasKey('large_photo_url', $result['user']);
        $this->assertArrayHasKey('photo_url', $result['user']);
        $this->assertArrayHasKey('small_photo_url', $result['user']);
        $this->assertArrayHasKey('tiny_photo_url', $result['user']);
        $this->assertArrayHasKey('username', $result['user']);
    }

    public function testYarnWeights()
    {
        $result = $this->client->yarnWeights();

        $this->assertArrayHasKey('crochet_gauge', $result['yarn_weights'][0]);
        $this->assertInternalType('integer', $result['yarn_weights'][0]['id']);
        $this->assertArrayHasKey('knit_gauge', $result['yarn_weights'][0]);
        $this->assertArrayHasKey('max_gauge', $result['yarn_weights'][0]);
        $this->assertArrayHasKey('min_gauge', $result['yarn_weights'][0]);
        $this->assertArrayHasKey('name', $result['yarn_weights'][0]);
        $this->assertArrayHasKey('ply', $result['yarn_weights'][0]);
        $this->assertArrayHasKey('wpi', $result['yarn_weights'][0]);
    }
}
