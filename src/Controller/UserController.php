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

    #[Route('/user', name: 'korv_user_create', methods: 'POST')]
    public function postUser(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $user = new User();
        $resultJson = json_decode($request->getContent(), true);
        if ( count($resultJson) < 4 ) {
            return $this->json(['status' => 400, 'message' => 'Não foi possível cadastrar esse usuário, porque faltou informações para que seja efeituado o cadastro.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
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
            return $this->json(['status' => 400, 'message' => 'Não foi possível cadastrar esse cadastro, porque já existe um email informado já cadastrado.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['status' => 200, 'message' => 'Funcionário cadastrado com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/user/:id', name: 'korv_user_put', methods: 'PUT')]
    public function putUser(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $user = $entityManager->getRepository(User::class)->find($id);
        if (empty($user)) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível atualizar o usuário, porque o usuário informado não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $requestJSON = json_decode($request->getContent(), true);
        if ( count($requestJSON) < 3 ) {
            return $this->json(['status' => 422, 'message' => 'Não foi possível atualizar esse usuário, porque faltou informações para que seja efeituada a atualização.'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $user->setName($requestJSON["name"]);
        $user->setEmail($requestJSON["email"]);
        $user->setRoles($requestJSON["roles"]);
        $user->setActivated($requestJSON["activated"]);

        $entityManager->flush();

        return $this->json(['status' => 200, 'message' => 'Usuário atualizado com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/user/:id/status', name: 'korv_user_put_status', methods: 'PUT')]
    public function putUserStatus(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $user = $entityManager->getRepository(User::class)->find($id);
        if (empty($user)) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível atualizar o status do usuário, porque o usuário informado não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $requestJSON = json_decode($request->getContent(), true);
        if ( !array_key_exists("activated", $requestJSON) ) {
            return $this->json(['status' => 422, 'message' => 'Não foi possível atualizar o status do usuário, porque faltou para qual status ele será atualizado.'], 422, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        $user->setActivated($requestJSON["activated"]);

        $entityManager->flush();

        return $this->json(['status' => 200, 'message' => 'Status do usuário atualizado com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/users', name: 'korv_user_get', methods: 'GET')]
    public function getUsers(EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $users = $entityManager->getRepository(User::class)->findAllUsersWithoutPassword();

        return $this->json($users, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/user/:id', name: 'korv_user_get_with_id', methods: 'GET')]
    public function getUserWithId(EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $currentUser = $entityManager->getRepository(User::class)->findUserWithoutPassword($id)[0];
        if (!$currentUser) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível atualizar o status do usuário, porque o usuário informado não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        return $this->json($currentUser, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }
}
