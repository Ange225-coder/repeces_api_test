<?php

    namespace App\Controller\RecepeManager;

    use App\Entity\Creator;
    use App\Entity\Recepe;
    use App\Service\VersioningService;
    use Doctrine\ORM\EntityManagerInterface;
    use JMS\Serializer\SerializationContext;
    use JMS\Serializer\SerializerInterface;
    use Nelmio\ApiDocBundle\Attribute\Model;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;
    use OpenApi\Attributes as OA;

    // Documentation
    #[OA\Post(summary: 'Créer une recette')]
    #[OA\RequestBody(
        description: 'Création d\'une recette',
        content: new OA\JsonContent(
            ref: new Model(type: Recepe::class, groups: ['getRecepe'])
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Une recette vient d\'être crée',
        content: new OA\JsonContent(
            ref: new Model(type: Recepe::class, groups: ['getRecepe'])
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'La requête a été mal formulée',
    )]
    #[OA\Response(
        response: 401,
        description: 'Vous n\'êtes pas autorisé a éffectuer cette requête ou le token JWT est invalide'
    )]
    #[OA\Response(
        response: 403,
        description: 'Vous n\'avez pas les droits suffisants pour éffectuer cette requête'
    )]
    #[OA\Tag(name: 'Recepes Manager')]


    class CreateRecepeController extends AbstractController
    {
        public function __construct(
            private readonly SerializerInterface $serializer,
            private readonly VersioningService $versioningService,
            private readonly EntityManagerInterface $entityManager,
            private readonly ValidatorInterface $validator,
            private readonly UrlGeneratorInterface $urlGenerator,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/recepes', name: 'create_recepe', methods: ['POST'])]
        #[IsGranted('ROLE_CREATOR', message: 'Vous n\'avez pas les droits requis pour créer une recette')]
        public function createRecepe(Request $request): JsonResponse
        {
            $creator = $this->getUser();

            if (!$creator instanceof Creator) {
                return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
            }

            $newRecepe = $this->serializer->deserialize($request->getContent(), Recepe::class, 'json');

            // Bind creator which logged by JWT
            $newRecepe->addCreator($creator);

            // Validation
            $error = $this->validator->validate($newRecepe);
            if($error->count() > 0) {
                return new JsonResponse($this->serializer->serialize($error, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            }

            // Invalidate cache
            $this->cache->invalidateTags(['recepeTag']);

            $this->entityManager->persist($newRecepe);
            //$this->entityManager->persist($creator);
            $this->entityManager->flush();

            // Groups context
            $context = SerializationContext::create();
            $context->setGroups(['getRecepe']);

            // Versioning
            $version = $this->versioningService->getVersion();
            $context->setVersion($version);

            $location = $this->urlGenerator->generate('recepe_details', ['id' => $newRecepe->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

            $newRecepeJson = $this->serializer->serialize($newRecepe, 'json', $context);

            return new JsonResponse($newRecepeJson, Response::HTTP_CREATED, ['Location' => $location, 'X-API-VERSION' => $version], true);
        }
    }
