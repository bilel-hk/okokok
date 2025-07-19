<?php
// Initialize session for flash messages
session_start();

// Include the PHPMailer class
require_once 'PHPMailer.php';

// Load configuration
$config = require 'config.php';

// Initialize mailer
$mailer = new PHPMailer();
$mailer->addFromAddresses($config['from_addresses']);
$mailer->addSubjects($config['subjects']);
$mailer->setBulkDelay($config['bulk_settings']['delay_microseconds']);
$mailer->setMaxBulkPerBatch($config['bulk_settings']['max_per_batch']);
$mailer->setEncoding($config['email_settings']['encoding']);
$mailer->setContentType($config['email_settings']['content_type']);

// Handle form submissions
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'send_single':
                    $recipient = trim($_POST['recipient']);
                    $subject = trim($_POST['subject']);
                    $message = $_POST['message'];
                    $use_html = isset($_POST['use_html']);
                    
                    if ($use_html) {
                        $message = $mailer->createHtmlTemplate($subject ?: 'Email', $message);
                    }
                    
                    $result = $mailer->sendMail($recipient, $subject, $message);
                    $_SESSION['flash'] = $result ? 
                        ['type' => 'success', 'message' => 'Email sent successfully!'] :
                        ['type' => 'error', 'message' => 'Failed to send email.'];
                    break;
                    
                case 'send_bulk':
                    $recipients = array_filter(array_map('trim', explode("\n", $_POST['recipients'])));
                    $subject = trim($_POST['bulk_subject']);
                    $message = $_POST['bulk_message'];
                    $use_html = isset($_POST['bulk_use_html']);
                    
                    if ($use_html) {
                        $message = $mailer->createHtmlTemplate($subject ?: 'Bulk Email', $message);
                    }
                    
                    $results = $mailer->sendBulkMail($recipients, $subject, $message);
                    $_SESSION['flash'] = [
                        'type' => 'info', 
                        'message' => "Bulk email completed. Sent: {$results['sent']}, Failed: {$results['failed']}"
                    ];
                    break;
                    
                case 'test_config':
                    $testEmail = trim($_POST['test_email']);
                    $testMessage = $mailer->createHtmlTemplate(
                        'Test Email',
                        '<h2>Configuration Test</h2><p>If you receive this email, your PHP mailer is working correctly!</p><p><strong>Test Details:</strong></p><ul><li>Date: ' . date('Y-m-d H:i:s') . '</li><li>From addresses: ' . count($config['from_addresses']) . '</li><li>Subjects: ' . count($config['subjects']) . '</li></ul>'
                    );
                    
                    $result = $mailer->sendMail($testEmail, 'PHP Mailer Test', $testMessage);
                    $_SESSION['flash'] = $result ? 
                        ['type' => 'success', 'message' => 'Test email sent successfully!'] :
                        ['type' => 'error', 'message' => 'Test email failed to send.'];
                    break;
            }
        }
    } catch (Exception $e) {
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()];
    }
    
    // Redirect to prevent resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get flash message and clear it
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Mailer Interface</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            max-width: 1200px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }
        .nav-pills .nav-link {
            border-radius: 10px;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .nav-pills .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 5px 15px rgba(102,126,234,0.3);
        }
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            color: white;
            padding: 12px 30px;
            transition: all 0.3s ease;
        }
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.3);
            color: white;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .config-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .stats-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 20px;
        }
        textarea {
            min-height: 120px;
        }
        .tab-content {
            padding: 30px;
        }
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <!-- Header -->
        <div class="text-center mb-4">
            <h1 class="logo text-white"><i class="fas fa-paper-plane me-3"></i>PHP Mailer Interface</h1>
            <p class="text-white-50">Professional email sending made simple</p>
        </div>

        <!-- Flash Messages -->
        <?php if ($flash): ?>
        <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : $flash['type']; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'exclamation-triangle' : 'info-circle'); ?> me-2"></i>
            <?php echo htmlspecialchars($flash['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Main Card -->
        <div class="card">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="mb-0"><i class="fas fa-envelope me-2"></i>Email Management System</h3>
                        <small>Send single emails, bulk campaigns, and test your configuration</small>
                    </div>
                    <div class="col-auto">
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-server me-1"></i>Native PHP mail()
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation Tabs -->
            <div class="card-body p-0">
                <ul class="nav nav-pills nav-fill p-3 mb-0" id="mainTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="single-tab" data-bs-toggle="pill" data-bs-target="#single" type="button" role="tab">
                            <i class="fas fa-paper-plane me-2"></i>Single Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="bulk-tab" data-bs-toggle="pill" data-bs-target="#bulk" type="button" role="tab">
                            <i class="fas fa-broadcast-tower me-2"></i>Bulk Email
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="config-tab" data-bs-toggle="pill" data-bs-target="#config" type="button" role="tab">
                            <i class="fas fa-cog me-2"></i>Configuration
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="test-tab" data-bs-toggle="pill" data-bs-target="#test" type="button" role="tab">
                            <i class="fas fa-vial me-2"></i>Test
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="mainTabsContent">
                    <!-- Single Email Tab -->
                    <div class="tab-pane fade show active" id="single" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <form method="POST">
                                    <input type="hidden" name="action" value="send_single">
                                    
                                    <div class="mb-3">
                                        <label for="recipient" class="form-label">
                                            <i class="fas fa-user me-2"></i>Recipient Email
                                        </label>
                                        <input type="email" class="form-control" id="recipient" name="recipient" required 
                                               placeholder="recipient@example.com">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Subject
                                        </label>
                                        <input type="text" class="form-control" id="subject" name="subject" 
                                               placeholder="Leave empty to use rotation">
                                        <div class="form-text">Leave empty to randomly select from configured subjects</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="message" class="form-label">
                                            <i class="fas fa-edit me-2"></i>Message
                                        </label>
                                        <textarea class="form-control" id="message" name="message" required 
                                                  placeholder="Enter your email message here..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="use_html" name="use_html" checked>
                                        <label class="form-check-label" for="use_html">
                                            <i class="fas fa-code me-2"></i>Use HTML template
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Email
                                    </button>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <div class="stats-card">
                                    <div class="feature-icon">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                    <h5>Single Email</h5>
                                    <p class="mb-0">Send individual emails with automatic sender and subject rotation</p>
                                </div>
                                
                                <div class="card border-0" style="background: #f8f9fa;">
                                    <div class="card-body">
                                        <h6><i class="fas fa-lightbulb me-2"></i>Features</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check text-success me-2"></i>HTML formatting</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Sender rotation</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Subject rotation</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Email validation</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Email Tab -->
                    <div class="tab-pane fade" id="bulk" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <form method="POST">
                                    <input type="hidden" name="action" value="send_bulk">
                                    
                                    <div class="mb-3">
                                        <label for="recipients" class="form-label">
                                            <i class="fas fa-users me-2"></i>Recipients
                                        </label>
                                        <textarea class="form-control" id="recipients" name="recipients" required 
                                                  placeholder="Enter email addresses, one per line:&#10;user1@example.com&#10;user2@example.com&#10;user3@example.com" 
                                                  rows="6"></textarea>
                                        <div class="form-text">Enter one email address per line</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="bulk_subject" class="form-label">
                                            <i class="fas fa-tag me-2"></i>Subject
                                        </label>
                                        <input type="text" class="form-control" id="bulk_subject" name="bulk_subject" 
                                               placeholder="Leave empty to use rotation">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="bulk_message" class="form-label">
                                            <i class="fas fa-edit me-2"></i>Message
                                        </label>
                                        <textarea class="form-control" id="bulk_message" name="bulk_message" required 
                                                  placeholder="Enter your bulk email message here..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="bulk_use_html" name="bulk_use_html" checked>
                                        <label class="form-check-label" for="bulk_use_html">
                                            <i class="fas fa-code me-2"></i>Use HTML template
                                        </label>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-broadcast-tower me-2"></i>Send Bulk Email
                                    </button>
                                </form>
                            </div>
                            <div class="col-lg-4">
                                <div class="stats-card" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                                    <div class="feature-icon">
                                        <i class="fas fa-broadcast-tower"></i>
                                    </div>
                                    <h5>Bulk Email</h5>
                                    <p class="mb-0">Send emails to multiple recipients with batching and delays</p>
                                </div>
                                
                                <div class="card border-0" style="background: #f8f9fa;">
                                    <div class="card-body">
                                        <h6><i class="fas fa-chart-bar me-2"></i>Settings</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-clock text-info me-2"></i>Delay: <?php echo number_format($config['bulk_settings']['delay_microseconds'] / 1000); ?>ms</li>
                                            <li><i class="fas fa-layer-group text-info me-2"></i>Batch size: <?php echo $config['bulk_settings']['max_per_batch']; ?></li>
                                            <li><i class="fas fa-shield-alt text-info me-2"></i>Rate limiting</li>
                                            <li><i class="fas fa-chart-line text-info me-2"></i>Progress tracking</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configuration Tab -->
                    <div class="tab-pane fade" id="config" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-6">
                                <h5><i class="fas fa-envelope me-2"></i>Sender Addresses</h5>
                                <?php foreach ($config['from_addresses'] as $index => $address): ?>
                                <div class="config-item">
                                    <strong><?php echo htmlspecialchars($address['name']); ?></strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($address['email']); ?></span>
                                </div>
                                <?php endforeach; ?>
                                
                                <h5 class="mt-4"><i class="fas fa-tags me-2"></i>Subject Lines</h5>
                                <?php foreach ($config['subjects'] as $index => $subject): ?>
                                <div class="config-item">
                                    <?php echo htmlspecialchars($subject); ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="col-lg-6">
                                <h5><i class="fas fa-cogs me-2"></i>Email Settings</h5>
                                <div class="config-item">
                                    <strong>Encoding:</strong> <?php echo htmlspecialchars($config['email_settings']['encoding']); ?>
                                </div>
                                <div class="config-item">
                                    <strong>Content Type:</strong> <?php echo htmlspecialchars($config['email_settings']['content_type']); ?>
                                </div>
                                
                                <h5 class="mt-4"><i class="fas fa-tachometer-alt me-2"></i>Bulk Settings</h5>
                                <div class="config-item">
                                    <strong>Delay:</strong> <?php echo number_format($config['bulk_settings']['delay_microseconds'] / 1000); ?>ms between emails
                                </div>
                                <div class="config-item">
                                    <strong>Batch Size:</strong> <?php echo $config['bulk_settings']['max_per_batch']; ?> emails per batch
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> To modify these settings, edit the <code>config.php</code> file.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Test Tab -->
                    <div class="tab-pane fade" id="test" role="tabpanel">
                        <div class="row">
                            <div class="col-lg-8">
                                <h5><i class="fas fa-vial me-2"></i>Test Configuration</h5>
                                <p class="text-muted">Send a test email to verify your configuration is working correctly.</p>
                                
                                <form method="POST">
                                    <input type="hidden" name="action" value="test_config">
                                    
                                    <div class="mb-3">
                                        <label for="test_email" class="form-label">
                                            <i class="fas fa-envelope me-2"></i>Test Email Address
                                        </label>
                                        <input type="email" class="form-control" id="test_email" name="test_email" required 
                                               placeholder="your-email@example.com">
                                        <div class="form-text">Enter an email address you have access to</div>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-gradient btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Test Email
                                    </button>
                                </form>
                            </div>
                            
                            <div class="col-lg-4">
                                <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);">
                                    <div class="feature-icon">
                                        <i class="fas fa-flask"></i>
                                    </div>
                                    <h5>Test Email</h5>
                                    <p class="mb-0">Verify your configuration with a test message</p>
                                </div>
                                
                                <div class="card border-0" style="background: #f8f9fa;">
                                    <div class="card-body">
                                        <h6><i class="fas fa-check-circle me-2"></i>Test Includes</h6>
                                        <ul class="list-unstyled mb-0">
                                            <li><i class="fas fa-check text-success me-2"></i>HTML formatting</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Header encoding</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Sender rotation</li>
                                            <li><i class="fas fa-check text-success me-2"></i>Configuration data</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-4">
            <p class="text-white-50">
                <i class="fas fa-code me-2"></i>
                Powered by native PHP mail() function | 
                <i class="fas fa-shield-alt me-2"></i>
                Secure & Reliable
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Character counter for textareas
        document.querySelectorAll('textarea').forEach(textarea => {
            const counter = document.createElement('div');
            counter.className = 'form-text text-end';
            counter.innerHTML = `<small>${textarea.value.length} characters</small>`;
            textarea.parentNode.appendChild(counter);
            
            textarea.addEventListener('input', function() {
                counter.innerHTML = `<small>${this.value.length} characters</small>`;
            });
        });
        
        // Email validation for recipients
        const recipientsTextarea = document.getElementById('recipients');
        if (recipientsTextarea) {
            recipientsTextarea.addEventListener('blur', function() {
                const emails = this.value.split('\n').filter(email => email.trim());
                const invalidEmails = emails.filter(email => !isValidEmail(email.trim()));
                
                if (invalidEmails.length > 0) {
                    this.classList.add('is-invalid');
                    let feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (!feedback) {
                        feedback = document.createElement('div');
                        feedback.className = 'invalid-feedback';
                        this.parentNode.appendChild(feedback);
                    }
                    feedback.textContent = `Invalid email(s): ${invalidEmails.join(', ')}`;
                } else {
                    this.classList.remove('is-invalid');
                    const feedback = this.parentNode.querySelector('.invalid-feedback');
                    if (feedback) feedback.remove();
                }
            });
        }
        
        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }
    </script>
</body>
</html>