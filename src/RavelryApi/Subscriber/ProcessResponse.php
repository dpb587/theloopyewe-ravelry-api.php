<?php

namespace RavelryApi\Subscriber;

use GuzzleHttp\Command\Event\ProcessEvent;
use GuzzleHttp\Command\Guzzle\Subscriber\ProcessResponse as BaseProcessResponse;
use RavelryApi\Model;

/**
 * We're overriding the default ProcessResponse that Guzzle provides in order
 * to return some additional data about the API request.
 */
class ProcessResponse extends BaseProcessResponse
{
    protected $debug;

    public function __construct($debug = false, array $responseLocations = [])
    {
        $this->debug = (Boolean) $debug;

        parent::__construct($responseLocations);
    }

    public function onProcess(ProcessEvent $event)
    {
        // we're overriding this to provide a better command result
        parent::onProcess($event);

        $response = $event->getResponse();
        $metadata = [];

        if ($this->debug) {
            // this retains these in-memory and would cause performance issues
            $this->metadata['request'] = $event->getRequest();
            $this->metadata['response'] = $event->getResponse();
        }

        $event->setResult(
            new Model(
                $event->getCommand(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
                $response->getHeader('etag'),
                $event->getResult()->toArray(),
                $metadata
            )
        );
    }
}
