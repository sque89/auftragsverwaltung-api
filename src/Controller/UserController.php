<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\Role;
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
        $normalizer = new ObjectNormalizer();
        $normalizer->setIgnoredAttributes(array('salt', 'password'));

        $this->entityManager = $entityManager;
        $this->encoder = $encoder;
        $this->serializer = new Serializer(array($normalizer), array(new JsonEncoder()));
    }

    private function parseRolesFromRequest($requestData) {
        $parsedRoles = [];
        foreach ($requestData["roles"] as $role) {
            $parsedRoles[] = $this->entityManager->getRepository(Role::class)->findOneByName($role);
        }
        return $parsedRoles;
    }

    private function changeCommonData($username, $data, $changeRoles = false) {
        $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
        $user->setFirstname($data['firstname']);
        $user->setLastname($data['lastname']);
        $user->setEmail($data['email']);

        if ($changeRoles) {
            $user->setRoles($this->parseRolesFromRequest($data));
        }

        $this->entityManager->flush();
        return $user;
    }

    /**
     * @Route("/api/user/{username}", name="getUserByUsername", methods="GET")
     */
    public function getUserByUsername($username) {
        try {
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
            return new Response(
                    $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/{username}", name="deleteUserByUsername", methods="DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteUserByUsername($username) {
        if ($username === $this->getUser()->getUsername()) {
            return $this->json(array('code' => 403, 'message' => 'Not allowed to delete yourself'), 403);
        }

        try {
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
            $this->entityManager->remove($user);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/activate/{username}", name="activateUserByUsername", methods="POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function activateUserByUsername($username) {
        if ($username === $this->getUser()->getUsername()) {
            return $this->json(array('code' => 403, 'message' => 'Not allowed to activate yourself'), 403);
        }

        try {
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
            $user->setIsActive(true);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/deactivate/{username}", name="deactivateUserByUsername", methods="POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deactivateUserByUsername($username) {
        if ($username === $this->getUser()->getUsername()) {
            return $this->json(array('code' => 403, 'message' => 'Not allowed to deactivate yourself'), 403);
        }

        try {
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
            $user->setIsActive(false);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($user, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/users", name="getAllUsers", methods="GET")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function getAllUsers() {
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            return new Response(
                    $this->serializer->serialize($users, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/users/unsensitive", name="getAllUsersUnsensitive", methods="GET")
     */
    public function getAllUsersUnsensitive() {
        try {
            $users = $this->entityManager->getRepository(User::class)->findAll();
            return new Response(
                    $this->serializer->serialize($users, 'json', ['groups' => ['unsensitive']]), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user", name="addUser", methods="PUT")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function addUser(Request $request) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $newUser = new User();
            $newUser->setFirstname($requestData['firstname']);
            $newUser->setLastname($requestData['lastname']);
            $newUser->setEmail($requestData['email']);
            $newUser->setUsername($requestData['username']);
            $newUser->setRoles($this->parseRolesFromRequest($requestData));
            $newUser->setPassword($this->encoder->encodePassword($newUser, $requestData['password']));
            $this->entityManager->persist($newUser);
            $this->entityManager->flush();
            return new Response(
                    $this->serializer->serialize($newUser, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/commondata", name="changeCommonDataForLoggedInUser", methods="POST")
     */
    public function changeCommonDataForLoggedInUser(Request $request) {
        try {
            $changedUser = $this->changeCommonData($this->getUser()->getUsername(), json_decode($request->getContent(), true), false);
            return new Response(
                    $this->serializer->serialize($changedUser, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/commondata/{username}", name="changeCommonDataByUsername", methods="POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function changeCommonDataByUsername(Request $request, $username) {
        try {
            $changedUser = $this->changeCommonData($username, json_decode($request->getContent(), true), true);
            return new Response(
                    $this->serializer->serialize($changedUser, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/password", name="changePasswordForLoggedInUser", methods="POST")
     */
    public function changePasswordForLoggedInUser(Request $request) {
        $responseJson = null;
        $httpStatus = 200;

        try {
            $requestData = json_decode($request->getContent(), true);
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($this->getUser()->getUsername());

            if ($requestData['newPassword'] !== $requestData['newPasswordConfirmation']) {
                $responseJson = json_encode(array('code' => 400, 'message' => 'Passwords do not match'));
                $httpStatus = 400;
            } else if (!$this->encoder->isPasswordValid($user, $requestData['currentPassword'])) {
                $responseJson = json_encode(array('code' => 403, 'message' => 'Wrong password'));
                $httpStatus = 403;
            } else {
                $user->setPassword($this->encoder->encodePassword($user, $requestData['newPassword']));
                $this->entityManager->flush();
                $responseJson = $this->serializer->serialize($user, 'json');
            }
            return new Response($responseJson, $httpStatus, ['Content-type' => 'application/json']);
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    /**
     * @Route("/api/user/password/{username}", name="changePasswordByUsername", methods="POST")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function changePasswordByUsername(Request $request, $username) {
        try {
            $requestData = json_decode($request->getContent(), true);
            $user = $this->entityManager->getRepository(User::class)->findOneByUsername($username);

            $user->setPassword($this->encoder->encodePassword($user, $requestData['newPassword']));
            $this->entityManager->flush();

            return new Response($this->serializer->serialize($user, 'json'), 200, ['Content-type' => 'application/json']);
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

}
