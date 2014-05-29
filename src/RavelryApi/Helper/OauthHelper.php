<?php

namespace RavelryApi\Helper;

use LogicException;
use RuntimeException;
use UnexpectedValueException;
use GuzzleHttp\Adapter\Transaction;
use GuzzleHttp\Event\BeforeEvent;
use RavelryApi\Authentication\OauthAuthentication;
use RavelryApi\Client;

/**
 * This integrates a couple helper methods for general OAuth session tasks.
 */
class OauthHelper
{
    protected $client;
    protected $config;
    protected $authentication;

    public function __construct(Client $client, array $config = [])
    {
        $this->client = $client;

        $this->config = array_merge(
            [
                'request_url' => 'https://www.ravelry.com/oauth/request_token',
                'authorize_url' => 'https://www.ravelry.com/oauth/authorize',
                'access_url' => 'https://www.ravelry.com/oauth/access_token',
            ],
            $config
        );

        $this->authentication = $client->getAuthentication();

        if (!$this->authentication instanceof OauthAuthentication) {
            throw new LogicException('The OAuth authentication handler is not available.');
        }
    }

    /**
     * Start setting up a new OAuth session.
     */
    public function beginSession($callback, array $scope = [])
    {
        $requestUrl = $this->config['request_url'];

        if (0 < count($scope)) {
            $requestUrl .= ((false === strpos($requestUrl, '?')) ? '?' : '&') . 'scope=' . rawurlencode(implode(' ', $scope));
        }

        $response = $this->sendCustomOauthPostRequest(
            $requestUrl,
            [
                'callback' => $callback,
                'token' => null,
                'token_secret' => null,
            ]
        );

        parse_str($response->getBody(), $responseData);

        if (!isset($responseData['oauth_callback_confirmed'])) {
            throw new UnexpectedValueException('Oauth exchange is missing `oauth_callback_confirmed`.');
        } elseif (!isset($responseData['oauth_token'])) {
            throw new UnexpectedValueException('Oauth exchange is missing `oauth_token`.');
        } elseif (!isset($responseData['oauth_token_secret'])) {
            throw new UnexpectedValueException('Oauth exchange is missing `oauth_token_secret`.');
        }

        if ('true' !== $responseData['oauth_callback_confirmed']) {
            throw new UnexpectedValueException('Oauth exchange must have `oauth_callback_confirmed` as `true`.');
        }

        $this->authentication->getTokenStorage()->setRequestToken($responseData['oauth_token']);
        $this->authentication->getTokenStorage()->setRequestTokenSecret($responseData['oauth_token_secret']);

        $redirect = $this->config['authorize_url'];
        $redirect .= ((false === strpos($redirect, '?')) ? '?' : '&') . 'oauth_token=' . rawurlencode($this->authentication->getTokenStorage()->getRequestToken());

        return $redirect;
    }

    /**
     * Finish an OAuth session, converting the request to access tokens.
     */
    public function confirmSession($token, $verifier)
    {
        if ($this->authentication->getTokenStorage()->getRequestToken() != $token) {
            throw new LogicException('The request tokens do not match.');
        }

        $response = $this->sendCustomOauthPostRequest(
            $this->config['access_url'],
            [
                'verifier' => $verifier,
                'token' => $this->authentication->getTokenStorage()->getRequestToken(),
                'token_secret' => $this->authentication->getTokenStorage()->getRequestTokenSecret(),
            ]
        );

        if (200 == $response->getStatusCode()) {
            parse_str($response->getBody(), $responseData);

            if (!isset($responseData['oauth_token'])) {
                throw new UnexpectedValueException('Oauth exchange is missing `oauth_token`.');
            } elseif (!isset($responseData['oauth_token_secret'])) {
                throw new UnexpectedValueException('Oauth exchange is missing `oauth_token_secret`.');
            }

            $this->authentication->getTokenStorage()->setAccessToken($responseData['oauth_token']);
            $this->authentication->getTokenStorage()->setAccessTokenSecret($responseData['oauth_token_secret']);
            $this->authentication->getTokenStorage()->setRequestToken(null);
            $this->authentication->getTokenStorage()->setRequestTokenSecret(null);

            $this->authentication->getTokenStorage()->save();

            // next time we should reload tokens
            $this->authentication->resetOauth();
        } else {
            throw new RuntimeException('Bad response');
        }
    }

    /**
     * An internal function which attempts to (hackily) reuse the upstream OAuth
     * signer.
     */
    protected function sendCustomOauthPostRequest($uri, array $config)
    {
        // this method is hacky. it'd be easier if Oauth1 let you sign a one-off request
        $guzzle = $this->client->getGuzzle();

        $request = $guzzle->createRequest(
            'POST',
            $uri,
            [
                'auth' => null,
            ]
        );

        $oauth = $this->authentication->createOauth($config);

        $request->getConfig()->set('auth', 'oauth');

        $oauth->onBefore(
            new BeforeEvent(
                new Transaction(
                    $guzzle,
                    $request
                )
            )
        );

        $request->getConfig()->set('auth', null);

        return $guzzle->send($request);
    }
}
