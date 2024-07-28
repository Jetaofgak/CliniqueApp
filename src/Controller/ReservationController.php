<?php
// src/Controller/ReservationController.php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Schedule;
use App\Form\ReservationType;
use App\Entity\Schedule;
use App\Form\ReservationType;
use App\Form\ReservationSearchType;
use App\Repository\RoomRepository;
use App\Repository\ScheduleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Room;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

            $availableRooms = $roomRepository->findAvailableRooms($startTime, $endTime);
        }


        return $this->render('reservation/search.html.twig', [
            'form' => $form->createView(),
            'available_rooms' => $availableRooms,
        ]);
    }

    #[Route('/new/{scheduleId}', name: 'reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ScheduleRepository $scheduleRepository, $scheduleId): Response
    {
        $reservation = new Reservation();
        
        // Find the schedule by ID
        $schedule = $scheduleRepository->find($scheduleId);
        if (!$schedule) {
            throw $this->createNotFoundException('Schedule not found');
        }
    
        // Automatically set the room based on the schedule
        $room = $schedule->getRoom();
        if (!$room) {
            throw $this->createNotFoundException('Room not found for this schedule');
        }
    
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Automatically set the room based on the schedule
            $reservation->setRoom($room);
            $reservation->setSchedule($schedule);
    
            $em->persist($reservation);
            $em->flush();
    
            return $this->redirectToRoute('reservation_success');
        }
    
        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'room' => $room,  // Pass the room to the template if needed
        ]);
    }
    #[Route('/new/{scheduleId}', name: 'reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em, ScheduleRepository $scheduleRepository, $scheduleId): Response
    {
        $reservation = new Reservation();
        
        // Find the schedule by ID
        $schedule = $scheduleRepository->find($scheduleId);
        if (!$schedule) {
            throw $this->createNotFoundException('Schedule not found');
        }
    
        // Automatically set the room based on the schedule
        $room = $schedule->getRoom();
        if (!$room) {
            throw $this->createNotFoundException('Room not found for this schedule');
        }
    
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Automatically set the room based on the schedule
            $reservation->setRoom($room);
            $reservation->setSchedule($schedule);
    
            $em->persist($reservation);
            $em->flush();
    
            return $this->redirectToRoute('reservation_success');
        }
    
        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'room' => $room,  // Pass the room to the template if needed
        ]);
    }
    #[Route('/', name: 'reservation_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        if (!$this->getUser()) {
            return new RedirectResponse($this->generateUrl('app_login'));
        }
        $reservations = $entityManager->getRepository(Reservation::class)->findAll();

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/{id}/edit', name: 'reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form = $this->createForm(ReservationType::class, $reservation);
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
    #[Route('/reservation/success', name: 'reservation_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('reservation/success.html.twig');
    }
    #[Route('/reservation/success', name: 'reservation_success', methods: ['GET'])]
    public function success(): Response
    {
        return $this->render('reservation/success.html.twig');
    }
}
