<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Voter;

use App\Domain\Model\Product;
use App\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

use function assert;

final class ProductVoter extends Voter
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param mixed $subject
     */
    protected function supports(string $attribute, $subject): bool
    {
        return $subject instanceof Product;
    }

    /**
     * @param mixed $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // If the user is anonymous, do not grant access
        if (! $user instanceof UserInterface) {
            return false;
        }

        assert($user instanceof User);
        assert($subject instanceof Product);

        if ($this->security->isGranted('ROLE_ADMINISTRATOR')) {
            return true;
        }

        if (! $this->security->isGranted('ROLE_COMPANY')) {
            return false;
        }

        $company = $subject->getCompany();

        return $company->hasUser($user);
    }
}