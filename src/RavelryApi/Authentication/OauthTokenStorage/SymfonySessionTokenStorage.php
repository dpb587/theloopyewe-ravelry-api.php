<?php

namespace RavelryApi\Authentication\OauthTokenStorage;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * A wrapper for persisting tokens within a Symfony2 session.
 *
 * This uses four keys (`atoken`, `asecret`, `rtoken`, `rsecret`) which are
 * stored, by default, under the prefix `_ravelryapi/` in the session. So, if
 * you're inspecting it you would notice (not all keys may be present)...
 *
 *     $session->get('_ravelryapi/atoken'); #= '...snip...'
 *     $session->get('_ravelryapi/asecret'); #= '...snip...'
 *     $session->get('_ravelryapi/rtoken'); #= '...snip...'
 *     $session->get('_ravelryapi/rsecret'); #= '...snip...'
 **/
class SymfonySessionTokenStorage implements TokenStorageInterface
{
    protected $session;
    protected $prefix;

    public function __construct(Session $session, $prefix = '_ravelryapi/')
    {
        $this->session = $session;
        $this->prefix = $prefix;
    }

    public function getAccessToken()
    {
        return $this->session->get($this->prefix . 'atoken');
    }

    public function getAccessTokenSecret()
    {
        return $this->session->get($this->prefix . 'asecret');
    }

    public function getRequestToken()
    {
        return $this->session->get($this->prefix . 'rtoken');
    }

    public function getRequestTokenSecret()
    {
        return $this->session->get($this->prefix . 'rsecret');
    }

    public function setAccessToken($token)
    {
        $this->session->set($this->prefix . 'atoken', $token);
    }

    public function setAccessTokenSecret($secret)
    {
        $this->session->set($this->prefix . 'asecret', $secret);
    }

    public function setRequestToken($token)
    {
        $this->session->set($this->prefix . 'rtoken', $token);
    }

    public function setRequestTokenSecret($secret)
    {
        $this->session->set($this->prefix . 'rsecret', $secret);
    }
}
