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
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CustomerController extends Controller {

    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager) {
        $normalizer = new ObjectNormalizer();

        $this->entityManager = $entityManager;
        $this->serializer = new Serializer(array($normalizer), array(new JsonEncoder(), new CsvEncoder()));
    }

    /**
     * @Route("/api/customer", name="getAllCustomers", methods="GET")
     */
    public function getAllCustomers() {
        try {
            $customers = $this->entityManager->getRepository(Customer::class)->findAll();
            return new Response(
                    $this->serializer->serialize($customers, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
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
     * @Route("/api/customer/find/{searchString}", name="getCustomersBySearchString", methods="GET")
     */
    public function getCustomersBySearchString($searchString) {
        try {
            $customer = $this->entityManager->getRepository(Customer::class)->findBySearchString($searchString);
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
            $customer->setName($requestData['name']);
            $customer->setPostcode($requestData['postcode']);
            $customer->setCity($requestData['city']);
            $customer->setAddress($requestData['address']);
            $customer->setContactPerson($requestData['contactPerson']);
            $customer->setMail($requestData['mail']);
            $customer->setPhone($requestData['phone']);
            $customer->setFax($requestData['fax']);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($customer, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/customer", name="addCustomer", methods="PUT")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addCustomer(Request $request) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $newCustomer = new Customer();
            $newCustomer->setName($requestData['name']);
            $newCustomer->setPostcode($requestData['postcode']);
            $newCustomer->setCity($requestData['city']);
            $newCustomer->setAddress($requestData['address']);
            $newCustomer->setContactPerson($requestData['contactPerson']);
            $newCustomer->setMail($requestData['mail']);
            $newCustomer->setPhone($requestData['phone']);
            $newCustomer->setFax($requestData['fax']);
            $this->entityManager->persist($newCustomer);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($newCustomer, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/customers/import", name="importCustomers", methods="GET")
     */
    public function importCustomers() {
        try {
            $csvCustomers = $this->serializer->decode(file_get_contents($this->get('kernel')->getProjectDir() . '/public/kunden-import/kunden.csv'), 'csv');
            $existingCustomer = null;
            $csvCustomer = null;

            foreach ($csvCustomers as $csvCustomer) {
                $indexedCsvCustomer = array_values($csvCustomer);
                $customerToPersist = null;

                $customerId = $indexedCsvCustomer[2] !== '' ? intval($indexedCsvCustomer[2]) : intval($indexedCsvCustomer[0]);

                $existingCustomer = $this->entityManager->getRepository(Customer::class)->findOneById($customerId);

                if ($existingCustomer) {
                    $existingCustomer->setName($indexedCsvCustomer[4] !== '' ? $indexedCsvCustomer[4] : $indexedCsvCustomer[1]);
                    $existingCustomer->setPostcode($indexedCsvCustomer[10]);
                    $existingCustomer->setCity($indexedCsvCustomer[11]);
                    $existingCustomer->setAddress($indexedCsvCustomer[9]);
                    $existingCustomer->setContactPerson($indexedCsvCustomer[12]);
                    $existingCustomer->setMail($indexedCsvCustomer[16]);
                    $existingCustomer->setPhone($indexedCsvCustomer[13]);
                    $existingCustomer->setFax($indexedCsvCustomer[15]);
                    $this->entityManager->flush();
                } else {
                    $customerToPersist = new Customer();
                    $customerToPersist->setId($customerId);
                    $customerToPersist->setName($indexedCsvCustomer[4] !== '' ? $indexedCsvCustomer[4] : $indexedCsvCustomer[1]);
                    $customerToPersist->setPostcode($indexedCsvCustomer[10]);
                    $customerToPersist->setCity($indexedCsvCustomer[11]);
                    $customerToPersist->setAddress($indexedCsvCustomer[9]);
                    $customerToPersist->setContactPerson($indexedCsvCustomer[12]);
                    $customerToPersist->setMail($indexedCsvCustomer[16]);
                    $customerToPersist->setPhone($indexedCsvCustomer[13]);
                    $customerToPersist->setFax($indexedCsvCustomer[15]);
                    $this->entityManager->persist($customerToPersist);
                    $this->entityManager->flush();
                }
            }


            return $this->json(array('code' => 200, 'message' => 'Import erfolgreich'), 200);
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

}
