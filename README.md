# OAuth 2.0 for Slack Provider

This package is based on League's [OAuth 2.0 Client](https://github.com/thephpleague/oauth2-client) with Slack v2 api.

## Installation

```bash
$ composer require iamprashant/oauth2-slack
```
## Usage

Usage is the same as The League's OAuth client, using `\IamPrashant\OAuth2\Client\Provider\Slack` as the provider.

### Authorize Url generation
```php
$provider = new \IamPrashant\OAuth2\Client\Provider\Slack([
    'clientId'          => '{client-id}',
    'clientSecret'      => '{client-secret}',
    'redirectUri'       => '{callback-url}',
]);
 
// to get authorization url
$authUrl = $provider->getAuthorizationUrl();


// if you need to request the user_scope also with global scope
$authUrl = $provider->getAuthorizationUrl(["user_scope"=>"users.profile:read,users:read.email,users:read,im:history"]);

// can request with global scope

$authUrl = $provider->getAuthorizationUrl(['scope' => 'user:read user:write file:write', "user_scope"=>"users.profile:read,users:read.email,users:read,im:history"]);


```

### Authorization Flow
```php
$provider = new \IamPrashant\OAuth2\Client\Provider\Slack([
    'clientId'          => '{client-id}',
    'clientSecret'      => '{client-secret}',
    'redirectUri'       => '{callback-url}',
]);

if (!isset($_GET['code'])) {

    // token requested
    $token = $provider->getAccessToken('authorization_code', [
        'code' => $_GET['code']
    ]);
    //  Token object will contains channels (if webhoooks enable) and team 
    try {
        // to get user details with complete informaiton
        $user = $provider->getResourceOwner($token);
        // Use these details to create a new profile
        var_dump($user);
    } catch (Exception $e) {
        // Failed to get user details
        exit('something got unexpected');
    }
 
    var_dump($token->getToken());
}
```

## Contributing

Feel free to create pull request, Thanks for contributing.
