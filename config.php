<?php

/**
 * Configuration file for PHP Mailer
 * Modify these settings according to your needs
 */

return [
    // Default sender addresses for rotation
    'from_addresses' => [
        ['email' => 'noreply@yourdomain.com', 'name' => 'Your Company Name'],
        ['email' => 'support@yourdomain.com', 'name' => 'Support Team'],
        ['email' => 'marketing@yourdomain.com', 'name' => 'Marketing Department'],
        ['email' => 'newsletter@yourdomain.com', 'name' => 'Newsletter Team']
    ],
    
    // Default subjects for rotation
    'subjects' => [
        'Important Update for You',
        'Special Offer Inside',
        'Don\'t Miss Out!',
        'Your Weekly Newsletter',
        'Exclusive Content Awaits',
        'New Features Available',
        'Thank You for Being With Us',
        'Limited Time Offer'
    ],
    
    // Bulk email settings
    'bulk_settings' => [
        'delay_microseconds' => 100000, // 100ms delay between emails
        'max_per_batch' => 50, // Maximum emails per batch
        'batch_delay_seconds' => 1 // Delay between batches in seconds
    ],
    
    // Default email settings
    'email_settings' => [
        'encoding' => 'UTF-8',
        'content_type' => 'text/html', // 'text/html' or 'text/plain'
    ],
    
    // Default headers for better deliverability
    'default_headers' => [
        'X-Priority' => '3', // 1 = High, 3 = Normal, 5 = Low
        'X-MSMail-Priority' => 'Normal',
        'Importance' => 'Normal',
        'Precedence' => 'bulk'
    ],
    
    // Logging settings
    'logging' => [
        'enabled' => true,
        'log_file' => 'email_log.txt',
        'log_errors' => true,
        'log_success' => false // Set to true if you want to log successful sends too
    ],
    
    // Development/Testing settings
    'development' => [
        'test_mode' => false, // Set to true to prevent actual email sending
        'test_recipient' => 'test@yourdomain.com', // All emails will go here in test mode
        'debug' => false // Enable detailed debugging output
    ]
];