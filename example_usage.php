<?php

// Include the PHPMailer class
require_once 'PHPMailer.php';

/**
 * Example usage of the PHPMailer class
 * This file demonstrates all the features including:
 * - Single email sending
 * - Bulk email sending
 * - Personalized emails
 * - HTML templates
 * - Rotating senders and subjects
 */

// Initialize the mailer
$mailer = new PHPMailer();

// Configure multiple "From" addresses for rotation
$mailer->addFromAddresses([
    ['email' => 'noreply@example.com', 'name' => 'Example Company'],
    ['email' => 'support@example.com', 'name' => 'Support Team'],
    ['email' => 'marketing@example.com', 'name' => 'Marketing Department'],
    ['email' => 'newsletter@example.com', 'name' => 'Newsletter Team']
]);

// Configure multiple subjects for rotation
$mailer->addSubjects([
    'Important Update for You',
    'Special Offer Inside',
    'Don\'t Miss Out!',
    'Your Weekly Newsletter',
    'Exclusive Content Awaits'
]);

// Configure bulk email settings
$mailer->setBulkDelay(100000); // 100ms delay between emails
$mailer->setMaxBulkPerBatch(25); // 25 emails per batch

// Example 1: Send a single HTML email
echo "=== Example 1: Single HTML Email ===\n";

$htmlContent = $mailer->createHtmlTemplate(
    'Welcome to Our Service!',
    '
    <h2>Hello and Welcome!</h2>
    <p>Thank you for joining our service. We\'re excited to have you on board!</p>
    <p>Here are some things you can do to get started:</p>
    <ul>
        <li>Complete your profile</li>
        <li>Explore our features</li>
        <li>Connect with other users</li>
    </ul>
    <p style="text-align: center;">
        <a href="https://example.com/getting-started" 
           style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
           Get Started
        </a>
    </p>
    '
);

$singleEmailResult = $mailer->sendMail(
    'user@example.com',
    'Welcome to Our Amazing Service!',
    $htmlContent,
    [
        'reply_to' => ['email' => 'support@example.com', 'name' => 'Support Team'],
        'custom_headers' => [
            'X-Campaign' => 'welcome-series',
            'X-Priority' => '1'
        ]
    ]
);

echo "Single email sent: " . ($singleEmailResult ? "Success" : "Failed") . "\n\n";

// Example 2: Send bulk emails with rotating subjects and senders
echo "=== Example 2: Bulk Email with Rotation ===\n";

$recipients = [
    'user1@example.com',
    'user2@example.com',
    'user3@example.com',
    'user4@example.com',
    'user5@example.com'
];

$bulkHtmlContent = $mailer->createHtmlTemplate(
    'Monthly Newsletter',
    '
    <h2>This Month\'s Highlights</h2>
    <p>Here\'s what\'s been happening this month:</p>
    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
        <h3>📈 New Features Released</h3>
        <p>We\'ve added some amazing new features to improve your experience.</p>
    </div>
    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
        <h3>🎉 Community Highlights</h3>
        <p>See what our amazing community has been up to this month.</p>
    </div>
    <div style="background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 5px;">
        <h3>📚 Educational Content</h3>
        <p>Check out our latest tutorials and guides.</p>
    </div>
    '
);

$bulkResults = $mailer->sendBulkMail(
    $recipients,
    '', // Empty subject will use rotation
    $bulkHtmlContent,
    [
        'custom_headers' => [
            'X-Campaign' => 'monthly-newsletter',
            'List-Unsubscribe' => '<mailto:unsubscribe@example.com>'
        ]
    ]
);

echo "Bulk email results:\n";
echo "Total: {$bulkResults['total']}\n";
echo "Sent: {$bulkResults['sent']}\n";
echo "Failed: {$bulkResults['failed']}\n";
if (!empty($bulkResults['errors'])) {
    echo "Errors:\n";
    foreach ($bulkResults['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

// Example 3: Send personalized bulk emails
echo "=== Example 3: Personalized Bulk Emails ===\n";

$personalizedRecipients = [
    [
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'data' => [
            'first_name' => 'John',
            'account_type' => 'Premium',
            'last_login' => '2024-01-15',
            'points' => '1,250'
        ]
    ],
    [
        'email' => 'jane@example.com',
        'name' => 'Jane Smith',
        'data' => [
            'first_name' => 'Jane',
            'account_type' => 'Basic',
            'last_login' => '2024-01-12',
            'points' => '450'
        ]
    ],
    [
        'email' => 'bob@example.com',
        'name' => 'Bob Johnson',
        'data' => [
            'first_name' => 'Bob',
            'account_type' => 'Premium',
            'last_login' => '2024-01-14',
            'points' => '2,100'
        ]
    ]
];

$personalizedSubject = 'Hello {{first_name}}, your {{account_type}} account update';

$personalizedContent = $mailer->createHtmlTemplate(
    'Account Update',
    '
    <h2>Hello {{first_name}}!</h2>
    <p>We hope you\'re enjoying your <strong>{{account_type}}</strong> account with us.</p>
    <div style="background: #e8f4f8; padding: 20px; margin: 20px 0; border-radius: 8px; border-left: 4px solid #007cba;">
        <h3>Your Account Summary</h3>
        <ul style="list-style: none; padding: 0;">
            <li><strong>Account Type:</strong> {{account_type}}</li>
            <li><strong>Last Login:</strong> {{last_login}}</li>
            <li><strong>Reward Points:</strong> {{points}}</li>
        </ul>
    </div>
    <p>Keep up the great work, {{first_name}}! Your engagement helps us build a better platform for everyone.</p>
    <p style="text-align: center; margin-top: 30px;">
        <a href="https://example.com/dashboard" 
           style="background: #007cba; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;">
           Visit Your Dashboard
        </a>
    </p>
    '
);

$personalizedResults = $mailer->sendPersonalizedBulkMail(
    $personalizedRecipients,
    $personalizedSubject,
    $personalizedContent,
    [
        'custom_headers' => [
            'X-Campaign' => 'account-update',
            'X-Priority' => '2'
        ]
    ]
);

echo "Personalized email results:\n";
echo "Total: {$personalizedResults['total']}\n";
echo "Sent: {$personalizedResults['sent']}\n";
echo "Failed: {$personalizedResults['failed']}\n";
if (!empty($personalizedResults['errors'])) {
    echo "Errors:\n";
    foreach ($personalizedResults['errors'] as $error) {
        echo "  - $error\n";
    }
}
echo "\n";

// Example 4: Send a plain text email
echo "=== Example 4: Plain Text Email ===\n";

$mailer->setContentType('text/plain');

$plainTextMessage = "Hello!\n\n";
$plainTextMessage .= "This is a plain text email sent using the PHP mail() function.\n\n";
$plainTextMessage .= "Features of this mailer:\n";
$plainTextMessage .= "- Native PHP mail() function\n";
$plainTextMessage .= "- Header encoding for better deliverability\n";
$plainTextMessage .= "- Rotating senders and subjects\n";
$plainTextMessage .= "- Bulk email support\n";
$plainTextMessage .= "- Personalization support\n\n";
$plainTextMessage .= "Best regards,\n";
$plainTextMessage .= "The Team";

$plainTextResult = $mailer->sendMail(
    'user@example.com',
    'Plain Text Email Example',
    $plainTextMessage
);

echo "Plain text email sent: " . ($plainTextResult ? "Success" : "Failed") . "\n\n";

// Reset to HTML for other examples
$mailer->setContentType('text/html');

// Example 5: Advanced configuration and custom headers
echo "=== Example 5: Advanced Configuration ===\n";

// Create a custom HTML template with advanced styling
$advancedContent = $mailer->createHtmlTemplate(
    'Advanced Email Example',
    '
    <div style="text-align: center; margin-bottom: 30px;">
        <img src="https://via.placeholder.com/200x80/007cba/ffffff?text=LOGO" alt="Company Logo" style="max-width: 200px;">
    </div>
    
    <h2 style="color: #007cba; border-bottom: 2px solid #007cba; padding-bottom: 10px;">Advanced Features Demo</h2>
    
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 10px; margin: 20px 0;">
        <h3 style="margin: 0 0 10px 0;">🚀 Premium Features</h3>
        <p style="margin: 0;">This email demonstrates advanced HTML formatting and styling capabilities.</p>
    </div>
    
    <div style="display: flex; flex-wrap: wrap; gap: 15px; margin: 20px 0;">
        <div style="flex: 1; min-width: 200px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;">
            <h4 style="color: #007cba; margin: 0 0 10px 0;">✉️ Header Encoding</h4>
            <p style="margin: 0; font-size: 14px;">Proper encoding for international characters and special symbols.</p>
        </div>
        <div style="flex: 1; min-width: 200px; background: #f8f9fa; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6;">
            <h4 style="color: #007cba; margin: 0 0 10px 0;">🔄 Rotation System</h4>
            <p style="margin: 0; font-size: 14px;">Automatic rotation of senders and subjects for better deliverability.</p>
        </div>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h4 style="color: #856404; margin: 0 0 10px 0;">⚡ Performance Optimized</h4>
        <p style="margin: 0; color: #856404;">Batched processing and configurable delays for efficient bulk email sending.</p>
    </div>
    
    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr style="background: #007cba; color: white;">
            <th style="padding: 12px; text-align: left; border: 1px solid #0056b3;">Feature</th>
            <th style="padding: 12px; text-align: left; border: 1px solid #0056b3;">Status</th>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #dee2e6;">HTML Support</td>
            <td style="padding: 10px; border: 1px solid #dee2e6;">✅ Enabled</td>
        </tr>
        <tr style="background: #f8f9fa;">
            <td style="padding: 10px; border: 1px solid #dee2e6;">Header Encoding</td>
            <td style="padding: 10px; border: 1px solid #dee2e6;">✅ Active</td>
        </tr>
        <tr>
            <td style="padding: 10px; border: 1px solid #dee2e6;">Bulk Processing</td>
            <td style="padding: 10px; border: 1px solid #dee2e6;">✅ Optimized</td>
        </tr>
    </table>
    ',
    [
        'container' => 'max-width: 700px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);'
    ]
);

$advancedResult = $mailer->sendMail(
    'advanced@example.com',
    '🎯 Advanced PHP Mailer Demo - Featuring Enhanced Deliverability',
    $advancedContent,
    [
        'reply_to' => ['email' => 'noreply@example.com', 'name' => 'No Reply'],
        'custom_headers' => [
            'X-Campaign' => 'advanced-demo',
            'X-Priority' => '1',
            'X-MSMail-Priority' => 'High',
            'Importance' => 'High',
            'List-Unsubscribe' => '<mailto:unsubscribe@example.com>, <https://example.com/unsubscribe>',
            'Precedence' => 'bulk'
        ]
    ]
);

echo "Advanced email sent: " . ($advancedResult ? "Success" : "Failed") . "\n\n";

echo "=== All Examples Completed ===\n";
echo "Check your email delivery logs for detailed information about sent emails.\n";
echo "Remember to configure your server's mail settings (sendmail/postfix) for actual email delivery.\n";

// Example of error handling and logging
function logEmailActivity($type, $details) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] $type: " . json_encode($details) . "\n";
    file_put_contents('email_log.txt', $logEntry, FILE_APPEND | LOCK_EX);
}

// Log the session
logEmailActivity('EMAIL_SESSION', [
    'single_email' => $singleEmailResult,
    'bulk_results' => $bulkResults,
    'personalized_results' => $personalizedResults,
    'plain_text' => $plainTextResult,
    'advanced_email' => $advancedResult
]);