<?php
$file = 'app/Http/Controllers/LineupsController.php';
if (!file_exists($file)) {
    die("File not found");
}
$content = file_get_contents($file);
$bom = pack('H*', 'EFBBBF');
if (substr($content, 0, 3) === $bom) {
    $content = substr($content, 3);
    echo "BOM removed.\n";
}
else {
    echo "No BOM found.\n";
}
// Remove any leading whitespace or newlines before <?php
$content = ltrim($content);
file_put_contents($file, $content);
echo "File cleaned.";
