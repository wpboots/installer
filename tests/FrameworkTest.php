<?php

class FrameworkTest extends TestCase
{
    public function setUp()
    {
        // Get rid of temporary directories and files.
        try {
            $this->exec('cd tests/composer && rm -rf boots && rm -rf vendor && rm composer.lock');
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
                    'Emca\\' => 'emca/'
                ]
            ]
        ]));
    }

    public function tearDown()
    {
        // Get rid of temporary directories and files.
        try {
            $this->exec('cd tests/framework && rm composer.json');
            $this->exec('cd tests/composer && rm -rf boots && rm -rf vendor && rm composer.lock');
        } catch (Exception $e) {}
    }

    public function testItInstallsInAppropriateDirectory()
    {
        // Migrate
        $this->exec('cd tests/composer && composer install');

        // Assertion
        $this->assertTrue(is_dir(__DIR__ . '/composer/boots'));
    }

    public function testItCreatesConfigFileOnInstall()
    {
        // Migrate
        $this->exec('cd tests/composer && composer install');

        // Assertion
        $configFile = __DIR__ . '/composer/boots/boots.php';
        $this->assertTrue(is_file($configFile));
        $this->assertEquals([
            'version' => '0.1',
            'mounted' => false,
            'extensions' => [],
            'autoload' => [
                'psr-4' => [
                    'Acme\\' => 'acme/',
                    'Emca\\' => 'emca/'
                ]
            ]
        ], require $configFile);
    }

    // TODO: Strict assertions.
    public function testItVersionsPsr4AutoloadsOnInstall()
    {
        // Migrate
        $this->testItInstallsInAppropriateDirectory();

        // Assertion
        $acmeDir = __DIR__ . '/composer/boots/acme';
        $acmeFileSrc = $acmeDir . '/Acme.php';
        $acmeFileVersioned = __DIR__ . '/composer/boots/Acme_0_1.php';
        $this->assertTrue(is_dir($acmeDir));
        $this->assertTrue(is_file($acmeFileSrc));
        $this->assertTrue(is_file($acmeFileVersioned));
        $this->assertEquals(file_get_contents($acmeFileVersioned), file_get_contents($acmeFileSrc));

        $emcaDir = __DIR__ . '/composer/boots/emca';
        $emcaFileSrc = $emcaDir . '/Emca.php';
        $emcaFileVersioned = __DIR__ . '/composer/boots/Emca_0_1.php';
        $this->assertTrue(is_dir($emcaDir));
        $this->assertTrue(is_file($emcaFileSrc));
        $this->assertTrue(is_file($emcaFileVersioned));
        $this->assertEquals(file_get_contents($emcaFileVersioned), file_get_contents($emcaFileSrc));
    }

    public function testItSetsCorrectVersionInConfigFileOnUpdate()
    {
        // Migrate
        $this->exec('cd tests/composer && composer install');
        $composerFile = __DIR__ . '/framework/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.1', $composer['version']);
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.2']
        )));
        $this->exec('cd tests/composer && composer update');

        // Assertion
        $config = require __DIR__ . '/composer/boots/boots.php';
        $this->assertEquals('0.2', $config['version']);
    }

    public function testItRetainsPreviousConfigExceptVersionOnUpdate()
    {
        // Migrate
        $this->exec('cd tests/composer && composer install');
        $configFile = __DIR__ . '/composer/boots/boots.php';
        $config = require $configFile;
        $config['extensions'] = ['Foo'];
        $config['foo'] = 'bar';
        $contents = '<?php return ' . var_export($config, true) . ';' . PHP_EOL;
        file_put_contents($configFile, $contents);

        // Update
        $composerFile = __DIR__ . '/framework/composer.json';
        $composer = json_decode(file_get_contents($composerFile), true);
        $this->assertEquals('0.1', $composer['version']);
        file_put_contents($composerFile, json_encode(array_replace(
            $composer,
            ['version' => '0.2']
        )));
        $this->exec('cd tests/composer && composer update');

        // Assertion
        $config = require $configFile;
        $this->assertEquals('0.2', $config['version']);
        $this->assertEquals(['Foo'], $config['extensions']);
        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals(['psr-4' => [
            'Acme\\' => 'acme/',
            'Emca\\' => 'emca/'
        ]], $config['autoload']);
    }

    // TODO: Strict assertions.
    public function testItVersionsPsr4AutoloadsOnUpdate()
    {
        // Migrate
        $this->testItSetsCorrectVersionInConfigFileOnUpdate();

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
}
