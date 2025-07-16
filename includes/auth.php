<?php
/**
 * Startet die Session, falls noch nicht geschehen.
 */
function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Checkt, ob gerade ein Nutzer eingeloggt ist.
 *
 * @return bool
 */
function isLoggedIn(): bool
{
    ensureSessionStarted();
    return isset($_SESSION['user_id']);
}

/**
 * Leitet auf die Login-Seite um, wenn nicht eingeloggt.
 */
function requireLogin(): void
{
    ensureSessionStarted();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Legt die Session-Variablen beim erfolgreichen Login an.
 *
 * @param int $userId Die User-ID aus der Datenbank.
 * @param string $userRole Die Rolle (z.B. 'admin' oder 'user').
 */
function loginUser(int $userId, string $userRole): void
{
    ensureSessionStarted();
    // Regenerate Session ID gegen Session-Fixation
    session_regenerate_id(true);

    $_SESSION['user_id'] = $userId;
    $_SESSION['user_role'] = $userRole;
}

/**
 * Loggt den aktuellen Nutzer aus.
 */
function logoutUser(): void
{
    ensureSessionStarted();
    // Alle Session-Daten löschen
    $_SESSION = [];
    // Cookie löschen
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

/**
 * Gibt true zurück, wenn der eingeloggte Nutzer ein Admin ist.
 *
 * @return bool
 */
function isAdmin(): bool
{
    ensureSessionStarted();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Erzwingt, dass der Nutzer ein Admin ist – leitet andernfalls zum Dashboard um.
 */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard.php');
        exit;
    }
}
