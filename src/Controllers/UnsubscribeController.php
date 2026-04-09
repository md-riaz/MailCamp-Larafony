<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\SmtpUnsubscription;
use App\Models\Subscription;
use Larafony\Framework\Routing\Advanced\Attributes\Route;
use Psr\Http\Message\ResponseInterface;

class UnsubscribeController extends Controller
{
    public function __construct()
    {
        parent::__construct(\Larafony\Framework\Web\Application::instance());
    }

    #[Route('/unsubscribe/{token}', 'GET')]
    public function unsubscribe(string $token): ResponseInterface
    {
        $decoded = Subscription::decodeToken($token);
        if ($decoded === null) {
            return $this->render('errors.404')->withStatus(404);
        }

        $subscription = Subscription::findByToken($token);
        if (!$subscription) {
            return $this->render('errors.404')->withStatus(404);
        }

        $email = strtolower(trim((string) $subscription->email));
        if ($email !== $decoded['email']) {
            return $this->render('errors.404')->withStatus(404);
        }

        $existing = SmtpUnsubscription::query()
            ->where('organization_id', '=', $decoded['organizationId'])
            ->where('smtp_setting_id', '=', $decoded['smtpId'])
            ->where('email', '=', $decoded['email'])
            ->first();

        if (!$existing) {
            (new SmtpUnsubscription())->fill([
                'organization_id' => $decoded['organizationId'],
                'subscription_id' => $subscription->id,
                'smtp_setting_id' => $decoded['smtpId'],
                'email' => $decoded['email'],
                'unsubscribed_at' => date('Y-m-d H:i:s'),
            ])->save();
        }

        $subscription->unsubscribe_date = date('Y-m-d H:i:s');
        $subscription->save();

        return $this->redirect('/?notice=unsubscribed&email=' . rawurlencode($decoded['email']), 302);
    }
}
