<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\SmtpSetting;

class SmtpSettingTest extends TestCase
{
    public function testEncryptPasswordReturnsBase64(): void
    {
        $encrypted = SmtpSetting::encryptPassword('mypassword');
        $this->assertSame(base64_encode('mypassword'), $encrypted);
    }

    public function testEncryptPasswordHandlesEmptyString(): void
    {
        $encrypted = SmtpSetting::encryptPassword('');
        $this->assertSame(base64_encode(''), $encrypted);
    }

    public function testEncryptPasswordHandlesSpecialCharacters(): void
    {
        $password = 'p@$$w0rd!#%^&*()';
        $encrypted = SmtpSetting::encryptPassword($password);
        $this->assertSame(base64_encode($password), $encrypted);
        $this->assertSame($password, base64_decode($encrypted));
    }
}
