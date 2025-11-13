<?php

declare(strict_types=1);

namespace App\Controllers;

use App\DTOs\LoginDto;
use App\DTOs\RegisterDto;
use App\Models\Organization;
use App\Models\User;
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
        $user = User::query()->where('email', '=', $dto->email)->first();

        if (!$user || !$user->verifyPassword($dto->password)) {
            return $this->render('auth.login', [
                'error' => 'Invalid credentials',
                'email' => $dto->email,
            ]);
        }

        if (!$user->is_active) {
            return $this->render('auth.login', [
                'error' => 'Account is inactive',
                'email' => $dto->email,
            ]);
        }

        // Login user
        Auth::login($user);

        return $this->redirect('/dashboard');
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
            'is_active' => true,
        ]);
        $org->save();

        // Create user
        $user = new User()->fill([
            'organization_id' => $org->id,
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => User::hashPassword($dto->password),
            'role' => 'admin', // First user is admin
            'is_active' => true,
        ]);
        $user->save();

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
