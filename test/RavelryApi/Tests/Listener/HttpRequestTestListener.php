<?php

namespace RavelryApi\Tests\Listener;

use Exception;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_BaseTestListener;
use PHPUnit_Framework_AssertionFailedError;
use GuzzleHttp\Event\SubscriberInterface;
use RavelryApi\Tests\Functional\TestCase;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\AbstractRequestEvent;

class HttpRequestTestListener extends PHPUnit_Framework_BaseTestListener implements
    SubscriberInterface
{
    protected $requests = [];

    protected function handleDebugging(PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        fwrite(STDERR, "========\n");
        fwrite(STDERR, "test: " . $test->toString() . "\n");

        foreach ($this->requests as $request) {
            fwrite(STDERR, "--------\n");
            fwrite(STDERR, ">>>>>>>>\n" . (string) $request['request'] . "\n");
            fwrite(STDERR, "<<<<<<<<\n" . (string) $request['response'] . "\n");
        }
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->handleDebugging($test);
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->handleDebugging($test);
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->handleDebugging($test);
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $test->attachClientListener($this);
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $this->requests = [];
    }

    public function getEvents()
    {
        return [
            'complete' => [
                'onComplete'
            ],
            'error' => [
                'onComplete'
            ],
        ];
    }

    public function onComplete(AbstractRequestEvent $e)
    {
        $this->requests[] = [
            'request' => $e->getRequest(),
            'response' => $e->getResponse(),
        ];
    }
}
