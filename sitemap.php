<?php
/**
 * One-URL sitemap for this dashboard install (any hostname).
 * Copyright (C) 2025-2026  Shane Daley, M0VUB Aka. ShaYmez
 */
require_once __DIR__ . '/include/seo.php';
header('Content-Type: application/xml; charset=UTF-8');
$url = htmlspecialchars(dashboardCanonicalUrl(), ENT_QUOTES, 'UTF-8');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url>
    <loc><?php echo $url; ?></loc>
    <changefreq>hourly</changefreq>
    <priority>1.0</priority>
  </url>
</urlset>
