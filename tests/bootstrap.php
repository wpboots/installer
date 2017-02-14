<?php

require __DIR__ . '/../vendor/autoload.php';

require __DIR__ . '/TestCase.php';

// Get rid of temporary directories and files.
exec('cd tests/framework && rm composer.json');
exec('cd tests/extension && rm composer.json');
exec('cd tests/composer && rm -rf boots && rm -rf vendor && rm composer.lock');

// Construct framework composer.json file.
$frameworkComposerFile = __DIR__ . '/framework/composer.json';
file_put_contents($frameworkComposerFile, json_encode([
    'name' => 'boots/boots',
    'type' => 'framework',
    'version' => '2.0',
    'autoload' => [
        'psr-4' => [
            'Acme\\Acme\\' => 'acme/',
            'Emca\\' => 'emca/',
        ],
    ],
]));

// Construct extension composer.json file.
$extensionComposerFile = __DIR__ . '/extension/composer.json';
file_put_contents($extensionComposerFile, json_encode([
    'name' => 'boots/extension-foo-bar',
    'type' => 'boots-extension',
    'version' => '1.0',
    'autoload' => [
        'psr-4' => [
            'Acme\\Extension\\' => 'acme/',
            'Emca\\Extension\\' => 'emca/',
        ],
    ],
    'extra' => [
        'class' => 'Emca\\Extension\\Emca',
    ],
]));

// Run composer install.
exec('cd tests/composer && composer install');

// Get rid of temporary directories and files.
register_shutdown_function(function () {
    exec('cd tests/framework && rm composer.json');
    exec('cd tests/extension && rm composer.json');
    exec('cd tests/composer && rm -rf boots && rm -rf vendor && rm composer.lock');
});
