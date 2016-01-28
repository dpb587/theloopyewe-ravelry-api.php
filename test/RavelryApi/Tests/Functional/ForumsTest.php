<?php

namespace RavelryApi\Tests\Functional;

class ForumsTest extends TestCase
{
    public function testSets()
    {
        $result = $this->client->forums->sets();

        $this->assertArrayHasKey('sort_order', $result['forum_sets'][0]);
        $this->assertInternalType('boolean', $result['forum_sets'][0]['default']);
        $this->assertArrayHasKey('name', $result['forum_sets'][0]);
        $this->assertInternalType('array', $result['forum_sets'][0]['selected_forums']);
        $this->assertArrayHasKey('permalink', $result['forum_sets'][0]);
    }

    public function testTopics()
    {
        $result = $this->client->forums->topics([
            'forum_id' => 57,
            'page' => 1,
        ]);

        $this->assertInternalType('boolean', $result['topics'][0]['ignored']);
        $this->assertArrayHasKey('replied_at', $result['topics'][0]);
        $this->assertInternalType('integer', $result['topics'][0]['id']);
        $this->assertInternalType('integer', $result['topics'][0]['forum_posts_count']);
        $this->assertInternalType('integer', $result['topics'][0]['latest_reply']);
        $this->assertInternalType('boolean', $result['topics'][0]['watched']);
        $this->assertInternalType('boolean', $result['topics'][0]['locked']);
        $this->assertInternalType('integer', $result['topics'][0]['forum_id']);
        $this->assertArrayHasKey('title', $result['topics'][0]);
        $this->assertInternalType('boolean', $result['topics'][0]['sticky']);
        $this->assertInternalType('boolean', $result['topics'][0]['archived']);
        $this->assertInternalType('integer', $result['topics'][0]['last_read']);
        $this->assertInternalType('integer', $result['topics'][0]['forum_images_count']);
        $this->assertArrayHasKey('created_at', $result['topics'][0]);

        $this->assertArrayHasKey('last_page', $result['paginator']);
        $this->assertArrayHasKey('results', $result['paginator']);
        $this->assertArrayHasKey('page_size', $result['paginator']);
        $this->assertArrayHasKey('page', $result['paginator']);
        $this->assertArrayHasKey('page_count', $result['paginator']);
    }
}
