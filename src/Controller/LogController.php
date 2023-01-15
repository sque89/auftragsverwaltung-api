<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class LogController extends AbstractController {

    private $entityManager;
    private $serializer;

    public function __construct() {
    }

    /**
     * @Route("/api/log", name="logError", methods="PUT")
     */
    public function logError(Request $request, LoggerInterface $logger) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $logger->error($requestData['message'], array(
                'application' => 'ng',
                'callStack' => $requestData['callStack']
            ));
            return $this->json(array('code' => 200), 200);
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }
}
