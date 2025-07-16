<section class="bg-gray-100 py-12 px-4 sm:px-8 lg:px-16 rounded shadow max-w-3xl mx-auto my-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Newsletter abonnieren</h2>
    <p class="text-gray-600 mb-6">
        Erhalte wichtige Infos, Termine und Angebote rund um den Flohmarkt direkt per E-Mail.
    </p>

    <form method="post" action="../handlers/newsletter-handler.php" class="space-y-4">
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">E-Mail-Adresse</label>
            <input type="email" name="email" id="email" required
                   class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring focus:ring-primary focus:border-primary">
        </div>

        <div class="flex items-center">
            <input id="newsletter" name="newsletter" type="checkbox" value="1"
                   class="h-4 w-4 text-primary border-gray-300 rounded focus:ring-primary">
            <label for="newsletter" class="ml-2 block text-sm text-gray-700">
                Ja, ich möchte den Flohmarkt-Newsletter erhalten.
            </label>
        </div>

        <button type="submit"
                class="inline-block bg-primary text-white font-semibold px-6 py-2 rounded hover:bg-opacity-90 focus:outline-none focus:ring focus:ring-primary">
            Abonnieren
        </button>
    </form>
    <?php if (isset($_GET['success'])): ?>
        <div class="max-w-xl mx-auto mt-6 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded">
            ✅ Danke! Bitte bestätige deine E-Mail-Adresse über den Link in deinem Posteingang.
        </div>
    <?php endif; ?>

</section>
