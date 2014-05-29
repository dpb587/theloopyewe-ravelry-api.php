<?php

namespace RavelryApi\Authentication\OauthTokenStorage;

/**
 * An interface for classes wanting to provide token storage for OAuth
 * authentication.
 */
interface TokenStorageInterface
{
    /**
     * Get the access token.
     */
    public function getAccessToken();

    /**
     * Get the access token's secret.
     */
    public function getAccessTokenSecret();

    /**
     * Get the request token.
     */
    public function getRequestToken();

    /**
     * Get the request token's secret.
     */
    public function getRequestTokenSecret();

    /**
     * Set the access token.
     */
    public function setAccessToken($token);

    /**
     * Set the access token's secret.
     */
    public function setAccessTokenSecret($secret);

    /**
     * Set the request token.
     */
    public function setRequestToken($token);

    /**
     * Set the request token's secret.
     */
    public function setRequestTokenSecret($secret);
}
