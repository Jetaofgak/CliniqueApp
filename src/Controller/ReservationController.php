<?php
// src/Controller/ReservationController.php

namespace App\Controller;

use App\Entity\Reservation;
use App\Form\ReservationFormType;
use App\Form\ReservationSearchType;
use App\Repository\RoomRepository;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Room;
use App\Entity\Schedule;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/search', name: 'reservation_search', methods: ['GET', 'POST'])]
    public function search(Request $request, RoomRepository $roomRepository): Response
    {
        $form = $this->createForm(ReservationSearchType::class);
        $form->handleRequest($request);

        $availableRooms = [];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $startTime = $data['starttime'];
            $endTime = $data['endtime'];

            $availableRooms = $roomRepository->findAvailableRooms($startTime, $endTime);
        }

        return $this->render('reservation/search.html.twig', [
            'form' => $form->createView(),
            'available_rooms' => $availableRooms,
        ]);
    }


    

     #[Route('/new/{scheduleId}', name: 'reservation_new', methods: ['GET', 'POST'])]
     public function new(Request $request, ScheduleRepository $scheduleRepository, EntityManagerInterface $entityManager, int $scheduleId): Response
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
             $openTime = $reservation->getSchedule()->getOpenTime();
             $closeTime = $reservation->getSchedule()->getCloseTime();
     
             // Check availability against existing reservations for the schedule
             $existingReservations = $entityManager->getRepository(Reservation::class)->findBy(['schedule' => $schedule]);
             foreach ($existingReservations as $existingReservation) {
                 $existingOpenTime = $existingReservation->getSchedule()->getOpenTime();
                 $existingCloseTime = $existingReservation->getSchedule()->getCloseTime();
     
                 if (
                     ($openTime < $existingCloseTime && $closeTime > $existingOpenTime)
                 ) {
                     $this->addFlash('error', 'The room is already booked for the selected time period.');
                     return $this->redirectToRoute('schedule_index');
                 }
             }
     
             // If available, persist reservation
             $entityManager->persist($reservation);
             $entityManager->flush();
     
             return $this->redirectToRoute('schedule_index');
         }
     
         return $this->render('reservation/new.html.twig', [
             'form' => $form->createView(),
         ]);
     }


    #[Route('/', name: 'reservation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

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
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('reservation_index');
    }
}
