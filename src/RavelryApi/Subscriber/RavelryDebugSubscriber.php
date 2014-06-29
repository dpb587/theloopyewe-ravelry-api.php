<?php

namespace RavelryApi\Subscriber;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;

/**
 * A simple subscriber which adds the `debug` flag to all requests, encouraging
 * Ravelry servers to retain additional debug information and API requests.
 */
class RavelryDebugSubscriber implements SubscriberInterface
{
    public function getEvents()
    {
        return [
            'before' => [
                'onBefore',
                RequestEvents::EARLY + 1,
            ],
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        $event->getRequest()->getQuery()->set('debug', '1');
    }
}
