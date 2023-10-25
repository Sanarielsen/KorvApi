<?php

namespace App\Controller;

use App\Entity\Region;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegionController extends AbstractController
{
    #[Route('/region', name: 'app_region')]
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
}
