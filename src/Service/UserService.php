<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Service responsible for User creation and update logic,
 * including password hashing.
 */
class UserService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * Persists a new user. Hashes plainPassword if provided.
     */
    public function create(User $user, ?string $plainPassword = null): void
    {
        if ($plainPassword) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword)
            );
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Updates an existing user. Only hashes password if a new plainPassword was provided.
     */
    public function update(User $user, ?string $plainPassword = null): void
    {
        if ($plainPassword) {
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $plainPassword)
            );
        }

        $this->entityManager->flush();
    }

    /**
     * Removes a user from the database.
     */
    public function delete(User $user): void
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();
    }

    /**
     * Hashes and sets a new password directly on the user entity.
     * Useful for password reset flows.
     */
    public function resetPassword(User $user, string $plainPassword): void
    {
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );
        $this->entityManager->flush();
    }
}
