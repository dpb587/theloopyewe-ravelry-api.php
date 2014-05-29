A PHP library for consuming the [Ravelry API](http://www.ravelry.com/api).

Consider this a functional prototype. This library's API may change. Not all the API calls have been tested.

*This project is not affiliated with [Ravelry](http://www.ravelry.com/).*


## Getting Started

For integration, it's easiest to require with [Composer](https://getcomposer.org/)...

    {
        "require" : {
            "dpb587/ravelry-api" : "dev-master"
        }
    }

For development, it's easiest to install with [Composer](https://getcomposer.org/)...

    git clone https://github.com/dpb587/ravelry-api-php
    cd ravelry-api-php
    composer.phar install

There are two authentication methods for you to decide between. For most cases, you should use OAuth in your
application and use your assigned access and secret key...

    $auth = new RavelryApi\Authentication\OauthAuthentication(
        new RavelryApi\Authentication\TokenStorage\NativeSessionTokenStorage(),
        $accessKey,
        $secretKey
    );

For personal use with your own account, you can simply use your access and personal key...

    $auth = new RavelryApi\Authentication\BasicAuthentication($accessKey, $personalKey);

Then create a client, passing the authentication handler...

    $ravelry = new \RavelryApi\Client($auth);

And now you can make API calls, using the returned result like an array...

    # get the first message and mark it as read
    $id =
        $ravelry->messages->list([ 'folder' => 'inbox' ])
        ['messages'][0]['id'];

    $message =
        $ravelry->messages->show([ 'id' => $id ])
        ['message'];

    $ravelry->messages->markRead([ 'id' => $id ]);

    echo $message['content_html'];
    #= "<p>I&#8217;m a message from the API!</p>"

Internally, the results are an object which provides some additional values...

    get_class($message);
    #= 'RavelryApi\\Model'

    $message->toArray();
    #= ['message'=>['sent_at'=>...]]

    $message->getEtag();
    #= '"18aa948e83e5e6b131d6b60998690fd5"'

    $message->getStatusCode();
    #= 200

    $message->getStatusText();
    #= 'OK'


## References

 * http://www.ravelry.com/api - Ravelry's API Documentation
 * http://github.com/dpb587/ravelry-api-php-cli - a simple CLI wrapper to this library


## License

[MIT License](./LICENSE)
