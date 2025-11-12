<?php

namespace App\Controllers;

use App\Models\SmtpSetting;

/**
 * SmtpSettingController
 * Manages SMTP configuration per organization
 */
class SmtpSettingController
{
    /**
     * Show SMTP settings form
     */
    public function index()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $settings = SmtpSetting::findByOrganization($organization_id);
        
        include __DIR__ . '/../../resources/views/smtp/index.php';
    }
    
    /**
     * Save SMTP settings
     */
    public function save()
    {
        session_start();
        
        if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
            header('Location: /dashboard');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        
        $data = [
            'organization_id' => $organization_id,
            'host' => $_POST['host'] ?? '',
            'port' => $_POST['port'] ?? 587,
            'encryption' => $_POST['encryption'] ?? 'tls',
            'username' => $_POST['username'] ?? '',
            'password' => SmtpSetting::encryptPassword($_POST['password'] ?? ''),
            'from_email' => $_POST['from_email'] ?? '',
            'from_name' => $_POST['from_name'] ?? '',
            'is_active' => true,
        ];
        
        $settings = SmtpSetting::findByOrganization($organization_id);
        
        if ($settings) {
            $settings->update($data);
        } else {
            $settings = new SmtpSetting();
            $settings->create($data);
        }
        
        $_SESSION['success'] = 'SMTP settings saved successfully';
        header('Location: /smtp-settings');
        exit;
    }
    
    /**
     * Test SMTP connection
     */
    public function test()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $settings = SmtpSetting::findByOrganization($organization_id);
        
        if (!$settings) {
            echo json_encode(['success' => false, 'message' => 'SMTP settings not found']);
            exit;
        }
        
        // Test SMTP connection
        try {
            $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mailer->isSMTP();
            $mailer->Host = $settings->host;
            $mailer->Port = $settings->port;
            $mailer->SMTPAuth = true;
            $mailer->Username = $settings->username;
            $mailer->Password = $settings->decryptPassword();
            $mailer->SMTPSecure = $settings->encryption === 'ssl' ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            
            $mailer->SMTPDebug = 0;
            $result = $mailer->smtpConnect();
            
            echo json_encode(['success' => $result, 'message' => $result ? 'Connection successful' : 'Connection failed']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
