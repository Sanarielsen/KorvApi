<?php

namespace App\Controller;

use App\Entity\Local;
use App\Entity\Sensor;
use App\Security\UserAuthenticatedVerifier;
use App\Shared\ResponseMessage;
use App\Validations\RequestPropertiesValidation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SensorController extends AbstractController
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
    #[Route('/sensor', name: 'korv_sensor_create', methods: 'POST')]
    public function postSensor(Request $request, EntityManagerInterface $entityManager): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestJSON = json_decode($request->getContent(), true);
        if ($requestJSON === null) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível cadastrar esse sensor, porque é necessário enviar um payload para essa requisição.');
        }
        if ( count($requestJSON) < 3 ) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível cadastrar esse sensor, porque faltou algumas informações nesse envio.');
        }

        $sensor = new Sensor();
        $hasCorrectRequest = $this->propertiesValidation->isBothHasTheSameProperties($sensor, $requestJSON, ['id', 'status', 'isActivated']);
        if (!$hasCorrectRequest) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível cadastrar esse sensor, porque envio das informações dessa rota está incorreta.');
        }

        $localRefer = $entityManager->getRepository(Local::class)->find($requestJSON['local']);
        if ( $localRefer === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível cadastrar esse sensor, porque o local informado não existe.');
        }

        $sensor->setName($requestJSON['name']);
        $sensor->setType($requestJSON['type']);
        $sensor->setStatus(false);
        $sensor->setIsActivated(true);

        $sensor->setLocal($localRefer);

        $entityManager->persist($sensor);
        $entityManager->flush();

        return $this->responseMessage->makeResponsePostMessage(200, 'Sensor cadastrado com sucesso.');
    }

    #[Route('/sensor/{id}', name: 'korv_sensor_put', methods: 'PUT')]
    public function putSensor(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $requestJSON = json_decode($request->getContent(), true);
        if ($requestJSON === null) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível atualizar esse sensor, porque é necessário enviar um payload para essa requisição.');
        }
        if ( count($requestJSON) < 5 ) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível atualizar esse sensor, porque faltou algumas informações nesse envio.');
        }

        $sensorCurrent = new Sensor();
        $hasCorrectRequest = $this->propertiesValidation->isBothHasTheSameProperties($sensorCurrent, $requestJSON, ['id']);
        if (!$hasCorrectRequest) {
            return $this->responseMessage->makeResponsePostMessage(442, 'Não foi possível atualizar esse sensor, porque envio das informações dessa rota está incorreta.');
        }

        $sensorCurrent = $entityManager->getRepository(Sensor::class)->find($id);
        if ( $sensorCurrent === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível atualizar esse sensor, porque o sensor informado não existe.');
        }

        $localRefer = $entityManager->getRepository(Local::class)->find($requestJSON['local']);
        if ( $localRefer === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível atualizar esse sensor, porque o local informado não existe.');
        }

        $sensorCurrent->setName($requestJSON['name']);
        $sensorCurrent->setType($requestJSON['type']);
        $sensorCurrent->setStatus($requestJSON['status']);
        $sensorCurrent->setIsActivated($requestJSON['isActivated']);
        $sensorCurrent->setLocal($localRefer);

        $entityManager->flush();

        return $this->responseMessage->makeResponsePostMessage(200, 'Sensor atualizado com sucesso.');
    }

    #[Route('/sensor/{id}', name: 'korv_sensor_delete', methods: 'DELETE')]
    public function deleteSensor(EntityManagerInterface $entityManager, int $id): Response
    {
        $accessResponse = $this->userAuthenticatedVerifier->getHasAccessInCurrentRoute(['KORV_ADMIN']);
        if ($accessResponse !== null) {
            return $accessResponse;
        }

        $sensorToBeDeleted = $entityManager->getRepository(Sensor::class)->find($id);
        if ( $sensorToBeDeleted === null ) {
            return $this->responseMessage->makeResponsePostMessage(400, 'Não foi possível excluir esse sensor, porque o sensor informado não existe.');
        }

        $entityManager->remove($sensorToBeDeleted);
        $entityManager->flush();

        return $this->responseMessage->makeResponsePostMessage(200, 'Sensor excluído com sucesso.');
    }
}
