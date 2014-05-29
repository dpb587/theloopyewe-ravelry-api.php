<?php

namespace RavelryApi\Authentication;

/**
 * A simple HTTP Basic auth when using your personal key.
 */
class BasicAuthentication implements AuthenticationInterface
{
    protected $username;
    protected $password;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getDefaultRequestOptions()
    {
        return [
            'auth' => [
                $this->username,
                $this->password,
                'Basic'
            ],
        ];
    }

    public function getEvents()
    {
        return [];
    }

    public function getAccessKey()
    {
        return $this->username;
    }
}
