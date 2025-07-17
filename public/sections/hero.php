<?php // hero.php ?>
<section id="hero" class="relative w-full h-[80vh] overflow-hidden">
    <!-- Hintergrundvideo -->
    <video autoplay muted loop playsinline preload="auto" class="absolute inset-0 w-full h-full object-cover z-0">
        <source src="/assets/video/mk-hero-video.mp4" type="video/mp4">
    </video>
    <!-- Halbtransparenter Overlay für bessere Lesbarkeit -->
    <div class="absolute inset-0 bg-black/40 z-10"></div>

    <!-- Inhalt über dem Video -->
    <div class="relative z-20 flex flex-col items-center justify-center h-full text-center px-4">
        <h1 class="text-4xl md:text-5xl font-bold text-white drop-shadow-lg mb-4">
            Willkommen bei <span class="text-primary">Mädchenkram Lippe</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-100 drop-shadow-md max-w-2xl mb-8">
            Buche jetzt ganz einfach deinen Flohmarkt-Stand online –
            <span class="text-primary font-semibold">schnell</span>,
            <span class="text-primary font-semibold">bequem</span>,
            <span class="text-primary font-semibold">sicher</span>
        </p>
        <a href="booking.php"
           class="bg-primary text-white px-6 py-3 rounded-xl hover:opacity-90 transition shadow-lg">
            Jetzt buchen
        </a>
    </div>
</section>
