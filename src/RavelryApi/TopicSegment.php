<?php

namespace RavelryApi;

/**
 * This is a simple wrapper to support a different way of using API calls.
 */
class TopicSegment
{
    protected $client;
    protected $prefix;

    public function __construct(Client $client, $prefix)
    {
        $this->client = $client;
        $this->prefix = $prefix;
    }

    public function __call($name, array $args)
    {
        return $this->client->__call($this->prefix . '_' . $name, $args);
    }

    public function __get($name)
    {
        return new TopicSegment($this->client, $this->prefix . '_' . $name);
    }
}
