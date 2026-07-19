<?php
// components/VercelAnalytics.php
// This component injects Vercel Web Analytics tracking script into the page.
// Usage: Call VercelAnalytics() in the <head> or before closing </body> tag to enable analytics tracking.
// The script uses the CDN version suitable for PHP/HTML applications.

function VercelAnalytics($debug = false)
{
    // Use debug version in development, production version otherwise
    $scriptSrc = $debug 
        ? 'https://cdn.vercel-insights.com/v1/script.debug.js'
        : 'https://cdn.vercel-insights.com/v1/script.js';
    
?>
    <!-- Vercel Web Analytics -->
    <script>
        window.va = window.va || function () { 
            (window.vaq = window.vaq || []).push(arguments); 
        };
    </script>
    <script defer src="<?php echo $scriptSrc; ?>"></script>
<?php
}
?>
