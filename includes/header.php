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

            <!-- Hamburger Button -->
            <button class="hamburger" id="hamburger" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>

            <nav class="main-nav" id="main-nav">
                <!-- Close button inside nav for mobile -->
                <button class="nav-close" id="nav-close" aria-label="Close menu">
                    <i class="fas fa-times"></i>
                </button>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="aboutUs.php">About</a></li>
                    <li><a href="events.php">Events</a></li>
                    <li><a href="#contact" class="contact-link">Contact</a></li>
                    <li><a href="support.php">ChatUs</a></li>

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
            <!-- Mobile nav backdrop -->
            <div class="nav-backdrop" id="nav-backdrop"></div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // --- Hamburger Menu ---
            const hamburger   = document.getElementById('hamburger');
            const navClose    = document.getElementById('nav-close');
            const mainNav     = document.getElementById('main-nav');
            const backdrop    = document.getElementById('nav-backdrop');

            function openNav()  { document.body.classList.add('nav-open'); }
            function closeNav() { document.body.classList.remove('nav-open'); }

            if (hamburger) hamburger.addEventListener('click', openNav);
            if (navClose)  navClose.addEventListener('click',  closeNav);
            if (backdrop)  backdrop.addEventListener('click',  closeNav);

            // Close nav when any link inside it is clicked (mobile UX)
            if (mainNav) {
                mainNav.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', closeNav);
                });
            }

            // --- Contact Link Handler ---
            const contactLink = document.querySelector('.contact-link');
            if (contactLink) {
                contactLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    closeNav();
                    if (!window.location.pathname.endsWith('index.php') && window.location.pathname !== '/') {
                        window.location.href = 'index.php#contact';
                        return;
                    }
                    const contactSection = document.getElementById('contact');
                    if (contactSection) contactSection.scrollIntoView({ behavior: 'smooth' });
                });
            }

            // --- Theme Toggle Handler ---
            const themeToggle = document.getElementById('theme-toggle');
            if (themeToggle) {
                const moonIcon = themeToggle.querySelector('.fa-moon');
                const sunIcon  = themeToggle.querySelector('.fa-sun');

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
                    sunIcon.style.display  = theme === 'dark' ? 'inline-block' : 'none';
                }
            }
        });
    </script>