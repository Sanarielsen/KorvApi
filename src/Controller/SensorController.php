<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SensorController extends AbstractController
{
    #[Route('/sensor', name: 'app_sensor')]
    public function index(): Response
    {
        return $this->render('sensor/index.html.twig', [
            'controller_name' => 'SensorController',
        ]);
    }
}
