<?php

namespace IamPrashant\OAuth2\Client\Resource;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Class SlackResourceOwner
 *
 * @author Prashant Srivastav<p_srivastav@outlook.com>
 *
 */
class SlackResourceOwner implements ResourceOwnerInterface
{

    /**
     * @var array
     */
    protected $response;

    /**
     * SlackResourceOwner constructor.
     *
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->response = $response;
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
     * Return user id
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->response['user']['id'] ?: null;
    }

    /**
     * Returns user name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->response['user']['name'] ?: null;
    }

    /**
     * Is user deleted?
     *
     * @return bool|null
     */
    public function isDeleted()
    {
        return $this->response['user']['deleted'] ?: null;
    }

    /**
     * Returns user color
     *
     * @return string|null
     */
    public function getColor()
    {
        return $this->response['user']['color'] ?: null;
    }

    /**
     * Returns user first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        $profile = $this->getProfile();
        return $profile['first_name'] ?: null;
    }

    /**
     * Returns user last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        $profile = $this->getProfile();
        return $profile['last_name'] ?: null;
    }

    /**
     * Returns user real name
     *
     * @return string|null
     */
    public function getRealName()
    {
        $profile = $this->getProfile();
        return $profile['real_name'] ?: null;
    }

    /**
     * Returns user email
     *
     * @return string|null
     */
    public function getEmail()
    {
        $profile = $this->getProfile();
        return $profile['email'] ?: null;
    }

    /**
     * Returns Skype username
     *
     * @return string|null
     */
    public function getSkype()
    {
        $profile = $this->getProfile();
        return $profile['skype'] ?: null;
    }

    /**
     * Returns phone number
     *
     * @return string|null
     */
    public function getPhone()
    {
        $profile = $this->getProfile();
        return $profile['phone'] ?: null;
    }

    /**
     * Returns 24x24 image url
     *
     * @return string|null
     */
    public function getImage24()
    {
        $profile = $this->getProfile();
        return $profile['image_24'] ?: null;
    }

    /**
     * Returns 32x32 image url
     *
     * @return string|null
     */
    public function getImage32()
    {
        $profile = $this->getProfile();
        return $profile['image_32'] ?: null;
    }

    /**
     * Returns 48x48 image url
     *
     * @return string|null
     */
    public function getImage48()
    {
        $profile = $this->getProfile();
        return $profile['image_48'] ?: null;
    }

    /**
     * Returns 72x72 image url
     *
     * @return string|null
     */
    public function getImage72()
    {
        $profile = $this->getProfile();
        return $profile['image_72'] ?: null;
    }

    /**
     * Returns 192x192 image url
     *
     * @return string|null
     */
    public function getImage192()
    {
        $profile = $this->getProfile();
        return $profile['image_192'] ?: null;
    }

    /**
     *
     * @return bool|null
     */
    public function isAdmin()
    {
        return $this->response['user']['is_admin'] ?: null;
    }

    /**
     *
     * @return string|null
     */
    public function isOwner()
    {
        return $this->response['user']['is_owner'] ?: null;
    }

    /**
     *
     * @return bool|null
     */
    public function hasTwoFactorAuthentication()
    {
        return $this->response['user']['has_2fa'] ?: null;
    }

    /**
     *
     * @return bool|null
     */
    public function hasFiles()
    {
        return $this->response['user']['has_files'] ?: null;
    }

    /**
     * Timezone
     *
     * @return string|null
     */
    public function getTimezone()
    {
        return $this->response['user']['tz'] ?: null;
    }

    /**
     * get User Profile
     *
     * @return array
     */
    public function getProfile()
    {
        if (array_key_exists('user', $this->response) && array_key_exists('profile', $this->response['user'])) {
            return $this->response['user']["profile"] ?: null;
        }

    }

}