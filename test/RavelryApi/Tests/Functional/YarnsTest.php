<?php

namespace RavelryApi\Tests\Functional;

class YarnsTest extends TestCase
{
    public function testSearch()
    {
        $result = $this->client->yarns->search([
            'query' => 'cascade',
            'fiber' => 'alpaca',
            'page_size' => 4,
        ]);

        $this->assertInternalType('integer', $result['yarns'][0]['id']);
        $this->assertArrayHasKey('permalink', $result['yarns'][0]);
        $this->assertArrayHasKey('yarn_company_name', $result['yarns'][0]);
        $this->assertArrayHasKey('rating_count', $result['yarns'][0]);
        $this->assertArrayHasKey('rating_average', $result['yarns'][0]);
    }

    public function testShow()
    {
        $result = $this->client->yarns->show([
            'id' => 51846,
        ]);

        $this->assertInternalType('integer', $result['yarn']['id']);
        $this->assertInternalType('array', $result['yarn']['photos']);
        $this->assertInternalType('array', $result['yarn']['yarn_company']);
        $this->assertArrayHasKey('permalink', $result['yarn']);
        $this->assertArrayHasKey('rating_count', $result['yarn']);
        $this->assertArrayHasKey('rating_average', $result['yarn']);
    }
}
