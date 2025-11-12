<?php

namespace App\Controllers;

use App\Models\Template;

/**
 * TemplateController
 * Manages email templates with HTML and variables
 */
class TemplateController
{
    /**
     * List all templates
     */
    public function index()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $templates = Template::findByOrganization($organization_id);
        
        include __DIR__ . '/../../resources/views/templates/index.php';
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        include __DIR__ . '/../../resources/views/templates/create.php';
    }
    
    /**
     * Store new template
     */
    public function store()
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        
        $template = new Template();
        $template->organization_id = $organization_id;
        $template->name = $_POST['name'] ?? '';
        $template->subject = $_POST['subject'] ?? '';
        $template->html_content = $_POST['html_content'] ?? '';
        $template->variables = json_encode($template->parseVariables());
        $template->is_active = true;
        $template->save();
        
        $_SESSION['success'] = 'Template created successfully';
        header('Location: /templates');
        exit;
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $template = Template::findById($id);
        
        if (!$template || $template->organization_id != $organization_id) {
            header('Location: /templates');
            exit;
        }
        
        include __DIR__ . '/../../resources/views/templates/edit.php';
    }
    
    /**
     * Update template
     */
    public function update($id)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $template = Template::findById($id);
        
        if (!$template || $template->organization_id != $organization_id) {
            header('Location: /templates');
            exit;
        }
        
        $template->name = $_POST['name'] ?? $template->name;
        $template->subject = $_POST['subject'] ?? $template->subject;
        $template->html_content = $_POST['html_content'] ?? $template->html_content;
        $template->variables = json_encode($template->parseVariables());
        $template->save();
        
        $_SESSION['success'] = 'Template updated successfully';
        header('Location: /templates');
        exit;
    }
    
    /**
     * Delete template
     */
    public function delete($id)
    {
        session_start();
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $organization_id = $_SESSION['organization_id'];
        $template = Template::findById($id);
        
        if ($template && $template->organization_id == $organization_id) {
            $template->delete();
            $_SESSION['success'] = 'Template deleted successfully';
        }
        
        header('Location: /templates');
        exit;
    }
}
