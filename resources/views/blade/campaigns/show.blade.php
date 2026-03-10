<?php 
$title = 'Campaign Details';
ob_start(); 
?>

<h1><?php echo htmlspecialchars($campaign->name); ?></h1>

<div style="margin-bottom: 20px;">
    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns" class="btn">← Back to Campaigns</a>
    <?php if ($campaign->status === 'draft'): ?>
    <a href="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>/recipients" class="btn btn-success">Import Recipients</a>
    <?php endif; ?>
</div>

<div class="stats">
    <div class="stat-card">
        <h3>Status</h3>
        <div class="stat-value" style="font-size: 1.5rem;"><?php echo htmlspecialchars($campaign->status); ?></div>
    </div>
    <div class="stat-card">
        <h3>Total Recipients</h3>
        <div class="stat-value"><?php echo $campaign->total_recipients; ?></div>
    </div>
    <div class="stat-card">
        <h3>Sent</h3>
        <div class="stat-value" style="color: #27ae60;"><?php echo $campaign->sent_count; ?></div>
    </div>
    <div class="stat-card">
        <h3>Failed</h3>
        <div class="stat-value" style="color: #e74c3c;"><?php echo $campaign->failed_count; ?></div>
    </div>
</div>

<?php if ($campaign->status === 'sent' || $campaign->status === 'sending'): ?>
<div class="stats">
    <div class="stat-card">
        <h3>Open Rate</h3>
        <div class="stat-value" style="font-size: 1.8rem;"><?php echo $stats['open_rate'] ?? '0'; ?>%</div>
    </div>
    <div class="stat-card">
        <h3>Click Rate</h3>
        <div class="stat-value" style="font-size: 1.8rem;"><?php echo $stats['click_rate'] ?? '0'; ?>%</div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h2>Campaign Information</h2>
    <table>
        <tr>
            <td><strong>Campaign ID:</strong></td>
            <td><?php echo $campaign->id; ?></td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><?php echo htmlspecialchars($campaign->status); ?></td>
        </tr>
        <tr>
            <td><strong>Created:</strong></td>
            <td><?php echo $campaign->created_at->format('F d, Y H:i:s'); ?></td>
        </tr>
        <?php if ($campaign->started_at): ?>
        <tr>
            <td><strong>Started:</strong></td>
            <td><?php echo date('F d, Y H:i:s', strtotime($campaign->started_at)); ?></td>
        </tr>
        <?php endif; ?>
        <?php if ($campaign->completed_at): ?>
        <tr>
            <td><strong>Completed:</strong></td>
            <td><?php echo date('F d, Y H:i:s', strtotime($campaign->completed_at)); ?></td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<?php if ($campaign->status === 'draft' && $campaign->total_recipients > 0): ?>
<div class="card">
    <h2>Ready to Launch</h2>
    <p>Your campaign has <?php echo $campaign->total_recipients; ?> recipients and is ready to be launched.</p>
    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>/launch">
        <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to launch this campaign?')">Launch Campaign</button>
    </form>
</div>
<?php elseif ($campaign->status === 'draft'): ?>
<div class="card">
    <h2>Import Recipients</h2>
    <p>Upload a CSV file with recipient information to add them to this campaign.</p>
    <form method="POST" action="<?= rtrim(parse_url($_ENV['APP_URL'] ?? getenv('APP_URL') ?: '', PHP_URL_PATH) ?? '', '/') ?>/campaigns/<?php echo $campaign->id; ?>/recipients" enctype="multipart/form-data">
        <div class="form-group">
            <label for="recipients_file">CSV File (email, name, custom_field1, ...)</label>
            <input type="file" id="recipients_file" name="recipients_file" accept=".csv" required>
            <small>Format: email,name,custom_field1,custom_field2,...</small>
        </div>
        <button type="submit" class="btn btn-success">Import Recipients</button>
    </form>
</div>
<?php endif; ?>

<?php 
$content = ob_get_clean(); 
include dirname(__DIR__, 3) . '/resources/views/blade/layout.php'; 
?>
