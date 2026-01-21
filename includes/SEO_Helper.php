<?php
class SEO_Helper
{
    private $pdo;
    private $page;
    private $settings;
    private $seo;
    private $lang;

    public function __construct($pdo, $page = 'home', $lang = 'en')
    {
        $this->pdo = $pdo;
        $this->page = $page;
        $this->settings = getSiteSettings($pdo);
        $this->seo = getSEOData($pdo, $page, $lang);
        $this->lang = $lang;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getTitle()
    {
        $siteName = $this->settings['site_name'] ?: 'MySeoFan';
        $metaTitle = $this->seo['meta_title'] ?? '';

        if (empty($metaTitle)) {
            return "$siteName - Instagram Media Downloader";
        }

        // If meta_title contains old name, replace it
        return str_ireplace('MySeoFan', $siteName, $metaTitle);
    }

    public function getDescription()
    {
        return $this->seo['meta_description'] ?? 'Download Instagram media instantly and for free.';
    }

    public function getOGTags()
    {
        $title = $this->getTitle();
        $desc = $this->getDescription();
        $image = $this->seo['og_image'] ?? '';

        return "
        <meta name=\"robots\" content=\"noindex, nofollow\">
        <meta property=\"og:title\" content=\"$title\">
        <meta property=\"og:description\" content=\"$desc\">
        <meta property=\"og:image\" content=\"$image\">
        <meta property=\"og:type\" content=\"website\">
        ";
    }

    public function getHreflangTags()
    {
        if (!$this->pdo)
            return "";

        $tags = "";
        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $langs = ['en', 'id', 'es', 'fr', 'de', 'ja', 'it', 'pt', 'tr', 'ru', 'ar', 'zh', 'ko']; // Expanded list

        // Current Path Logic
        if ($this->page === 'home') {
            foreach ($langs as $l) {
                $path = ($l === 'en') ? '/' : "/$l/";
                $tags .= "<link rel=\"alternate\" hreflang=\"$l\" href=\"$host$path\">\n";
            }
            // Canonical
            $current_path = ($this->lang === 'en') ? '/' : "/{$this->lang}/";
            $tags .= "<link rel=\"canonical\" href=\"$host$current_path\">\n";
        } elseif (in_array($this->page, ['video', 'photo', 'reels', 'igtv', 'carousel'])) {
            $tool_slug = $this->page . "-downloader";
            foreach ($langs as $l) {
                $path = ($l === 'en') ? "/$tool_slug/" : "/$l/$tool_slug/";
                $tags .= "<link rel=\"alternate\" hreflang=\"$l\" href=\"$host$path\">\n";
            }
            // Canonical
            $current_path = ($this->lang === 'en') ? "/$tool_slug/" : "/{$this->lang}/$tool_slug/";
            $tags .= "<link rel=\"canonical\" href=\"$host$current_path\">\n";
        } elseif ($this->page === 'blog_detail' || $this->page === 'static_page') {
            $table = ($this->page === 'blog_detail') ? 'blog_posts' : 'pages';
            $slug = $_GET['slug'] ?? '';
            $prefix = ($this->page === 'blog_detail') ? 'post' : 'page';

            $stmt = $this->pdo->prepare("SELECT translation_group FROM $table WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $group = $stmt->fetchColumn();

            if ($group) {
                $stmt = $this->pdo->prepare("SELECT lang_code, slug FROM $table WHERE translation_group = ?");
                $stmt->execute([$group]);
                $variants = $stmt->fetchAll();
                foreach ($variants as $v) {
                    $l = $v['lang_code'];
                    $s = $v['slug'];
                    $path = ($l === 'en') ? "/$prefix/$s/" : "/$l/$prefix/$s/";
                    $tags .= "<link rel=\"alternate\" hreflang=\"$l\" href=\"$host$path\">\n";

                    if ($l === $this->lang) {
                        $tags .= "<link rel=\"canonical\" href=\"$host$path\">\n";
                    }
                }
            }
        }

        return $tags;
    }

    public function getSchemaMarkup()
    {
        if (!empty($this->seo['schema_markup'])) {
            return $this->seo['schema_markup'];
        }

        // Auto-generator for basic schema
        $siteName = $this->settings['site_name'] ?? 'Instagram Downloader';
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        $desc = $this->getDescription();

        $schema = [
            "@context" => "https://schema.org",
            "@type" => "SoftwareApplication",
            "name" => $siteName,
            "operatingSystem" => "Windows, macOS, Android, iOS",
            "applicationCategory" => "MultimediaApplication",
            "offers" => [
                "@type" => "Offer",
                "price" => "0",
                "priceCurrency" => "USD"
            ],
            "description" => $desc,
            "url" => $url
        ];

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
    }
}
