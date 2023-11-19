<?php

namespace App\Controller;

use App\Entity\Region;
use App\Security\UserAuthenticatedVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegionController extends AbstractController
{
    private UserAuthenticatedVerifier $userAuthenticatedVerifier;

    public function __construct(UserAuthenticatedVerifier $userAuthenticatedVerifier)
    {
        $this->userAuthenticatedVerifier = $userAuthenticatedVerifier;
    }
    #[Route('/region', name: 'korv_region_create', methods: "POST")]
    public function postRegion(Request $request, EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestJSON = json_decode($request->getContent(), true);
        if (!array_key_exists("name", $requestJSON)) {
            return $this->json(['status' => 422, 'message' => 'Não foi possível criar essa região, porque faltou o nome dela.'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $region = new Region();
        $region->setName($requestJSON["name"]);

        $entityManager->persist($region);
        $entityManager->flush();

        return $this->json(['status' => 200, 'message' => 'Região criada com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/region/{id}', name: 'korv_region_update', methods: 'PUT')]
    public function putRegion(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $regionExists = $entityManager->getRepository(Region::class)->findOneByIdExists($id);
        if (!$regionExists) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível atualizar essa região, porque a região informada não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }
        $requestJSON = json_decode($request->getContent(), true);
        if (!array_key_exists("name", $requestJSON)) {
            return $this->json(['status' => 422, 'message' => 'Não foi possível atualizar essa região, porque faltou o nome dela.'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $region = $entityManager->getRepository(Region::class)->find($id);
        $region->setName($requestJSON["name"]);

        $entityManager->flush();

        return $this->json(['status' => 200, 'message' => 'Região atualizada com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/region/{id}', name: 'korv_region_delete', methods: 'DELETE')]
    public function deleteRegion(EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $regionExists = $entityManager->getRepository(Region::class)->findOneByIdExists($id);
        if (!$regionExists) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível excluir essa região, porque a região informada não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $region = $entityManager->getRepository(Region::class)->find($id);

        $entityManager->remove($region);
        $entityManager->flush();

        return $this->json(['status' => 200, 'message' => 'Região excluída com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/regions', name: 'korv_region_get', methods: 'GET')]
    public function getRegion(EntityManagerInterface $entityManager) : Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN', 'EMPLOYEE']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $regions = $entityManager->getRepository(Region::class)->returnAllRegions();

        return $this->json($regions, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/region/{id}', name: 'korv_region_get_with_id', methods: 'GET')]
    public function getRegionWithId(EntityManagerInterface $entityManager, int $id) : Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN', 'EMPLOYEE']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $currentRegion = $entityManager->getRepository(Region::class)->findOneByIdExists($id);
        if (!$currentRegion) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível visualizar essa região, porque a região informada não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        return $this->json($currentRegion[0], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }
}
