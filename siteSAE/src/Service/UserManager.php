<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager implements UserManagerInterface
{

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ){}

    public function processNewUser(User $user, ?string $plainPassword): void
    {
        $hashedPass = $this->hasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPass);
    }

}