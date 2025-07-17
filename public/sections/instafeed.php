<?php
$dir = new DirectoryIterator(__DIR__ . '/../assets/img/instafeed/');
?>
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-4">
    <!-- Card -->
    <?php foreach ($dir as $fileinfo): ?>
        <!-- Kopiere weitere Cards -->
        <div class="relative group overflow-hidden rounded-xl shadow-md">
            <img src="assets/img/instafeed/<?= $fileinfo->getFilename() ?>" alt="Instafeed Bild"
                 class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105"/>
            <div class="absolute inset-0 bg-white/10 backdrop-blur-md opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                <p class="text-white text-lg font-semibold">Event-Szene 📍</p>
            </div>
        </div>
    <?php endforeach; ?>
    <!-- ... weitere Bilder ... -->
</div>

