<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;

class JWTLoginSuccessListener
{
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();

        if (!is_object($user)) {
            return;
        }

        $data = $event->getData();
        $data['id'] = $user->getId();

        $event->setData($data);
    }
}
