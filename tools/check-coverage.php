<?php

declare(strict_types=1);

if ($argc < 3) {
    fwrite(STDERR, "Usage: php tools/check-coverage.php <clover.xml> <minimum-percent>\n");

    exit(1);
}

$cloverPath = $argv[1];
$minimum = (float) $argv[2];

if (!is_file($cloverPath)) {
    fwrite(STDERR, "Coverage report not found: {$cloverPath}\n");

    exit(1);
}

$document = new DOMDocument();

if (!$document->load($cloverPath)) {
    fwrite(STDERR, "Unable to parse coverage report: {$cloverPath}\n");

    exit(1);
}

$metrics = $document->getElementsByTagName('metrics')->item(0);

if (!$metrics instanceof DOMElement) {
    fwrite(STDERR, "Coverage metrics not found in report: {$cloverPath}\n");

    exit(1);
}

$statements = (int) $metrics->getAttribute('statements');
$coveredStatements = (int) $metrics->getAttribute('coveredstatements');

if ($statements === 0) {
    fwrite(STDERR, "Coverage report contains zero statements.\n");

    exit(1);
}

$coverage = ($coveredStatements / $statements) * 100;
$formattedCoverage = number_format($coverage, 2, '.', '');
$formattedMinimum = number_format($minimum, 2, '.', '');

fwrite(STDOUT, "Line coverage: {$formattedCoverage}% (minimum: {$formattedMinimum}%)\n");

if ($coverage < $minimum) {
    fwrite(STDERR, "Coverage threshold not reached.\n");

    exit(1);
}

exit(0);
