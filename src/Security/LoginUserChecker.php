<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LoginUserChecker implements UserCheckerInterface {
    public function checkPreAuth(UserInterface $user) {
        if (!$user instanceof User) {
            return;
        }
        // TODO create exception DisabledUserException, throw it and handle it --> message username or password wrong
        if (!$user->getIsActive()) {
            throw new \Exception('User is disabled');
        }
    }

    public function checkPostAuth(UserInterface $user) {
        if (!$user instanceof User) {
            return;
        }
    }
}