<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\SecurityBundle\Security;
class ProductVoter extends Voter
{
    public const DELETE = 'PRODUCT_DELETE';

    public function __construct(
        private Security $security
    ){}
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::DELETE])
            && $subject instanceof \App\Entity\Product;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        if ($attribute == self::DELETE) {
            if ($this->security->isGranted('ROLE_ADMIN')) {
                return true;
            }
            return false;
        }       return false;
    }
}
