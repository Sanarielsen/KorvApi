<?php

namespace App\Controller;

use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use function PHPUnit\Framework\isEmpty;

class RegionController extends AbstractController
{
    #[Route('/region', name: 'korv_region_create', methods: "POST")]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $requestJSON = json_decode($request->getContent(), true);
        if (!array_key_exists("name", $requestJSON)) {
            return $this->json(['status' => '422', 'message' => 'Erro ao criar a nova região.'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $region = new Region();
        $region->setName($requestJSON["name"]);

        $entityManager->persist($region);
        $entityManager->flush();

        return $this->json(['status' => '200', 'message' => 'Região criada com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/region/{id}', name: 'korv_region_update', methods: 'PUT')]
    public function putRegion(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $regionExists = $entityManager->getRepository(Region::class)->findOneByIdExists($id);
        if (!$regionExists) {
            return $this->json(['status' => '404', 'message' => 'A região informada não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }
        $requestJSON = json_decode($request->getContent(), true);
        if (!array_key_exists("name", $requestJSON)) {
            return $this->json(['status' => '422', 'message' => 'Erro ao modificar essa região.'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $region = $entityManager->getRepository(Region::class)->find($id);
        $region->setName($requestJSON["name"]);

        $entityManager->flush();

        return $this->json(['status' => '200', 'message' => 'Região atualizada com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/region', name: 'korv_region_delete', methods: 'DELETE')]
    public function deleteRegion(Request $request, EntityManagerInterface $entityManager): Response
    {
        $resultJson = json_decode($request->getContent(), true);
        if ( !is_array($resultJson) ) {
            return $this->json(['status' => '422'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }
        if (!array_key_exists("id", $resultJson)) {
            return $this->json(['status' => '422'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }
        $regionExists = $entityManager->getRepository(Region::class)->findOneByIdExists($resultJson["id"]);
        if (!$regionExists) {
            return $this->json(['status' => '404'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $region = $entityManager->getRepository(Region::class)->find($resultJson["id"]);

        $entityManager->remove($region);
        $entityManager->flush();

        return $this->json(['status' => '200', 'message' => 'Região excluída com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/regions', name: 'korv_region_get', methods: 'GET')]
    public function getRegion(EntityManagerInterface $entityManager) : Response
    {
        $regions = $entityManager->getRepository(Region::class)->findAll();

        return $this->json($regions, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/region/{id}', name: 'korv_region_get_with_id', methods: 'GET')]
    public function getRegionWithId(EntityManagerInterface $entityManager, int $id) : Response
    {
        $currentRegion = $entityManager->getRepository(Region::class)->find($id);
        if (!$currentRegion) {
            return $this->json(['status' => '404'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        return $this->json($currentRegion, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }
}
