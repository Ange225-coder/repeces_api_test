<?php

    namespace App\Controller\RecepeManager;

    use App\Repository\RecepeRepository;
    use App\Service\VersioningService;
    use JMS\Serializer\SerializationContext;
    use JMS\Serializer\SerializerInterface;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Contracts\Cache\ItemInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;

    class AllRecepeController extends AbstractController
    {
        public function __construct(
            private readonly RecepeRepository $recepeRepository,
            private readonly SerializerInterface $serializer,
            private readonly VersioningService $versioningService,
            private readonly TagAwareCacheInterface $cache
        ){}


        #[Route(path: '/api/recepes', name: 'all_recepes', methods: ['GET'])]
        public function allRecepes(Request $request): JsonResponse
        {
            // Pagination
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 3);

            // Groups context
            $context = SerializationContext::create();
            $context->setGroups(['getRecepe']);

            // Caching
            $idCache = 'get_all_recepes_'.$page.'_'.$limit;
            $recepeListJson = $this->cache->get($idCache, function (ItemInterface $item) use ($page, $limit,$context) {

                // For checking cache
                error_log('RECEPE CACHING');

                $item->tag('recepeTag');

                $recepeList = $this->recepeRepository->findAllWithPagination($page,$limit);

                return $this->serializer->serialize($recepeList, 'json', $context);
            });

            // Versioning
            $version = $this->versioningService->getVersion();
            $context->setVersion($version);

            return new JsonResponse($recepeListJson, Response::HTTP_OK, ['X-API-VERSION' => $version], true);
        }
    }
