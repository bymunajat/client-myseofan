<?php
class Logger
{
    public static function log($action, $details = '')
    {
        global $pdo;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $admin_id = $_SESSION['admin_id'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$admin_id, $action, $details, $ip]);
            } catch (Exception $e) {
                // Silently fail to avoid breaking app flow
            }
        }
    }
}
?>