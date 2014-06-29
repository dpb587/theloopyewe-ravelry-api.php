<?php

namespace RavelryApi\Tests\Functional;

class NeedlesTest extends TestCase
{
    public function testTypes()
    {
        $result = $this->client->needles->types();

        $this->assertArrayHasKey('description', $result['needle_types'][0]);
        $this->assertArrayHasKey('needle_size_id', $result['needle_types'][0]);
        $this->assertArrayHasKey('name', $result['needle_types'][0]);
        $this->assertArrayHasKey('type_name', $result['needle_types'][0]);
        $this->assertInternalType('integer', $result['needle_types'][0]['id']);
        $this->assertArrayHasKey('metric_name', $result['needle_types'][0]);
        $this->assertArrayHasKey('length', $result['needle_types'][0]);
    }

    public function testSizes()
    {
        $result = $this->client->needles->sizes();

        $this->assertArrayHasKey('us', $result['needle_sizes'][0]);
        $this->assertArrayHasKey('metric', $result['needle_sizes'][0]);
        $this->assertArrayHasKey('hook', $result['needle_sizes'][0]);
        $this->assertInternalType('integer', $result['needle_sizes'][0]['id']);
    }

    public function testList()
    {
        $result = $this->client->needles->list([
            'username' => 'TheLoopyEwe',
        ]);

        $this->assertArrayHasKey('needle_type_id', $result['needle_records'][0]);
        $this->assertArrayHasKey('comment', $result['needle_records'][0]);
        $this->assertInternalType('array', $result['needle_records'][0]['needle_type']);
        $this->assertInternalType('integer', $result['needle_records'][0]['id']);
    }
}
