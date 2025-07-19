<?php

/**
 * Simple example showing how to use the PHPMailer with configuration
 */

// Include required files
require_once 'PHPMailer.php';

// Load configuration
$config = require 'config.php';

// Initialize mailer
$mailer = new PHPMailer();

// Setup mailer with configuration
$mailer->addFromAddresses($config['from_addresses']);
$mailer->addSubjects($config['subjects']);
$mailer->setBulkDelay($config['bulk_settings']['delay_microseconds']);
$mailer->setMaxBulkPerBatch($config['bulk_settings']['max_per_batch']);
$mailer->setEncoding($config['email_settings']['encoding']);
$mailer->setContentType($config['email_settings']['content_type']);

echo "PHP Mailer - Simple Example\n";
echo "============================\n\n";

// Example 1: Send a simple email with random sender and subject
echo "1. Sending single email with rotation...\n";

$htmlMessage = $mailer->createHtmlTemplate(
    'Simple Email Test',
    '
    <h2>Hello!</h2>
    <p>This is a test email sent using the PHP native mail() function.</p>
    <p><strong>Features demonstrated:</strong></p>
    <ul>
        <li>✅ HTML email support</li>
        <li>✅ Automatic sender rotation</li>
        <li>✅ Automatic subject rotation</li>
        <li>✅ Proper header encoding</li>
    </ul>
    <p>Best regards,<br>Your PHP Mailer</p>
    '
);

$result1 = $mailer->sendMail(
    'recipient@example.com', // Replace with actual email
    '', // Empty subject = use rotation
    $htmlMessage
);

echo "Result: " . ($result1 ? "✅ Success" : "❌ Failed") . "\n\n";

// Example 2: Send bulk emails
echo "2. Sending bulk emails...\n";

$recipients = [
    'user1@example.com',
    'user2@example.com',
    'user3@example.com'
];

$bulkMessage = $mailer->createHtmlTemplate(
    'Bulk Email Test',
    '
    <h2>Bulk Email Campaign</h2>
    <p>This email was sent as part of a bulk email campaign using optimized batching and delivery.</p>
    <div style="background: #f0f8ff; padding: 15px; border-radius: 5px; margin: 15px 0;">
        <h3>🚀 Bulk Email Features:</h3>
        <ul>
            <li>Batched processing for better performance</li>
            <li>Configurable delays to avoid spam filters</li>
            <li>Automatic sender and subject rotation</li>
            <li>Error handling and reporting</li>
        </ul>
    </div>
    '
);

$bulkResults = $mailer->sendBulkMail(
    $recipients,
    '', // Use rotation
    $bulkMessage
);

echo "Bulk Results:\n";
echo "  Total: {$bulkResults['total']}\n";
echo "  Sent: {$bulkResults['sent']}\n";
echo "  Failed: {$bulkResults['failed']}\n";

if (!empty($bulkResults['errors'])) {
    echo "  Errors:\n";
    foreach ($bulkResults['errors'] as $error) {
        echo "    - $error\n";
    }
}
echo "\n";

// Example 3: Send personalized email
echo "3. Sending personalized email...\n";

$personalizedRecipients = [
    [
        'email' => 'john@example.com',
        'name' => 'John Doe',
        'data' => [
            'first_name' => 'John',
            'company' => 'Acme Corp',
            'plan' => 'Premium'
        ]
    ]
];

$personalizedSubject = 'Hello {{first_name}} from {{company}}!';
$personalizedMessage = $mailer->createHtmlTemplate(
    'Personalized Email',
    '
    <h2>Hello {{first_name}}!</h2>
    <p>Thank you for choosing our <strong>{{plan}}</strong> plan for {{company}}.</p>
    <div style="background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4caf50;">
        <h3>✨ Personalization Features:</h3>
        <p>This email demonstrates variable replacement:</p>
        <ul>
            <li>Name: {{first_name}}</li>
            <li>Company: {{company}}</li>
            <li>Plan: {{plan}}</li>
        </ul>
    </div>
    <p>Best regards,<br>The Team</p>
    '
);

$personalizedResults = $mailer->sendPersonalizedBulkMail(
    $personalizedRecipients,
    $personalizedSubject,
    $personalizedMessage
);

echo "Personalized Results:\n";
echo "  Total: {$personalizedResults['total']}\n";
echo "  Sent: {$personalizedResults['sent']}\n";
echo "  Failed: {$personalizedResults['failed']}\n\n";

echo "============================\n";
echo "All examples completed!\n";
echo "============================\n\n";

// Configuration tips
echo "💡 Configuration Tips:\n";
echo "----------------------\n";
echo "1. Edit config.php to set your email addresses and subjects\n";
echo "2. Make sure your server has mail() function configured\n";
echo "3. Check email_log.txt for detailed logs\n";
echo "4. Test with a small batch first\n";
echo "5. Adjust delays based on your server's email limits\n\n";

echo "📧 Email Headers Used:\n";
echo "----------------------\n";
echo "- MIME-Version: 1.0\n";
echo "- Content-Type: text/html; charset=UTF-8\n";
echo "- Content-Transfer-Encoding: 8bit\n";
echo "- X-Mailer: PHP/" . phpversion() . "\n";
echo "- Message-ID: Unique for each email\n";
echo "- Date: Current timestamp\n";
echo "- From: Rotated from your configured addresses\n";
echo "- Return-Path: Same as From address\n\n";

echo "🔧 Advanced Usage:\n";
echo "------------------\n";
echo "For more advanced features, see example_usage.php\n";
echo "- Custom headers\n";
echo "- Reply-To addresses\n";
echo "- Advanced HTML templates\n";
echo "- Error handling\n";
echo "- Logging customization\n";