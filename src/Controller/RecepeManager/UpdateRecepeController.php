<?php

    namespace App\Controller\RecepeManager;

    use App\Entity\Creator;
    use App\Entity\Recepe;
    use Doctrine\ORM\EntityManagerInterface;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class UpdateRecepeController extends AbstractController
    {
        public function __construct(
            private readonly SerializerInterface $serializer,
            private readonly EntityManagerInterface $entityManager,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/recepes/{id}', name: 'update_recepe', methods: ['PUT'])]
        #[IsGranted('ROLE_CREATOR')]
        public function updateRecepe(Recepe $currentRecepe, Request $request): JsonResponse
        {
            $creator = $this->getUser();

            if (!$creator instanceof Creator) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }

            if (!$currentRecepe->getCreator()->contains($creator)) {
                return new JsonResponse(['Erreur' => 'Vous n\'êtes pas autorisé à modifier cette recette !'], Response::HTTP_FORBIDDEN);
            }

            $updatedRecepe = $this->serializer->deserialize($request->getContent(), Recepe::class, 'json');

            if ($updatedRecepe->getName()) {
                $currentRecepe->setName($updatedRecepe->getName());
            }

            if ($updatedRecepe->getDescription()) {
                $currentRecepe->setDescription($updatedRecepe->getDescription());
            }

            // Invalidate cache
            $this->cache->invalidateTags(['recepeTag']);

            $this->entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
