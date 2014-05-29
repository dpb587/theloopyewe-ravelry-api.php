<?php

namespace RavelryApi;

use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Command\Guzzle\GuzzleClientInterface;
use GuzzleHttp\Command\Guzzle\Command;
use RavelryApi\Subscriber\ProcessResponse;
use GuzzleHttp\Command\Guzzle\Description;

/**
 * A more Ravelry-specific client which patches in some additional behaviors.
 */
class ServiceClient extends GuzzleClient
{
    /**
     * We're overriding this to support operation-specific requestion options.
     * Currently this is for disabling `auth` on specific operations.
     */
    public static function defaultCommandFactory(Description $description)
    {
        return function (
            $name,
            array $args = [],
            GuzzleClientInterface $client
        ) use ($description) {

            $operation = null;

            if ($description->hasOperation($name)) {
                $operation = $description->getOperation($name);
            } else {
                $name = ucfirst($name);
                if ($description->hasOperation($name)) {
                    $operation = $description->getOperation($name);
                }
            }

            if (!$operation) {
                return null;
            }

            // this is the only line which is patched
            $args += ($operation->getData('defaults') ?: []);

            return new Command($operation, $args, clone $client->getEmitter());
        };
    }

    /**
     * We're overriding this to use our command factory from above and to use
     * custom objects for API results.
     */
    protected function processConfig(array $config)
    {
        $config['command_factory'] = self::defaultCommandFactory($this->getDescription());

        // we'll add our own patched processor after this
        parent::processConfig(
            array_merge(
                $config,
                [
                    'process' => false,
                ]
            )
        );

        if (!isset($config['process']) || $config['process'] === true) {
            $this->getEmitter()->attach(
                new ProcessResponse(
                    isset($config['debug']) ? $config['debug'] : false,
                    isset($config['response_locations']) ? $config['response_locations'] : []
                )
            );
        }
    }
}
