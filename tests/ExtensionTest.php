<?php

class ExtensionTest extends TestCase
{
    public function testItInstallsInAppropriateDirectory()
    {
        $this->assertTrue(is_dir($this->extensionDir));
    }

    public function testItCreatesConfigFileOnInstall()
    {
        $config = $this->config();
        $this->assertEquals([
            'version' => '1.0',
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

    public function testItVersionsPsr4AutoloadsOnInstall()
    {
        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Acme_1_0.php'),
            file_get_contents($this->extensionDir . '/acme/Acme.php')
        );
        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Emca_1_0.php'),
            file_get_contents($this->extensionDir . '/emca/Emca.php')
        );
    }

    public function testItUpdatesConfigFileOnUpdate()
    {
        $this->extensionComposer(['version' => '1.1']);
        $this->composerUpdate();

        $config = $this->config();
        $config = $config['extensions']['foo-bar'];
        $this->assertEquals('1.1', $config['version']);
        $this->assertEquals(['psr-4' => [
            'Acme\\Extension\\' => 'acme/',
            'Emca\\Extension\\' => 'emca/',
        ]], $config['autoload']);
    }

    public function testItVersionsPsr4AutoloadsOnUpdate()
    {
        $this->extensionComposer(['version' => '1.1']);
        $this->composerUpdate();

        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Acme_1_1.php'),
            file_get_contents($this->extensionDir . '/acme/Acme.php')
        );
        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Emca_1_1.php'),
            file_get_contents($this->extensionDir . '/emca/Emca.php')
        );
    }

    public function testItShouldAllowInstallationWithMountingModes()
    {
        // false => no mounting.
        $this->extensionComposer(['version' => '1.1', 'extra' => ['mount' => false]]);
        $this->composerUpdate();
        $config = $this->config();
        $config = $config['extensions']['foo-bar'];
        $this->assertEquals('1.1', $config['version']);
        $this->assertFalse($config['mounted']);
        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Acme.php'),
            file_get_contents($this->extensionDir . '/acme/Acme.php')
        );

        // global => only global mounting.
        $this->extensionComposer(['version' => '1.2', 'extra' => ['mount' => 'global']]);
        $this->composerUpdate();
        $config = $this->config();
        $config = $config['extensions']['foo-bar'];
        $this->assertEquals('1.2', $config['version']);
        $this->assertEquals('global', $config['mounted']);
        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Acme_global.php'),
            file_get_contents($this->extensionDir . '/acme/Acme.php')
        );

        // local => only local mounting.
        $this->extensionComposer(['version' => '1.3', 'extra' => ['mount' => 'local']]);
        $this->composerUpdate();
        $config = $this->config();
        $config = $config['extensions']['foo-bar'];
        $this->assertEquals('1.3', $config['version']);
        $this->assertEquals('local', $config['mounted']);
        $this->assertEquals(
            file_get_contents($this->extensionDir . '/Acme_local.php'),
            file_get_contents($this->extensionDir . '/acme/Acme.php')
        );
    }
}
