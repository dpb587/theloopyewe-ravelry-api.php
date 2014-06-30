<?php

namespace RavelryApi\Tests\Listener;

use Exception;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;
use PHPUnit_Framework_BaseTestListener;
use PHPUnit_Framework_AssertionFailedError;
use GuzzleHttp\Event\SubscriberInterface;
use RavelryApi\Tests\Functional\TestCase;
use GuzzleHttp\Command\Event\PrepareEvent;

class ApiUsageTestListener extends PHPUnit_Framework_BaseTestListener implements
    SubscriberInterface
{
    protected $path;
    protected $depth = 0;
    protected $calls = [];
    protected $currCalls;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->currCalls = [];
    }

    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->currCalls = [];
    }

    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->currCalls = [];
    }

    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->currCalls = [];
    }

    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->currCalls = [];
    }

    public function startTest(PHPUnit_Framework_Test $test)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        $test->attachServiceClientListener($this);
    }

    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        foreach ($this->currCalls as $call) {
            foreach ($call['args'] as $key => $value) {
                $this->calls[$call['name']][$key][] = $test->toString();
            }
        }

        $this->currCalls = [];
    }

    public function startTestSuite(PHPUnit_Framework_TestSuite $suite) {
        $this->depth += 1;
    }

    public function endTestSuite(PHPUnit_Framework_TestSuite $suite) {
        $this->depth -= 1;

        if (0 == $this->depth) {
            $merged = $this->calls;
            $schema = json_decode(file_get_contents(__DIR__ . '/../../../../src/RavelryApi/schema.json'), true);

            foreach ($schema['operations'] as $name => $operation) {
                foreach ($operation['parameters'] as $key => $value) {
                    if (in_array($key, [ 'debug', 'etag', 'extras' ])) {
                        continue;
                    }

                    $merged[$name][$key] = isset($merged[$name][$key]) ? $merged[$name][$key] : [];
                }
            }

            $fh = fopen($this->path, 'w');

            fwrite($fh, '<html><head><title>API Methods</title></head><body><table width="100%">');

            ksort($merged);

            foreach ($merged as $name => $args) {
                ksort($args);

                fwrite($fh, '<tr><td><strong><code>' . $name . '</code></strong></td>');

                $first = true;

                foreach ($args as $key => $tests) {
                    if (!$first) {
                        fwrite($fh, '<tr><td>&nbsp;</td>');
                    }

                    if ($tests) {
                        fwrite($fh, '<td style="background-color:#E7F6EC;"><code>' . $key . '</code></td>');
                        fwrite($fh, '<td style="background-color:#10A54A;color:#ffffff;text-align:center;"><code>yes</code></td>');
                    } else {
                        fwrite($fh, '<td style="background-color:#F5E8E8;"><code>' . $key . '</code></td>');
                        fwrite($fh, '<td style="background-color:#A41E22;color:#ffffff;text-align:center;"><code>no</code></td>');
                    }

                    fwrite($fh, '</tr>');

                    $first = false;
                }
            }

            fwrite($fh, '</table></body></html>');
            fclose($fh);
        }
    }

    public function getEvents()
    {
        return [
            'prepare' => [
                'onPrepare'
            ],
        ];
    }

    public function onPrepare(PrepareEvent $e)
    {
        $command = $e->getCommand();

        $this->currCalls[] = [
            'name' => $command->getName(),
            'args' => $command->toArray(),
        ];
    }
}