<?php
// src/Security/TwoFactorAuthenticationFailureHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class TwoFactorAuthenticationFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        // Return the response to tell the client that two-factor authentication failed.
        return new Response('{"error": "2fa_failed", "two_factor_complete": false}');
    }
}
