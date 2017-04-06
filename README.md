# Authentiq Provider for OAuth 2.0 Client

This package provides Authentiq OAuth 2.0 support for the PHP League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client).

## Installation

To install, use composer:

```
composer require authentiq/oauth2-authentiq
```


## Usage

Usage is the same as The League's OAuth client, using `Authentiq\OAuth2\Client\Provider\Authentiq` as the provider.

### Authorization Code Flow

```php
$provider = new Authentiq\OAuth2\Client\Provider\Authentiq([
    'clientId'     => 'authentiq-client-id',
    'clientSecret' => 'authentiq-client-secret',
    'redirectUri'  => 'your-callback-url',
    'scope'        => 'aq:name aq push email'
]);

// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl();

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }
    exit('Invalid state');

} else {
    try {
        // Try to get an the IdToken using the authorization code grant.
        $idToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        // Using the ID token, create the resource owner.
        $resourceOwner = $provider->getResourceOwner($idToken);
                
        // Now the $resourceOwner contains all the user info you need to create the user, 
        // store the unique user id from the sub 
        // or present the info you asked for.


    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {

        // Failed to get the access token or user details.
        exit($e->getMessage());

    }
}
```

## Refreshing a Token

Authentiq's OAuth implementation does not use refresh tokens.
