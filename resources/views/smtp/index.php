<?php 
$title = 'SMTP Settings';
ob_start(); 
?>

<h1>SMTP Settings</h1>

<div class="card">
    <form method="POST" action="/smtp-settings">
        <div class="form-group">
            <label for="host">SMTP Host</label>
            <input type="text" id="host" name="host" value="<?php echo htmlspecialchars($settings->host ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="port">SMTP Port</label>
            <input type="number" id="port" name="port" value="<?php echo htmlspecialchars($settings->port ?? '587'); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="encryption">Encryption</label>
            <select id="encryption" name="encryption" required>
                <option value="tls" <?php echo ($settings->encryption ?? 'tls') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                <option value="ssl" <?php echo ($settings->encryption ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                <option value="none" <?php echo ($settings->encryption ?? '') === 'none' ? 'selected' : ''; ?>>None</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="username">SMTP Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($settings->username ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">SMTP Password</label>
            <input type="password" id="password" name="password" placeholder="<?php echo isset($settings) ? '••••••••' : ''; ?>" <?php echo !isset($settings) ? 'required' : ''; ?>>
            <?php if (isset($settings)): ?>
            <small>Leave blank to keep existing password</small>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="from_email">From Email</label>
            <input type="email" id="from_email" name="from_email" value="<?php echo htmlspecialchars($settings->from_email ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <label for="from_name">From Name</label>
            <input type="text" id="from_name" name="from_name" value="<?php echo htmlspecialchars($settings->from_name ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-success">Save Settings</button>
            <?php if (isset($settings)): ?>
            <button type="button" class="btn" onclick="testConnection()">Test Connection</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<script>
function testConnection() {
    fetch('/smtp-settings/test', { method: 'POST' })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => {
            alert('Test failed: ' + error);
        });
}
</script>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
