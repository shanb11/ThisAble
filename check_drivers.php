<?php
echo "<h2>PHP PDO Drivers Available:</h2>";
echo "<pre>";
print_r(PDO::getAvailableDrivers());
echo "</pre>";

echo "<h2>PostgreSQL Extension:</h2>";
if (extension_loaded('pgsql')) {
    echo "✅ pgsql extension is loaded<br>";
} else {
    echo "❌ pgsql extension is NOT loaded<br>";
}

if (extension_loaded('pdo_pgsql')) {
    echo "✅ pdo_pgsql extension is loaded<br>";
} else {
    echo "❌ pdo_pgsql extension is NOT loaded<br>";
}

phpinfo();
?>
