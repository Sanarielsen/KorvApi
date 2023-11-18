<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Routing\Annotation\Route;
class LoginController extends AbstractController
{
    #[Route('/login', name: 'korv_login')]
    public function login(#[CurrentUser] ?User $authenticatedUser, JWTTokenManagerInterface $JWTManager, Security $security, EntityManagerInterface $entityManager): Response
    {
        if (null === $authenticatedUser) {
            return $this->json([
                'status' => 400,
                'message' => 'missing credentials',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $token = $JWTManager->create($authenticatedUser);
        $authenticatedUser->setLastLoginAt(new \DateTimeImmutable('now'));

        $entityManager->flush();

        $security->login($authenticatedUser, "json_login", "korv_login");

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
                'message' => "A aplicação não possui um usuário para deslogar.",
            ], Response::HTTP_ACCEPTED);
        }
        $security->logout(false);

        return $this->json([
            'status' => 200,
            'message' => "O usuário atual foi deslogado com sucesso da aplicação.",
        ], Response::HTTP_OK);
    }
}
