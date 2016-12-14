<?php

namespace Boots\Installer;

/**
 * This file is part of the Boots\Installer package.
 *
 * @package    Boots\Installer
 * @subpackage Installer
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    1.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/installer
 * @copyright  2014-2016 Kamal Khan
 * @license    https://github.com/wpboots/installer/blob/master/LICENSE
 */

use Composer\Package\PackageInterface;
use Composer\Installer\LibraryInstaller;

class Installer extends LibraryInstaller
{
    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prettyName = $package->getPrettyName();
        if (!preg_match('/\/(([a-z]+-)*[a-z]+)$/', $prettyName, $match)) {
            throw new \InvalidArgumentException(
                'Unable to install extension. Boots extensions '
                .'should only contain lowercase alphabets and '
                .'hyphens; and should not start or end with hyphens.'
            );
        }

        return "extend/{$match[1]}";
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'boots-extension';
    }
}
