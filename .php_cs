<?php
require_once __DIR__ . '/vendor/jadu/php-style/src/Config.php';

use Jadu\Style\Config;
use PhpCsFixer\Finder;

$finder = Finder::create();
$finder->in(__DIR__ . '/src');
$finder->in(__DIR__ . '/tests');

$config = new Config();
$config->setFinder($finder);
$rules = $config->getRules();
$config->setRules(
    array_merge(
        $rules,
        [
            'no_superfluous_phpdoc_tags' => false,
            'fully_qualified_strict_types' => false,
        ]
    )
);

return $config;
