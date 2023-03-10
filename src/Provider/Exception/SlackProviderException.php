<?php

namespace IamPrashant\OAuth2\Client\Provider\Exception;

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Psr\Http\Message\ResponseInterface;

class SlackProviderException extends IdentityProviderException
{
    /**
     * @param  ResponseInterface $response
     * @param  string|null       $message
     *
     * @return IdentityProviderException
     * @throws \IamPrashant\OAuth2\Client\Provider\Exception\SlackProviderException
     */
    public static function fromResponse(ResponseInterface $response, $message = null): SlackProviderException
    {
        throw new static($message, $response->getStatusCode(), (string) $response->getBody());
    }
}