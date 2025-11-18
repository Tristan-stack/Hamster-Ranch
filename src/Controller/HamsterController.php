<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\HamsterRepository;
use App\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Faker\Factory;
use App\Entity\Hamster;


#[Route('/api', name: 'api_')]
final class HamsterController extends AbstractController
{

    public function __construct(private HamsterRepository $hamsterRepository, private SerializerInterface $serializer, private EntityManagerInterface $entityManager)
    {
        $this->hamsterRepository = $hamsterRepository;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
    }

    #[Route('/hamsters', name: 'hamsters_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $hamsters = $user->getHamsters();
        $json = $this->serializer->serialize(
            $hamsters,
            'json',
            ['groups' => 'read']
        );
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/hamsters/{id}', name: 'hamster_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $hamster = $this->hamsterRepository->find($id);

        if (!$hamster) {
            return new JsonResponse(['error' => 'Hamster non trouvé'], 404);
        }

        // Vérifier que l'utilisateur est le propriétaire OU qu'il est admin
        if ($hamster->getOwner() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $json = $this->serializer->serialize(
            $hamster,
            'json',
            ['groups' => 'read']
        );
        return new JsonResponse($json, 200, [], true);
    }

    #[Route('/hamsters/reproduce', name: 'hamster_reproduce', methods: ['POST'])]
    public function reproduce(Request $request): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $hamster1 = $this->hamsterRepository->find($data['idHamster1'] ?? 0);
        $hamster2 = $this->hamsterRepository->find($data['idHamster2'] ?? 0);

        if (
            !$hamster1 || !$hamster2 ||
            !$user->getHamsters()->contains($hamster1) ||
            !$user->getHamsters()->contains($hamster2) ||
            $hamster1->getActive() !== 1 ||
            $hamster2->getActive() !== 1 ||
            $hamster1->getGenre() === $hamster2->getGenre()
        ) {
            return new JsonResponse(['error' => 'Reproduction impossible'], 400);
        }

        // Vieillir tous les hamsters existants AVANT de créer le nouveau
        $this->ageAllHamsters($user);

        $faker = Factory::create();
        $newHamster = new Hamster();
        $newHamster->setName($faker->firstName());
        $newHamster->setHunger(100);
        $newHamster->setAge(0);
        $newHamster->setGenre($faker->randomElement(['m', 'f']));
        $newHamster->setActive(1);
        $newHamster->setOwner($user);
        $user->addHamster($newHamster);

        $this->entityManager->persist($newHamster);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($newHamster, 'json', ['groups' => 'read']);
        return new JsonResponse($json, 201, [], true);
    }

    private function ageAllHamsters(User $user): void
    {
        foreach ($user->getHamsters() as $hamster) {
            $hamster->setAge($hamster->getAge() + 5);
            $hamster->setHunger(max(0, $hamster->getHunger() - 5));
        }
    }
}
