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
 * @copyright  2014-2016 Kamal Khan
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
        $installer = new Installer($io, $composer);
        $composer->getInstallationManager()->addInstaller($installer);
    }
}
