<?php include_once 'includes/header.php'; ?>

<div class="price-list-container">
    <h1>Event Category Price Guide</h1>
    <p class="price-intro">Standard pricing guidelines for different event categories. Actual prices may vary based on event specifics.</p>

    <div class="price-cards-container">
        <!-- Business Events -->
        <div class="price-card business">
            <div class="category-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <h2>Business Events</h2>
            <ul class="price-ranges">
                <li>Conferences: ₹2,000 - ₹5,000</li>
                <li>Seminars: ₹1,500 - ₹3,000</li>
                <li>Networking: ₹1,000 - ₹2,500</li>
                <li>Workshops: ₹2,500 - ₹4,000</li>
            </ul>
            <p class="note">* Includes professional networking opportunities</p>
        </div>

        <!-- Arts & Culture -->
        <div class="price-card culture">
            <div class="category-icon">
                <i class="fas fa-palette"></i>
            </div>
            <h2>Arts & Culture</h2>
            <ul class="price-ranges">
                <li>Art Exhibitions: ₹500 - ₹1,500</li>
                <li>Cultural Shows: ₹1,000 - ₹3,000</li>
                <li>Theater: ₹800 - ₹2,500</li>
                <li>Dance Shows: ₹1,200 - ₹3,500</li>
            </ul>
            <p class="note">* Special rates for students with valid ID</p>
        </div>

        <!-- Workshops -->
        <div class="price-card workshop">
            <div class="category-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h2>Workshops</h2>
            <ul class="price-ranges">
                <li>Skill Development: ₹1,500 - ₹3,000</li>
                <li>Creative Arts: ₹1,000 - ₹2,500</li>
                <li>Professional: ₹2,000 - ₹4,000</li>
                <li>Technical: ₹2,500 - ₹5,000</li>
            </ul>
            <p class="note">* Materials included in price</p>
        </div>

        <!-- Technical Events -->
        <div class="price-card technical">
            <div class="category-icon">
                <i class="fas fa-laptop-code"></i>
            </div>
            <h2>Technical Events</h2>
            <ul class="price-ranges">
                <li>Hackathons: ₹1,000 - ₹3,000</li>
                <li>Tech Summits: ₹2,500 - ₹5,000</li>
                <li>Coding Contests: ₹500 - ₹1,500</li>
                <li>Tech Workshops: ₹2,000 - ₹4,000</li>
            </ul>
            <p class="note">* Includes development tools access</p>
        </div>

        <!-- Sports Events -->
        <div class="price-card sports">
            <div class="category-icon">
                <i class="fas fa-running"></i>
            </div>
            <h2>Sports Events</h2>
            <ul class="price-ranges">
                <li>Tournaments: ₹800 - ₹2,000</li>
                <li>Training Camps: ₹1,500 - ₹3,500</li>
                <li>Marathon: ₹1,000 - ₹2,500</li>
                <li>Sports Meet: ₹500 - ₹1,500</li>
            </ul>
            <p class="note">* Equipment provided for specific events</p>
        </div>

        <!-- Party Events -->
        <div class="price-card party">
            <div class="category-icon">
                <i class="fas fa-glass-cheers"></i>
            </div>
            <h2>Party Events</h2>
            <ul class="price-ranges">
                <li>DJ Nights: ₹1,500 - ₹3,000</li>
                <li>Theme Parties: ₹2,000 - ₹4,000</li>
                <li>Food Festivals: ₹1,000 - ₹2,500</li>
                <li>Gala Events: ₹2,500 - ₹5,000</li>
            </ul>
            <p class="note">* Food and beverages may be included</p>
        </div>

        <!-- Concerts -->
        <div class="price-card concerts">
            <div class="category-icon">
                <i class="fas fa-music"></i>
            </div>
            <h2>Concerts</h2>
            <ul class="price-ranges">
                <li>Local Artists: ₹1,000 - ₹3,000</li>
                <li>National Artists: ₹2,500 - ₹7,500</li>
                <li>International: ₹5,000 - ₹15,000</li>
                <li>Music Festivals: ₹3,000 - ₹10,000</li>
            </ul>
            <p class="note">* VIP packages available separately</p>
        </div>
    </div>

    <div class="price-notes">
        <h3>Important Notes:</h3>
        <ul>
            <li>All prices are indicative and may vary based on event scale and requirements</li>
            <li>Group discounts available for bookings of 5 or more tickets</li>
            <li>Early bird discounts may apply on selected events</li>
            <li>Special rates for students and senior citizens (valid ID required)</li>
            <li>Platform fee of 15% applies to all ticket sales</li>
        </ul>
    </div>
</div>

<style>
.price-list-container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.price-list-container h1 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 20px;
}

.price-intro {
    text-align: center;
    color: #666;
    margin-bottom: 40px;
}

.price-cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 40px;
}

.price-card {
    background: #fff;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.price-card:hover {
    transform: translateY(-5px);
}

.category-icon {
    text-align: center;
    font-size: 2.5em;
    margin-bottom: 20px;
    color: #3498db;
}

.price-card h2 {
    text-align: center;
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.price-ranges {
    list-style: none;
    padding: 0;
    margin-bottom: 20px;
}

.price-ranges li {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
    color: #34495e;
}

.price-ranges li:last-child {
    border-bottom: none;
}

.note {
    font-size: 0.9em;
    color: #666;
    font-style: italic;
    text-align: center;
}

.price-notes {
    background: #f8f9fa;
    padding: 25px;
    border-radius: 10px;
    margin-top: 40px;
}

.price-notes h3 {
    color: #2c3e50;
    margin-bottom: 15px;
}

.price-notes ul {
    list-style: none;
    padding-left: 0;
}

.price-notes ul li {
    margin-bottom: 10px;
    padding-left: 20px;
    position: relative;
    color: #34495e;
}

.price-notes ul li::before {
    content: "•";
    color: #3498db;
    position: absolute;
    left: 0;
}

@media (max-width: 768px) {
    .price-cards-container {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }

    .price-card {
        padding: 20px;
    }

    .category-icon {
        font-size: 2em;
    }

    .price-card h2 {
        font-size: 1.3em;
    }
}

@media (max-width: 480px) {
    .price-list-container {
        margin: 20px auto;
    }

    .price-card {
        padding: 15px;
    }

    .category-icon {
        font-size: 1.8em;
    }

    .price-ranges li {
        font-size: 0.9em;
    }
}
</style>

<?php include_once 'includes/footer.php'; ?>