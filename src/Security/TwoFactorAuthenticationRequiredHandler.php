<?php
// src/Security/TwoFactorAuthenticationRequiredHandler.php
namespace App\Security;

use Scheb\TwoFactorBundle\Security\Http\Authentication\AuthenticationRequiredHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TwoFactorAuthenticationRequiredHandler implements AuthenticationRequiredHandlerInterface
{
    public function onAuthenticationRequired(Request $request, TokenInterface $token): Response
    {
        // Return the response to tell the client that two-factor authentication is required.
        return new Response('{"error": "access_denied", "two_factor_complete": false}');
    }
}
