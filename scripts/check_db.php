<?php
/**
 * Database Structure Inspector
 * Displays all tables and their column information
 */

require_once '../includes/db.php';

try {
    // Get all tables
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);

    echo "=== DATABASE STRUCTURE ===\n\n";

    foreach ($tables as $table) {
        echo "📋 Table: {$table}\n";
        echo str_repeat("-", 50) . "\n";

        $cols = $pdo->query("PRAGMA table_info({$table})")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cols as $col) {
            $pk = $col['pk'] ? ' [PRIMARY KEY]' : '';
            $notnull = $col['notnull'] ? ' [NOT NULL]' : '';
            $default = $col['dflt_value'] ? " [DEFAULT: {$col['dflt_value']}]" : '';

            echo "  • {$col['name']} ({$col['type']}){$pk}{$notnull}{$default}\n";
        }

        echo "\n";
    }

    echo "✅ Database inspection completed successfully!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
