<?php
require_once __DIR__ . '/../../includes/auth.php';
requireLogin();

require_once __DIR__ . '/../../includes/db.php';

// ID des zu bearbeitenden Users aus GET-Parameter
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: /admin/users.php');
    exit;
}

try {
    $db = connect();
} catch (PDOException $e) {
    die("Datenbank-Fehler: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

// Verarbeiten des Formulars
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Eingaben holen und bereinigen
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    $errors = [];
    // Validierung
    if ($firstName === '') {
        $errors[] = 'Vorname ist erforderlich.';
    }
    if ($lastName === '') {
        $errors[] = 'Nachname ist erforderlich.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Bitte geben Sie eine gültige E-Mail-Adresse ein.';
    }

    if (empty($errors)) {
        // Dynamisches SQL für Passwort: nur setzen, wenn angegeben
        $fields = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
        ];
        $sqlParts = [];
        foreach ($fields as $col => $val) {
            $sqlParts[] = "`$col` = :$col";
        }

        // Wenn Passwort gesetzt, hashe und füge hinzu
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sqlParts[] = '`password` = :password';
            $fields['password'] = $hash;
        }

        $sql = 'UPDATE users SET ' . implode(', ', $sqlParts) . ' WHERE id = :id';
        $stmt = $db->prepare($sql);
        // Parameter binden
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        foreach ($fields as $col => $val) {
            $stmt->bindValue(':' . $col, $val, PDO::PARAM_STR);
        }

        if ($stmt->execute()) {
            // Erfolgreich aktualisiert
            header('Location: /dashboard.php?updated=1');
            exit;
        } else {
            $errors[] = 'Datenbank-Fehler beim Speichern der Änderungen.';
        }
    }
}

// Aktuelle Daten des Users laden
$stmt = $db->prepare('SELECT first_name, last_name, email FROM users WHERE id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    header('Location: /admin/users.php');
    exit;
}
?>

<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="py-32 px-4 max-w-lg mx-auto">
    <h1 class="text-2xl font-bold mb-6">Benutzer #<?= htmlspecialchars($id) ?> bearbeiten</h1>

    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" class="bg-white p-6 rounded-lg shadow space-y-4">
        <div>
            <label class="block text-sm font-medium">Vorname</label>
            <input placeholder="Vorname"
                   name="first_name"
                   type="text"
                   value="<?= htmlspecialchars($user['first_name'], ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-primary"
                   required
            >
        </div>
        <div>
            <label class="block text-sm font-medium">Nachname</label>
            <input placeholder="Nachname"
                   name="last_name"
                   type="text"
                   value="<?= htmlspecialchars($user['last_name'], ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-primary"
                   required
            >
        </div>
        <div>
            <label class="block text-sm font-medium">E-Mail</label>
            <input placeholder="E-Mail"
                   name="email"
                   type="email"
                   value="<?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>"
                   class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-primary"
                   required
            >
        </div>
        <div>
            <label class="block text-sm font-medium">Passwort (leer lassen, um unverändert zu lassen)</label>
            <input placeholder="Passwort"
                   name="password"
                   type="password"
                   class="w-full border rounded p-2 focus:outline-none focus:ring-2 focus:ring-primary"
            >
        </div>
        <div class="text-right space-x-2">
            <a
                    href="/dashboard.php"
                    class="inline-block px-4 py-2 border rounded hover:bg-gray-50"
            >Abbrechen</a>
            <button
                    aria-label="speichern"
                    type="submit"
                    class="bg-primary text-white px-4 py-2 rounded hover:opacity-90"
            >Speichern
            </button>
        </div>
    </form>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>
