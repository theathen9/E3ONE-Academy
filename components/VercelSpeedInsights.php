<?php
// components/VercelSpeedInsights.php
// This component injects Vercel Speed Insights tracking script into the page.
// Usage: Call VercelSpeedInsights() in the <head> or before closing </body> tag to enable speed insights tracking.
// The script uses the CDN version suitable for PHP/HTML applications.

function VercelSpeedInsights()
{
    // Speed Insights script initialization
    // This follows the official Vercel documentation for static HTML integration
?>
    <!-- Vercel Speed Insights -->
    <script>
        window.si = window.si || function () { 
            (window.siq = window.siq || []).push(arguments); 
        };
    </script>
    <script defer src="/_vercel/speed-insights/script.js"></script>
<?php
}
?>
