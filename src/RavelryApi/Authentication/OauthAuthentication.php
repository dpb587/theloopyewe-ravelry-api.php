<?php

namespace RavelryApi\Authentication;

use GuzzleHttp\Subscriber\Oauth\Oauth1;
use RavelryApi\Authentication\OauthTokenStorage\TokenStorageInterface;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\BeforeEvent;

/**
 * A subscriber to sign requests with OAuth authorization headers.
 */
class OauthAuthentication implements AuthenticationInterface
{
    protected $config;
    protected $tokenStorage;
    protected $oauth;

    public function __construct(TokenStorageInterface $tokenStorage, $accessKey, $secretKey, array $config = [])
    {
        $this->tokenStorage = $tokenStorage;

        $this->config = array_merge(
            $config,
            [
                'consumer_key' => $accessKey,
                'consumer_secret' => $secretKey,
            ]
        );
    }

    public function getAccessKey()
    {
        return $this->config['consumer_key'];
    }

    public function getEvents()
    {
        return [
            'before' => [
                'onBefore',
                RequestEvents::SIGN_REQUEST,
            ],
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        return $this->getOauth()->onBefore($event);
    }

    /**
     * Make sure all requests default to using oauth
     * 
     * @todo possibly deprecate this and move the responsibility directly into
     * the schema request_options.
     */
    public function getDefaultRequestOptions()
    {
        return [
            'auth' => 'oauth',
        ];
    }

    /**
     * Retrieve the storage handler managing the tokens.
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * Create an OAuth signer and, optionally, override some of the default
     * config options.
     */
    public function createOauth(array $config = [])
    {
        return new Oauth1(
            array_merge(
                $this->config,
                [
                    'token' => $this->tokenStorage->getAccessToken(),
                    'token_secret' => $this->tokenStorage->getAccessTokenSecret(),
                ],
                $config
            )
        );
    }

    /**
     * Reset the OAuth client. Useful in case you update the token storage.
     */
    public function resetOauth()
    {
        $this->oauth = null;
    }

    /**
     * Retrieve the current OAuth signer (or create a new one if it doesn't
     * exist yet).
     */
    protected function getOauth()
    {
        if (null == $this->oauth) {
            $this->oauth = $this->createOauth();
        }

        return $this->oauth;
    }
}
