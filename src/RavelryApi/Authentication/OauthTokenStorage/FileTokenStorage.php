<?php

namespace RavelryApi\Authentication\OauthTokenStorage;

/**
 * A wrapper for persisting tokens into a flat file in JSON format.
 *
 * The file will look something like (not all keys may be present)...
 *
 *     {
 *         "access_token": "...snip...",
 *         "access_token_secret": "...snip...",
 *         "request_token": "...snip...",
 *         "request_token_secret": "...snip..."
 *     }
 *
 * You can manually save the file with the `save` method, and, by default, it
 * will automatically save when the process exits.
 */
class FileTokenStorage implements TokenStorageInterface
{
    protected $path;
    protected $autosave;
    protected $contents;

    public function __construct($path, $autosave = true)
    {
        $this->path = $path;
        $this->autosave = $autosave;

        $this->load();
    }

    public function __destruct()
    {
        if ($this->autosave) {
            $this->save();
        }
    }

    public function load()
    {
        if (file_exists($this->path)) {
            $this->contents = json_decode(file_get_contents($this->path), true);
        } else {
            $this->contents = [];
        }
    }

    public function save()
    {
        if (!file_exists($this->path)) {
            touch($this->path);
            chmod($this->path, 0600);
        }

        $fh = fopen($this->path, 'w');
        fwrite($fh, json_encode($this->contents, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
        fclose($fh);
    }

    public function getAccessToken()
    {
        return isset($this->contents['access_token']) ? $this->contents['access_token'] : null;
    }

    public function getAccessTokenSecret()
    {
        return isset($this->contents['access_token_secret']) ? $this->contents['access_token_secret'] : null;
    }

    public function getRequestToken()
    {
        return isset($this->contents['request_token']) ? $this->contents['request_token'] : null;
    }

    public function getRequestTokenSecret()
    {
        return isset($this->contents['request_token_secret']) ? $this->contents['request_token_secret'] : null;
    }

    public function setAccessToken($token)
    {
        if (null === $token) {
            unset($this->contents['access_token']);
        } else {
            $this->contents['access_token'] = $token;
        }
    }

    public function setAccessTokenSecret($secret)
    {
        if (null === $secret) {
            unset($this->contents['access_token_secret']);
        } else {
            $this->contents['access_token_secret'] = $secret;
        }
    }

    public function setRequestToken($token)
    {
        if (null === $token) {
            unset($this->contents['request_token']);
        } else {
            $this->contents['request_token'] = $token;
        }
    }

    public function setRequestTokenSecret($secret)
    {
        if (null === $secret) {
            unset($this->contents['request_token_secret']);
        } else {
            $this->contents['request_token_secret'] = $secret;
        }
    }
}
