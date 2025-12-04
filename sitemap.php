<?php
/**
 * Simple Sitemap Generator
 *
 * Place this script in the root directory of your website.
 * It will automatically scan for all 'index.html' files, check them for
 * "noindex" rules, and generate a valid sitemap.xml.
 *
 * How it works:
 * 1. It starts scanning from the directory it's in.
 * 2. It recursively goes through all subdirectories.
 * 3. It reads each 'index.html' file to check for a meta robots "noindex" tag.
 * 4. If a "noindex" tag is found, the file is skipped.
 * 5. For every valid 'index.html', it creates a clean URL
 *    (e.g., /var/www/html/about/index.html becomes https://yourdomain.com/about/).
 * 6. It gathers the last modification date of the file for the <lastmod> tag.
 * 7. It outputs the final XML with the correct headers.
 */

// --- CONFIGURATION ---

// Set your website's full base URL (including https://)
// NO trailing slash
$baseURL = "https://www.flashysurf.com";

// Optional: List any directories you want to exclude from the sitemap.
// This is useful for admin panels, vendor folders, etc.
$excludeDirs = ['vendor', 'node_modules', '.git'];


// --- SCRIPT LOGIC (No need to edit below this line) ---

/**
 * Checks if the given HTML content contains a "noindex" meta tag.
 * This respects rules like <meta name="robots" content="noindex">
 * or <meta name="googlebot" content="noindex, nofollow">.
 *
 * @param string $htmlContent The HTML content of the file.
 * @return bool True if a "noindex" directive is found, false otherwise.
 */
function hasNoIndex($htmlContent) {
    // Regex to find meta tags like name="robots" or name="googlebot"
    // It's case-insensitive (i flag)
    $pattern = '/<meta\s+name\s*=\s*["\'](robots|googlebot)["\']\s+content\s*=\s*["\'](.*?)["\']/i';
    
    if (preg_match($pattern, $htmlContent, $matches)) {
        // $matches[2] contains the value of the "content" attribute
        $contentAttribute = $matches[2];
        // Check if "noindex" exists within the content attribute
        if (strpos(strtolower($contentAttribute), 'noindex') !== false) {
            return true;
        }
    }
    return false;
}


// Set the correct header for XML output
header('Content-Type: application/xml; charset=utf-8');

// Start the XML output
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

// Get the full path to the root directory of the script
$rootPath = realpath(__DIR__);
$urls = [];

// Use a Recursive Directory Iterator to scan all files and folders
$directory = new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS);
$iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file) {
    // --- Exclusion Logic ---

    // 1. Skip the sitemap script itself
    if ($file->getRealPath() == __FILE__) {
        continue;
    }

    // 2. Check if the file's path contains any excluded directory names
    $shouldExclude = false;
    foreach ($excludeDirs as $dir) {
        // Ensure we match full directory names to avoid partial matches (e.g., 'admin' in 'radmin')
        if (strpos($file->getRealPath(), DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR) !== false) {
            $shouldExclude = true;
            break; // Found an excluded dir, no need to check further
        }
    }
    if ($shouldExclude) {
        continue;
    }

    // --- URL Generation Logic ---

    // We only care about files named 'index.html'
    if ($file->isFile() && $file->getFilename() === 'index.html') {
        
        // *** NEW: Check for "noindex" meta tag ***
        // Read the file's content to check for the directive.
        $htmlContent = file_get_contents($file->getRealPath());
        if (hasNoIndex($htmlContent)) {
            continue; // Skip this file if it's marked as "noindex"
        }

        // Get the path of the directory containing index.html
        $dirPath = $file->getPath();

        // Get the path relative to the website's root
        // On Windows, this replaces backslashes with forward slashes for the URL
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', str_replace($rootPath, '', $dirPath));

        // Build the final clean URL
        // Example: /about becomes https://www.yourdomain.com/about/
        // The root index.html results in an empty relative path, becoming https://www.yourdomain.com/
        $url = rtrim($baseURL, '/') . $relativePath . '/';

        // Get the last modification time of the file for the <lastmod> tag
        $lastMod = date('Y-m-d', $file->getMTime());

        // Store the URL and last modification date
        $urls[$url] = $lastMod;
    }
}

// Ensure the root URL is included if an index.html exists at the root and is not "noindex"
$rootIndexFile = $rootPath . DIRECTORY_SEPARATOR . 'index.html';
if (file_exists($rootIndexFile)) {
    $rootUrl = rtrim($baseURL, '/') . '/';
    // Check root index for "noindex" only if it hasn't been processed already
    if (!isset($urls[$rootUrl])) {
        $rootContent = file_get_contents($rootIndexFile);
        if (!hasNoIndex($rootContent)) {
            $urls[$rootUrl] = date('Y-m-d', filemtime($rootIndexFile));
        }
    }
}


// --- XML OUTPUT ---

// Loop through the collected URLs and print them in XML format
foreach ($urls as $url => $lastMod) {
    echo '  <url>' . "\n";
    echo '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
    echo '    <lastmod>' . $lastMod . '</lastmod>' . "\n";
    // You can optionally add these tags with static values if you want
    echo '    <changefreq>weekly</changefreq>' . "\n";
    echo '    <priority>0.8</priority>' . "\n";
    echo '  </url>' . "\n";
}

// Close the XML
echo '</urlset>' . "\n";