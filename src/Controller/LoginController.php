<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


class LoginController extends AbstractController
{
    #[Route('/login', name: 'korv_login')]
    public function login(#[CurrentUser] ?User $authenticatedUser, Request $request, Security $security, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $JWTManager): Response
    {
        if (null === $authenticatedUser) {
            return $this->json([
                'status' => 400,
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $JWTManager->create($authenticatedUser);

        return $this->json([
            'email' => $authenticatedUser->getEmail(),
            'roles' => $authenticatedUser->getRoles(),
            'token' => $token,
        ]);
    }

    public function logout(Security $security): Response
    {
        if ($security->getUser() === null) {
            return $this->json([
                'status' => 202,
                'message' => "The application don't have user authenticated to logout.",
            ], Response::HTTP_ACCEPTED);
        }
        $security->logout(false);

        return $this->json([
            'status' => 200,
            'message' => "The current user has successfully logged out of the application.",
        ], Response::HTTP_OK);
    }
}
