<?php
// src/Controller/TwoFactorController.php

namespace App\Controller;

use Endroid\QrCode\Builder\BuilderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;

class TwoFactorController extends AbstractController
{
    private $qrCodeBuilder;
    private $totpAuthenticator;
    private $entityManager;

    public function __construct(BuilderInterface $qrCodeBuilder, TotpAuthenticatorInterface $totpAuthenticator, EntityManagerInterface $entityManager)
    {
        $this->qrCodeBuilder = $qrCodeBuilder;
        $this->totpAuthenticator = $totpAuthenticator;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/2fa/setup", name="2fa_setup")
     */
    public function setup(UserInterface $user): Response
    {
        // Ensure the user is of the correct type
        if (!$user instanceof User) {
            throw new \LogicException('The logged in user is not of type User.');
        }

        // Generate or retrieve the secret for the user
        $secret = $user->getTotpSecret();
        if (!$secret) {
            // Generate new secret if it doesn't exist
            $secret = $this->totpAuthenticator->generateSecret();
            $user->setTotpSecret($secret);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $username = $user->getUsername();
        $otpauthUrl = 'otpauth://totp/' . urlencode($username) . '?secret=' . $secret . '&issuer=YourAppName';

        $qrCodeUrl = $this->generateQrCodeUrl($otpauthUrl);

        return $this->render('security/2fa_setup.html.twig', [
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    private function generateQrCodeUrl(string $otpauthUrl): string
    {
        $result = $this->qrCodeBuilder
            ->data($otpauthUrl)
            ->size(300)
            ->margin(10)
            ->build();

        return $result->getDataUri();
    }
}