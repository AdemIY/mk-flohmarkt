<?php
require_once __DIR__ . '/../includes/db.php';
session_start();
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$db = connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = $_POST['csrf'] ?? '';
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
        die('csrf attention');
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['password_repeat'] ?? '';
    $firstName = trim($_POST['first_name'] ?? 'Unbekannt');
    $lastName = trim($_POST['last_name'] ?? '');
    $newsletter = isset($_POST['newsletter']) ? 1 : 0;


    $stmt = $db->prepare("SELECT id, password, role FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // CREATE Registrierung
        if (!$password || $password !== $passwordRepeat) {
            $error = 'Bitte gültiges Passwort eingeben (beide Felder müssen übereinstimmen).';
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (email, password, role, first_name, last_name, is_active, newsletter_opt_in) VALUES (:email, :password, 'user', :first_name, :last_name, 1, :newsletter_opt_in)");
            $stmt->execute(['email' => $email, 'password' => $hashed, 'first_name' => $firstName, 'last_name' => $lastName, 'newsletter_opt_in' => $newsletter]);
            $_SESSION['user_id'] = $db->lastInsertId();
            header('Location: /dashboard.php');
            exit;
        }
    } elseif (empty($user['password'])) {
        // E-Mail mit Token verschicken – NIEMALS hier direkt das Passwort setzen
        header('Location: /handlers/send-password-link.php?email=' . urlencode($email));
        exit;
    } else {
        // Login
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'E-Mail oder Passwort ist falsch.';
        }
    }
}

?>


<?php include 'partials/header.php'; ?>

    <section class="max-w-md mx-auto py-32 ">
        <h1 class="text-2xl font-bold mb-4">Login / Registrierung</h1>
        <form method="POST" id="login-form" class="space-y-6 bg-white p-6 shadow rounded w-full">
            <p id="password-link-info"></p>
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">E-Mail</label>
                <input type="email" id="email" name="email" required
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2"
                       placeholder="deine@email.de">
                <span id="login-error-info"
                      class="text-red-500 my-3 inline-block"><?= !empty($error) ? htmlspecialchars($error) : '' ?></span>
                <span id="email-tooltip" class="text-sm font-medium"></span>
            </div>

            <div id="name-container" class="hidden space-y-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">Vorname</label>
                    <input minlength="2" maxlength="35" id="first_name" type="text" name="first_name"
                           autocomplete="first_name"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2"
                           placeholder="Vorname">
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Nachname</label>
                    <input minlength="2" maxlength="35" id="last_name" type="text" name="last_name"
                           autocomplete="last_name"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2"
                           placeholder="Nachname">
                </div>
            </div>

            <div id="password-container" class="hidden space-y-4">
                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-gray-700">Passwort</label>
                    <input id="password" minlength="8" type="password" name="password" autocomplete="current-password"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2 pr-10"
                           placeholder="Passwort"
                           pattern=".*[!@#$%^&*].*" title="Mindestens 8 Zeichen und ein Sonderzeichen (!@#$%^&*)"
                           required>
                    <button type="button" class="absolute top-9 right-3 toggle-password text-sm text-gray-500"
                            data-target="password">👁️
                    </button>
                </div>

                <div id="repeat-container" class="hidden relative">
                    <label for="password-repeat" class="block text-sm font-medium text-gray-700">Passwort
                        bestätigen</label>
                    <input id="password-repeat" minlength="8" type="password" name="password_repeat"
                           autocomplete="current-password"
                           class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2 pr-10"
                           placeholder="Passwort wiederholen"
                           pattern=".*[!@#$%^&*].*" title="Mindestens 8 Zeichen und ein Sonderzeichen (!@#$%^&*)"
                           required>
                    <button type="button" class="absolute top-9 right-3 toggle-password text-sm text-gray-500"
                            data-target="password-repeat">👁️
                    </button>
                    <span id="check-password-hint" class="hidden"></span>
                </div>
            </div>
            <label id="newsletter" class="hidden" for="newsletter">Newsletter abonnieren um keine wichtigen Termine und
                Neuigkeiten zu
                verpassen.
                <input type="checkbox" name="newsletter" value="1"></label>
            <button id="login-submit-btn" type="submit"
                    class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded shadow transition"
                    title="Absenden" disabled>
                Absenden
            </button>
        </form>

    </section>
    <script src="/js/login.js" defer></script>
<?php include 'partials/footer.php'; ?>
