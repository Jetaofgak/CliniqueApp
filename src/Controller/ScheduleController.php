<?php
// src/Controller/ScheduleController.php

namespace App\Controller;

use App\Entity\Schedule;
use App\Form\ScheduleFormType;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/schedule')]
class ScheduleController extends AbstractController
{
    #[Route('/', name: 'schedule_index', methods: ['GET'])]
    public function index(ScheduleRepository $scheduleRepository): Response
    {
        $schedules = $scheduleRepository->findAll();

        return $this->render('schedule/index.html.twig', [
            'schedules' => $schedules,
        ]);
    }

    #[Route('/new', name: 'schedule_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $schedule = new Schedule();
        $form = $this->createForm(ScheduleFormType::class, $schedule);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($schedule);
            $entityManager->flush();

            $this->addFlash('success', 'Schedule created successfully.');

            return $this->redirectToRoute('schedule_index');
        }

        return $this->render('schedule/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'schedule_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Schedule $schedule, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ScheduleFormType::class, $schedule);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Schedule updated successfully.');

            return $this->redirectToRoute('schedule_index');
        }

        return $this->render('schedule/edit.html.twig', [
            'form' => $form->createView(),
            'schedule' => $schedule,
        ]);
    }

    #[Route('/{id}', name: 'schedule_delete', methods: ['POST'])]
    public function delete(Request $request, Schedule $schedule, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$schedule->getId(), $request->request->get('_token'))) {
            $entityManager->remove($schedule);
            $entityManager->flush();

            $this->addFlash('success', 'Schedule deleted successfully.');
        }

        return $this->redirectToRoute('schedule_index');
    }
}
