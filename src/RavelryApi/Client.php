<?php

namespace RavelryApi;

use GuzzleHttp\Client as BaseClient;
use GuzzleHttp\Command\Guzzle\Description;
use RavelryApi\Authentication\AuthenticationInterface;

class Client
{
    const VERSION = '0.2.0-dev';

    protected $authentication;
    protected $guzzle;
    protected $serviceClient;
    protected $serviceDescription;

    public function __construct(AuthenticationInterface $authentication, array $config = [], array $serviceConfig = [])
    {
        $this->authentication = $authentication;

        $this->guzzle = new BaseClient(
            array_merge_recursive(
                [
                    'defaults' => array_merge(
                        $this->authentication->getDefaultRequestOptions(),
                        [
                            'headers' => [
                                'User-Agent' => BaseClient::getDefaultUserAgent() . ' ravelry-api-php/' . static::VERSION,
                            ],
                        ]
                    ),
                ],
                $config
            )
        );

        $this->guzzle->getEmitter()->attach($this->authentication);

        $this->serviceClient = new ServiceClient(
            $this->guzzle,
            self::getServiceDescription(),
            $serviceConfig
        );
    }

    public static function loadServiceDescription()
    {
        return json_decode(file_get_contents(__DIR__ . '/schema.json'), true);
    }

    /**
     * Create our service description from the schema file. We inject a few
     * runtime-specific values into the description.
     */
    public function getServiceDescription()
    {
        if (null === $this->serviceDescription) {
            $serviceDescription = static::loadServiceDescription();
            $serviceDescription['ravelry.access_key'] = $this->authentication->getAccessKey();

            // we'll preset the upload_image parameter with our access_key
            $serviceDescription['operations']['upload_image']['parameters']['access_key']['default'] = $serviceDescription['ravelry.access_key'];

            $this->serviceDescription = new Description($serviceDescription);
        }

        return $this->serviceDescription;
    }

    /**
     * Get the current authentication handler being used.
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /** 
    * Get the core Guzzle client.
    */
    public function getGuzzle()
    {
        return $this->guzzle;
    }

    /**
     * This makes the API look a bit more segmented supporting calls like:
     *
     *     $api->inStoreSales->addByPattern(...)
     * 
     * Instead of smashing them together like:
     *
     *     $api->inStoreSales_addByPattern(...)
     *
     * Both work, but I prefer the former.
     */
    public function __get($name)
    {
        return new TopicSegment($this, $name);
    }

    /**
     * Forward method calls to the service client to run the APi call.
     */
    public function __call($name, array $args)
    {
        return call_user_func_array(
            [
                $this->serviceClient,
                strtolower($name)
            ],
            $args
        );
    }
}
