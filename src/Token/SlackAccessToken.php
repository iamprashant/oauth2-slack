<?php

namespace IamPrashant\OAuth2\Client\Token;

use IamPrashant\OAuth2\Client\Resource\SlackResourceAuthorizedUser;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Class SlackAccessToken
 *
 * @author Prashant Srivastav<p_srivastav@outlook.com>
 *
 */
class SlackAccessToken extends AccessToken
{
    /**
     * @var array
     */
    protected $scope;

    /**
     * @var string
     */
    protected $channel_id;

    /**
     * @var string
     */
    protected $channel_name;

    /**
     * @var string
     */
    protected $channel_configuration_url;

    /**
     * @var string
     */
    protected $channel_url;

    /**
     * @var string
     */
    protected $token_type;

    /**
     * @var bool
     */
    protected $is_enterprise_install = false;

    /**
     * @var string
     */
    protected $team_name;

    /**
     * @var string
     */
    protected $team_id;

    /**
     * @var SlackResourceAuthorizedUser
     */
    protected $authed_user;

    public function __construct(array $options)
    {

        parent::__construct($options);
        if (empty($options['ok']) && $options["ok"] == false) {
            throw new InvalidArgumentException('failed to obtain access_token');
        }

        if (!empty($options['scope'])) {
            $scope = explode(",", $options['scope']);
            $this->scope = $scope;

            if (in_array("incoming-webhook", $scope)) {
                $channel_info = $options['incoming_webhook'];

                if (!empty($channel_info['channel'])) {
                    $this->channel_name = $channel_info['channel'];
                }
                if (!empty($channel_info['channel_id'])) {
                    $this->channel_id = $channel_info['channel_id'];
                }
                if (!empty($channel_info['configuration_url'])) {
                    $this->channel_configuration_url = $channel_info['configuration_url'];
                }
                if (!empty($channel_info['url'])) {
                    $this->channel_url = $channel_info['url'];
                }
            }
        }

        if (!empty($options['is_enterprise_install'])) {
            $this->is_enterprise_install = $options['is_enterprise_install'];
        }

        if (!empty($options['team'])) {
            $team = $options['team'];
            if (!empty($team['name'])) {
                $this->team_name = $team['name'];
            }
            if (!empty($team['id'])) {
                $this->team_id = $team['id'];
            }

        }

        if (!empty($options['authed_user'])) {
            $this->authed_user = new SlackResourceAuthorizedUser($options['authed_user']);
        }

    }

    /**
     *
     * @return SlackResourceAuthorizedUser
     */
    public function getAuthedUser(): SlackResourceAuthorizedUser
    {
        return $this->authed_user;
    }
    /**
     *
     * @return bool
     */
    public function is_webhook_enabled(): bool
    {
        if ($this->scope && count($this->scope) > 0) {
            if (in_array("incoming-webhook", $this->scope)) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return bool
     */
    public function is_enterprise(): bool
    {
        return $this->is_enterprise_install;
    }

    /**
     * Get channel id
     *
     * @return string|null
     */
    public function getChannelId()
    {
        return $this->channel_id ?: null;
    }

    /**
     * Get channel name
     *
     * @return string|null
     */
    public function getChannelName()
    {
        return $this->channel_name ?: null;
    }

    /**
     * Get channel url
     *
     * @return string|null
     */
    public function getChannelUrl()
    {
        return $this->channel_url ?: null;
    }

    /**
     * Get Team Id
     *
     * @return string|null
     */
    public function getTeamId()
    {
        return $this->team_id ?: null;
    }

    /**
     * Get Team Name
     *
     * @return string|null
     */
    public function getTeamName()
    {
        return $this->team_name ?: null;
    }

    /**
     * Get scope
     *
     * @return array
     */
    public function getScope()
    {
        return $this->scope ?: [];
    }

}