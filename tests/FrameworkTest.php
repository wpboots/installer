<?php

class FrameworkTest extends TestCase
{
    public function testItInstallsInAppropriateDirectory()
    {
        $this->assertTrue(is_dir($this->frameworkDir));
    }

    public function testItCreatesConfigFileOnInstall()
    {
        $this->assertTrue(is_file($this->configFile));

        $config = $this->config();

        $this->assertEquals('2.0', $config['version']);
        $this->assertTrue($config['mounted']);
        $this->assertEquals([
            'psr-4' => [
                'Acme\\Acme\\' => 'acme/',
                'Emca\\' => 'emca/',
            ],
        ], $config['autoload']);
    }

    public function testItVersionsPsr4AutoloadsOnInstall()
    {
        $this->assertEquals(
            file_get_contents($this->frameworkDir . '/Acme_2_0.php'),
            file_get_contents($this->frameworkDir . '/acme/Acme.php')
        );
        $this->assertEquals(
            file_get_contents($this->frameworkDir . '/Emca_2_0.php'),
            file_get_contents($this->frameworkDir . '/emca/Emca.php')
        );
    }

    public function testItUpdatesConfigFileOnUpdate()
    {
        $this->config(['foo' => 'bar']);

        $this->frameworkComposer(['version' => '2.1']);
        $this->composerUpdate();

        $config = $this->config();
        $this->assertEquals('2.1', $config['version']);
        $this->assertEquals('bar', $config['foo']);
        $this->assertEquals(['psr-4' => [
            'Acme\\Acme\\' => 'acme/',
            'Emca\\' => 'emca/'
        ]], $config['autoload']);
    }

    public function testItVersionsPsr4AutoloadsOnUpdate()
    {
        $this->frameworkComposer(['version' => '2.1']);
        $this->composerUpdate();

        $this->assertEquals(
            file_get_contents($this->frameworkDir . '/Acme_2_1.php'),
            file_get_contents($this->frameworkDir . '/acme/Acme.php')
        );
        $this->assertEquals(
            file_get_contents($this->frameworkDir . '/Emca_2_1.php'),
            file_get_contents($this->frameworkDir . '/emca/Emca.php')
        );
    }

    public function testItCascadesExtensionsOnUpdate()
    {
        // $this->extensionComposer(['version' => '1.1']);
        $this->frameworkComposer(['version' => '2.1']);
        $this->composerUpdate();
        // $this->exec('cd tests/composer && composer update boots/boots');

        $config = $this->config();
        $this->assertEquals('2.1', $config['version']);
        $this->assertEquals(
            file_get_contents($this->srcExtensionDir . '/Acme_framework.php'),
            file_get_contents($this->extensionDir . '/acme/Acme.php')
        );
    }
}
