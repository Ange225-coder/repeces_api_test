<?php

    namespace App\Controller\RecepeManager;

    use App\Entity\Creator;
    use App\Entity\Recepe;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class DeleteRecepeController extends AbstractController
    {
        public function __construct(
            private readonly EntityManagerInterface $entityManager,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/recepes/{id}', name: 'delete_recepe', methods: ['DELETE'])]
        #[IsGranted('ROLE_CREATOR')]
        public function deleteRecepe(Recepe $currentRecepe): JsonResponse
        {
            $creator = $this->getUser();

            /**
             * Ici indiquer un message clair au lieu du null
             */
            if (!$creator instanceof Creator) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }

            if (!$currentRecepe->getCreator()->contains($creator)) {
                return new JsonResponse(['Erreur' => 'Vous n\'êtes pas autorisé à supprimer cette recette'], Response::HTTP_FORBIDDEN);
            }

            // Invalidate cache
            $this->cache->invalidateTags(['recepeTag']);

            $this->entityManager->remove($currentRecepe);
            $this->entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
