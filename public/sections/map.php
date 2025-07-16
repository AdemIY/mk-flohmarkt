<?php // map.php ?>
<section id="map" class="bg-gray-100 py-16">
    <div class="max-w-5xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-8">
            Hier findest du <span class="text-primary">Uns</span>
        </h2>
        <div class="relative w-full h-[400px] rounded-xl overflow-hidden shadow-lg">
            <div id="mapOverlay" class="absolute inset-0 bg-gray-300 flex items-center justify-center z-10">
                <button onclick="loadMap()" class="bg-primary text-white px-6 py-3 rounded-lg hover:opacity-90 z-20">
                    Karte anzeigen
                </button>
            </div>
            <div id="mapContainer" class="w-full h-full"></div>
        </div>
    </div>
</section>
<script>
    function loadMap() {
        const container = document.getElementById('mapContainer');
        const overlay = document.getElementById('mapOverlay');
        container.innerHTML = `
      <iframe
        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d6053.832985515859!2d8.867761176984086!3d51.93449657941266!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47ba44845173ffff%3A0x1b3d7272c5f17a71!2sKunstmarkt%20Detmold%20E.v.!5e1!3m2!1sde!2sde!4v1749980683920!5m2!1sde!2sde"
        class="w-full h-full"
        loading="lazy"
        style="border:0;"
        allowfullscreen
        referrerpolicy="no-referrer-when-downgrade"
      ></iframe>`;
        overlay.remove();
    }
</script>