<?php

    namespace App\Controller\CreatorManager;

    use App\Entity\Creator;
    use App\Service\VersioningService;
    use JMS\Serializer\SerializationContext;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    class CreatorDetailsController extends AbstractController
    {
        public function __construct(
            private readonly SerializerInterface $serializer,
            private readonly VersioningService $versioningService
        ){}


        #[Route(path: '/api/creators/{id}', name: 'creator_details', methods: ['GET'])]
        public function creatorDetails(Creator $creator): JsonResponse
        {
            $context = SerializationContext::create();
            $context->setGroups(['getCreator']);

            // Versioning
            $version = $this->versioningService->getVersion();
            $context->setVersion($version);

            $creatorDetailsJson = $this->serializer->serialize($creator, 'json', $context);

            return new JsonResponse($creatorDetailsJson, Response::HTTP_OK, ['X-API-VERSION' => $version], true);
        }
    }
