A PHP library for interacting with the [Ravelry API](http://www.ravelry.com/api).

Consider this a functional prototype. This library's API may change. Not all the API calls have been tested.

*This project is not affiliated with [Ravelry](http://www.ravelry.com/).*


## Getting Started

The main code is located in [`./src`](./src) and dependencies are managed with [Composer](https://getcomposer.org).


### Source Code

For integration, it's easiest to require with [`composer`](https://getcomposer.org/)...

    composer.phar require theloopyewe/ravelry-api=0.2.0

For development, it's easiest to clone with [`git`](http://git-scm.com/)...

    git clone https://github.com/theloopyewe/ravelry-api-php
    cd ravelry-api-php
    composer.phar install


### Authentication

There are two authentication methods for you to decide between. In both cases you can find the necessary keys from the
**apps** tab of your [Ravelry Pro](https://www.ravelry.com/pro) account.

For [OAuth](http://oauth.net/), use an [`OauthTokenStorage`](./src/RavelryApi/Authentication/OauthTokenStorage) handler
and include your access and secret key...

    $auth = new RavelryApi\Authentication\OauthAuthentication(
        new RavelryApi\Authentication\TokenStorage\NativeSessionTokenStorage(),
        $accessKey,
        $secretKey
    );

For personal use with your own account, you can use your access and personal key...

    $auth = new RavelryApi\Authentication\BasicAuthentication($accessKey, $personalKey);


### Usage

Create a new `RavelryApi\Client`, including the authentication handler you're using...

    $ravelry = new RavelryApi\Client($auth);

And now you can make API calls, using the returned result like an array...

    # find the first message from the inbox
    $id =
        $ravelry->messages->list([ 'folder' => 'inbox' ])
        ['messages'][0]['id'];

    # load and show the message
    $message =
        $ravelry->messages->show([ 'id' => $id ])
        ['message'];

    echo $message['content_html'];
    #> <p>I&#8217;m a message from the API!</p>

    # then mark it as read
    $ravelry->messages->markRead([ 'id' => $id ]);

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


## Tests

The testing code is located in [`./test`](./test) and are run with [`phpunit`](http://phpunit.de/).


### Functional Tests

The functional tests will actually communicate with the Ravelry API to run through various API calls and validate the
expected responses. You must configure a `RAVELRY_TEST_ACCESS_KEY` and `RAVELRY_TEST_PERSONAL_KEY` to use for
authentication with the tests. All tests run as specifically as possible and avoid changing things that tests didn't
create, however, it is testing so you should probably use a dedicated test account.

There is a `./test/cleanup.php` script which will clean up any objects left behind from failed tests.


## References

 * http://www.ravelry.com/api - Ravelry's API Documentation
 * https://github.com/theloopyewe/ravelry-api-php-cli - a simple CLI for the Ravelry API which uses this library


## License

[MIT License](./LICENSE)
