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
}
