<?php

namespace RavelryApi;

use GuzzleHttp\Command\Model as BaseModel;
use GuzzleHttp\Command\CommandInterface;

/**
 * This is the object to hold the results of API requests. It holds a bit more
 * detail than the default Guzzle result.
 */
class Model extends BaseModel
{
    protected $command;
    protected $etag;
    protected $metadata;
    protected $statusCode;
    protected $statusText;

    public function __construct(
        CommandInterface $command,
        $statusCode,
        $statusText,
        $etag,
        array $data,
        array $metadata = []
    ) {
        $this->command = $command;
        $this->statusCode = $statusCode;
        $this->statusText = $statusText;
        $this->etag = $etag;
        $this->metadata = $metadata;

        parent::__construct($data);
    }

    /**
     * Get the Command which was used for this API request.
     * 
     * @todo this may be inefficient for batching apps, but it'll enable some
     * cool paginator and meta requests.
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Get the ETag that the API returned.
     */
    public function getETag()
    {
        return $this->etag;
    }

    /**
     * Some arbitrary metadata. Mostly just for debugging right now.
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get the HTTP status code that the raw response returned.
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Get the HTTP status text that the raw response returned.
     */
    public function getStatusText()
    {
        return $this->statusText;
    }
}
