<?php

/**
 * PHP Mailer Class using native mail() function
 * Supports HTML emails, header encoding, rotating senders/subjects, and bulk email sending
 * 
 * @author AI Assistant
 * @version 1.0
 */
class PHPMailer
{
    private $fromAddresses = [];
    private $subjects = [];
    private $defaultHeaders = [];
    private $encoding = 'UTF-8';
    private $contentType = 'text/html';
    private $bulkDelay = 0; // Delay between bulk emails in microseconds
    private $maxBulkPerBatch = 50; // Maximum emails per batch
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->initializeDefaultHeaders();
    }
    
    /**
     * Initialize default headers for better deliverability
     */
    private function initializeDefaultHeaders()
    {
        $this->defaultHeaders = [
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=' . $this->encoding,
            'Content-Transfer-Encoding' => '8bit',
            'X-Mailer' => 'PHP/' . phpversion(),
            'X-Priority' => '3',
            'Message-ID' => $this->generateMessageId(),
            'Date' => date('r'),
        ];
    }
    
    /**
     * Add a "From" address for rotation
     * 
     * @param string $email Email address
     * @param string $name Display name
     */
    public function addFromAddress($email, $name = '')
    {
        $this->fromAddresses[] = [
            'email' => $email,
            'name' => $name
        ];
    }
    
    /**
     * Add multiple "From" addresses at once
     * 
     * @param array $addresses Array of ['email' => '', 'name' => ''] arrays
     */
    public function addFromAddresses(array $addresses)
    {
        foreach ($addresses as $address) {
            $email = $address['email'] ?? '';
            $name = $address['name'] ?? '';
            if ($email) {
                $this->addFromAddress($email, $name);
            }
        }
    }
    
    /**
     * Add a subject for rotation
     * 
     * @param string $subject Email subject
     */
    public function addSubject($subject)
    {
        $this->subjects[] = $subject;
    }
    
    /**
     * Add multiple subjects at once
     * 
     * @param array $subjects Array of subject strings
     */
    public function addSubjects(array $subjects)
    {
        $this->subjects = array_merge($this->subjects, $subjects);
    }
    
    /**
     * Set bulk email delay (in microseconds)
     * 
     * @param int $delay Delay in microseconds
     */
    public function setBulkDelay($delay)
    {
        $this->bulkDelay = (int)$delay;
    }
    
    /**
     * Set maximum emails per batch
     * 
     * @param int $max Maximum emails per batch
     */
    public function setMaxBulkPerBatch($max)
    {
        $this->maxBulkPerBatch = (int)$max;
    }
    
    /**
     * Set content type (text/html or text/plain)
     * 
     * @param string $type Content type
     */
    public function setContentType($type)
    {
        $this->contentType = $type;
        $this->defaultHeaders['Content-Type'] = $type . '; charset=' . $this->encoding;
    }
    
    /**
     * Set character encoding
     * 
     * @param string $encoding Character encoding
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;
        $this->defaultHeaders['Content-Type'] = $this->contentType . '; charset=' . $encoding;
    }
    
    /**
     * Generate a unique Message-ID
     * 
     * @return string Message ID
     */
    private function generateMessageId()
    {
        $domain = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        return '<' . uniqid() . '.' . time() . '@' . $domain . '>';
    }
    
    /**
     * Get a random "From" address
     * 
     * @return array From address details
     */
    private function getRandomFromAddress()
    {
        if (empty($this->fromAddresses)) {
            throw new Exception('No "From" addresses configured. Use addFromAddress() first.');
        }
        
        return $this->fromAddresses[array_rand($this->fromAddresses)];
    }
    
    /**
     * Get a random subject
     * 
     * @return string Subject
     */
    private function getRandomSubject()
    {
        if (empty($this->subjects)) {
            throw new Exception('No subjects configured. Use addSubject() first.');
        }
        
        return $this->subjects[array_rand($this->subjects)];
    }
    
    /**
     * Encode header value for better deliverability
     * 
     * @param string $value Header value
     * @return string Encoded header value
     */
    private function encodeHeader($value)
    {
        if (mb_detect_encoding($value, 'ASCII', true) === false) {
            return '=?' . $this->encoding . '?B?' . base64_encode($value) . '?=';
        }
        return $value;
    }
    
    /**
     * Format email address with name
     * 
     * @param string $email Email address
     * @param string $name Display name
     * @return string Formatted email address
     */
    private function formatEmailAddress($email, $name = '')
    {
        if (empty($name)) {
            return $email;
        }
        
        $encodedName = $this->encodeHeader($name);
        return '"' . $encodedName . '" <' . $email . '>';
    }
    
    /**
     * Build email headers
     * 
     * @param array $options Email options
     * @return string Headers string
     */
    private function buildHeaders($options)
    {
        $headers = $this->defaultHeaders;
        
        // Generate new Message-ID for each email
        $headers['Message-ID'] = $this->generateMessageId();
        $headers['Date'] = date('r');
        
        // Set From address
        $fromAddress = $options['from'] ?? $this->getRandomFromAddress();
        $headers['From'] = $this->formatEmailAddress($fromAddress['email'], $fromAddress['name']);
        
        // Set Reply-To if specified
        if (!empty($options['reply_to'])) {
            $headers['Reply-To'] = $this->formatEmailAddress(
                $options['reply_to']['email'],
                $options['reply_to']['name'] ?? ''
            );
        }
        
        // Set Return-Path
        $headers['Return-Path'] = $fromAddress['email'];
        
        // Add custom headers if provided
        if (!empty($options['custom_headers']) && is_array($options['custom_headers'])) {
            $headers = array_merge($headers, $options['custom_headers']);
        }
        
        // Convert headers array to string
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }
        
        return rtrim($headerString);
    }
    
    /**
     * Sanitize email address
     * 
     * @param string $email Email address
     * @return string Sanitized email address
     */
    private function sanitizeEmail($email)
    {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Validate email address
     * 
     * @param string $email Email address
     * @return bool True if valid
     */
    private function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Send a single email
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject (optional if using rotation)
     * @param string $message Email body
     * @param array $options Additional options
     * @return bool True on success
     */
    public function sendMail($to, $subject = '', $message = '', $options = [])
    {
        try {
            // Validate and sanitize recipient
            $to = $this->sanitizeEmail($to);
            if (!$this->validateEmail($to)) {
                throw new Exception('Invalid recipient email address: ' . $to);
            }
            
            // Use random subject if not provided
            if (empty($subject)) {
                $subject = $this->getRandomSubject();
            }
            
            // Encode subject
            $encodedSubject = $this->encodeHeader($subject);
            
            // Build headers
            $headers = $this->buildHeaders($options);
            
            // Send email using native mail() function
            $result = mail($to, $encodedSubject, $message, $headers);
            
            if (!$result) {
                throw new Exception('Failed to send email to: ' . $to);
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log('PHPMailer Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send bulk emails
     * 
     * @param array $recipients Array of recipient email addresses
     * @param string $subject Email subject (optional if using rotation)
     * @param string $message Email body
     * @param array $options Additional options
     * @return array Results array with success/failure counts
     */
    public function sendBulkMail($recipients, $subject = '', $message = '', $options = [])
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        $batches = array_chunk($recipients, $this->maxBulkPerBatch);
        
        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $recipient) {
                try {
                    // Use different subject for each email if rotating
                    $currentSubject = empty($subject) ? $this->getRandomSubject() : $subject;
                    
                    if ($this->sendMail($recipient, $currentSubject, $message, $options)) {
                        $results['sent']++;
                    } else {
                        $results['failed']++;
                        $results['errors'][] = 'Failed to send to: ' . $recipient;
                    }
                    
                    // Add delay between emails if specified
                    if ($this->bulkDelay > 0) {
                        usleep($this->bulkDelay);
                    }
                    
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = $e->getMessage();
                }
            }
            
            // Add a longer delay between batches
            if ($batchIndex < count($batches) - 1 && $this->bulkDelay > 0) {
                sleep(1); // 1 second delay between batches
            }
        }
        
        return $results;
    }
    
    /**
     * Send personalized bulk emails
     * 
     * @param array $recipients Array of recipient data [['email' => '', 'name' => '', 'data' => []], ...]
     * @param string $subjectTemplate Subject template with placeholders
     * @param string $messageTemplate Message template with placeholders
     * @param array $options Additional options
     * @return array Results array
     */
    public function sendPersonalizedBulkMail($recipients, $subjectTemplate = '', $messageTemplate = '', $options = [])
    {
        $results = [
            'total' => count($recipients),
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];
        
        foreach ($recipients as $recipient) {
            try {
                $email = $recipient['email'] ?? '';
                $name = $recipient['name'] ?? '';
                $data = $recipient['data'] ?? [];
                
                if (empty($email)) {
                    $results['failed']++;
                    $results['errors'][] = 'Missing email address in recipient data';
                    continue;
                }
                
                // Prepare placeholders
                $placeholders = array_merge($data, [
                    'name' => $name,
                    'email' => $email
                ]);
                
                // Replace placeholders in subject and message
                $personalizedSubject = $this->replacePlaceholders($subjectTemplate, $placeholders);
                $personalizedMessage = $this->replacePlaceholders($messageTemplate, $placeholders);
                
                // Use random subject if template is empty
                if (empty($personalizedSubject)) {
                    $personalizedSubject = $this->getRandomSubject();
                }
                
                if ($this->sendMail($email, $personalizedSubject, $personalizedMessage, $options)) {
                    $results['sent']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = 'Failed to send to: ' . $email;
                }
                
                // Add delay between emails
                if ($this->bulkDelay > 0) {
                    usleep($this->bulkDelay);
                }
                
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = $e->getMessage();
            }
        }
        
        return $results;
    }
    
    /**
     * Replace placeholders in text
     * 
     * @param string $text Text with placeholders
     * @param array $data Placeholder data
     * @return string Text with replaced placeholders
     */
    private function replacePlaceholders($text, $data)
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{{' . $key . '}}', $value, $text);
        }
        return $text;
    }
    
    /**
     * Create an HTML email template
     * 
     * @param string $title Email title
     * @param string $content Email content
     * @param array $styles Additional CSS styles
     * @return string HTML email template
     */
    public function createHtmlTemplate($title, $content, $styles = [])
    {
        $defaultStyles = [
            'body' => 'font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 20px;',
            'container' => 'max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);',
            'header' => 'background: #007cba; color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; margin: -20px -20px 20px -20px;',
            'footer' => 'margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; font-size: 12px; color: #666; text-align: center;'
        ];
        
        $styles = array_merge($defaultStyles, $styles);
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="' . $this->encoding . '">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($title) . '</title>
</head>
<body style="' . $styles['body'] . '">
    <div style="' . $styles['container'] . '">
        <div style="' . $styles['header'] . '">
            <h1 style="margin: 0;">' . htmlspecialchars($title) . '</h1>
        </div>
        <div class="content">
            ' . $content . '
        </div>
        <div style="' . $styles['footer'] . '">
            <p>This email was sent using PHP native mail() function.</p>
        </div>
    </div>
</body>
</html>';
    }
}