<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Annotation\Route;


class LoginController extends AbstractController
{
    #[Route('/login', name: 'korv_api')]
    public function index(#[CurrentUser] ?User $user): Response
    {
        if (null === $user) {
            return $this->json([
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = ""; // somehow create an API token for $user

        return $this->json([
            'user' => $user->getUserIdentifier(),
            'token' => $token,
        ]);
    }
}
