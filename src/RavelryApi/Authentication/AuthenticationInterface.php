<?php

namespace RavelryApi\Authentication;

use GuzzleHttp\Event\SubscriberInterface;

/**
 * An interface for classes wanting to provide authentication to Ravelry.
 */
interface AuthenticationInterface extends SubscriberInterface
{
    /**
     * Get options which are applied to all requests, by default.
     */
    public function getDefaultRequestOptions();

    /**
     * Retrieve the access key that the authentication is using.
     */
    public function getAccessKey();
}
