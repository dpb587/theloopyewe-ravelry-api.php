<?php

namespace RavelryApi\Tests\Functional;

class LibraryTest extends TestCase
{
    public function testSearch()
    {
        $result = $this->client->library->search([
            'username' => 'TheLoopyEwe',
            'type' => 'book',
            'page' => 2,
            'page_size' => 5,
        ]);

        $this->assertInternalType('integer', $result['volumes'][0]['id']);
        $this->assertInternalType('boolean', $result['volumes'][0]['has_downloads']);
        $this->assertArrayHasKey('title', $result['volumes'][0]);
        $this->assertArrayHasKey('author_name', $result['volumes'][0]);
        $this->assertInternalType('array', $result['volumes'][0]['cover_image_size']);
        $this->assertArrayHasKey('cover_image_url', $result['volumes'][0]);
    }
}
