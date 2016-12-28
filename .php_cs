<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create();
$finder->in(__DIR__ . '/src');
$finder->in(__DIR__ . '/tests');

$config = new Config();
$config->setFinder($finder);
$config->setRules([
    // Use the Symfony style as a base
    '@Symfony' => true,

    // Use short array syntax
    'array_syntax' => ['syntax' => 'short'],

    // Concat with spaces
    'concat_space' => ['spacing' => 'one'],

    // Order use statements alphabetically
    'ordered_imports' => true,

    // Do not vertically align params
    'phpdoc_align' => false,
]);

return $config;
