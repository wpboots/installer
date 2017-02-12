<?php

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    /*
    public function setUp()
    {
        // Get rid of temporary directories and files.
        try {
            $this->exec('cd tests/composer && rm -rf boots && rm -rf vendor');
        } catch (Exception $e) {}

        // Construct framework composer.json file.
        $frameworkComposerFile = __DIR__ . '/framework/composer.json';
        file_put_contents($frameworkComposerFile, json_encode([
            'name' => 'boots/boots',
            'type' => 'framework',
            'version' => '0.1',
            'autoload' => [
                'psr-4' => [
                    'Acme\\' => 'acme/',
                    'Emca\\' => 'emca/',
                ],
            ],
        ]));

        // Construct extension composer.json file.
        $extensionComposerFile = __DIR__ . '/extension/composer.json';
        file_put_contents($extensionComposerFile, json_encode([
            'name' => 'boots/extension-foo-bar',
            'type' => 'boots-extension',
            'version' => '0.1',
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
    }

    public function tearDown()
    {
        // Get rid of temporary directories and files.
        try {
            $this->exec('cd tests/framework && rm composer.json');
            $this->exec('cd tests/extension && rm composer.json');
            $this->exec('cd tests/composer && rm -rf boots && rm -rf vendor');
        } catch (Exception $e) {}
    }*/

    protected function exec($command)
    {
        exec($command, $output, $status);
        if ($status !== 0) {
            $error = 'Failed to run shell command.';
            if (count($output)) {
                $error = PHP_EOL . implode(PHP_EOL, $output);
            }
            throw new RuntimeException($error, $status);
        }
    }
}
