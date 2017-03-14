<?php

namespace Authentiq\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Grant\AbstractGrant;
use Authentiq\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

class Authentiq extends AbstractProvider
{
    public $scope = [];
    public $idToken;

    protected function domain()
    {
        return 'https://dev.connect.authentiq.io/backchannel-logout/';
    }

    public function getBaseAuthorizationUrl()
    {
        return $this->domain() . '/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->domain() . '/token';
    }

    public function getResourceOwnerDetailsUrl(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return null;
    }

    public function getClientId()
    {
        return $this->clientId;
    }

    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    public function getProviderAlgorithm()
    {
        return $this->algorithm;
    }

    public function getDomain()
    {
        return $this->domain();
    }

    protected function getDefaultScopes()
    {
        if (in_array("openid", explode(" ", $this->scope))) {
            return $this->scope;
        } else {
            $scopes = explode(" ", $this->scope);
            array_push($scopes, "openid");
            return join(" ", $scopes);
        }
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        //Check id token here!
    }

    public function getResourceOwner(\League\OAuth2\Client\Token\AccessToken $token)
    {
        $data = $token->getIdTokenClaims();
        return $this->createResourceOwner($data, $token);
    }

    protected function createResourceOwner(array $response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return new AuthentiqResourceOwner($response);
    }

    protected function getScopeSeparator()
    {
        return ' ';
    }

    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        return new AccessToken($response, $this);
    }
}