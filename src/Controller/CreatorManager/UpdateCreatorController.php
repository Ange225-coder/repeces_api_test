<?php

    namespace App\Controller\CreatorManager;

    use App\Entity\Creator;
    use Doctrine\ORM\EntityManagerInterface;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class UpdateCreatorController extends AbstractController
    {
        public function __construct(
            private readonly EntityManagerInterface $entityManager,
            private readonly SerializerInterface $serializer,
            private readonly UserPasswordHasherInterface $passwordHasher,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/creators/me', name: 'update_creator', methods: ['PUT'])]
        #[IsGranted('ROLE_CREATOR', message: 'Vous n\'avez pas les droits requis pour éffectuer cette requête.')]
        public function updateCreator(Request $request): JsonResponse
        {
            $creator = $this->getUser();

            if(!$creator instanceof Creator) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }

            $newCreator = $this->serializer->deserialize($request->getContent(), Creator::class, 'json');

            if ($newCreator->getEmail()) {
                $creator->setEmail($newCreator->getEmail());
            }

            if ($newCreator->getLastName()) {
                $creator->setLastName($newCreator->getLastName());
            }

            if ($newCreator->getFirstName()) {
                $creator->setFirstName($newCreator->getFirstName());
            }

            if ($newCreator->getPassword()) {
                $creator->setPassword($this->passwordHasher->hashPassword($creator, $newCreator->getPassword()));
            }

            $this->cache->invalidateTags(['creatorTag']);

            //$this->entityManager->persist($newCreator);
            $this->entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
