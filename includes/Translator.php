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

        $cacheKey = md5($text . $targetLang);

        // Memory cache check
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        // Database cache check would go here for production
        // For now, let's implement a simple file-based cache or just on-the-fly

        try {
            if (self::$translator === null) {
                self::$translator = new GoogleTranslate();
            }

            self::$translator->setTarget($targetLang);
            $translated = self::$translator->translate($text);

            self::$cache[$cacheKey] = $translated;
            return $translated;
        } catch (Exception $e) {
            return $text; // Fallback to original
        }
    }
}
