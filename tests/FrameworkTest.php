<?php

class FrameworkTest extends TestCase
{
    public function testItInstallsInAppropriateDirectory()
    {
        // Migrate
        $this->exec('cd tests/composer && composer install');

        // Assertion
        $this->assertTrue(is_dir(__DIR__ . '/composer/boots'));
    }

    public function testItCreatesConfigFileOnInstall()
    {
        // Assertion
        $configFile = __DIR__ . '/composer/boots/boots.php';
        $this->assertTrue(is_file($configFile));
        $config = require $configFile;
        $this->assertTrue(is_array($config));
        $this->assertEquals('0.2', $config['version']);
        $this->assertTrue($config['mounted']);
        $this->assertEquals([
            'psr-4' => [
                'Acme\\Acme\\' => 'acme/',
                'Emca\\' => 'emca/',
            ],
        ], $config['autoload']);
    }

    // TODO: Strict assertions.
    public function testItVersionsPsr4AutoloadsOnInstall()
    {
        // Assertion
        $acmeDir = __DIR__ . '/composer/boots/acme';
        $acmeFileSrc = $acmeDir . '/Acme.php';
        $acmeFileVersioned = __DIR__ . '/composer/boots/Acme_0_2.php';
        $this->assertTrue(is_dir($acmeDir));
        $this->assertTrue(is_file($acmeFileSrc));
        $this->assertTrue(is_file($acmeFileVersioned));
        $this->assertEquals(file_get_contents($acmeFileVersioned), file_get_contents($acmeFileSrc));

        $emcaDir = __DIR__ . '/composer/boots/emca';
        $emcaFileSrc = $emcaDir . '/Emca.php';
        $emcaFileVersioned = __DIR__ . '/composer/boots/Emca_0_2.php';
        $this->assertTrue(is_dir($emcaDir));
        $this->assertTrue(is_file($emcaFileSrc));
        $this->assertTrue(is_file($emcaFileVersioned));
        $this->assertEquals(file_get_contents($emcaFileVersioned), file_get_contents($emcaFileSrc));
    }

    public function testItSetsCorrectVersionInConfigFileOnUpdate()
    {
        $composerFile = __DIR__ . '/framework/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.2', $composer['version']);
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.3']
        )));
        $this->exec('cd tests/composer && composer update');

        // Assertion
        $config = require __DIR__ . '/composer/boots/boots.php';
        $this->assertEquals('0.3', $config['version']);
    }

    public function testItRetainsPreviousConfigOnUpdate()
    {
        $configFile = __DIR__ . '/composer/boots/boots.php';
        $config = require $configFile;
        $config['foo'] = 'bar';
        $contents = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        file_put_contents($configFile, $contents);

        // Update
        $composerFile = __DIR__ . '/framework/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.3', $composer['version']);
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.4']
        )));
        $this->exec('cd tests/composer && composer update');

        // Assertion
        $config = require $configFile;
        $this->assertEquals('0.4', $config['version']);
        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals(['psr-4' => [
            'Acme\\Acme\\' => 'acme/',
            'Emca\\' => 'emca/'
        ]], $config['autoload']);
    }

    // TODO: Strict assertions.
    public function testItVersionsPsr4AutoloadsOnUpdate()
    {
        // Assertion
        $acmeDir = __DIR__ . '/composer/boots/acme';
        $acmeFileSrc = $acmeDir . '/Acme.php';
        $acmeFileVersioned = __DIR__ . '/composer/boots/Acme_0_4.php';
        $this->assertTrue(is_dir($acmeDir));
        $this->assertTrue(is_file($acmeFileSrc));
        $this->assertTrue(is_file($acmeFileVersioned));
        $this->assertEquals(file_get_contents($acmeFileVersioned), file_get_contents($acmeFileSrc));

        $emcaDir = __DIR__ . '/composer/boots/emca';
        $emcaFileSrc = $emcaDir . '/Emca.php';
        $emcaFileVersioned = __DIR__ . '/composer/boots/Emca_0_4.php';
        $this->assertTrue(is_dir($emcaDir));
        $this->assertTrue(is_file($emcaFileSrc));
        $this->assertTrue(is_file($emcaFileVersioned));
        $this->assertEquals(file_get_contents($emcaFileVersioned), file_get_contents($emcaFileSrc));
    }

    public function testItMountsAllExtensionsOnUpdate()
    {
        $configFile = __DIR__ . '/composer/boots/boots.php';
        // Update
        $composerFile = __DIR__ . '/framework/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.4', $composer['version']);
        // Assert extension
        $config = require $configFile;
        $this->assertEquals('0.6', $config['extensions']['foo-bar']['version']);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/extension/Acme_framework1.php'),
            file_get_contents(__DIR__ . '/composer/boots/extend/foo-bar/acme/Acme.php')
        );
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.5']
        )));
        $this->exec('cd tests/composer && composer update');

        $config = require $configFile;

        // Assert framework
        $this->assertEquals('0.5', $config['version']);

        // Assert extension
        $this->assertEquals('0.6', $config['extensions']['foo-bar']['version']);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/extension/Acme_framework2.php'),
            file_get_contents(__DIR__ . '/composer/boots/extend/foo-bar/acme/Acme.php')
        );
    }
}
