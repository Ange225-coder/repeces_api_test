<?php

    namespace App\Controller\RecepeManager;

    use App\Entity\Creator;
    use App\Entity\Recepe;
    use Doctrine\ORM\EntityManagerInterface;
    use JMS\Serializer\SerializerInterface;
    use Nelmio\ApiDocBundle\Attribute\Model;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\JsonResponse;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Attribute\Route;
    use Symfony\Component\Security\Http\Attribute\IsGranted;
    use Symfony\Component\Validator\Validator\ValidatorInterface;
    use Symfony\Contracts\Cache\TagAwareCacheInterface;
    use OpenApi\Attributes as OA;

    // Documentation
    #[OA\Put(summary: 'Mettre une recette à jour en fonction de son ID')]
    #[OA\RequestBody(
        description: 'Mettre à jour une recette en fonction de son identifiant',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: Recepe::class, groups: ['getRecepe'])
        )
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'L\'identifiant de la recette',
        in: 'path',
        schema: new OA\Schema(type: 'integer')
    )]
    #[OA\Response(
        response: 204,
        description: 'Le recette à été modifié'
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
    #[OA\Response(
        response: 404,
        description: 'La recette n\'as pas été trouvé'
    )]
    #[OA\Tag(name: 'Recepes Manager')]


    class UpdateRecepeController extends AbstractController
    {
        public function __construct(
            private readonly SerializerInterface $serializer,
            private readonly EntityManagerInterface $entityManager,
            private readonly TagAwareCacheInterface $cache,
            private readonly ValidatorInterface $validator
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

            // Validation
            $error = $this->validator->validate($currentRecepe);
            if ($error->count() > 0) {
                return new JsonResponse($this->serializer->serialize($error, 'json'), Response::HTTP_BAD_REQUEST, [], true);
            }

            // Invalidate cache
            $this->cache->invalidateTags(['recepeTag']);

            $this->entityManager->flush();

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
    }
