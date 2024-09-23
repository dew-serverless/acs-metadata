<?php

declare(strict_types=1);

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\VarExporter\VarExporter;

// Requires two parameters
// 1. The script name
// 2. The destination path to store the artifact
if ($argc !== 2) {
    printf('Usage: php %s <dst>'.PHP_EOL, $argv[0]);
    exit(1);
}

$srcPath = new SplFileInfo(getcwd());
$dstPath = rtrim($argv[1], DIRECTORY_SEPARATOR);

// Validate the destination path
if (! is_dir($dstPath)) {
    printf('The path "%s" does not exist.'.PHP_EOL, $dstPath);
    exit(1);
}

if (! is_writable($dstPath)) {
    printf('The path "%s" is not writable.'.PHP_EOL, $dstPath);
    exit(1);
}

$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($srcPath->getRealPath()),
        function ($current, $key, $iterator) use ($srcPath) {
            $basePath = $srcPath->getRealPath();

            // Generate JSON files only within supported language folders
            return str_starts_with($key, $basePath.'/en_us')
                || str_starts_with($key, $basePath.'/zh_cn');
        }
    )
);

foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'json') {
        continue;
    }

    $relativePath = substr($file->getRealpath(), strlen($srcPath->getRealPath()));

    printf('[-] Generate %s'.PHP_EOL, $relativePath);

    // Retrieve the JSON data
    $json = file_get_contents($file->getRealPath());

    // Convert the JSON data to a PHP array
    $decoded = json_decode($json, associative: true);

    // Build the data in PHP array representation
    $result = sprintf('<?php return %s;'.PHP_EOL, VarExporter::export($decoded));

    // Change the file extension from json to php
    $outPathname = substr($dstPath.$relativePath, 0, -4).'php';
    $outPath = dirname($outPathname);

    if (! file_exists($outPath)) {
        mkdir($outPath, recursive: true);
    }

    // Persist the data permanently
    file_put_contents($outPathname, $result);
}

echo '[-] Generate successfully'.PHP_EOL;
