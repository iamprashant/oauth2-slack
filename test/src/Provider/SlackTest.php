<?php
namespace IamPrashant\OAuth2\Client\Test\Provider;

use IamPrashant\OAuth2\Client\Provider\Slack;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SlackTest extends TestCase
{
    protected $oAuthProvider;

    protected static function getMethod($name)
    {
        $class = new ReflectionClass('IamPrashant\OAuth2\Client\Provider\Slack');
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    protected function setUp(): void
    {
        $this->oAuthProvider = new Slack([
            'clientId' => '2891448282336.4600541919650',
            'clientSecret' => 'fcdc7d349c1f2333db2c68deba38579b',
            'redirectUri' => 'https://4e08-128-106-235-65.ap.ngrok.io/users/slack_login',
        ]);
    }

    protected function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testAuthorizationUrl()
    {
        $url = $this->oAuthProvider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('scope', $query);

    }

    public function testAuthorizationUrlWithUserScope()
    {
        $url = $this->oAuthProvider->getAuthorizationUrl(["user_scope" => "users.profile:read,users:read.email,users:read,im:history"]);
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('user_scope', $query);

    }
    public function testGetAuthorizationUrl()
    {
        $params = [];
        $url = $this->oAuthProvider->getAuthorizationUrl($params);
        $uri = parse_url($url);

        $this->assertEquals('/oauth/v2/authorize', $uri['path']);
    }

    public function testGetBaseAccessTokenUrl()
    {
        $params = [];
        $url = $this->oAuthProvider->getBaseAccessTokenUrl($params);
        $uri = parse_url($url);
        $this->assertEquals('/api/oauth.v2.access', $uri['path']);
    }

    public function testGetResourceOwnerDetailsUrl()
    {
        $authUser = json_decode('{"ok": true,"url": "https:\/\/myteam.slack.com\/","team": "My Team","user": "cal","team_id": "T12345","user_id": "U12345"}', true);
        $token = m::mock('IamPrashant\OAuth2\Client\Token\SlackAccessToken', [['ok' => true, 'access_token' => 'sample-access_token']]);
        $token->shouldReceive('__toString')->andReturn('sample-access_token');

        $provider = m::mock('IamPrashant\OAuth2\Client\Provider\Slack');
        $provider->shouldReceive('getAuthorizedUser')->andReturn($authUser);
        $provider->shouldReceive('getResourceOwnerDetailsUrl')
            ->once()->andReturn('https://slack.com/api/users.info?user=U12345');

        $url = $provider->getResourceOwnerDetailsUrl($token);
        $uri = parse_url($url);

        $this->assertEquals('/api/users.info', $uri['path']);
        $this->assertEquals('user=U12345', $uri['query']);
    }

    public function testGetAccessToken()
    {
        $tokens = array(
            "parent" => "parent_sample-token",
            "authed_user" => "authed_user_token",
        );

        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"ok":true,"app_id":"xx","authed_user":{"id":"xx","scope":"im:history,users:read,users:read.email,users.profile:read","access_token":"authed_user_token","token_type":"user"},"scope":"commands,users:read.email,users:read,incoming-webhook","token_type":"bot","bot_user_id":"xx","team":{"id":"xxxx-team-id","name":"xxxx-team"},"enterprise":null,"is_enterprise_install":false,"incoming_webhook":{"channel":"#ch-xxxxx","channel_id":"xxxxx-ch-id","configuration_url":"https:\/\/intelcues.slack.com\/services\/xx","url":"https:\/\/hooks.slack.com\/services\/xx\/B04HY08G1C2\/xx"},"access_token":"parent_sample-token"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->oAuthProvider->setHttpClient($client);
        $token = $this->oAuthProvider->getAccessToken('authorization_code', ['code' => 'sample-code']);
        $this->assertEquals('parent_sample-token', $token->getToken());
        $this->assertEquals('xxxx-team', $token->getTeamName());
        $this->assertEquals('xxxx-team-id', $token->getTeamId());
        $this->assertEquals('xxxxx-ch-id', $token->getChannelId());
        $this->assertEquals('#ch-xxxxx', $token->getChannelName());
        $this->assertNull($token->getRefreshToken());
        $this->assertNull($token->getResourceOwnerId());
    }

    public function testCheckResponseThrowsIdentityProviderException()
    {
        $this->expectException(\IamPrashant\OAuth2\Client\Provider\Exception\SlackProviderException::class);
        $this->expectExceptionMessage('not_authed');
        $response = m::mock('Psr\Http\Message\ResponseInterface');
        $response->shouldReceive('getBody')->andReturn('{"ok": false, "error": "not_authed"}');
        $response->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $response->shouldReceive('getStatusCode')->andReturn(401);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')->once()->andReturn($response);
        $this->oAuthProvider->setHttpClient($client);
        $this->oAuthProvider->getAccessToken('authorization_code', ['code' => 'sample-authorization_code']);

    }

    public function testGetAuthorizedUserDetails()
    {

        $authed_user_token = "user-token";
        $token_type = "user";
        $user_id = uniqid();

        $postResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $postResponse->shouldReceive('getBody')->andReturn('{"ok": true, "scope": "commands,users:read.email,users:read","access_token": "sample-token-0","enterprise": null,"is_enterprise_install": false, "authed_user":{"id":"' . $user_id . '","scope":"im:history,users:read,users:read.email,users.profile:read","access_token":"' . $authed_user_token . '","token_type":"' . $token_type . '"}}');
        $postResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $postResponse->shouldReceive('getStatusCode')->andReturn(200);
        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(1)
            ->andReturn($postResponse);
        $this->oAuthProvider->setHttpClient($client);

        $token = $this->oAuthProvider->getAccessToken('authorization_code', ['code' => 'sample-authorization_code']);
        $user = $this->oAuthProvider->getAuthorizedUser($token);

        $this->assertEquals($user_id, $user->getId());
        $this->assertEquals($token_type, $user->getTokenType());
        $this->assertEquals($authed_user_token, $user->getAccessToken());

    }

    public function testGetResourceOwnerDetails()
    {
        $id = uniqid();
        $name = uniqid();
        $deleted = false;
        $color = uniqid();
        $profile = [
            "first_name" => uniqid(),
            "last_name" => uniqid(),
            "real_name" => uniqid(),
            "email" => uniqid(),
            "skype" => uniqid(),
            "phone" => uniqid(),
            "image_24" => uniqid(),
            "image_32" => uniqid(),
            "image_48" => uniqid(),
            "image_72" => uniqid(),
            "image_192" => uniqid(),
        ];

        $isAdmin = true;
        $isOwner = true;
        $has2FA = true;
        $hasFiles = true;

        $accessTokenResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $accessTokenResponse->shouldReceive('getBody')->andReturn('{"ok": true, "scope": "commands,users:read.email,users:read","access_token": "sample-token-0","enterprise": null,"is_enterprise_install": false, "authed_user":{"id":"xx","scope":"im:history,users:read,users:read.email,users.profile:read","access_token":"authed_user_token","token_type":"user"}}');
        $accessTokenResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $accessTokenResponse->shouldReceive('getStatusCode')->andReturn(200);

        $userResponse = m::mock('Psr\Http\Message\ResponseInterface');
        $userResponse->shouldReceive('getBody')->andReturn('{"ok": true,"user": {"id": "' . $id . '","name": "' . $name . '","deleted": false,"color": "' . $color . '","profile": {"first_name": "' . $profile["first_name"] . '","last_name": "' . $profile["last_name"] . '","real_name": "' . $profile["real_name"] . '","email": "' . $profile["email"] . '","skype": "' . $profile["skype"] . '","phone": "' . $profile["phone"] . '","image_24": "' . $profile["image_24"] . '","image_32": "' . $profile["image_32"] . '","image_48": "' . $profile["image_48"] . '","image_72": "' . $profile["image_72"] . '","image_192": "' . $profile["image_192"] . '"},"is_admin": true,"is_owner": true,"has_2fa": true,"has_files": true}}');
        $userResponse->shouldReceive('getHeader')->andReturn(['content-type' => 'json']);
        $userResponse->shouldReceive('getStatusCode')->andReturn(200);

        $client = m::mock('GuzzleHttp\ClientInterface');
        $client->shouldReceive('send')
            ->times(2)
            ->andReturn($accessTokenResponse, $userResponse);
        $this->oAuthProvider->setHttpClient($client);

        $token = $this->oAuthProvider->getAccessToken('authorization_code', ['code' => 'sample-authorization_code']);
        $user = $this->oAuthProvider->getResourceOwner($token);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($id, $user->toArray()['user']['id']);
        $this->assertEquals($name, $user->getName());
        $this->assertEquals($name, $user->toArray()['user']['name']);
        $this->assertEquals($deleted, $user->isDeleted());
        $this->assertEquals($deleted, $user->toArray()['user']['deleted']);
        $this->assertEquals($color, $user->getColor());
        $this->assertEquals($color, $user->toArray()['user']['color']);
        $this->assertEquals($profile, $user->getProfile());
        $this->assertEquals($profile, $user->toArray()['user']['profile']);

        $this->assertEquals($profile['first_name'], $user->getFirstName());
        $this->assertEquals($profile['first_name'], $user->toArray()['user']['profile']['first_name']);
        $this->assertEquals($profile['last_name'], $user->getLastName());
        $this->assertEquals($profile['last_name'], $user->toArray()['user']['profile']['last_name']);
        $this->assertEquals($profile['real_name'], $user->getRealName());
        $this->assertEquals($profile['real_name'], $user->toArray()['user']['profile']['real_name']);
        $this->assertEquals($profile['email'], $user->getEmail());
        $this->assertEquals($profile['email'], $user->toArray()['user']['profile']['email']);
        $this->assertEquals($profile['skype'], $user->getSkype());
        $this->assertEquals($profile['skype'], $user->toArray()['user']['profile']['skype']);
        $this->assertEquals($profile['phone'], $user->getPhone());
        $this->assertEquals($profile['phone'], $user->toArray()['user']['profile']['phone']);
        $this->assertEquals($profile['image_24'], $user->getImage24());
        $this->assertEquals($profile['image_24'], $user->toArray()['user']['profile']['image_24']);
        $this->assertEquals($profile['image_32'], $user->getImage32());
        $this->assertEquals($profile['image_32'], $user->toArray()['user']['profile']['image_32']);
        $this->assertEquals($profile['image_48'], $user->getImage48());
        $this->assertEquals($profile['image_48'], $user->toArray()['user']['profile']['image_48']);
        $this->assertEquals($profile['image_72'], $user->getImage72());
        $this->assertEquals($profile['image_72'], $user->toArray()['user']['profile']['image_72']);
        $this->assertEquals($profile['image_192'], $user->getImage192());
        $this->assertEquals($profile['image_192'], $user->toArray()['user']['profile']['image_192']);

        $this->assertEquals($isAdmin, $user->isAdmin());
        $this->assertEquals($isAdmin, $user->toArray()['user']['is_admin']);
        $this->assertEquals($isOwner, $user->isOwner());
        $this->assertEquals($isOwner, $user->toArray()['user']['is_owner']);
        $this->assertEquals($has2FA, $user->hasTwoFactorAuthentication());
        $this->assertEquals($has2FA, $user->toArray()['user']['has_2fa']);
        $this->assertEquals($hasFiles, $user->hasFiles());
        $this->assertEquals($hasFiles, $user->toArray()['user']['has_files']);

    }
}