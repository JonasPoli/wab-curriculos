<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the User entity.
 * These tests run without a database connection.
 */
class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
    }

    public function testGetDisplayNameReturnsNameWhenSet(): void
    {
        $this->user->setName('Jonas Poli');
        $this->user->setUsername('jonaspoli');

        $this->assertSame('Jonas Poli', $this->user->getDisplayName());
    }

    public function testGetDisplayNameFallsBackToUsername(): void
    {
        $this->user->setUsername('jonaspoli');

        $this->assertSame('jonaspoli', $this->user->getDisplayName());
    }

    public function testRolesAlwaysContainRoleUser(): void
    {
        $this->user->setRoles([]);

        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testRolesAreUnique(): void
    {
        $this->user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $roles = $this->user->getRoles();

        $this->assertSame(array_unique($roles), $roles);
    }

    public function testPlainPasswordIsErasedAfterEraseCredentials(): void
    {
        $this->user->setPlainPassword('mysecretpassword');
        $this->assertSame('mysecretpassword', $this->user->getPlainPassword());

        $this->user->eraseCredentials();

        $this->assertNull($this->user->getPlainPassword());
    }

    public function testEmailGetterAndSetter(): void
    {
        $this->user->setEmail('test@wab.com.br');

        $this->assertSame('test@wab.com.br', $this->user->getEmail());
    }

    public function testOnPrePersistSetsCreatedAt(): void
    {
        $before = new \DateTimeImmutable();
        $this->user->onPrePersist();
        $after = new \DateTimeImmutable();

        $this->assertNotNull($this->user->getCreatedAt());
        $this->assertGreaterThanOrEqual($before, $this->user->getCreatedAt());
        $this->assertLessThanOrEqual($after, $this->user->getCreatedAt());
    }

    public function testOnPreUpdateSetsUpdatedAt(): void
    {
        $before = new \DateTime();
        $this->user->onPreUpdate();
        $after = new \DateTime();

        $this->assertNotNull($this->user->getUpdatedAt());
        $this->assertGreaterThanOrEqual($before, $this->user->getUpdatedAt());
        $this->assertLessThanOrEqual($after, $this->user->getUpdatedAt());
    }

    public function testGetUserIdentifierReturnsUsername(): void
    {
        $this->user->setUsername('adminuser');

        $this->assertSame('adminuser', $this->user->getUserIdentifier());
    }
}
