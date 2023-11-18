<?php

namespace App\Controller;

use App\Entity\Local;
use App\Entity\Region;
use App\Shared\ResponseMessage;
use App\Security\UserAuthenticatedVerifier;
use App\Validations\RequestPropertiesValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LocalController extends AbstractController
{
    private UserAuthenticatedVerifier $userAuthenticatedVerifier;
    private RequestPropertiesValidation $propertiesValidation;
    private ResponseMessage $responseMessage;

    public function __construct(ResponseMessage $responseMessage, UserAuthenticatedVerifier $userAuthenticatedVerifier, RequestPropertiesValidation $propertiesValidation)
    {
        $this->userAuthenticatedVerifier = $userAuthenticatedVerifier;
        $this->propertiesValidation = $propertiesValidation;
        $this->responseMessage = $responseMessage;
    }

    #[Route('/local', name: 'korv_local_create', methods: 'POST')]
    public function postLocal(Request $request, EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestJSON = json_decode($request->getContent(), true);
        if ($requestJSON === null) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível criar esse local, porque é necessário enviar um payload para essa requisição.');
        }
        if ( count($requestJSON) < 3 ) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível criar esse local, porque faltou algumas informações nesse envio.');
        }

        $local = new Local();
        $hasCorrectRequest = $this->propertiesValidation->isBothHasTheSameProperties($local, $requestJSON, ['id', 'address']);
        if (!$hasCorrectRequest) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível criar esse local, porque envio das informações dessa rota está incorreta.');
        }

        $regionRefer = $entityManager->getRepository(Region::class)->find($requestJSON['region']);
        if ( $regionRefer === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível criar esse local, porque a região informada não existe.');
        }

        $local->setName($requestJSON['name']);
        $local->setType($requestJSON['type']);

        $addressValue = "korv-" . $requestJSON['name'] . "-local";
        $addressValueUnderscore = str_replace(' ', '-', strtolower($addressValue));
        $local->setAddress($addressValueUnderscore);

        $local->setRegion($regionRefer);

        $entityManager->persist($local);
        $entityManager->flush();

        return $this->responseMessage->makeResponsePostMessage(200, 'Local criado com sucesso.');
    }

    #[Route('/local/:id', name: 'korv_local_put', methods: 'PUT')]
    public function putLocal(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestJSON = json_decode($request->getContent(), true);
        if ($requestJSON === null) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível atualizar esse local, porque é necessário enviar um payload para essa requisição.');
        }
        if ( count($requestJSON) < 4 ) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível atualizar esse local, porque faltou algumas informações nesse envio.');
        }

        $local = new Local();
        $hasCorrectRequest = $this->propertiesValidation->isBothHasTheSameProperties($local, $requestJSON, ['id']);
        if (!$hasCorrectRequest) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível atualizar esse local, porque envio das informações dessa rota está incorreta.');
        }

        $regionRefer = $entityManager->getRepository(Region::class)->find($requestJSON['region']);
        if ( $regionRefer === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível atualizar esse local, porque a região informada não existe.');
        }

        $local = $entityManager->getRepository(Local::class)->find($id);
        $local->setName($requestJSON['name']);
        $local->setType($requestJSON['type']);
        $local->setAddress($requestJSON['address']);
        $local->setRegion($regionRefer);

        $entityManager->flush();

        return $this->responseMessage->makeResponsePostMessage(200, 'Local atualizado com sucesso.');
    }

    #[Route('/local/:id', name: 'korv_local_delete', methods: 'DELETE')]
    public function deleteLocal(EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $localToBeDeleted = $entityManager->getRepository(Local::class)->find($id);
        if ( $localToBeDeleted === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível excluir esse local, porque o local informado não existe.');
        }

        $entityManager->remove($localToBeDeleted);
        $entityManager->flush();

        return $this->responseMessage->makeResponsePostMessage(200, 'Local excluído com sucesso.');
    }

    #[Route('/locals', name: 'korv_local_get', methods: 'GET')]
    public function getLocals(EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN', 'EMPLOYEE']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $locals = $entityManager->getRepository(Local::class)->findAllLocals();

        return $this->json($locals, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }

    #[Route('/local/{id}', name: 'korv_local_get_with_id', methods: 'GET')]
    public function getLocalWithId(EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN', 'EMPLOYEE']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $currentLocal = $entityManager->getRepository(Local::class)->findLocalById($id);
        if (!$currentLocal) {
            return $this->json(['status' => 404, 'message' => 'Não foi possível visualizar este local, porque o local informado não existe.'], 404, ['Content-Type'=>'application/json; charset=utf-8']);
        }

        return $this->json($currentLocal, 200, ['Content-Type'=>'application/json; charset=utf-8']);
    }
}
