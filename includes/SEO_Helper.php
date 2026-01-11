<?php
class SEO_Helper
{
    private $pdo;
    private $page;
    private $settings;
    private $seo;

    public function __construct($pdo, $page = 'home', $lang = 'en')
    {
        $this->pdo = $pdo;
        $this->page = $page;
        $this->settings = getSiteSettings($pdo);
        $this->seo = getSEOData($pdo, $page, $lang);
    }

    public function getTitle()
    {
        return $this->seo['meta_title'] ?? ($this->settings['site_name'] ?? 'Instagram Downloader');
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
        <meta property=\"og:title\" content=\"$title\">
        <meta property=\"og:description\" content=\"$desc\">
        <meta property=\"og:image\" content=\"$image\">
        <meta property=\"og:type\" content=\"website\">
        ";
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
