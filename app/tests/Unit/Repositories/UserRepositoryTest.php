<?php

namespace Unit\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use PHPUnit\Framework\TestCase;
use Faker\Factory as Faker;

class UserRepositoryTest extends TestCase
{
    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->faker          = Faker::create();
        $this->user           = ['name' => 'user', 'email' => 'user@gmail.com'];
        $this->userRepository = new UserRepository();
    }

    /**
     * A basic unit test store
     *
     * @return void
     */
    public function testStore()
    {
        $user = $this->userRepository->storeUser($this->user);
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($this->user['name'], $user->name);
        $this->assertEquals($this->user['email'], $user->email);
        $this->assertDatabaseHas('users', $this->user);
    }

    public function testUpdate()
    {
        $user    = factory(User::class)->create();
        $newUser = $this->userRepository->updateUser($this->user, $user);
        $this->assertInstanceOf(User::class, $newUser);
        $this->assertEquals($newUser->name, $this->user['name']);
        $this->assertEquals($newUser->email, $this->user['email']);
        $this->assertDatabaseHas('users', $this->user);
    }

    public function testDestroy()
    {
        $user       = factory(User::class)->create();
        $deleteUser = $this->userRepository->destroyUser($user);
        $this->assertTrue($deleteUser);
        $this->assertDatabaseMissing('users', $user->toArray());
    }
}
