<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScheduleController extends AbstractController
{
    #[Route('/schedule', name: 'app_schedule')]
    public function index(): Response
    {
        return $this->render('schedule/index.html.twig', [
            'controller_name' => 'ScheduleController',
        ]);
    }
}
