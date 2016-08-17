<?php

use Symfony\CS\Config\Config;
use Symfony\CS\Finder\DefaultFinder;
use Symfony\CS\FixerInterface;

$finder = DefaultFinder::create();
$finder->in(__DIR__ . '/src');
$finder->in(__DIR__ . '/tests');

$config = Config::create();
$config->finder($finder);
$config->level(FixerInterface::SYMFONY_LEVEL);
$config->fixers([
    '-phpdoc_params',
    'long_array_syntax',
    'ordered_use',
    'unused_use',
    'strict',
    'strict_param',
]);

return $config;
