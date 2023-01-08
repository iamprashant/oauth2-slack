<?php

namespace IamPrashant\OAuth2\Client\Resource;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class SlackResourceOwner
 *
 * @author Prashant Srivastav<p_srivastav@outlook.com>
 *
 */
class SlackResourceAuthorizedUser implements ResourceOwnerInterface
{
    /**
     * @var array
     */
    protected $response;

    /**
     * SlackResourceAuthorizedUser constructor.
     *
     * @param $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     *
     * @return string
     */
    public function getId()
    {
        return $this->response['id'];
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }

    /**
     * Get user access token
     *
     * @return string|null
     */
    public function getAccessToken()
    {
        return $this->response['access_token'] ?: null;
    }

    /**
     * Get token type
     *
     * @return string|null
     */
    public function getTokenType()
    {
        return $this->response['token_type'] ?: null;
    }
}