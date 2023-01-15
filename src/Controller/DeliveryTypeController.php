<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\DeliveryType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class DeliveryTypeController extends AbstractController {

    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager) {
        $normalizer = array(new ObjectNormalizer());
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer($normalizer, array(new JsonEncoder()));
    }

    /**
     * @Route("/api/delivery-types", name="getAllDeliveryTypes", methods="GET")
     */
    public function getAllDeliveryTypes() {
        try {
            $deliveryTypes = $this->entityManager->getRepository(DeliveryType::class)->findAll();
            return new Response(
                    $this->serializer->serialize($deliveryTypes, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }
}
