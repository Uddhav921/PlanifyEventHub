<?php
include_once 'includes/header.php';
?>

<div class="terms-container">
    <h1>Terms & Conditions</h1>
    
    <section class="terms-section">
        <h2>For Event Organizers</h2>
        <div class="terms-card">
            <h3>Commission Structure</h3>
            <p>By using our platform to host your events, you agree to the following terms:</p>
            <ul>
                <li>A platform fee of 15% will be deducted from each ticket sale</li>
                <li>This fee covers platform maintenance, marketing, and customer support</li>
                <li>Payments will be processed after event completion</li>
            </ul>
        </div>

        <div class="terms-card">
            <h3>Event Posting Guidelines</h3>
            <ul>
                <li>All event information must be accurate and truthful</li>
                <li>Pricing must include all applicable taxes and fees</li>
                <li>Events must comply with local laws and regulations</li>
                <li>Organizers are responsible for event execution and safety</li>
            </ul>
        </div>

        <div class="terms-card">
            <h3>Payment Terms</h3>
            <ul>
                <li>Net payment = Total ticket sales - 15% platform fee</li>
                <li>Payments will be processed within 7 business days after event completion</li>
                <li>Minimum payout threshold: Rs 1000</li>
                <li>Cancellation policies must be clearly stated</li>
            </ul>
        </div>
    </section>

    <section class="terms-section">
        <h2>General Terms</h2>
        <div class="terms-card">
            <h3>User Agreement</h3>
            <p>By accessing and using Planify, you agree to:</p>
            <ul>
                <li>Provide accurate registration information</li>
                <li>Maintain the security of your account</li>
                <li>Not transfer your account to others</li>
                <li>Comply with all applicable laws and regulations</li>
            </ul>
        </div>
    </section>
</div>

<style>
.terms-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.terms-container h1 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 40px;
    font-size: 2.5em;
}

.terms-section {
    margin-bottom: 40px;
}

.terms-section h2 {
    color: #3498db;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #3498db;
}

.terms-card {
    background: #fff;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.terms-card h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.3em;
}

.terms-card p {
    color: #34495e;
    margin-bottom: 15px;
    line-height: 1.6;
}

.terms-card ul {
    list-style-type: none;
    padding-left: 0;
}

.terms-card ul li {
    color: #34495e;
    margin-bottom: 10px;
    padding-left: 20px;
    position: relative;
    line-height: 1.6;
}

.terms-card ul li::before {
    content: "•";
    color: #3498db;
    position: absolute;
    left: 0;
    font-weight: bold;
}

/* Responsive Design */
@media (max-width: 768px) {
    .terms-container {
        margin: 20px auto;
    }

    .terms-container h1 {
        font-size: 2em;
    }

    .terms-card {
        padding: 20px;
    }

    .terms-section h2 {
        font-size: 1.5em;
    }

    .terms-card h3 {
        font-size: 1.2em;
    }
}

@media (max-width: 480px) {
    .terms-container h1 {
        font-size: 1.8em;
    }

    .terms-card {
        padding: 15px;
    }

    .terms-section h2 {
        font-size: 1.3em;
    }

    .terms-card h3 {
        font-size: 1.1em;
    }

    .terms-card ul li {
        font-size: 0.95em;
    }
}
</style>

<?php
include_once 'includes/footer.php';
?>