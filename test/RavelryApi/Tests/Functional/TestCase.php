<?php

namespace RavelryApi\Tests\Functional;

use GuzzleHttp\Event\SubscriberInterface;
use RavelryApi\Client;
use RavelryApi\Authentication\BasicAuthentication;

/**
 * @group functional
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $client;
    private $clientListeners = [];

    static public function createClient()
    {
        return new Client(
            new BasicAuthentication(
                getenv('RAVELRY_TEST_ACCESS_KEY'),
                getenv('RAVELRY_TEST_PERSONAL_KEY')
            )
        );
    }

    public function setUp()
    {
        $this->client = self::createClient();

        foreach ($this->clientListeners as $clientListener) {
            $this->attachClientListener($clientListener);
        }

        $this->clientListeners = [];
    }

    public function tearDown()
    {
        $this->client = null;
    }

    public function attachClientListener(SubscriberInterface $clientListener)
    {
        if (null !== $this->client) {
            $this->client->getGuzzle()->getEmitter()->attach($clientListener);
        } else {
            $this->clientListeners[] = $clientListener;
        }

        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function retryTimes($callable, $delay = 10, $times = 6)
    {
        for ($try = 0; $try < $times; $try += 1) {
            if (null !== $result = call_user_func_array($callable, [ $this ])) {
                return $result;
            }

            sleep($delay);
        }

        throw new \RuntimeException('Failed to finish within ' . $times . ' tries.');
    }
}
