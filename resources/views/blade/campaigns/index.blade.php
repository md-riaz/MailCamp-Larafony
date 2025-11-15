<?php 
$title = 'Campaigns';
ob_start(); 
?>

<h1>Email Campaigns</h1>

<div style="margin-bottom: 20px;">
    <a href="/campaigns/create" class="btn btn-success">Create New Campaign</a>
</div>

<div class="card">
    <?php if (!empty($campaigns)): ?>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Status</th>
                <th>Recipients</th>
                <th>Sent</th>
                <th>Failed</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($campaigns as $campaign): ?>
            <tr>
                <td><a href="/campaigns/<?php echo $campaign->id; ?>"><?php echo htmlspecialchars($campaign->name); ?></a></td>
                <td>
                    <span style="
                        padding: 4px 8px; 
                        border-radius: 4px; 
                        font-size: 0.85em;
                        background: <?php 
                            echo $campaign->status === 'sent' ? '#d4edda' : 
                                 ($campaign->status === 'sending' ? '#fff3cd' : 
                                 ($campaign->status === 'draft' ? '#d1ecf1' : '#f8d7da')); 
                        ?>;
                        color: <?php 
                            echo $campaign->status === 'sent' ? '#155724' : 
                                 ($campaign->status === 'sending' ? '#856404' : 
                                 ($campaign->status === 'draft' ? '#0c5460' : '#721c24')); 
                        ?>;
                    "><?php echo htmlspecialchars($campaign->status); ?></span>
                </td>
                <td><?php echo $campaign->total_recipients; ?></td>
                <td><?php echo $campaign->sent_count; ?></td>
                <td><?php echo $campaign->failed_count; ?></td>
                <td><?php echo $campaign->created_at->format('M d, Y'); ?></td>
                <td>
                    <a href="/campaigns/<?php echo $campaign->id; ?>" class="btn">View</a>
                    <?php if ($campaign->status === 'draft'): ?>
                    <form method="POST" action="/campaigns/<?php echo $campaign->id; ?>/launch" style="display: inline;">
                        <button type="submit" class="btn btn-success" onclick="return confirm('Launch this campaign?')">Launch</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>No campaigns yet. <a href="/campaigns/create">Create your first campaign</a></p>
    <?php endif; ?>
</div>

<?php 
$content = ob_get_clean(); 
include __DIR__ . '/../layout.php'; 
?>
