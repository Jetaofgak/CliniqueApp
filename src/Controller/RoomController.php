<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\RoomType;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ScheduleRepository;
use App\Repository\RoomRepository;
use App\Repository\ReservationRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RoomController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ReservationRepository $reservationRepository;

    public function __construct(EntityManagerInterface $entityManager, ReservationRepository $reservationRepository)
    {
        $this->entityManager = $entityManager;
        $this->reservationRepository = $reservationRepository;
    }

    #[Route('/room', name: 'room_index')]
    public function index(): Response
    {
        if (!$this->getUser()) {
            return new RedirectResponse($this->generateUrl('app_login'));
        }
        $rooms = $this->entityManager->getRepository(Room::class)->findAll();

        return $this->render('room/index.html.twig', [
            'rooms' => $rooms,
        ]);
    }

    #[Route('/room/new', name: 'room_new')]
    public function new(Request $request): Response
    {
        $room = new Room();
        $form = $this->createForm(RoomType::class, $room);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($room);
            $this->entityManager->flush();

            return $this->redirectToRoute('room_index');
        }

        return $this->render('room/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/room/{name}/edit', name: 'room_edit')]
    public function edit(Request $request, string $name): Response
    {
        $room = $this->entityManager->getRepository(Room::class)->findOneBy(['name' => $name]);

        if (!$room) {
            throw $this->createNotFoundException('No room found for name ' . $name);
        }

        $form = $this->createForm(RoomType::class, $room);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('room_index');
        }

        return $this->render('room/edit.html.twig', [
            'form' => $form->createView(),
            'room' => $room,
        ]);
    }

    #[Route('/room/{id}/delete', name: 'room_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, RoomRepository $roomRepository): Response
    {
        $room = $roomRepository->find($id);
    
        if (!$room) {
            throw $this->createNotFoundException('Room not found');
        }
    
        if ($this->isCsrfTokenValid('delete' . $room->getId(), $request->request->get('_token'))) {
            $roomRepository->deleteRoomWithSchedulesAndReservations($room);
        }
    
        return $this->redirectToRoute('room_index');
    }

    #[Route('/room/{name}/schedule', name: 'room_schedule')]
    public function schedule(string $name, ScheduleRepository $scheduleRepository): Response
    {
        $room = $this->entityManager->getRepository(Room::class)->findOneBy(['name' => $name]);

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
    public function showStatistics(int $id): Response
    {
        $room = $this->entityManager->getRepository(Room::class)->find($id);

        if (!$room) {
            throw $this->createNotFoundException('Room not found');
        }

        // Fetch reservations for the room
        $reservations = $this->reservationRepository->findBy(['room' => $room]);

        // Prepare data for the chart
        $data = [];
        foreach ($reservations as $reservation) {
            $date = $reservation->getStarttime()->format('Y-m-d');
            if (!isset($data[$date])) {
                $data[$date] = 0;
            }
            $data[$date]++;
        }

        // Prepare chart data
        $dates = array_keys($data);
        $counts = array_values($data);

        return $this->render('room/statistics.html.twig', [
            'room' => $room,
            'dates' => $dates,
            'counts' => $counts,
        ]);
    }

    #[Route('/room/availability', name: 'room_availability')]
    public function availability(RoomRepository $roomRepository): Response
    {
        // Fetch all rooms and their schedules
        $rooms = $roomRepository->findAll();

        // Prepare room data to show availability
        $roomAvailability = [];
        foreach ($rooms as $room) {
            $schedules = $room->getSchedules();
            $reservations = [];
            foreach ($schedules as $schedule) {
                foreach ($schedule->getReservations() as $reservation) {
                    $reservations[] = [
                        'starttime' => $reservation->getStarttime(),
                        'endtime' => $reservation->getEndtime(),
                    ];
                }
            }
            $roomAvailability[] = [
                'room' => $room,
                'schedules' => $schedules,
                'reservations' => $reservations
            ];
        }

        return $this->render('room/availability.html.twig', [
            'roomAvailability' => $roomAvailability,
        ]);
    }
}

