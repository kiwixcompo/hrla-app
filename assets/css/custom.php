<?php
/**
 * Dynamic CSS Generator
 * Generates CSS from site settings
 */

require_once '../../config/app.php';
require_once '../../config/site-settings.php';

header('Content-Type: text/css');
header('Cache-Control: max-age=3600'); // Cache for 1 hour

echo generateCustomCSS();
?>
