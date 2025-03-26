<?php
// Create directories
$vendorDir = __DIR__ . '/vendor';
$phpmailerDir = $vendorDir . '/phpmailer/phpmailer/src';

if (!file_exists($vendorDir)) {
    mkdir($vendorDir, 0777, true);
}

if (!file_exists($phpmailerDir)) {
    mkdir($phpmailerDir, 0777, true);
}

// PHPMailer class files to download
$files = [
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php'
];

// Download each file
foreach ($files as $filename => $url) {
    $content = file_get_contents($url);
    if ($content === false) {
        die("Failed to download $filename");
    }
    
    $filepath = $phpmailerDir . '/' . $filename;
    if (file_put_contents($filepath, $content) === false) {
        die("Failed to save $filename");
    }
    
    echo "Successfully downloaded and saved $filename\n";
}

echo "PHPMailer setup complete!\n";
?> 