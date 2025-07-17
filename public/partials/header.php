<?php if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: text/html; charset=utf-8');
?>

<!DOCTYPE html>
<html lang="de" class="scroll-smooth scroll-pt-10">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mädchenkram Kreis Lippe</title>

    <!-- Tailwind CSS -->
    <link href="/assets/css/styles.css" rel="stylesheet">
    <link rel="icon" href="/img/logo.jpg" type="image/x-icon">
</head>
<body class="bg-white text-gray-800">

<!-- Header -->

<header class="bg-white shadow-md fixed w-full z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-3">
            <!-- Logo -->
            <a href="/" class="">
                <img src="/assets/img/maedchenkram-flohmarkt-new-logo.png"
                     alt="maedchenkram lippe logo" width="100"
                     class="w-1/2 md:w-3/4 lg:w-1/2">
            </a>
            <!-- Desktop Navigation -->
            <nav class="hidden md:flex gap-3 lg:gap-5">
                <a href="/" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Home</a>
                <a href="/booking.php" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Buchen</a>
                <a href="#dates" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Termine</a>
                <a href="#faq" class="text-gray-600 hover:text-primary text-lg link-hover-underline">FAQ</a>
                <a href="#map" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Anfahrt</a>
                <a href="/contact.php" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Kontakt</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/dashboard.php"
                       class="text-blue-600  text-lg link-hover-underline">Dashboard</a>
                    <a href="/logout.php" class="text-red-600 text-lg link-hover-underline">Logout</a>
                <?php else: ?>
                    <a href="/login.php" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Login</a>
                <?php endif; ?>
            </nav>

            <!-- Mobile Burger -->
            <div class="md:hidden">
                <button id="menuBtn" class="text-gray-600 hover:cursor-pointer focus:outline-none scale-150">
                    ☰
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <nav id="mobileMenu" class="md:hidden hidden my-2 space-y-2">
            <a href="/" class="block text-gray-600 hover:text-primary text-lg">Home</a>
            <a href="#dates" class="block text-gray-600 hover:text-primary text-lg">Termine</a>
            <a href="/booking.php" class="text-gray-600 hover:text-primary text-lg link-hover-underline">Buchen</a>
            <a href="#faq" class="block text-gray-600 hover:text-primary text-lg">FAQ</a>
            <a href="#map" class="text-gray-600 hover:text-primary text-lg">Anfahrt</a>
            <a href="/contact.php" class="block text-gray-600 hover:text-primary text-lg">Kontakt</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/dashboard.php" class="text-blue-600 hover:underline text-lg">Dashboard</a>
                <a href="/logout.php" class="text-red-600 hover:underline text-lg">Logout</a>
            <?php else: ?>
                <a href="/login.php" class="text-gray-600 hover:text-primary text-lg">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>