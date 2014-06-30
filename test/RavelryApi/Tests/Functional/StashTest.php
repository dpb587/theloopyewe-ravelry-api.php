<?php

namespace RavelryApi\Tests\Functional;

class StashTest extends TestCase
{
    public function ztestBasicCrud()
    {
        $now = time();

        $whoami = $this->client->currentUser();

        $result = $this->client->stash->create([
            'username' => $whoami['user']['username'],
            'handspun' => false,
            'location' => 'Basement Closet',
            'notes' =>  'apitest ref' . $now,
            'pack' => [
                'color_family_id' => 2,
                'colorway' => 'Carousel',
                'dye_lot' => 'B9',
                'length_units' => 'yards',
                'purchased_date' => '2014-05-26',
                'shop-id' => 3163,
                'skein_length' => 384,
                'skeins' => 2,
                'total_length' => 768,
                'weight_units' => 'grams',
            ],
            'stash_status_id' => 1,
            'yarn_id' => 51846,
        ]);

        $id = $result['bookmark']['id'];

        $this->assertArrayHasKey('created_at', $result['bookmark']);

        $this->assertEquals('pattern', $result['bookmark']['type']);

        $this->assertEquals('apitest ref' . $now, $result['bookmark']['comment']);

        $this->assertInternalType('array', $result['bookmark']['favorited']);

        $this->assertEquals(275864, $result['bookmark']['favorited']['id']);

        // there's a noticeable delay before stash are searchable
        $result = $this->retryTimes(
            function ($that) use ($whoami, $now, $id) {
                $result = $that->getClient()->stash->list([
                    'username' => $whoami['user']['username'],
                    'types' => [ 'pattern' ],
                    'query' => 'ref' . $now,
                ]);

                if (1 == count($result['stash'])) {
                    return $result;
                }
            }
        );

        $this->assertCount(1, $result['stash']);
        $this->assertEquals($id, $result['stash'][0]['id']);
        $this->assertEquals(275864, $result['stash'][0]['favorited']['id']);
        $this->assertEquals('apitest ref' . $now, $result['stash'][0]['comment']);


        $result = $this->client->stash->update([
            'username' => $whoami['user']['username'],
            'id' => $id,
            'comment' => 'apitest update ref' . $now,
        ]);

        $this->assertArrayHasKey('bookmark', $result);
        $this->assertEquals($id, $result['bookmark']['id']);
        $this->assertEquals(275864, $result['bookmark']['favorited']['id']);


        $result = $this->client->stash->list([
            'username' => $whoami['user']['username'],
            'types' => [ 'pattern' ],
            'query' => 'ref' . $now,
        ]);

        $this->assertArrayHasKey('stash', $result);

        $this->assertCount(1, $result['stash']);
        $this->assertEquals($id, $result['stash'][0]['id']);
        $this->assertEquals(275864, $result['stash'][0]['favorited']['id']);
        $this->assertEquals('apitest update ref' . $now, $result['stash'][0]['comment']);


        $result = $this->client->stash->delete([
            'username' => $whoami['user']['username'],
            'id' => $id,
        ]);

        $this->assertEquals($id, $result['bookmark']['id']);
        $this->assertEquals(275864, $result['bookmark']['favorited']['id']);


        $result = $this->client->stash->list([
            'username' => $whoami['user']['username'],
            'types' => [ 'pattern' ],
            'query' => 'ref' . $now,
        ]);

        $this->assertArrayHasKey('stash', $result);
        $this->assertCount(0, $result['stash']);
    }

    public function testList()
    {
        $result = $this->client->stash->list([
            'username' => 'TheLoopyEwe',
            'page_size' => 5,
        ]);

        $this->assertInternalType('integer', $result['stash'][0]['id']);
        $this->assertInternalType('array', $result['stash'][0]['yarn']);
        $this->assertArrayHasKey('updated_at', $result['stash'][0]);
        $this->assertArrayHasKey('colorway_name', $result['stash'][0]);
        $this->assertArrayHasKey('name', $result['stash'][0]);
        $this->assertArrayHasKey('permalink', $result['stash'][0]);
    }

    public function testShow()
    {
        $result = $this->client->stash->show([
            'username' => 'TheLoopyEwe',
            'id' => 76586,
        ]);

        $this->assertInternalType('integer', $result['stash']['id']);
        $this->assertInternalType('array', $result['stash']['yarn']);
        $this->assertInternalType('array', $result['stash']['photos']);
        $this->assertInternalType('array', $result['stash']['packs']);
        $this->assertArrayHasKey('updated_at', $result['stash']);
        $this->assertArrayHasKey('colorway_name', $result['stash']);
        $this->assertArrayHasKey('name', $result['stash']);
        $this->assertArrayHasKey('permalink', $result['stash']);
    }
}
