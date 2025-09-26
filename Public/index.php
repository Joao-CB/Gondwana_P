<?php
// Load Composer dependencies
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Joao\ApiP\Controllers\RateController;

// Create Slim app
$app = AppFactory::create();

// Landing page route
$app->get('/', function ($request, $response, $args) {
    // HTML for homepage
    $html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Gondwana Rates</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
         <!-- Navbar -->
        <nav class="navbar">
        <div class="nav-container">
         <!-- Logo -->
         <a href="/" class="logo">
         <img src="https://cdn.brandfetch.io/idIGTw8iYF/w/252/h/64/theme/light/logo.png?c=1bxid64Mup7aczewSAYMX&t=1755114121246" alt="Gondwana Logo" height="50">
       </a>

    <!-- Hamburger button (mobile menu toggle) -->
    <button class="menu-toggle" aria-label="Toggle menu">
      ☰
    </button>

    <!-- Nav links -->
    <ul class="nav-links">
      <li><a href="/">Home</a></li>
      <li><a href="/index.html">Rates</a></li>
    </ul>
    </div>
    </nav>

        <!-- Hero Section -->
        <section class="hero">
            <div class="hero-overlay"></div>
            <div class="hero-content">
                <h1>Discover Gondwana Rates</h1>
                <p>Check real-time availability and secure your stay instantly.</p>
                <a href="/index.html" class="btn">Get Started</a>
            </div>
        </section>

        <!-- Info Section -->
        <section class="info-section">
            <div class="info-container">
                <h2>Why Choose Gondwana?</h2>
                <p>We provide accurate, real-time rates with simple booking options and excellent customer service.</p>
                
                <div class="cards">
                    <!-- Card 1 -->
                    <div class="card">
                        <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=400&q=80" alt="Fast Booking">
                        <h3>Fast Booking</h3>
                        <p>Secure your stay quickly with our easy-to-use system.</p>
                    </div>
                    <!-- Card 2 -->
                    <div class="card">
                        <img src="https://images.unsplash.com/photo-1526772662000-3f88f10405ff?auto=format&fit=crop&w=400&q=80" alt="Real-Time Rates">
                        <h3>Real-Time Rates</h3>
                        <p>Get the most accurate rates directly from our API.</p>
                    </div>
                    <!-- Card 3 -->
                    <div class="card">
                        <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80" alt="Support">
                        <h3>24/7 Support</h3>
                        <p>Our team is here to assist you any time, any day.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer>
            <p>&copy;  Gondwana API. All rights reserved.</p>
        </footer>

        <!-- Mobile menu script -->
        <script>
         const menuToggle = document.querySelector(".menu-toggle");
         const navLinks = document.querySelector(".nav-links");

         // Toggle "show" class on click
         menuToggle.addEventListener("click", () => {
            navLinks.classList.toggle("show");
         });
        </script>

    </body>
    </html>
    HTML;

    // Send HTML response
    $response->getBody()->write($html);
    return $response;
});

// API route for fetching rates
$app->post('/api/rates', RateController::class . ':getRates');

// Run Slim app
$app->run();
