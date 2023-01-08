<?php

namespace IamPrashant\OAuth2\Client\Provider;

use IamPrashant\OAuth2\Client\Provider\Exception\SlackProviderException;
use IamPrashant\OAuth2\Client\Resource\SlackResourceAuthorizedUser;
use IamPrashant\OAuth2\Client\Resource\SlackResourceOwner;
use IamPrashant\OAuth2\Client\Token\SlackAccessToken;
use League\OAuth2\Client\Grant\AbstractGrant;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Slack
 *
 * @author Prashant Srivastav<p_srivastav@outlook.com>
 *
 */
class Slack extends AbstractProvider
{

    const AUTHORIZATION_URL = 'https://slack.com/oauth/v2/authorize';
    const ACCESS_TOKEN_URL = 'https://slack.com/api/oauth.v2.access';
    const AUTHORIZED_USER_URL = 'https://slack.com/api/users.info';

    public function __construct(array $options = [], array $collaborators = [])
    {
        parent::__construct($options, $collaborators);
    }

    /**
     * Returns the base URL for authorizing a client.
     *
     * @return string
     */
    public function getBaseAuthorizationUrl(): string
    {
        return self::AUTHORIZATION_URL;
    }

    /**
     * Returns the base URL for requesting an access token.
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params): string
    {
        return self::ACCESS_TOKEN_URL;
    }

    /**
     * Requests and returns the resource owner of given access token.
     *
     * @param  AccessToken $token
     * @return SlackResourceOwner
     */
    public function getResourceOwner(AccessToken $token)
    {
        $response = $this->fetchResourceOwnerDetails($token);
        return $this->createResourceOwner($response, $token);
    }

    /**
     * Returns the URL for requesting the resource owner's details.
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token): string
    {
        $authorizedUser = $this->getAuthorizedUser($token);
        $params = [
            'user' => $authorizedUser->getId(),
        ];
        return self::AUTHORIZED_USER_URL . '?' . http_build_query($params);
    }

    /**
     * Checks a provider response for errors.
     *
     * @param ResponseInterface $response
     * @param array|string      $data Parsed response data
     *
     * @return \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     * @throws \IamPrashant\OAuth2\Client\Provider\Exception\SlackProviderException
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (isset($data['ok']) && $data['ok'] === false) {
            return SlackProviderException::fromResponse($response, $data['error']);
        }
    }

    /**
     * Create new resources owner using the generated access token.
     *
     * @param array       $response
     * @param AccessToken $token
     *
     * @return SlackResourceOwner
     */
    protected function createResourceOwner(array $response, AccessToken $token): SlackResourceOwner
    {
        return new SlackResourceOwner($response);
    }

    /**
     * @return array
     */
    protected function getDefaultScopes(): array
    {
        return ["commands", "users.profile:read", "incoming-webhook"];
    }

    /**
     * @param SlackAccessToken $token
     *
     * @return mixed
     */
    public function fetchAuthorizedUserDetails(SlackAccessToken $token): SlackResourceAuthorizedUser
    {
        return $token->getAuthedUser();
    }

    /**
     * @param AccessToken $token
     *
     * @return SlackAuthorizedUser
     */
    public function getAuthorizedUser(SlackAccessToken $token): SlackResourceAuthorizedUser
    {
        return $this->fetchAuthorizedUserDetails($token);
    }

    /**
     * @param  mixed|null $token object or string
     *
     * @return array
     */
    protected function getAuthorizationHeaders($token = null): array
    {
        if ($token != null && $token) {
            if (is_string($token)) {
                return array(
                    "Authorization" => "Bearer " . $token,
                );
            }

            if (is_object($token) && $token instanceof AccessToken) {
                return array(
                    "Authorization" => "Bearer " . $token->getToken(),
                );
            }
        }
        return [];

    }

    /**
     * Builds the authorization URL.
     *
     * @param  array $options
     * @return string Authorization URL
     */
    public function getAuthorizationUrl($options = [])
    {
        $global_scope = $this->getDefaultScopes();
        $user_scope = [];
        if (array_key_exists("scope", $options)) {
            if (!is_array($options["scope"])) {
                $global_scope = explode($this->getScopeSeparator(), $options["scope"]);
            } else {
                $global_scope = $options["scope"];
            }
        }

        if (array_key_exists("user_scope", $options)) {
            if (!is_array($options["user_scope"])) {
                $user_scope = explode($this->getScopeSeparator(), $options["user_scope"]);
            } else {
                $user_scope = $options["scope"];
            }
        }

        $options['scope'] = implode(',', $global_scope);
        $options['user_scope'] = implode(',', $user_scope);
        $base = $this->getBaseAuthorizationUrl();

        $options += [
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
        ];
        $query = $this->getAuthorizationQuery($options);

        return $this->appendQuery($base, $query);
    }

    protected function createAccessToken(array $response, AbstractGrant $grant)
    {
        return new SlackAccessToken($response);
    }
}