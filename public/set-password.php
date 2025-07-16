<?php
require_once __DIR__ . '/../includes/db.php';
session_start();
if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$db = connect();
$token = $_GET['token'] ?? '';
$error = null;
$success = false;
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $csrf = $_POST['csrf'] ?? '';
        if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $csrf)) {
            die('CSRF-ATTENTION');
        }
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordRepeat = $_POST['password_repeat'] ?? '';

        if (!$password) {
            $error = 'Bitte gib ein Passwort ein.';
        } else {
            $stmt = $db->prepare("SELECT u.id, u.email
                                        FROM password_resets pr
                                        JOIN users u ON pr.user_id = u.id
                                        WHERE pr.token = :token
                                        AND pr.expires_at >= NOW()
                                                                    ");
            $stmt->execute(['token' => $token]);
            $reset = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset) {
                $error = 'Ungültiger oder abgelaufener Link.';
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE users SET password = :password WHERE id = :id");
                $stmt->execute(['password' => $hashed, 'id' => $reset['id']]);

                // Optional: direkt einloggen
                $_SESSION['user_id'] = $reset['id'];

                $success = true;
            }
        }
    }
} catch (PDOException $e) {
    $error = $e->getMessage();
    exit();
}

?>
<?php include 'partials/header.php'; ?>
    <main class="container p-4 mx-auto py-32">
        <h1 class="text-3xl font-bold text-center mb-10">Passwort setzen</h1>
        <section class="flex items-center justify-center h-auto">
            <?php if ($success): ?>
                <div>
                    <p class="text-green-600">✅ Passwort erfolgreich gesetzt. Du bist jetzt eingeloggt.
                        <a href="/dashboard.php">
                            <span class="p-2 bg-primary rounded text-white font-bold block text-center mt-4 w-fit border-2 border-transparent hover:bg-white hover:border-2 hover:border-primary hover:text-primary duration-200">Weiter zum Dashboard</span></a>
                    </p>
                </div>
            <?php elseif ($error): ?>
                <p style="color: red;"><?= htmlspecialchars($error) ?></p>
            <?php elseif ($token): ?>
                <form id="new-password" method="POST" class="w-full max-w-md space-y-6 bg-white p-6 shadow rounded">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Neues Passwort</label>
                        <input id="password" type="password" name="password" required placeholder="Neues Passwort"
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2">
                    </div>

                    <div>
                        <label for="password_repeat" class="block text-sm font-medium text-gray-700">Passwort
                            bestätigen</label>
                        <input id="password_repeat" type="password" name="password_repeat" required
                               placeholder="Neues Passwort wiederholen"
                               class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-primary focus:ring focus:ring-primary/30 px-3 py-2">
                    </div>

                    <button type="submit"
                            class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded shadow transition">
                        Passwort speichern
                    </button>
                </form>

            <?php else: ?>
                <p style="color: red;">Kein gültiger Token übergeben.</p>
            <?php endif; ?>
        </section>
    </main>
<?php include 'partials/footer.php'; ?>