<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = (new Finder())
    ->in([
        __DIR__ . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . 'tests',
    ])
    ->append([__FILE__])
;

return (new Config())
    ->setRules([
        '@PSR12' => true,
        '@PER-CS2.0' => true,
        'not_operator_with_space' => true,
    ])
    ->setFinder($finder)
;
