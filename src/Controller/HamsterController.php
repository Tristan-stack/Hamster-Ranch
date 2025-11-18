<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\HamsterRepository;
use Symfony\Component\Serializer\SerializerInterface;

final class HamsterController extends AbstractController
{

    public function __construct(private HamsterRepository $hamsterRepository, private SerializerInterface $serializer)
    {
        $this->hamsterRepository = $hamsterRepository;
        $this->serializer = $serializer;
    }

    // #[Route('/hamster', name: 'app_hamster')]
    // public function index(): JsonResponse
    // {
    //     return $this->json([
    //         'message' => 'Welcome to your new controller!',
    //         'path' => 'src/Controller/HamsterController.php',
    //     ]);
    // }

    #[Route('/hamsters', name: 'hamsters_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $hamsters = $this->hamsterRepository->findAll();
        $json = $this->serializer->serialize(
            $hamsters,
            'json',
            ['groups' => 'read']
        );
        return new JsonResponse($json, 200, [], true);
    }
}
