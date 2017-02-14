<?php

abstract class TestCase extends PHPUnit_Framework_TestCase
{
    protected $configFile = __DIR__ . '/composer/boots/boots.php';

    protected $frameworkDir = __DIR__ . '/composer/boots';

    protected $srcFrameworkDir = __DIR__ . '/framework';

    protected $extendDir = __DIR__ . '/composer/boots/extend';

    protected $extensionDir = __DIR__ . '/composer/boots/extend/foo-bar';

    protected $srcExtensionDir = __DIR__ . '/extension';

    protected function configFile()
    {
        return __DIR__ . '/composer/boots/boots.php';
    }

    protected function frameworkDir()
    {
        return __DIR__ . '/composer/boots';
    }

    protected function srcFrameworkDir()
    {
        return __DIR__ . '/framework';
    }

    protected function extendDir()
    {
        return __DIR__ . '/composer/boots/extend';
    }

    protected function extensionDir()
    {
        return __DIR__ . '/composer/boots/extend/foo-bar';
    }

    protected function srcExtensionDir()
    {
        return __DIR__ . '/extension';
    }

    protected function config(array $replace = [])
    {
        $config = require $this->configFile();
        if (count($replace)) {
            $config = array_replace_recursive($config, $replace);
            $contents = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
            file_put_contents($this->configFile(), $contents);
        }
        return $config;
    }

    protected function composer($path, array $replace = [])
    {
        $composer = json_decode(file_get_contents($path), true);
        if (count($replace)) {
            $composer = array_replace_recursive($composer, $replace);
            file_put_contents($path, json_encode($composer));
        }
        return $composer;
    }

    protected function frameworkComposer(array $replace = [])
    {
        return $this->composer($this->srcFrameworkDir() . '/composer.json', $replace);
    }

    protected function extensionComposer(array $replace = [])
    {
        return $this->composer($this->srcExtensionDir() . '/composer.json', $replace);
    }

    protected function composerInstall()
    {
        $this->exec('cd tests/composer && composer install');
    }

    protected function composerUpdate()
    {
        $this->exec('cd tests/composer && composer update');
    }

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
        foreach ($output as $o) {
            echo $o . PHP_EOL;
        }
    }

    public function setUp()
    {
        $this->frameworkComposer(['version' => '2.0']);
        $this->extensionComposer(['version' => '1.0', 'extra' => ['mount' => true]]);
        $this->exec('cd tests/composer && rm -rf boots && rm -rf vendor && rm composer.lock');
        $this->composerInstall();
    }
}
