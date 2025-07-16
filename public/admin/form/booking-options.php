<?php
// Datei: admin/form-settings.php
require_once __DIR__ . '/../../../includes/db.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activeOptions = $_POST['options'] ?? [];
    $newLabel = trim($_POST['new_label'] ?? '');
    $newPrice = $_POST['new_price'] ?? 0;
    $is_active = isset($_POST['new_active']) ? 1 : 0;

    try {
        $db = connect();

        if (!empty($_POST['delete_id']) && is_numeric($_POST['delete_id'])) {
            $stmt = $db->prepare("DELETE FROM booking_options WHERE id = :id");
            $stmt->execute(['id' => $_POST['delete_id']]);
            $success = "Option erfolgreich gelöscht.";
        } else {
            $db->exec("UPDATE booking_options SET is_active = 0");

            $stmt = $db->prepare("UPDATE booking_options SET is_active = 1 WHERE id = :id");
            foreach ($activeOptions as $id) {
                $stmt->execute(['id' => $id]);
            }

            foreach ($_POST['prices'] as $id => $price) {
                $label = trim($_POST['labels'][$id] ?? '');
                if (!empty($label) && is_numeric($price)) {
                    $stmt = $db->prepare("UPDATE booking_options SET label = :label, price = :price WHERE id = :id");
                    $stmt->execute([
                        'label' => $label,
                        'price' => $price,
                        'id' => $id
                    ]);
                }
            }

            if (!empty($newLabel) && is_numeric($price)) {
                $stmt = $db->prepare("INSERT INTO booking_options (label, price, is_active) VALUES (:label, :price, :is_active)");
                $stmt->execute([
                    'label' => $newLabel,
                    'price' => $newPrice,
                    'is_active' => $is_active
                ]);
            }

            $success = "Änderungen erfolgreich gespeichert.";
        }
    } catch (PDOException $e) {
        $error = "Datenbankfehler: " . $e->getMessage();
    }
}

// Alle Optionen auslesen (inkl. Status)
try {
    $db = connect();
    $stmt = $db->query("SELECT id, label, price, is_active FROM booking_options ORDER BY label");
    $formOptions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Fehler beim Laden der Optionen: " . $e->getMessage());
}
?>
<?php include __DIR__ . '/../../partials/header.php'; ?>

<main class="py-32 max-w-4xl mx-auto px-4">
    <?php include '../sections/admin-navigation.php'; ?>

    <div class="bg-white shadow-md rounded-2xl p-6 mt-10">
        <h2 class="text-2xl font-bold mb-6">Buchungsformular-Felder verwalten</h2>

        <?php if (!empty($success)): ?>
            <div class="mb-4 p-4 bg-green-100 text-green-800 rounded"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="mb-4 p-4 bg-red-100 text-red-800 rounded"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" novalidate>
            <div class="space-y-4">
                <?php foreach ($formOptions as $opt): ?>
                    <div class="flex items-center justify-between p-4 border rounded-lg">
                        <div>
                            <input type="text" value="<?= htmlspecialchars($opt['label']) ?>"
                                   name="labels[<?= $opt['id'] ?>]"
                                   class="font-medium text-gray-800 border p-1 rounded">
                            <input type="number" value="<?= $opt['price'] ?>" step="0.01"
                                   name="prices[<?= $opt['id'] ?>]"
                                   class="text-sm text-gray-800 border p-1 rounded w-24"> €
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="inline-flex relative items-center cursor-pointer">
                                <input
                                        type="checkbox"
                                        name="options[]"
                                        value="<?= $opt['id'] ?>"
                                        class="sr-only peer"
                                    <?= $opt['is_active'] ? 'checked' : '' ?>
                                >
                                <span
                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary rounded-full
                           peer-checked:bg-primary transition-colors"
                                ></span>
                                <span class="ml-3 text-sm font-medium text-gray-900">
                                    <?= $opt['is_active'] ? 'Aktiv' : 'Inaktiv' ?>
                                </span>
                            </label>

                            <!-- Lösch-Button -->
                            <button type="submit" name="delete_id" value="<?= $opt['id'] ?>"
                                    onclick="return confirm('Willst du diese Option wirklich löschen?')"
                                    class="text-red-600 text-sm hover:underline ml-4">
                                Löschen
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Neue Option -->
                <div class="mt-8 border-t pt-4">
                    <h3 class="mt-4 font-semibold text-lg">Neue Option hinzufügen</h3>
                    <input type="text" name="new_label" placeholder="Bezeichnung"
                           class="border p-1 rounded w-full mt-2 mb-2">
                    <input type="number" step="0.01" name="new_price" placeholder="Preis"
                           class="border p-1 rounded w-24 mb-2">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="new_active" value="1" class="mr-2">
                        Aktiv
                    </label>
                </div>
            </div>

            <div class="mt-6 text-right">
                <button
                        type="submit"
                        class="bg-primary text-white px-6 py-3 rounded-lg font-semibold hover:opacity-90 transition-opacity"
                >
                    Speichern
                </button>
            </div>
        </form>
    </div>
</main>

<?php include __DIR__ . '/../../partials/footer.php'; ?>
