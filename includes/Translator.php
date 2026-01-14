<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/db.php';

use Stichoza\GoogleTranslate\GoogleTranslate;

class Translator
{
    private static $translator = null;
    private static $cache = [];

    // Optimized Preloader: Loads all translations for a language in ONE query
    public static function preload($targetLang)
    {
        if ($targetLang === 'en')
            return;

        global $pdo;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT hash, translated_text FROM translation_cache WHERE target_lang = ?");
                $stmt->execute([$targetLang]);
                $results = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR); // Fetch as [hash => text]

                if ($results) {
                    // Merge into memory cache
                    self::$cache = array_merge(self::$cache, $results);
                }
            } catch (\Exception $e) {
                // Silent fail
            }
        }
    }

    public static function translate($text, $targetLang = 'en')
    {
        if ($targetLang === 'en' || empty($text)) {
            return $text;
        }

        // 1. Check Memory Cache (Hit mostly here after preload)
        $cacheKey = md5($text . $targetLang);
        // Note: The preloader likely keyed by 'hash' which IS the md5. 
        // We used FETCH_KEY_PAIR so $results is [hash => text].
        // So checking self::$cache[$cacheKey] works perfectly.

        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // 2. Check Database Cache (Fallback for un-preloaded items)
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
