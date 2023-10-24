<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            $user = new User();
            $resultJson = json_decode($request->getContent(), true);

            $user->setEmail($resultJson["username"]);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $resultJson["password"]
                )
            );
            $user->setRoles($resultJson["roles"]);

            $userParams = get_object_vars($user);
            if ( count($userParams) <= 3 ) {
                return $this->json(['status' => '400', 'message' => 'Erro ao prosseguir com esse cadastro. Preencha todos os campos para prosseguir o cadastro'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
            }
            $userFound = $entityManager->getRepository(User::class)->findUserByEmail($user->getEmail());
            if ( $userFound > 0 ) {
                return $this->json(['status' => '400', 'message' => 'Erro ao prosseguir com esse cadastro. Existem alguns campos que não passaram na validação do cadastro'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json(['status' => '200', 'message' => 'Funcionário cadastrado com sucesso.'], 200, ['Content-Type'=>'application/json; charset=utf-8']);
        } catch (Exception $e) {
            throw $this->createAccessDeniedException('Cannot create new user with current credentials');
        }
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }
}
