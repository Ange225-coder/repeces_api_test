<?php

    namespace App\Controller\RecepeManager;

    use App\Entity\Recepe;
    use App\Service\VersioningService;
    use JMS\Serializer\SerializationContext;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;

    class RecepeDetailsController extends AbstractController
    {
        public function __construct(
            private readonly SerializerInterface $serializer,
            private readonly VersioningService $versioningService
        ){}


        #[Route(path: '/api/recepes/{id}', name: 'recepe_details', methods: ['GET'])]
        public function recepeDetails(Recepe $recepe): JsonResponse
        {
            $context = SerializationContext::create();
            $context->setGroups(['getRecepe']);

            // Versioning
            $version = $this->versioningService->getVersion();
            $context->setVersion($version);

            $recepeDetailsJson = $this->serializer->serialize($recepe, 'json', $context);

            return new JsonResponse($recepeDetailsJson, Response::HTTP_OK, ['X-API-VERSION' => $version], true);
        }
    }
