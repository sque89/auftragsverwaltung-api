<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Job;
use App\Entity\User;
use App\Service\JobService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

class JobController extends Controller {

    private $jobService;
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, JobService $jobService) {
        $normalizer = array( new DateTimeNormalizer(), new ObjectNormalizer());
        $this->jobService = $jobService;
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer($normalizer, array(new JsonEncoder()));
    }

    private function setJobData(Job &$job, $data) {
        $job->setDateIncoming(new \DateImmutable($data["dateIncoming"]));
        $job->setDateDeadline(new \Date($data["dateDeadline"]));
        $job->setDeliveryType($data["deliveryType"]);
        $job->setDescription($data["description"]);
        $job->setNotes($data["notes"]);
        $job->setExternalPurchase($data["externalPurchase"]);
        $job->updateArrangers(array_map(
            function($arranger) { return $this->entityManager->getRepository(User::class)->findOneById($arranger); },
            $data["arrangers"]
        ));
    }

    /**
     * @Route("/api/jobs", name="getJobs", methods="GET")
     */
    public function getJobs() {
        try {
            $jobs = $this->entityManager->getRepository(Job::class)->findAll();
            return new Response(
                    $this->serializer->serialize(array("jobs" => $jobs), 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
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
                    $this->serializer->serialize($job, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/job/{id}", name="updateJob", methods="POST")
     */
    public function updateJob($id, Request $request) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $job = $this->entityManager->getRepository(Job::class)->findOneById($id);
            $this->setJobData($job, $requestData);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($job, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }
}
