<?php 
$title = 'Create Template';
ob_start(); 
?>

<h1>Create Email Template</h1>

<div class="card">
    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates">
        <div class="form-group">
            <label for="name">Template Name</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="subject">Email Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Use {{variable}} for dynamic content" required>
        </div>
        
        <div class="form-group">
            <label for="html_content">HTML Content</label>
            <textarea id="html_content" name="html_content" style="min-height: 300px; font-family: monospace;" required></textarea>
            <small>Use {{variable}} for dynamic content. Example: {{name}}, {{email}}, etc.</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-success">Create Template</button>
            <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/templates" class="btn">Cancel</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Example Template</h3>
    <pre style="background: #f4f4f4; padding: 15px; border-radius: 4px; overflow-x: auto;">
&lt;html&gt;
&lt;body&gt;
    &lt;h1&gt;Hello {{name}}!&lt;/h1&gt;
    &lt;p&gt;Thank you for subscribing to our newsletter.&lt;/p&gt;
    &lt;p&gt;Your email address is: {{email}}&lt;/p&gt;
    &lt;p&gt;Best regards,&lt;br&gt;The Team&lt;/p&gt;
    &lt;p&gt;&lt;small&gt;&lt;a href="{{unsubscribe_url}}"&gt;Unsubscribe&lt;/a&gt;&lt;/small&gt;&lt;/p&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
