# PHP Mailer Script using Native `mail()` Function

A comprehensive PHP mailer script that uses the native PHP `mail()` function to send HTML-formatted emails with advanced features for better inbox delivery and bulk email capabilities.

## 🚀 Features

### Core Features
- ✅ **Native PHP `mail()` Function**: No external dependencies required
- ✅ **HTML Email Support**: Send beautifully formatted HTML emails
- ✅ **Header Encoding**: Proper encoding for better deliverability and spam filter avoidance
- ✅ **Sender Rotation**: Multiple "From" addresses that rotate automatically
- ✅ **Subject Rotation**: Multiple subjects that rotate randomly
- ✅ **Bulk Email Processing**: Efficient bulk email sending with batching
- ✅ **Personalization**: Template variables for personalized emails
- ✅ **Error Handling**: Comprehensive error handling and logging

### Advanced Features
- 🔄 **Automatic Rotation**: Random selection of senders and subjects
- 📧 **Email Validation**: Built-in email address validation and sanitization
- ⚡ **Performance Optimization**: Configurable delays and batch processing
- 🎨 **HTML Templates**: Built-in HTML template generator
- 📊 **Detailed Reporting**: Success/failure statistics for bulk operations
- 🔧 **Configurable**: Easy configuration through config file
- 🐛 **Debug Support**: Optional debugging and logging features

## 📁 File Structure

```
├── PHPMailer.php       # Main mailer class
├── config.php          # Configuration file
├── simple_example.php  # Simple usage examples
├── example_usage.php   # Comprehensive examples
└── README.md          # This documentation
```

## 🛠️ Setup

### 1. Basic Setup

1. Upload all files to your web server
2. Edit `config.php` with your email addresses and settings
3. Ensure your server has the `mail()` function enabled
4. Test with `simple_example.php`

### 2. Server Configuration

Make sure your server has a working mail configuration:

**For Linux servers with Postfix/Sendmail:**
```bash
# Check if mail function is available
php -m | grep mail

# Test basic mail functionality
echo "Test email body" | mail -s "Test Subject" your-email@example.com
```

**For development (using a local mail catcher like MailHog):**
```bash
# Install MailHog for testing
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

## 📖 Quick Start

### Basic Usage

```php
<?php
require_once 'PHPMailer.php';

// Initialize mailer
$mailer = new PHPMailer();

// Add sender addresses
$mailer->addFromAddress('noreply@example.com', 'Your Company');
$mailer->addFromAddress('support@example.com', 'Support Team');

// Add subjects for rotation
$mailer->addSubject('Welcome to our service!');
$mailer->addSubject('Important update for you');

// Send a single email
$result = $mailer->sendMail(
    'recipient@example.com',
    'Custom Subject', // Or leave empty for rotation
    '<h1>Hello!</h1><p>This is an HTML email.</p>'
);

echo $result ? 'Email sent!' : 'Failed to send email';
?>
```

### Bulk Email Example

```php
<?php
require_once 'PHPMailer.php';

$mailer = new PHPMailer();
$mailer->addFromAddress('newsletter@example.com', 'Newsletter Team');
$mailer->addSubject('Monthly Newsletter');

// Configure bulk settings
$mailer->setBulkDelay(100000); // 100ms delay between emails
$mailer->setMaxBulkPerBatch(50); // 50 emails per batch

$recipients = [
    'user1@example.com',
    'user2@example.com',
    'user3@example.com'
];

$htmlContent = $mailer->createHtmlTemplate(
    'Newsletter',
    '<h2>This Month\'s Updates</h2><p>Here\'s what\'s new...</p>'
);

$results = $mailer->sendBulkMail($recipients, '', $htmlContent);

echo "Sent: {$results['sent']}, Failed: {$results['failed']}";
?>
```

## 🎯 Advanced Usage

### Personalized Emails

```php
$recipients = [
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

$subject = 'Hello {{first_name}}!';
$message = $mailer->createHtmlTemplate(
    'Personal Update',
    '<h2>Hello {{first_name}}!</h2><p>Your {{plan}} plan at {{company}} is active.</p>'
);

$results = $mailer->sendPersonalizedBulkMail($recipients, $subject, $message);
```

### Custom Headers for Better Deliverability

```php
$options = [
    'reply_to' => ['email' => 'support@example.com', 'name' => 'Support'],
    'custom_headers' => [
        'X-Campaign' => 'newsletter-2024',
        'List-Unsubscribe' => '<mailto:unsubscribe@example.com>',
        'X-Priority' => '1',
        'Importance' => 'High'
    ]
];

$mailer->sendMail('recipient@example.com', 'Subject', $message, $options);
```

## ⚙️ Configuration Options

Edit `config.php` to customize:

```php
return [
    'from_addresses' => [
        ['email' => 'noreply@yourdomain.com', 'name' => 'Your Company'],
        // Add more addresses for rotation
    ],
    
    'subjects' => [
        'Important Update',
        'Special Offer',
        // Add more subjects for rotation
    ],
    
    'bulk_settings' => [
        'delay_microseconds' => 100000, // Delay between emails
        'max_per_batch' => 50, // Emails per batch
    ],
    
    'email_settings' => [
        'encoding' => 'UTF-8',
        'content_type' => 'text/html',
    ]
];
```

## 📧 Email Headers for Better Deliverability

The script automatically includes these headers to improve inbox delivery:

- **MIME-Version**: Ensures proper email format
- **Content-Type**: Specifies HTML content with UTF-8 encoding
- **Content-Transfer-Encoding**: Uses 8bit for better compatibility
- **Message-ID**: Unique identifier for each email
- **Date**: RFC-compliant timestamp
- **Return-Path**: Proper bounce handling
- **X-Mailer**: Identifies the sending script
- **X-Priority**: Email priority setting

## 🔄 Rotation Features

### Sender Rotation
- Automatically rotates between configured "From" addresses
- Helps distribute sending load across multiple addresses
- Improves deliverability by avoiding single-sender limits

### Subject Rotation
- Randomly selects from configured subject lines
- Reduces spam filter triggers from repetitive subjects
- Increases engagement with varied messaging

## 📊 Bulk Email Optimization

### Batching
- Processes emails in configurable batches
- Prevents server overload and timeout issues
- Includes delays between batches

### Performance Settings
```php
$mailer->setBulkDelay(100000);      // 100ms between emails
$mailer->setMaxBulkPerBatch(50);    // 50 emails per batch
```

### Error Handling
- Validates email addresses before sending
- Provides detailed error reporting
- Continues processing even if some emails fail

## 🐛 Debugging and Logging

### Enable Logging
Set in `config.php`:
```php
'logging' => [
    'enabled' => true,
    'log_file' => 'email_log.txt',
    'log_errors' => true,
    'log_success' => false
]
```

### Error Information
The script logs:
- Failed email attempts with reasons
- Invalid email addresses
- Server-level mail() function errors
- Batch processing statistics

## 🔧 Troubleshooting

### Common Issues

1. **Mail function not working**
   - Check if `mail()` function is enabled: `php -m | grep mail`
   - Verify server mail configuration (sendmail/postfix)

2. **Emails going to spam**
   - Set up SPF, DKIM, and DMARC records for your domain
   - Use proper "From" addresses with your domain
   - Include unsubscribe headers
   - Avoid spam trigger words in subjects

3. **Slow bulk sending**
   - Increase batch size: `setMaxBulkPerBatch(100)`
   - Reduce delay: `setBulkDelay(50000)`
   - Check server email rate limits

4. **Headers not working**
   - Verify email client support for HTML
   - Check character encoding settings
   - Test with different email providers

### Testing

1. **Test with small batches first**
2. **Use a test email address you control**
3. **Check server mail logs**: `/var/log/mail.log`
4. **Monitor delivery rates and adjust settings**

## 📝 Best Practices

### For Better Deliverability
1. **Use your own domain** for "From" addresses
2. **Set up proper DNS records** (SPF, DKIM, DMARC)
3. **Include unsubscribe options** in marketing emails
4. **Monitor bounce rates** and remove invalid addresses
5. **Start with small volumes** and gradually increase

### For Performance
1. **Use appropriate batch sizes** based on server capacity
2. **Set reasonable delays** to avoid rate limiting
3. **Monitor server resources** during bulk operations
4. **Use queue systems** for very large volumes

### For Compliance
1. **Obtain proper consent** before sending emails
2. **Include physical address** in marketing emails
3. **Honor unsubscribe requests** promptly
4. **Keep email lists clean** and up-to-date

## 🚀 Examples

Run the included examples:

```bash
# Simple usage examples
php simple_example.php

# Comprehensive feature demonstration
php example_usage.php
```

## 📄 License

This script is provided as-is for educational and commercial use. Feel free to modify and distribute according to your needs.

## 🤝 Contributing

Contributions are welcome! Please ensure:
- Code follows PSR standards
- Include proper documentation
- Test thoroughly before submitting
- Follow security best practices

## ⚠️ Security Notes

1. **Validate all input** before processing
2. **Sanitize email addresses** to prevent injection
3. **Rate limit** email sending to prevent abuse
4. **Use HTTPS** for web interfaces
5. **Keep logs secure** and rotate regularly

---

**Happy Mailing!** 📧