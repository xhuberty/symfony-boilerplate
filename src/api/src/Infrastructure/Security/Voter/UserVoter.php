<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\Voter;

use App\Domain\Enum\Role;
use App\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function assert;
use function in_array;

final class UserVoter extends AppVoter
{
    public const UPDATE_USER = 'UPDATE_USER';
    public const GET_USER    = 'GET_USER';

    /**
     * @param mixed $subject
     */
    protected function supports(string $attribute, $subject): bool
    {
        if (
            ! in_array(
                $attribute,
                [
                    self::UPDATE_USER,
                    self::GET_USER,
                ]
            )
        ) {
            return false;
        }

        return $subject instanceof User;
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
        assert($subject instanceof User);

        switch ($attribute) {
            case self::UPDATE_USER:
            case self::GET_USER:
                // The administrator can update/get any user.
                if ($this->security->isGranted(Role::getSymfonyRole(Role::ADMINISTRATOR()))) {
                    return true;
                }

                // Other users may only update/get themselves.
                return $user->getId() === $subject->getId();

            default:
                return false;
        }
    }
}
