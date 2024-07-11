<?php

// src/Controller/ReservationController.php
namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Form\ReservationFormType;
use App\Repository\ScheduleRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;


#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/new/{scheduleId}', name: 'reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ScheduleRepository $scheduleRepository, EntityManagerInterface $entityManager, string $scheduleId): Response
    {
        $schedule = $scheduleRepository->find($scheduleId);
        if (!$schedule) {
            throw $this->createNotFoundException('No schedule found for id ' . $scheduleId);
        }

        $reservation = new Reservation();
        $reservation->setSchedule($schedule);

        $form = $this->createForm(ReservationFormType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Check availability
            $existingReservations = $entityManager->getRepository(Reservation::class)->findBy(['schedule' => $schedule]);
            foreach ($existingReservations as $existingReservation) {
                if (
                    ($reservation->getStartTime() < $existingReservation->getEndTime() && $reservation->getEndTime() > $existingReservation->getStartTime())
                ) {
                    $this->addFlash('error', 'The room is already booked for the selected time period.');
                    return $this->redirectToRoute('schedule_index');
                }
            }

            $entityManager->persist($reservation);
            $entityManager->flush();

            return $this->redirectToRoute('schedule_index');
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[Route('/', name: 'reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAll();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }
      #[Route('/{id}/edit', name: 'reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationFormType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): RedirectResponse
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reservation_index');
    }
}
