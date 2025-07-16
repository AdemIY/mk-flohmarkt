<?php
session_start();

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
?>
<?php include 'partials/header.php'; ?>

<section class="py-32 bg-white">
    <div class="max-w-xl mx-auto px-4">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Kontaktiere uns</h1>
        <p class="text-center text-gray-600 mb-8">Du hast Fragen? Schreib uns gern eine Nachricht.</p>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div id="success-message"
                 class="bg-green-100 text-green-800 border border-green-300 rounded p-4 mb-6 col-span-12 relative">
                ✅ Persönliche Daten erfolgreich aktualisiert!
                <button
                        onclick="document.getElementById('success-message').remove()"
                        class="absolute top-1 right-1 text-gray-600 bg-gray-300/40 hover:bg-gray-400/60 rounded-full px-2 py-1 text-sm leading-none font-bold"
                        aria-label="Schließen"
                        title="Schließen"
                >×
                </button>
            </div>
        <?php endif; ?>

        <form action="handlers/submit-contact.php" method="POST" id="contact-form" class="space-y-5">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <div>
                <label for="firstname" class="block text-sm font-medium text-gray-700">Vorname</label>
                <input type="text" name="firstname" id="firstname" required minlength="2"
                       placeholder="Max"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label for="lastname" class="block text-sm font-medium text-gray-700">Nachname</label>
                <input type="text" name="lastname" id="lastname" required minlength="2"
                       placeholder="Mustermann"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-Mail-Adresse</label>
                <input type="email" name="email" id="email" required
                       placeholder="max@example.com"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Telefonnummer (optional)</label>
                <input type="tel" name="phone" id="phone" minlength="7" maxlength="30"
                       placeholder="+49 123 4567890"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700">Nachricht</label>
                <textarea name="message" id="message" required minlength="10" maxlength="300"
                          placeholder="Worum geht es?"
                          class="mt-1 w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
            </div>

            <button type="submit"
                    class="w-full bg-primary text-white font-semibold py-2 px-6 rounded-lg hover:bg-opacity-90 transition">
                Nachricht senden
            </button>

            <div id="form-error" class="text-red-500 mt-4 hidden"></div>
        </form>
    </div>
</section>


<script>
    document.getElementById('contact-form').addEventListener('submit', function (e) {
        const firstname = document.getElementById('firstname').value.trim();
        const lastname = document.getElementById('lastname').value.trim();
        const email = document.getElementById('email').value.trim();
        const message = document.getElementById('message').value.trim();
        const errorDiv = document.getElementById('form-error');

        if (!firstname || !lastname || !email || !message) {
            e.preventDefault();
            errorDiv.textContent = 'Bitte fülle alle Felder aus.';
            errorDiv.classList.remove('hidden');
        } else {
            errorDiv.classList.add('hidden');
        }
    });
</script>

<?php include 'partials/footer.php'; ?>
