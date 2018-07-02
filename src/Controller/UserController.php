<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserController extends Controller {

    private $entityManager;
    private $encoder;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder) {
        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->serializer = new Serializer(array(new ObjectNormalizer()), array(new JsonEncoder()));
    }

    /**
     * @Route("/api/user/{username}", name="getUserByUsername", methods="GET")
     */
    public function getUserByUsername($username) {
        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
        return new Response(
                $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
        );
    }

    /**
     * @Route("/api/users", name="getAllUsers", methods="GET")
     */
    public function getAllUsers() {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return new Response(
                $this->serializer->serialize(array('users' => $users), 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
        );
    }

    /**
     * @Route("/api/edit/user/commondata/{username}", name="changeCommonDataByUsername", methods="POST")
     */
    public function changeCommonDataByUsername(Request $request, $username) {
        $isAuthorized = $this->getUser()->getUsername() === $username || $this->getUser()->isAdmin();

        if ($isAuthorized) {
            $requestData = json_decode($request->getContent(), true);
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
            $user->setFirstname($requestData['firstname']);
            $user->setLastname($requestData['lastname']);
            $user->setEmail($requestData['email']);
            $this->entityManager->flush();

            return new Response(
                    $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } else {
            return $this->json(array(
                        'code' => 403,
                        'message' => 'Changing a different user than the currently logged in, is just permitted to admins'
                            ), 403);
        }
    }

    /**
     * @Route("/api/edit/user/password/{username}", name="changePasswordByUsername", methods="POST")
     */
    public function changePasswordByUsername(Request $request, $username) {
        $requestData = json_decode($request->getContent(), true);
        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);

        $isAuthorized = ($this->encoder->isPasswordValid($user, $requestData['currentPassword'])) || $this->getUser()->isAdmin();

        if ($isAuthorized) {
            if ($requestData['newPassword'] === $requestData['newPasswordConfirmation']) {
                $user->setPassword($this->encoder->encodePassword($user, $requestData['newPassword']));
                $this->entityManager->flush();

                return new Response(
                        $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
                );
            } else {
                return $this->json(array(
                            'code' => 400,
                            'message' => 'Passwords do not match'
                                ), 400);
            }
        } else {
            return $this->json(array(
                        'code' => 403,
                        'message' => 'Changing a different user than the currently logged in, is just permitted to admins'
                            ), 403);
        }
    }

}
