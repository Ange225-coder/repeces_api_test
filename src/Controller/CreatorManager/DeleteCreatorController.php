<?php

    namespace App\Controller\CreatorManager;

    use App\Entity\Creator;
    use Doctrine\ORM\EntityManagerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class DeleteCreatorController extends AbstractController
    {
        public function __construct(
            private readonly EntityManagerInterface $entityManager,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/creators/me', name: 'delete_creator', methods: ['DELETE'])]
        #[IsGranted('ROLE_CREATOR')]
        public function deleteCreator(): JsonResponse
        {
            $creator = $this->getUser();

            if (!$creator instanceof Creator) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }

            // Invalidate the creator tag
            $this->cache->invalidateTags(['creatorTag']);

            $this->entityManager->remove($creator);
            $this->entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
