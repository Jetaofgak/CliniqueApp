<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ScheduleRepository;
use App\Repository\RoomRepository;
use App\Repository\ReservationRepository;

class RoomController extends AbstractController
{
    #[Route('/room', name: 'room_index')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $rooms = $entityManager->getRepository(Room::class)->findAll();

        return $this->render('room/index.html.twig', [
            'rooms' => $rooms,
        ]);
    }

    #[Route('/room/new', name: 'room_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($room);
            $entityManager->flush();

            return $this->redirectToRoute('room_index');
        }

        return $this->render('room/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/room/{name}/edit', name: 'room_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, string $name): Response
    {
        $room = $entityManager->getRepository(Room::class)->findOneBy(['name' => $name]);

        if (!$room) {
            throw $this->createNotFoundException('No room found for name ' . $name);
        }

        $form = $this->createForm(RoomType::class, $room);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('room_index');
        }

        return $this->render('room/edit.html.twig', [
            'form' => $form->createView(),
            'room' => $room,
        ]);
    }

    #[Route('/room/{name}/delete', name: 'room_delete', methods: ['POST'])]
    public function delete(Request $request, EntityManagerInterface $entityManager, string $name): Response
    {
        $room = $entityManager->getRepository(Room::class)->findOneBy(['name' => $name]);

        if ($room) {
            $entityManager->remove($room);
            $entityManager->flush();
        }

        return $this->redirectToRoute('room_index');
    }
    #[Route('/room/{name}/schedule', name: 'room_schedule')]
    public function schedule(string $name, ScheduleRepository $scheduleRepository, EntityManagerInterface $entityManager): Response
    {
        $room = $entityManager->getRepository(Room::class)->findOneBy(['name' => $name]);

        if (!$room) {
            throw $this->createNotFoundException('No room found for name ' . $name);
        }

        $schedules = $scheduleRepository->findBy(['room' => $room]);

        return $this->render('room/schedule.html.twig', [
            'room' => $room,
            'schedules' => $schedules,
        ]);
    }
    #[Route('/room/{id}/statistics', name: 'room_statistics')]
    public function statistics(int $id, RoomRepository $roomRepository, ReservationRepository $reservationRepository): Response
    {
        $room = $roomRepository->find($id);
        if (!$room) {
            throw $this->createNotFoundException('No room found for id ' . $id);
        }

        // Fetch reservations for this room
        $reservations = $reservationRepository->findBy(['room' => $room]);

        // Calculate statistics
        $totalReservations = count($reservations);
        $totalReservationTime = 0;
        foreach ($reservations as $reservation) {
            $totalReservationTime += $reservation->getEndTime()->getTimestamp() - $reservation->getStartTime()->getTimestamp();
        }

        return $this->render('room/statistics.html.twig', [
            'room' => $room,
            'totalReservations' => $totalReservations,
            'totalReservationTime' => $totalReservationTime,
        ]);
    }
}
