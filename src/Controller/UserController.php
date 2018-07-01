<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller {

    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/user/{username}", name="getUserByUsername", methods="GET")
     */
    public function getUserByUsername($username) {
        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);

        return $this->json(array(
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles()
        ));
    }

    /**
     * @Route("/api/user/{username}", name="changeUserByUsername", methods="POST")
     */
    public function changeUserByUsername(Request $request, $username) {
        $requestData = json_decode($request->getContent(), true);
        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
        $user->setFirstname($requestData['firstname']);
        $user->setLastname($requestData['lastname']);
        $user->setEmail($requestData['email']);
        $this->entityManager->flush();

        return $this->json(array(
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'firstname' => $user->getFirstname(),
                    'lastname' => $user->getLastname(),
                    'email' => $user->getEmail(),
                    'roles' => $user->getRoles()
        ));
    }

}
