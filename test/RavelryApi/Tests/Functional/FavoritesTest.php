<?php

namespace RavelryApi\Tests\Functional;

class FavoritesTest extends TestCase
{
    public function testBasicCrud()
    {
        $now = time();

        $whoami = $this->client->currentUser();


        $result = $this->client->favorites->create([
            'username' => $whoami['user']['username'],
            'comment' => 'apitest ref' . $now,
            'favorited_id' => 275864,
            'type' => 'pattern',
        ]);

        $id = $result['bookmark']['id'];

        $this->assertArrayHasKey('created_at', $result['bookmark']);

        $this->assertEquals('pattern', $result['bookmark']['type']);

        $this->assertEquals('apitest ref' . $now, $result['bookmark']['comment']);

        $this->assertInternalType('array', $result['bookmark']['favorited']);

        $this->assertEquals(275864, $result['bookmark']['favorited']['id']);

        // there's a noticeable delay before favorites are searchable
        $result = $this->retryTimes(
            function ($that) use ($whoami, $now, $id) {
                $result = $that->getClient()->favorites->list([
                    'username' => $whoami['user']['username'],
                    'types' => [ 'pattern' ],
                    'query' => 'ref' . $now,
                ]);

                if (1 == count($result['favorites'])) {
                    return $result;
                }
            }
        );

        $this->assertCount(1, $result['favorites']);
        $this->assertEquals($id, $result['favorites'][0]['id']);
        $this->assertEquals(275864, $result['favorites'][0]['favorited']['id']);
        $this->assertEquals('apitest ref' . $now, $result['favorites'][0]['comment']);


        $result = $this->client->favorites->update([
            'username' => $whoami['user']['username'],
            'id' => $id,
            'comment' => 'apitest update ref' . $now,
        ]);

        $this->assertArrayHasKey('bookmark', $result);
        $this->assertEquals($id, $result['bookmark']['id']);
        $this->assertEquals(275864, $result['bookmark']['favorited']['id']);


        $result = $this->client->favorites->list([
            'username' => $whoami['user']['username'],
            'types' => [ 'pattern' ],
            'query' => 'ref' . $now,
        ]);

        $this->assertArrayHasKey('favorites', $result);

        $this->assertCount(1, $result['favorites']);
        $this->assertEquals($id, $result['favorites'][0]['id']);
        $this->assertEquals(275864, $result['favorites'][0]['favorited']['id']);
        $this->assertEquals('apitest update ref' . $now, $result['favorites'][0]['comment']);


        $result = $this->client->favorites->delete([
            'username' => $whoami['user']['username'],
            'id' => $id,
        ]);

        $this->assertEquals($id, $result['bookmark']['id']);
        $this->assertEquals(275864, $result['bookmark']['favorited']['id']);


        $result = $this->client->favorites->list([
            'username' => $whoami['user']['username'],
            'types' => [ 'pattern' ],
            'query' => 'ref' . $now,
        ]);

        $this->assertArrayHasKey('favorites', $result);
        $this->assertCount(0, $result['favorites']);
    }

    /**
     * list
     */

    public function testList()
    {
        $result = $this->client->favorites->list([
            'username' => 'TheLoopyEwe',
        ]);

        $this->validateGeneralResult($result);
    }

    public function testListPaginating()
    {
        $result = $this->client->favorites->list([
            'username' => 'TheLoopyEwe',
            'page' => 2,
            'page_size' => 10,
        ]);

        $this->validateGeneralResult($result);

        $this->assertCount(10, $result['favorites']);
    }

    public function testListTwoTypes()
    {
        $result = $this->client->favorites->list([
            'username' => 'TheLoopyEwe',
            'types' => [ 'pattern', 'designer' ],
        ]);

        $this->validateGeneralResult($result);
    }

    public function testListQuery()
    {
        $result = $this->client->favorites->list([
            'username' => 'TheLoopyEwe',
            'types' => [ 'pattern' ],
            'query' => 'socks',
        ]);

        $this->validateGeneralResult($result);
    }

    /**
     * utility
     */

    protected function validateGeneralResult($result)
    {
        $this->assertArrayHasKey('comment', $result['favorites'][0]);
        $this->assertArrayHasKey('created_at', $result['favorites'][0]);
        $this->assertArrayHasKey('favorited', $result['favorites'][0]);
        $this->assertArrayHasKey('id', $result['favorites'][0]);
        $this->assertArrayHasKey('type', $result['favorites'][0]);


        $this->assertArrayHasKey('last_page', $result['paginator']);
        $this->assertArrayHasKey('results', $result['paginator']);
        $this->assertArrayHasKey('page_size', $result['paginator']);
        $this->assertArrayHasKey('page', $result['paginator']);
        $this->assertArrayHasKey('page_count', $result['paginator']);
    }
}
