<?php

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->notName('*.md')
    ->notName('*.js')
    ->notName('composer.*')
    ->notName('phpunit.xml*')
    ->notName('.git*')
    ->notName('.php_cs')
    ->notName('*.yml')
    ->exclude('vendor')
    ->in(__DIR__);

return Symfony\CS\Config\Config::create()
    ->fixers(
        array(
            'encoding',
            'linefeed',
            'indentation',
            'trailing_spaces',
            'unused_use',
            'php_closing_tag',
            'standardize_not_equal',
            'short_tag',
            'ternary_spaces',
            'spaces_cast',
            'object_operator',
            'visibility',
            'function_declaration',
            'include',
            'extra_empty_lines',
            'braces',
            'lowercase_keywords',
            'lowercase_constants',
            'controls_spaces',
            'psr0',
            'elseif',
            'eof_ending'
        )
    )->finder($finder);
