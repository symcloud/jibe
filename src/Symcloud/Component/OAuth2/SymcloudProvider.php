<?php

namespace Symcloud\Component\OAuth2;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

class SymcloudProvider extends AbstractProvider
{
    /**
     * @var string
     */
    private $server;

    /**
     * SymcloudProvider constructor.
     * @param string $server
     * @param string $client
     */
    public function __construct($server, $client)
    {
        parent::__construct(array('clientId' => $client['id'], 'clientSecret' => $client['secret']));

        $this->server = $server;

        $this->redirectHandler = function ($url) {
            echo($url);
        };
    }

    public function setOptions($options)
    {
        foreach ($options as $option => $value) {
            if (property_exists($this, $option)) {
                $this->{$option} = $value;
            }
        }
    }

    /**
     * Get the URL that this provider uses to begin authorization.
     *
     * @return string
     */
    public function urlAuthorize()
    {
        return $this->server . 'admin/oauth/v2/auth';
    }

    /**
     * Get the URL that this provider users to request an access token.
     *
     * @return string
     */
    public function urlAccessToken()
    {
        return $this->server . 'admin/oauth/v2/token';
    }

    /**
     * Get the URL that this provider uses to request user details.
     *
     * Since this URL is typically an authorized route, most providers will require you to pass the access_token as
     * a parameter to the request. For example, the google url is:
     *
     * 'https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token='.$token
     *
     * @param AccessToken $token
     * @return string
     */
    public function urlUserDetails(AccessToken $token)
    {
        // TODO: Implement urlUserDetails() method.
    }

    /**
     * Given an object response from the server, process the user details into a format expected by the user
     * of the client.
     *
     * @param object $response
     * @param AccessToken $token
     * @return mixed
     */
    public function userDetails($response, AccessToken $token)
    {
        // TODO: Implement userDetails() method.
    }
}
