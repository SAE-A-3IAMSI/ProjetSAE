<?php

namespace App\Service;

use App\Entity\User;

interface UserManagerInterface
{
    public function processNewUser(User $user, ?string $plainPassword): void;

}