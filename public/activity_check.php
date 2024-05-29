<?php
session_start();

$timeout_duration = 60; // 1 Minute = 60 Sekunden

if (isset($_SESSION['user_id'])) {
    // Prüfen, ob das Timeout-Variable gesetzt ist
    if (isset($_SESSION['last_activity'])) {
        $elapsed_time = time() - $_SESSION['last_activity'];

        // Prüfen, ob die vergangene Zeit größer als die Timeout-Dauer ist
        if ($elapsed_time >= $timeout_duration) {
            // Session-Variablen leeren
            $_SESSION = array();
            // Session zerstören
            session_destroy();

            // Cache-Vermeidung Header setzen
            header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            // Umleitung zur index.html
            header("Location: /index.html");
            exit();
        }
    }
    
    // Zeitstempel der letzten Aktivität aktualisieren
    $_SESSION['last_activity'] = time();
} else {
    // Wenn der Benutzer nicht eingeloggt ist, zur Login-Seite umleiten
    header("Location: /login.html");
    exit();
}
?>
