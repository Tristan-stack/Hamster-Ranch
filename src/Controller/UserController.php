<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/api', name: 'api_')]
final class UserController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private SerializerInterface $serializer,
        private UserService $userService
    ) {}

    #[Route('/users', name: 'users_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->userRepository->findAll();

        $json = $this->serializer->serialize(
            $users,
            'json',
            ['groups' => 'read']
        );

        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => 'Email invalide'], 400);
        }

        if (strlen($password) < 8) {
            return new JsonResponse(['error' => 'Mot de passe trop court (min 8 caractères)'], 400);
        }

        try {
            $user = $this->userService->createUser($email, $password);
            $json = $this->serializer->serialize($user, 'json', ['groups' => 'read']);
            return new JsonResponse($json, 201, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}
