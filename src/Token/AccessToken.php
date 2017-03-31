<?php
/**
 * Authentiq Access Token
 * User: alexkeramidas
 * Date: 14/3/2017
 * Time: 8:34 μμ
 */

namespace Authentiq\OAuth2\Client\Token;

use Exception;
use Firebase\JWT\BeforeValidException;
use InvalidArgumentException;
use Firebase\JWT\JWT;
use RuntimeException;

class AccessToken extends \League\OAuth2\Client\Token\AccessToken
{
    protected $idToken;
    protected $idTokenClaims;

    /**
     * Authentiq Access Token constructor that extends the original Access token constructor and gives back user info through the id token.
     */

    public function __construct(array $options = [], $provider, $clientSecret)
    {
            if (!isset($clientSecret)) {
            throw new InvalidArgumentException('Please use the parent constructor with only one argument as a client_secret is needed for this one');
        }

        parent::__construct($options);


        JWT::$leeway = 60;

        if(!empty($options['id_token'])){
            $this->idToken = $options['id_token'];
            $this->idTokenClaims = null;
            try {
                $tokens = explode('.', $this->idToken);
                // Check if the id_token contains signature
                if(count($tokens) == 3 && !empty($tokens[2])) {
                    $idTokenClaims = (array)JWT::decode($this->idToken, $clientSecret, $provider->getProviderAlgorithm());
                }
            }  catch (Exception $e) {
                throw new RuntimeException("Unable to decode the id_token! The secret or the encryption algorithm used is incorrect");
            }

        }

        if ($provider->getClientId() != $idTokenClaims['aud']){
            throw new RuntimeException('Invalid audience');
        }

        if($idTokenClaims['nbf'] > time() || $idTokenClaims['exp'] < time()) {
            // Additional validation is being performed in firebase/JWT itself
            throw new BeforeValidException("The id token is invalid!");
        }

        if($idTokenClaims['sub'] == null){
            throw new RuntimeException("The id token's sub is invalid!");
        }

        if($idTokenClaims['iss'] == null || $idTokenClaims['iss']  != $provider->getDomain()){
            throw new RuntimeException("The id token's issuer is invalid!");
        }

        if($idTokenClaims['iat'] == null){
            throw new RuntimeException("The id token's issued time is null!");
        }

        $this->idTokenClaims = $idTokenClaims;
    }

    public function getIdTokenClaims()
    {
        return $this->idTokenClaims;
    }
}