<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

class Translator
{
    private static $translator = null;
    private static $cache = [];

    public static function translate($text, $targetLang = 'en')
    {
        if ($targetLang === 'en' || empty($text)) {
            return $text;
        }

        // 1. Check Memory Cache
        $cacheKey = md5($text . $targetLang);
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // 2. Check Database Cache
        global $pdo;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT translated_text FROM translation_cache WHERE hash = ? LIMIT 1");
                $stmt->execute([$cacheKey]);
                $cached = $stmt->fetchColumn();
                if ($cached) {
                    self::$cache[$cacheKey] = $cached;
                    return $cached;
                }
            } catch (\Exception $e) {
                // DB error, continue to live translation
            }
        }

        // 3. Live Translate
        try {
            if (self::$translator === null) {
                self::$translator = new GoogleTranslate();
            }

            self::$translator->setTarget($targetLang);
            $translated = self::$translator->translate($text);

            // 4. Save to Database
            if ($pdo && !empty($translated)) {
                try {
                    $stmt = $pdo->prepare("INSERT OR IGNORE INTO translation_cache (hash, source_text, target_lang, translated_text) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$cacheKey, $text, $targetLang, $translated]);
                } catch (\Exception $e) {
                    // Ignore insert errors
                }
            }

            self::$cache[$cacheKey] = $translated;
            return $translated;
        } catch (Exception $e) {
            return $text; // Fallback to original
        }
    }
}
