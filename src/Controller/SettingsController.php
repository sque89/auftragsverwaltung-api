<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Setting;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SettingsController extends AbstractController {

    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager) {
        $normalizer = array(new ObjectNormalizer());
        $this->entityManager = $entityManager;
        $this->serializer = new Serializer($normalizer, array(new JsonEncoder()));
    }

    #[Route('/api/settings', name: 'get_settings', methods: ['GET'])]
    public function getAllSettings() {
        try {
            $settings = $this->entityManager->getRepository(Setting::class)->findAll();
            return new Response(
                    $this->serializer->serialize($settings, 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
            );
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

    #[Route('/api/settings', name: 'set_settings', methods: ['POST'])]
    public function setSettings(Request $request) {
        try {
            $requestData = json_decode($request->getContent(), true);

            if (is_array($requestData)) {
                foreach ($requestData as $requestSetting) {
                    $setting = $this->entityManager->getRepository(Setting::class)->findOneById($requestSetting['id']);
                    if ($setting) {
                        $setting->setValue($requestSetting['value']);
                    }
                }
                $this->entityManager->flush();
                return new Response(
                    $this->serializer->serialize(array($setting), 'json'), Response::HTTP_OK, ['Content-type' => 'application/json']
                );
            } else {
                return $this->json(array('code' => 500, 'message' => 'Bitte ein Array mit Einstellungen übergeben'), 500);
            }
        } catch (Exception $ex) {
            return $this->json(array('code' => 500, 'message' => $ex->getMessage()), 500);
        }
    }

}
