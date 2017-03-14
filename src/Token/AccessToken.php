<?php
/**
 * User: alexkeramidas
 * Date: 14/3/2017
 * Time: 8:34 μμ
 */

namespace Authentiq\OAuth2\Client\Token;

use RuntimeException;
use \Firebase\JWT\JWT;

class AccessToken extends \League\OAuth2\Client\Token\AccessToken
{
    protected $idToken;
    protected $idTokenClaims;

    /**
     * Token constructor.
     */
    public function __construct(array $options = [], $provider)
    {
        parent::__construct($options);

        JWT::$leeway = 60;

        if(!empty($options['id_token'])){
            $this->idToken = $options['id_token'];
            $this->idTokenClaims = null;
            try {
                $tokens = explode('.', $this->idToken);
                // Check if the id_token contains signature
                if(count($tokens) == 3 && !empty($tokens[2])) {
                    $idTokenClaims = (array)JWT::decode($this->idToken, $provider->getClientSecret(), ['HS256']);
                }
            }  catch (JWT_Exception $e) {
                throw new RuntimeException("Unable to parse the id_token!");
            }

        }

        if ($provider->getClientId() != $idTokenClaims['aud']){
            throw new \RuntimeException('Invalid audience');
        }

        if($idTokenClaims['nbf'] > time() || $idTokenClaims['exp'] < time()) {
            // Additional validation is being performed in firebase/JWT itself
            throw new RuntimeException("The id token is invalid!");
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

    protected function algorithm($provider)
    {
        if ($provider->getProviderAlgorithm() != null) {
            return $provider->getProviderAlgorithm();
        } else {
            return ['HS256'];
        }
    }

}