<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class SecurityController extends AbstractController
{
    private $totpAuthenticator;
    private $entityManager;

    public function __construct(
        TotpAuthenticatorInterface $totpAuthenticator,
        EntityManagerInterface $entityManager
    ) {
        $this->totpAuthenticator = $totpAuthenticator;
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('/2fa', name: 'two_factor_login')]
    public function twoFactorLogin(): Response
    {
        return $this->render('security/2fa.html.twig');
    }
}

 