<?php
$dir = new DirectoryIterator(__DIR__ . '/../assets/img/instafeed/');
?>
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2 p-2">
    <?php foreach ($dir as $fileinfo): ?>
        <?php
        if ($fileinfo->isDot() || !$fileinfo->isFile()) {
            continue;
        }
        ?>
        <div class="relative group overflow-hidden rounded-lg shadow-sm">
            <img src="assets/img/instafeed/<?= htmlspecialchars($fileinfo->getFilename()) ?>" alt="Instafeed Bild"
                 class="w-full h-auto object-cover transition-transform duration-300 group-hover:scale-105"/>
            <div class="absolute inset-0 bg-white/10 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition duration-300 flex items-center justify-center">
                <p class="text-white text-sm font-medium">Event-Szene 📍</p>
            </div>
        </div>
    <?php endforeach; ?>
</div>
