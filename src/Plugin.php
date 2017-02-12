<?php

namespace Boots\Installer;

/**
 * This file is part of the Boots\Installer package.
 *
 * @package    Boots\Installer
 * @subpackage Plugin
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    1.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/installer
 * @copyright  2014-2017 Kamal Khan
 * @license    https://github.com/wpboots/installer/blob/master/LICENSE
 */

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

/**
 * @package Boots\Installer
 * @subpackage Plugin
 */
class Plugin implements PluginInterface
{
    /**
     * {@inheritDoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $frameworkInstaller = new FrameworkInstaller($io, $composer);
        $extensionInstaller = new ExtensionInstaller($io, $composer);

        $manager = $composer->getInstallationManager();
        $manager->addInstaller($frameworkInstaller);
        $manager->addInstaller($extensionInstaller);
    }
}
