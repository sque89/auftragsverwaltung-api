<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Job;
use App\Entity\User;
use \App\Entity\DeliveryType;
use \App\Entity\Customer;
use App\Service\JobService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class JobController extends AbstractController {

    private $jobService;
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, JobService $jobService) {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $normalizer = array(new DateTimeNormalizer(), new ObjectNormalizer($classMetadataFactory));
        $normalizer[1]->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $this->jobService = $jobService;
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer($normalizer, array(new JsonEncoder()));
    }

    private function setJobData(Job &$job, $data) {
        $job->setDateIncoming(new \DateTime($data["dateIncoming"]));
        $job->setDateDeadline(new \DateTime($data["dateDeadline"]));
        $job->setDeliveryType($this->entityManager->getRepository(DeliveryType::class)->findOneById($data["deliveryType"]["id"]));
        $job->setCustomer($this->entityManager->getRepository(Customer::class)->findOneById($data["customer"]["id"]));
        $job->setDescription($data["description"]);
        $job->setNotes($data["notes"]);
        $job->setExternalPurchase($data["externalPurchase"]);
        $job->setInvoiceNumber($data["invoiceNumber"]);
        $job->updateArrangers(array_map(
                        function($arranger) {
                    return $this->entityManager->getRepository(User::class)->findOneById($arranger["id"]);
                }, $data["arrangers"]
        ));
    }

    /**
     * @Route("/api/jobs", name="getJobs", methods="GET")
     */
    public function getJobs() {
        try {
            $jobs = $this->entityManager->getRepository(Job::class)->findAll();
            return new Response(
                    $this->serializer->serialize($jobs, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/jobs/timespan/{from}/{to}", name="getJobsInTimespan", methods="GET")
     */
    public function getJobsInTimespan($from, $to) {
        try {

            $jobs = $this->entityManager->getRepository(Job::class)->findByTimespan(
                    new \DateTime("@$from"), new \DateTime("@$to")
            );
            return new Response(
                    $this->serializer->serialize($jobs, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/jobs/timespan/{from}/{to}/income/count", name="getJobIncomeCountInTimespan", methods="GET")
     */
    public function getJobIncomeCountInTimespan($from, $to) {
        try {

            $jobs = $this->entityManager->getRepository(Job::class)->findByTimespan(
                    new \DateTime("@$from"),
                    new \DateTime("@$to"),
                    'dateIncoming'
            );
            return new Response(
                    $this->serializer->serialize($jobs, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/jobs/open/current-user", name="getOpenJobsForLoggedInUser", methods="GET")
     */
    public function getOpenJobsForLoggedInUser() {
        try {
            $jobs = $this->entityManager->getRepository(Job::class)->findOpenJobsForUser($this->getUser());
            return new Response(
                    $this->serializer->serialize($jobs, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/jobs/open/count", name="getOpenJobsCount", methods="GET")
     */
    public function getOpenJobsCount() {
        try {
            $count = $this->entityManager->getRepository(Job::class)->getOpenJobCount();
            return new Response(
                    $this->serializer->serialize($count, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/jobs/open/intime/count", name="getOpenJobsIntimeCount", methods="GET")
     */
    public function getOpenJobsIntimeCount() {
        try {
            $count = $this->entityManager->getRepository(Job::class)->getOpenJobIntimeCount();
            return new Response(
                    $this->serializer->serialize($count, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/jobs/open/overdue/count", name="getOpenJobsOverdueCount", methods="GET")
     */
    public function getOpenJobsOverdueCount() {
        try {
            $count = $this->entityManager->getRepository(Job::class)->getOpenJobOverdueCount();
            return new Response(
                    $this->serializer->serialize($count, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/job/{id}", name="getJobById", methods="GET")
     */
    public function getJobById($id) {
        try {

            $job = $this->entityManager->getRepository(Job::class)->findOneById($id);
            return new Response(
                    $this->serializer->serialize($job, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/job", name="createJob", methods="POST")
     */
    public function createJob(Request $request) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $job = new Job();
            $job->setId($this->jobService->generateJobId());
            $this->setJobData($job, $requestData);
            $this->entityManager->persist($job);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($job, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/job/{id}", name="updateJob", methods="POST")
     */
    public function updateJob($id, Request $request) {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $requestData = json_decode($request->getContent(), true);
            $job = $this->entityManager->getRepository(Job::class)->find($id, \Doctrine\DBAL\LockMode::OPTIMISTIC, $requestData['version']);

            if ($job->getInvoiceNumber() !== NULL && !$this->getUser()->isAdministrator()) {
                return $this->json(array('code' => 403, 'message' => 'Not allowed to change closed Job if not Administrator'), 403);
            }

            $this->setJobData($job, $requestData);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
            return new Response(
                    $this->serializer->serialize($job, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (\Doctrine\ORM\OptimisticLockException $ole) {
            $this->entityManager->getConnection()->rollBack();
            return $this->json(array('code' => 423, 'message' => $ole->getMessage()), 423);
        } catch (\Exception $ex) {
            $this->entityManager->getConnection()->rollBack();
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/job/{id}/invoice", name="setInvoiceNumber", methods="POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function setInvoiceNumber($id, Request $request) {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $requestData = json_decode($request->getContent(), true);
            $job = $this->entityManager->getRepository(Job::class)->find($id, \Doctrine\DBAL\LockMode::OPTIMISTIC, $requestData['job']['version']);
            $job->setInvoiceNumber($requestData["invoiceNumber"]);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
            return new Response(
                    $this->serializer->serialize($job, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (\Doctrine\ORM\OptimisticLockException $ole) {
            $this->entityManager->getConnection()->rollBack();
            return $this->json(array('code' => 423, 'message' => $ole->getMessage()), 423);
        } catch (\Exception $ex) {
            $this->entityManager->getConnection()->rollBack();
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

}
