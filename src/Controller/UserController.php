<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticatedVerifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private UserAuthenticatedVerifier $userAuthenticatedVerifier;

    public function __construct(UserAuthenticatedVerifier $userAuthenticatedVerifier, EmailVerifier $emailVerifier)
    {
        $this->userAuthenticatedVerifier = $userAuthenticatedVerifier;
    }

    #[Route('/user', name: 'korv_user_register', methods: 'POST')]
    public function postRegister(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        try {
            $user = new User();
            $resultJson = json_decode($request->getContent(), true);
            if ( count($resultJson) < 4 ) {
                return $this->json(['status' => '400', 'message' => 'Erro ao prosseguir com esse cadastro. Existem alguns campos que não passaram na validação do cadastro'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
            }

            $user->setName($resultJson["name"]);
            $user->setEmail($resultJson["email"]);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $resultJson["password"]
                )
            );
            $user->setRoles($resultJson["roles"]);
            $user->setActivated(true);
            $user->setCreatedAt(new \DateTimeImmutable('now'));
            $user->setLastLoginAt(new \DateTimeImmutable('now'));

            $userFound = $entityManager->getRepository(User::class)->findUserByEmail($user->getEmail());
            if ( $userFound !== [] ) {
                return $this->json(['status' => '400', 'message' => 'Erro ao prosseguir com esse cadastro. Existem alguns campos que não passaram na validação do cadastro'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['status' => '200', 'message' => 'Funcionário cadastrado com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
        } catch (Exception $e) {
            throw $this->createAccessDeniedException('Cannot create new user with current credentials');
        }
    }
}
