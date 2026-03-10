<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use App\DTOs\LoginDto;
use App\Models\User;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/login', 'GET')]
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }

        return $this->render('auth.login');
    }

    #[Route('/login', 'POST')]
    public function store(LoginDto $dto): ResponseInterface
    {
        /** @var User|null $user */
        $user = User::query()->where('email', '=', $dto->email)->first();

        if (!$user instanceof User) {
            return $this->render('auth.login', [
                'error' => 'Invalid credentials',
                'email' => $dto->email,
            ]);
        }

        if (Auth::attempt($user, $dto->password)) {
            return $this->redirect('/dashboard');
        }

        return $this->render('auth.login', [
            'error' => 'Invalid credentials or account is inactive',
            'email' => $dto->email,
        ]);
    }
}
