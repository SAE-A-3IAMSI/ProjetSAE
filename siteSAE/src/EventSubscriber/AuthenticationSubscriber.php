<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthenticationSubscriber
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ){}

    #[AsEventListener]
    public function connectionSuccess(LoginSuccessEvent $event) {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add("success", "Connexion reussie !");
    }

    #[AsEventListener]
    public function connectionFail(LoginFailureEvent $event) {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add("error", "Login et/ou mot de passe incorrect !");
    }

    #[AsEventListener]
    public function deconnection(LogoutEvent $event) {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add("success", "Déconnexion reussie !");
    }
}