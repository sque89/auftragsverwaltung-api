<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener {

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }

    /**
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event) {
        if (!($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        $user = $event->getUser();
        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUsername();
        $payload['firstname'] = $user->getFirstname();
        $payload['lastname'] = $user->getLastname();
        $payload['email'] = $user->getEmail();
        $payload['roles'] = $user->getRoles();
        $payload['isActive'] = $user->getIsActive();

        $event->setData($payload);
    }

}
