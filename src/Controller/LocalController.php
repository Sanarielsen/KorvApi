<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocalController extends AbstractController
{
    #[Route('/local', name: 'app_local')]
    public function index(): Response
    {
        return $this->render('local/index.html.twig', [
            'controller_name' => 'LocalController',
        ]);
    }
}
