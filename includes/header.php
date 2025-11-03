<?php
include_once 'includes/functions.php';
session_check();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planify - The Event Hub</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><a href="index.php">Planify the Event Hub</a></h1>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="aboutUs.php">About</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="#contact" class="contact-link">Contact</a></li>
                    <li><a href="chat.php">ChatUs</a></li>
                    
                    <?php if (is_logged_in()): ?>
                        <?php if (is_admin()): ?>
                            <li><a href="admin/dashboard.php">Admin Dashboard</a></li>
                        <?php else: ?>
                            <li><a href="user/dashboard.php">My Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                    <?php endif; ?>

                    <li>
                        <button id="theme-toggle" class="theme-toggle">
                            <i class="fas fa-moon"></i>
                            <i class="fas fa-sun"></i>
                        </button>
                    </li>
                </ul>

                <?php if (is_logged_in()): ?>
                    <div class="welcome-user">
                        <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <script>
        // Contact Link Handler
        document.addEventListener('DOMContentLoaded', function() {
            const contactLink = document.querySelector('.contact-link');
            
            contactLink.addEventListener('click', function(e) {
                e.preventDefault();
                
                if (!window.location.pathname.endsWith('index.php')) {
                    window.location.href = 'index.php#contact';
                    return;
                }
                
                const contactSection = document.getElementById('contact');
                if (contactSection) {
                    contactSection.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });

        // Theme Toggle Handler
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('theme-toggle');
            const moonIcon = themeToggle.querySelector('.fa-moon');
            const sunIcon = themeToggle.querySelector('.fa-sun');

            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeToggle(savedTheme);

            themeToggle.addEventListener('click', function() {
                const currentTheme = document.documentElement.getAttribute('data-theme');
                const newTheme = currentTheme === 'light' ? 'dark' : 'light';

                document.documentElement.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                updateThemeToggle(newTheme);
            });

            function updateThemeToggle(theme) {
                moonIcon.style.display = theme === 'dark' ? 'none' : 'inline-block';
                sunIcon.style.display = theme === 'dark' ? 'inline-block' : 'none';
            }
        });
    </script>