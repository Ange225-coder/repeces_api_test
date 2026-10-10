<?php

    namespace App\Controller\CreatorManager;

    use App\Entity\Creator;
    use App\Service\VersioningService;
    use Doctrine\ORM\EntityManagerInterface;
    use JMS\Serializer\SerializationContext;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class CreateCreatorController extends AbstractController
    {
        public function __construct(
            private readonly SerializerInterface $serializer,
            private readonly ValidatorInterface $validator,
            private readonly EntityManagerInterface $entityManager,
            private readonly UrlGeneratorInterface $urlGenerator,
            private readonly UserPasswordHasherInterface $passwordHasher,
            private readonly VersioningService $versioningService,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/creators', name: 'create_creator', methods: ['POST'])]
        public function createCreator(Request $request): JsonResponse
        {
            $newCreator = $this->serializer->deserialize($request->getContent(), Creator::class, 'json');

            // Validation
            $errors = $this->validator->validate($newCreator);
            if ($errors->count() > 0) {
                return new JsonResponse($this->serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            }

            // Hashed password before saving
            $passwordHashed = $this->passwordHasher->hashPassword($newCreator, $newCreator->getPassword());
            $newCreator->setPassword($passwordHashed);

            // Invalidate creator cache
            $this->cache->invalidateTags(['creatorTag']);

            $this->entityManager->persist($newCreator);
            $this->entityManager->flush();

            $location = $this->urlGenerator->generate('creator_details', ['id' => $newCreator->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $context = SerializationContext::create();
            $context->setGroups(['getCreator']);

            // Versioning
            $version = $this->versioningService->getVersion();
            $context->setVersion($version);

            $newCreatorJson = $this->serializer->serialize($newCreator, 'json', $context);

            return new JsonResponse($newCreatorJson, Response::HTTP_CREATED, ['Location' => $location, 'X-API-VERSION' => $version], true);
        }
    }
