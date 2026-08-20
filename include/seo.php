<?php
/**
 * P25Reflector-Dashboard2 - Public SEO / social / structured-data helpers
 * Copyright (C) 2025-2026  Shane Daley, M0VUB Aka. ShaYmez
 */

function dashboardCanonicalUrl() {
    $forwarded = isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) : '';
    $https = ($forwarded === 'https')
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    $scheme = $https ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $host = preg_replace('/[^a-zA-Z0-9.\-:\[\]]/', '', $host);
    return $scheme . '://' . $host . '/';
}

function dashboardAbsoluteAssetUrl($path) {
    if ($path === false || $path === '' || $path === null) {
        return '';
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    return rtrim(dashboardCanonicalUrl(), '/') . '/' . ltrim($path, '/');
}

function renderSeoHead($title, $description, $siteName) {
    $url = htmlspecialchars(dashboardCanonicalUrl(), ENT_QUOTES, 'UTF-8');
    $titleE = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $descE = htmlspecialchars($description, ENT_QUOTES, 'UTF-8');
    $siteE = htmlspecialchars($siteName, ENT_QUOTES, 'UTF-8');
    $image = '';
    if (function_exists('getLogoPath')) {
        $image = dashboardAbsoluteAssetUrl(getLogoPath());
    }
    if ($image === '') {
        $image = dashboardAbsoluteAssetUrl('favicon.svg');
    }
    $imageE = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
    $jsonLd = json_encode(array(
        '@context' => 'https://schema.org',
        '@type' => 'WebApplication',
        'name' => $siteName,
        'description' => $description,
        'url' => dashboardCanonicalUrl(),
        'applicationCategory' => 'MultimediaApplication',
        'operatingSystem' => 'Web',
        'isAccessibleForFree' => true,
        'author' => array(
            '@type' => 'Person',
            'name' => 'Shane Daley, M0VUB',
            'url' => 'https://github.com/ShaYmez/P25Reflector-Dashboard2'
        )
    ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    ?>
    <meta name="description" content="<?php echo $descE; ?>">
    <meta name="author" content="M0VUB Aka ShaYmez">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="<?php echo $url; ?>">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="apple-touch-icon" href="favicon.svg">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?php echo $siteE; ?>">
    <meta property="og:title" content="<?php echo $titleE; ?>">
    <meta property="og:description" content="<?php echo $descE; ?>">
    <meta property="og:url" content="<?php echo $url; ?>">
    <meta property="og:image" content="<?php echo $imageE; ?>">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo $titleE; ?>">
    <meta name="twitter:description" content="<?php echo $descE; ?>">
    <meta name="twitter:image" content="<?php echo $imageE; ?>">
    <script type="application/ld+json"><?php echo $jsonLd; ?></script>
    <?php
}
