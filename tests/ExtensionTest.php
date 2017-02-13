<?php

class ExtensionTest extends TestCase
{
    public function testItInstallsInAppropriateDirectory()
    {
        // Migrate
        $this->exec('cd tests/composer && composer install');

        // Assertion
        $this->assertTrue(is_dir(__DIR__ . '/composer/boots/extend/foo-bar'));
    }

    public function testItCreatesConfigFileOnInstall()
    {
        // Assertion
        $configFile = __DIR__ . '/composer/boots/boots.php';
        $this->assertTrue(is_file($configFile));
        $config = require $configFile;
        $this->assertTrue(is_array($config['extensions']));
        $this->assertTrue(is_array($config['extensions']['foo-bar']));
        $this->assertEquals([
            'version' => '0.1',
            'mounted' => true,
            'class' => 'Emca\\Extension\\Emca',
            'autoload' => [
                'psr-4' => [
                    'Acme\\Extension\\' => 'acme/',
                    'Emca\\Extension\\' => 'emca/',
                ],
            ],
        ], $config['extensions']['foo-bar']);
    }

    // TODO: Strict assertions.
    public function testItVersionsPsr4AutoloadsOnInstall()
    {
        // Assertion
        $acmeDir = __DIR__ . '/composer/boots/extend/foo-bar/acme';
        $acmeFileSrc = $acmeDir . '/Acme.php';
        $acmeFileVersioned = __DIR__ . '/extension/Acme_0_1.php';
        $this->assertTrue(is_dir($acmeDir));
        $this->assertTrue(is_file($acmeFileSrc));
        $this->assertTrue(is_file($acmeFileVersioned));
        $this->assertEquals(file_get_contents($acmeFileVersioned), file_get_contents($acmeFileSrc));

        $emcaDir = __DIR__ . '/composer/boots/extend/foo-bar/emca';
        $emcaFileSrc = $emcaDir . '/Emca.php';
        $emcaFileVersioned = __DIR__ . '/extension/Emca_0_1.php';
        $this->assertTrue(is_dir($emcaDir));
        $this->assertTrue(is_file($emcaFileSrc));
        $this->assertTrue(is_file($emcaFileVersioned));
        $this->assertEquals(file_get_contents($emcaFileVersioned), file_get_contents($emcaFileSrc));
    }

    public function testItSetsCorrectVersionInConfigFileOnUpdate()
    {
        $composerFile = __DIR__ . '/extension/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.1', $composer['version']);
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.2']
        )));
        $this->exec('cd tests/composer && composer update');

        // Assertion
        $config = require __DIR__ . '/composer/boots/boots.php';
        $this->assertEquals('0.2', $config['extensions']['foo-bar']['version']);
    }

    public function testItRetainsPreviousConfigOnUpdate()
    {
        $configFile = __DIR__ . '/composer/boots/boots.php';
        $config = require $configFile;
        $config['beep'] = 'boop';
        $contents = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        file_put_contents($configFile, $contents);

        // Update
        $composerFile = __DIR__ . '/extension/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.2', $composer['version']);
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.3']
        )));
        $this->exec('cd tests/composer && composer update');

        // Assertion
        $config = require $configFile;
        $this->assertTrue($config['extensions']['foo-bar']['mounted']);
        $this->assertEquals('0.3', $config['extensions']['foo-bar']['version']);
        $this->assertEquals('boop', $config['beep']);
        $this->assertEquals(['psr-4' => [
            'Acme\\Extension\\' => 'acme/',
            'Emca\\Extension\\' => 'emca/'
        ]], $config['extensions']['foo-bar']['autoload']);
    }

    // TODO: Strict assertions.
    public function testItVersionsPsr4AutoloadsOnUpdate()
    {
        // Assertion
        $acmeDir = __DIR__ . '/composer/boots/extend/foo-bar/acme';
        $acmeFileSrc = $acmeDir . '/Acme.php';
        $acmeFileVersioned = __DIR__ . '/extension/Acme_0_3.php';
        $this->assertTrue(is_dir($acmeDir));
        $this->assertTrue(is_file($acmeFileSrc));
        $this->assertTrue(is_file($acmeFileVersioned));
        $this->assertEquals(file_get_contents($acmeFileVersioned), file_get_contents($acmeFileSrc));

        $emcaDir = __DIR__ . '/composer/boots/extend/foo-bar/emca';
        $emcaFileSrc = $emcaDir . '/Emca.php';
        $emcaFileVersioned = __DIR__ . '/extension/Emca_0_3.php';
        $this->assertTrue(is_dir($emcaDir));
        $this->assertTrue(is_file($emcaFileSrc));
        $this->assertTrue(is_file($emcaFileVersioned));
        $this->assertEquals(file_get_contents($emcaFileVersioned), file_get_contents($emcaFileSrc));
    }

    public function testItShouldAllowInstallationWithMountingModes()
    {
        $composerFile = __DIR__ . '/extension/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertFalse(array_key_exists('mount', $composer['extra']));

        // false => no mounting.
        file_put_contents($composerFile, json_encode(array_replace_recursive(
            $composer,
            ['version' => '0.4', 'extra' => ['mount' => false]]
        )));
        $this->exec('cd tests/composer && composer update');
        // Assertion
        $config = require __DIR__ . '/composer/boots/boots.php';
        $this->assertEquals('0.4', $config['extensions']['foo-bar']['version']);
        $this->assertFalse($config['extensions']['foo-bar']['mounted']);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/extension/Acme.php'),
            file_get_contents(__DIR__ . '/composer/boots/extend/foo-bar/acme/Acme.php')
        );

        // global => only global mounting.
        file_put_contents($composerFile, json_encode(array_replace_recursive(
            $composer,
            ['version' => '0.5', 'extra' => ['mount' => 'global']]
        )));
        $this->exec('cd tests/composer && composer update');
        // Assertion
        $config = require __DIR__ . '/composer/boots/boots.php';
        $this->assertEquals('0.5', $config['extensions']['foo-bar']['version']);
        $this->assertEquals('global', $config['extensions']['foo-bar']['mounted']);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/extension/Acme_global.php'),
            file_get_contents(__DIR__ . '/composer/boots/extend/foo-bar/acme/Acme.php')
        );

        // local => only local mounting.
        file_put_contents($composerFile, json_encode(array_replace_recursive(
            $composer,
            ['version' => '0.6', 'extra' => ['mount' => 'local']]
        )));
        $this->exec('cd tests/composer && composer update');
        // Assertion
        $config = require __DIR__ . '/composer/boots/boots.php';
        $this->assertEquals('0.6', $config['extensions']['foo-bar']['version']);
        $this->assertEquals('local', $config['extensions']['foo-bar']['mounted']);
        $this->assertEquals(
            file_get_contents(__DIR__ . '/extension/Acme_local.php'),
            file_get_contents(__DIR__ . '/composer/boots/extend/foo-bar/acme/Acme.php')
        );
    }
}
