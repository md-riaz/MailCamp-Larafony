<?php

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Controllers\Controller;
use App\DTOs\RegisterDto;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserProfile;
use Larafony\Framework\Auth\Auth;
use Larafony\Framework\Database\ORM\Entities\Role;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/register', 'GET')]
    public function show(ServerRequestInterface $request): ResponseInterface
    {
        if (Auth::check()) {
            return $this->redirect('/dashboard');
        }

        return $this->render('auth.register');
    }

    #[Route('/register', 'POST')]
    public function store(RegisterDto $dto): ResponseInterface
    {
        $existingUser = User::query()->where('email', '=', $dto->email)->first();
        if ($existingUser) {
            return $this->render('auth.register', [
                'error' => 'Email already exists',
                'name' => $dto->name,
                'email' => $dto->email,
                'organization_name' => $dto->organization_name,
            ]);
        }

        $org = new Organization()->fill([
            'name' => $dto->organization_name,
            'slug' => Organization::generateSlug($dto->organization_name),
            'is_active' => 1,
        ]);
        $org->save();

        $baseUsername = trim(strtolower(preg_replace('/[^A-Za-z0-9_]+/', '_', strstr($dto->email, '@', true) ?: $dto->name)), '_');
        if ($baseUsername === '') {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $suffix = 1;
        while (User::query()->where('username', '=', $username)->first()) {
            $suffix++;
            $username = $baseUsername . '_' . $suffix;
        }

        $user = new User();
        $user->email = $dto->email;
        $user->username = $username;
        $user->password = $dto->password;
        $user->is_active = 1;
        $user->save();

        $profile = new UserProfile();
        $profile->user_id = (int) $user->id;
        $profile->organization_id = (int) $org->id;
        $profile->name = $dto->name;
        $profile->save();

        $adminRole = Role::query()->where('name', '=', 'admin')->first();
        if ($adminRole instanceof Role) {
            $user->addRole($adminRole);
        }

        Auth::login($user);

        return $this->redirect('/dashboard');
    }
}
