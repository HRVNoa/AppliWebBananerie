<?php
namespace App\Security;

use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserConfirmedChecker implements UserCheckerInterface
{
        public function checkPreAuth(UserInterface $user): void
        {
        // Ici, on ne fait rien de particulier avant l'authentification.
        }

        public function checkPostAuth(UserInterface $user): void
        {
        if (!$user->isConfirmed()) {
        // Lancez une exception si l'utilisateur n'est pas confirmé.
        throw new CustomUserMessageAccountStatusException('Votre compte n\'est pas encore confirmé.');
         }
    }
}
