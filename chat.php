<?php
session_start();
include_once 'includes/db_connect.php';
date_default_timezone_set('Asia/Kolkata'); // Set Indian timezone

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$qa_data = [
    // Booking Related Questions
    [
        'question' => 'How do I book an event?',
        'answer' => "To book an event:\n- Browse events page\n- Select your event\n- Click 'Book Now'\n- Choose tickets\n- Complete payment\n- Receive confirmation"
    ],
    [
        'question' => 'What payment methods do you accept?',
        'answer' => "We accept:\n- Credit/Debit Cards\n- UPI (Google Pay, PhonePe)\n- Net Banking\n- Mobile Wallets\n- PayTM\n- Net Banking"
    ],
    [
        'question' => 'Can I cancel my booking?',
        'answer' => "Yes, you can cancel through your dashboard:\n- 100% refund: 7+ days before\n- 50% refund: 3-7 days before\n- No refund: <3 days before\n- Cancellation fees may apply"
    ],
    [
        'question' => 'How do I get my event tickets?',
        'answer' => "E-tickets are:\n- Sent to your email instantly\n- Available in dashboard\n- Downloadable as PDF\n- Include QR code\n- Can be printed or shown on phone"
    ],
    [
        'question' => 'What is your platform fee?',
        'answer' => "Platform fees:\n- 15% standard fee\n- Includes payment processing\n- Covers customer support\n- Marketing tools included\n- Security features"
    ],
    // Account Management
    [
        'question' => 'How do I create an account?',
        'answer' => "To create account:\n- Click 'Sign Up'\n- Enter email and password\n- Verify email address\n- Complete profile\n- Start booking events"
    ],
    [
        'question' => 'How do I reset my password?',
        'answer' => "Reset password steps:\n- Click 'Forgot Password'\n- Enter email address\n- Check email for reset link\n- Create new password\n- Login with new password"
    ],
    // Event Related
    [
        'question' => 'What types of events do you offer?',
        'answer' => "Event categories include:\n- Music Concerts\n- Comedy Shows\n- Theatre Plays\n- Sports Events\n- Business Conferences\n- Cultural Festivals\n- Online Webinars"
    ],
    [
        'question' => 'Can I book multiple tickets?',
        'answer' => "Yes, multiple booking options:\n- Select quantity needed\n- Group booking available\n- Bulk discounts apply\n- Max 10 tickets per transaction\n- Special arrangements for larger groups"
    ],
    [
        'question' => 'What if an event gets cancelled?',
        'answer' => "For cancelled events:\n- Full refund processed\n- Email notification sent\n- Alternative dates if available\n- Refund within 5-7 days\n- Option to transfer to another event"
    ],
    // Technical Support
    [
        'question' => 'How do I contact support?',
        'answer' => "Contact options:\n- Live chat support\n- Email: support@planify.com\n- Phone: +91 XXX XXX XXXX\n- Contact form\n- Social media channels"
    ],
    [
        'question' => 'Is my payment information secure?',
        'answer' => "Security measures:\n- SSL encryption\n- PCI DSS compliance\n- Secure payment gateway\n- Two-factor authentication\n- Regular security audits"
    ],
    // Special Features
    [
        'question' => 'Do you have a mobile app?',
        'answer' => "Mobile access options:\n- Mobile-friendly website\n- Progressive Web App\n- iOS app coming soon\n- Android app coming soon\n- Offline ticket access"
    ],
    [
        'question' => 'Are there any discounts available?',
        'answer' => "Available discounts:\n- Early bird offers\n- Group bookings\n- Student discounts\n- Loyalty rewards\n- Seasonal promotions\n- Special occasion deals"
    ],
    [
        'question' => 'How do I become an event organizer?',
        'answer' => "Become organizer steps:\n- Register account\n- Submit verification documents\n- Complete organizer profile\n- Accept terms & conditions\n- Start creating events"
    ]
];
// Keep your existing $qa_data array here
// ...existing qa_data array...

// Handle chat message
$chat_response = '';
$current_time = date('h:i A'); // 12-hour format with AM/PM

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $user_message = strtolower(trim($_POST['message']));
    
    // Find matching question/answer
    $best_match = null;
    $highest_similarity = 0;
    
    foreach ($qa_data as $qa) {
        $similarity = similar_text(strtolower($qa['question']), $user_message, $percent);
        if ($percent > $highest_similarity) {
            $highest_similarity = $percent;
            $best_match = $qa;
        }
    }
    
    if ($highest_similarity > 60) {
        $chat_response = nl2br($best_match['answer']);
    } else {
        $chat_response = "I'm sorry, I couldn't find a specific answer to your question. Please try rephrasing or check our suggested questions.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Support | Planify</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>

// Update the style section in the head

    // ...existing styles...

    .chat-messages {
        height: 500px;
        overflow-y: auto;
        padding: 20px;
        background: #f8f9fa;
        font-size: 16px; /* Increased base font size */
    }

    .message-content {
        max-width: 80%;
        padding: 15px 20px; /* Increased padding */
        border-radius: 10px;
        margin: 5px 0;
        white-space: pre-line;
        font-size: 1.1em; /* Larger message text */
        line-height: 1.5;
    }

    .message-time {
        font-size: 0.9em; /* Slightly larger timestamp */
        color: #6c757d;
        margin-top: 4px;
    }

    .chat-input input {
        flex: 1;
        padding: 15px; /* Increased input padding */
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1.1em; /* Larger input text */
    }

    .chat-input button {
        padding: 15px 30px; /* Larger button */
        font-size: 1.1em; /* Larger button icon */
    }

    .suggested-questions h3 {
        margin-bottom: 20px;
        color: #2c3e50;
        font-size: 1.4em; /* Larger heading */
        padding-bottom: 10px;
        border-bottom: 2px solid #3498db;
    }

    .question-list button {
        width: 100%;
        text-align: left;
        padding: 12px 18px; /* Increased padding */
        background: none;
        border: 1px solid #ddd;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s;
        font-size: 1.05em; /* Larger question text */
        color: #2c3e50;
        line-height: 1.4;
    }

    /* Add better readability for bot responses */
    .bot-message .message-content {
        background: #e9ecef;
        color: #2c3e50;
        font-size: 1.1em;
        line-height: 1.6;
    }

    /* Make list items in bot responses more readable */
    .bot-message .message-content ul li {
        font-size: 1.1em;
        margin-bottom: 8px;
    }

        .chat-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
        }

        .chat-box {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            order: 2;
        }

        .suggested-questions {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 20px;
            order: 1;
        }

        .chat-messages {
            height: 500px;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .chat-input {
            padding: 20px;
            border-top: 1px solid #eee;
            background: #fff;
        }

        .message {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
        }

        .user-message {
            align-items: flex-end;
        }

        .bot-message {
            align-items: flex-start;
        }

        .message-content {
            max-width: 80%;
            padding: 12px 16px;
            border-radius: 10px;
            margin: 5px 0;
            white-space: pre-line;
        }

        .user-message .message-content {
            background: #3498db;
            color: #fff;
        }

        .bot-message .message-content {
            background: #e9ecef;
            color: #2c3e50;
        }

        .message-time {
            font-size: 0.8em;
            color: #6c757d;
            margin-top: 4px;
        }

        .chat-form {
            display: flex;
            gap: 10px;
        }

        .chat-input input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }

        .chat-input button {
            padding: 12px 24px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .chat-input button:hover {
            background: #2980b9;
        }

        .question-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .question-list li {
            margin-bottom: 10px;
        }

        .question-list button {
            width: 100%;
            text-align: left;
            padding: 10px 15px;
            background: none;
            border: 1px solid #ddd;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9em;
            color: #2c3e50;
        }

        .question-list button:hover {
            background: #f8f9fa;
            border-color: #3498db;
            transform: translateX(5px);
        }

        .suggested-questions h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.2em;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }

        @media (max-width: 768px) {
            .chat-container {
                grid-template-columns: 1fr;
            }
            
            .suggested-questions {
                order: 2;
            }
            
            .chat-box {
                order: 1;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="chat-container">
        <div class="suggested-questions">
            <h3>Suggested Questions</h3>
            <ul class="question-list">
                <?php foreach ($qa_data as $qa): ?>
                    <li>
                        <button type="button" onclick="askQuestion('<?php echo addslashes($qa['question']); ?>')">
                            <?php echo htmlspecialchars($qa['question']); ?>
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="chat-box">
            <div class="chat-messages" id="chatMessages">
                <div class="message bot-message">
                    <div class="message-content">
                        Hello! How can I help you today?
                    </div>
                    <span class="message-time"><?php echo $current_time; ?></span>
                </div>
                <?php if ($chat_response): ?>
                    <div class="message user-message">
                        <div class="message-content">
                            <?php echo htmlspecialchars($_POST['message']); ?>
                        </div>
                        <span class="message-time"><?php echo $current_time; ?></span>
                    </div>
                    <div class="message bot-message">
                        <div class="message-content">
                            <?php echo $chat_response; ?>
                        </div>
                        <span class="message-time"><?php echo $current_time; ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="chat-input">
                <form method="POST" class="chat-form" id="chatForm">
                    <input type="text" 
                           name="message" 
                           placeholder="Type your question..." 
                           required 
                           autocomplete="off">
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            chatMessages.scrollTop = chatMessages.scrollHeight;

            window.askQuestion = function(question) {
                const form = document.getElementById('chatForm');
                const input = form.querySelector('input[name="message"]');
                input.value = question;
                form.submit();
            }

            // Prevent double submission
            document.getElementById('chatForm').addEventListener('submit', function(e) {
                const button = this.querySelector('button');
                button.disabled = true;
                setTimeout(() => button.disabled = false, 1000);
            });
        });
    </script>
</body>
</html>