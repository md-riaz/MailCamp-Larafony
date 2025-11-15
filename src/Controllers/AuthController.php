<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\LoginDto;
use App\DTOs\RegisterDto;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserProfile;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Larafony\Framework\Web\Controller;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/login', 'GET')]
    public function showLogin(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render('auth.login');
    }

    #[Route('/login', 'POST')]
    public function login(LoginDto $dto): ResponseInterface
    {
        // Find user by email
        /** @var User|null $user */
        $user = User::query()->where('email', '=', $dto->email)->first();

        if (!$user instanceof User) {
            return $this->render('auth.login', [
                'error' => 'Invalid credentials',
                'email' => $dto->email,
            ]);
        }

        // Attempt login with Auth facade (auto-checks password and is_active)
        if (Auth::attempt($user, $dto->password)) {
            return $this->redirect('/dashboard');
        }

        return $this->render('auth.login', [
            'error' => 'Invalid credentials or account is inactive',
            'email' => $dto->email,
        ]);
    }

    #[Route('/register', 'GET')]
    public function showRegister(ServerRequestInterface $request): ResponseInterface
    {
        return $this->render('auth.register');
    }

    #[Route('/register', 'POST')]
    public function register(RegisterDto $dto): ResponseInterface
    {
        // Check if email exists
        $existingUser = User::query()->where('email', '=', $dto->email)->first();
        if ($existingUser) {
            return $this->render('auth.register', [
                'error' => 'Email already exists',
                'name' => $dto->name,
                'email' => $dto->email,
                'organization_name' => $dto->organization_name,
            ]);
        }

        // Create organization
        $org = new Organization()->fill([
            'name' => $dto->organization_name,
            'slug' => Organization::generateSlug($dto->organization_name),
            'is_active' => 1,
        ]);
        $org->save();

        // Create user
        $user = new User();
        $user->email = $dto->email;
        $user->password = $dto->password; // Auto-hashed with Argon2id
        $user->is_active = 1;
        $user->save();

        // Create user profile
        $profile = new UserProfile();
        $profile->user_id = $user->id;
        $profile->organization_id = $org->id;
        $profile->name = $dto->name;
        $profile->save();

        // Assign admin role (using framework's RBAC)
        $adminRole = \Larafony\Framework\Database\ORM\Entities\Role::query()
            ->where('name', '=', 'admin')
            ->first();
        
        if ($adminRole) {
            $user->addRole($adminRole);
        }

        // Auto-login
        Auth::login($user);

        return $this->redirect('/dashboard');
    }

    #[Route('/logout', ['GET', 'POST'])]
    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        Auth::logout();
        return $this->redirect('/login');
    }
}
