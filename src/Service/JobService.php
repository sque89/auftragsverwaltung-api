<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Job;

class JobService {

    private $entityManager;

    public function __construct(EntityManagerInterface $em) {
        $this->entityManager = $em;
    }

    private function getId($year, $month, $counter) {
        return sprintf("%d-%'.02d-%'.03d", $year, $month, $counter);
    }

    public function generateJobId() {
        $generatedId = null;
        $currentYear = intval(date('Y'));
        $currentMonth = intval(date('m'));
        $latestJob = $this->entityManager->getRepository(Job::class)->findLatest();

        if ($latestJob) {
            $latestJobYear = intval(explode('-', $latestJob->getId())[0]);
            $latestJobMonth = intval(explode('-', $latestJob->getId())[1]);
            $latestJobNumber = intval(explode('-', $latestJob->getId())[2]);

            if ($latestJobNumber == 999) {
                throw new \Exception('Keine Jobnummern mehr übrig für den laufenden Monat');
            }

            // Aktuelles Jahr kleiner oder aktueller Monat im gleichen Jahr kleiner als letzter Job --> Fehler
            if ($currentYear < $latestJobYear || ($currentYear == $latestJobYear && $currentMonth < $latestJobMonth)) {
                throw new \Exception('Fehler bei ID-Berechnung. Es existiert ein Job in der Zukunft.');
            }

            if ($currentYear == $latestJobYear && $currentMonth == $latestJobMonth) {
                $generatedId = $this->getId($currentYear, $currentMonth, $latestJobNumber + 1);
            } else {
                $generatedId = $this->getId($currentYear, $currentMonth, 1);
            }
        } else {
            $generatedId = $this->getId($currentYear, $currentMonth, 1);
        }

        return $generatedId;
    }
}