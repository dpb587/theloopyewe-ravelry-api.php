<?php

namespace RavelryApi\Tests\Functional;

class ShopsTest extends TestCase
{
    public function testSearch()
    {
        $result = $this->client->shops->search([
            'query' => 'ewe',
            'lat' => '40.5566585',
            'lng' => '-105.067652',
            'radius' => 10,
            'units' => 'km',
        ]);

        $this->assertInternalType('integer', $result['shops'][0]['id']);
        $this->assertArrayHasKey('name', $result['shops'][0]);
        $this->assertArrayHasKey('location', $result['shops'][0]);
    }

    public function testShow()
    {
        $result = $this->client->shops->show([
            'id' => 3163,
        ]);

        $this->assertInternalType('integer', $result['shop']['id']);
        $this->assertArrayHasKey('name', $result['shop']);
        $this->assertArrayHasKey('location', $result['shop']);
    }

    public function testShowWithIncludes()
    {
        $result = $this->client->shops->show([
            'id' => 3163,
            'include' => [ 'brands', 'schedules' ],
        ]);

        $this->assertInternalType('integer', $result['shop']['id']);
        $this->assertArrayHasKey('name', $result['shop']);
        $this->assertArrayHasKey('location', $result['shop']);

        $this->assertArrayHasKey('name', $result['brands'][0]);
        $this->assertArrayHasKey('most_recent_purchase', $result['brands'][0]);
        $this->assertInternalType('array', $result['brands'][0]['yarns']);
        $this->assertInternalType('integer', $result['brands'][0]['id']);
        $this->assertInternalType('boolean', $result['brands'][0]['verified']);
        $this->assertInternalType('boolean', $result['brands'][0]['advertised']);

        $this->assertArrayHasKey('day_name', $result['schedules'][0]);
        $this->assertArrayHasKey('opening_time', $result['schedules'][0]);
        $this->assertArrayHasKey('closing_time', $result['schedules'][0]);
        $this->assertArrayHasKey('day_of_week', $result['schedules'][0]);
        $this->assertInternalType('boolean', $result['schedules'][0]['closed']);
    }
}
