<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Job;
use App\Entity\User;
use App\Entity\Task;
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

class TaskController extends AbstractController {

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

    /**
     * @Route("/api/task", name="createTaskForLoggedInUser", methods="POST")
     */
    public function createTaskForLoggedInUser(Request $request) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $task = new Task();
            $task->setWorkingTime($requestData['task']['workingTime']);
            $task->setDescription($requestData['task']['description']);
            $task->setDate(new \DateTime($requestData['task']['date']));
            $task->setArranger($this->entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUsername()));
            $task->setJob($this->entityManager->getRepository(Job::class)->findOneById($requestData['job']['id']));
            $this->entityManager->persist($task);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($task, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/task/{taskId}", name="changeTaskForLoggedInUser", methods="POST")
     */
    public function changeTaskForLoggedInUser($taskId, Request $request) {
        $this->entityManager->getConnection()->beginTransaction();
        try {
            $requestData = json_decode($request->getContent(), true);
            $task = $this->entityManager->getRepository(Task::class)->find($taskId, \Doctrine\DBAL\LockMode::OPTIMISTIC, $requestData['version']);

            if ($task->getArranger()->getId() === $this->getUser()->getId()) {
                $task->setWorkingTime($requestData['workingTime']);
                $task->setDescription($requestData['description']);
                $task->setDate(new \DateTime($requestData['date']));
                $task->setArranger($this->entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUsername()));
                $this->entityManager->flush();
                $this->entityManager->getConnection()->commit();
                return new Response(
                        $this->serializer->serialize($task, 'json', ['groups' => ['api']]), Response::HTTP_OK, ['Content-type' => 'application/json']
                );
            } else {
                return $this->json(array('code' => 403, 'message' => 'Not allowed to change task from another user'), 403);
            }
        } catch(\Doctrine\ORM\OptimisticLockException $ole) {
            $this->entityManager->getConnection()->rollBack();
            return $this->json(array('code' => 423, 'message' => $ole->getMessage()), 423);
        } catch (\Exception $ex) {
            $this->entityManager->getConnection()->rollBack();
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }
}
