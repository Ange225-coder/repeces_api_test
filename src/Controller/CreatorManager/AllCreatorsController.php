<?php

    namespace App\Controller\CreatorManager;

    use App\Repository\CreatorRepository;
    use App\Service\VersioningService;
    use JMS\Serializer\SerializationContext;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class AllCreatorsController extends AbstractController
    {
        public function __construct(
            private readonly CreatorRepository $creatorRepository,
            private readonly SerializerInterface $serializer,
            private readonly VersioningService $versioningService,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/creators', name: 'all_creators', methods: ['GET'])]
        public function AllCreators(): JsonResponse
        {
            $creatorList = $this->creatorRepository->findAll();

            $context = SerializationContext::create();

            // Caching
            $idCache = 'all_creator_cache';
            $creatorJson = $this->cache->get($idCache, function (ItemInterface $item) use ($creatorList, $context) {
                // Check the caching message
                error_log('CACHING');

                $item->tag('creatorTag');

                $context->setGroups(['getCreator']);

                // Versioning
                $version = $this->versioningService->getVersion();
                $context->setVersion($version);

                return $this->serializer->serialize($creatorList, 'json', $context);
            });

            return new JsonResponse($creatorJson, Response::HTTP_OK, ['X-API-VERSION' => $this->versioningService->getVersion()], true);
        }
    }
