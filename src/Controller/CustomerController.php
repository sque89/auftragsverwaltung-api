<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Customer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerController extends Controller {
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager) {
        $normalizer = new ObjectNormalizer();

        $this->entityManager = $entityManager;
        $this->serializer = new Serializer(array($normalizer), array(new JsonEncoder()));
    }

    /**
     * @Route("/api/customer", name="getAllCustomers", methods="GET")
     */
    public function getAllCustomers() {
        try {
            $customers = $this->entityManager->getRepository(Customer::class)->findAll();
            return new Response(
                    $this->serializer->serialize(array('customers' => $customers), 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/customer/{id}", name="getCustomerById", methods="GET")
     */
    public function getCustomerById($id) {
        try {
            $customer = $this->entityManager->getRepository(Customer::class)->findOneById($id);
            return new Response(
                    $this->serializer->serialize($customer, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/customer/{id}", name="changeCustomerById", methods="POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function changeCustomerById(Request $request, $id) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $customer = $this->entityManager->getRepository(Customer::class)->findOneById($id);
            $customer->setName($requestData["name"]);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($customer, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }
}
