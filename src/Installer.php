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
use Composer\Repository\InstalledRepositoryInterface;

class Installer extends LibraryInstaller
{
    protected $extDir = 'extend';

    protected $extType = 'boots-extension';

    protected $manifestFile = 'boots.json';

    protected function writeManifest($path, array $manifest)
    {
        file_put_contents($path, json_encode($manifest));
    }

    protected function mountExtension(PackageInterface $package)
    {
        $version = $package->getPrettyVersion();
        $extra = $package->getExtra();
        $class = $extra['class'];
        $autoload = $package->getAutoload();
        $baseDir = dirname($this->composer->getConfig()->getConfigSource()->getName());
        $installPath = $this->getInstallPath($package);
        $path = "{$baseDir}/{$installPath}/{$this->manifestFile}";
        $this->writeManifest($path, [
            'version' => $version,
            'class' => $class,
            'autoload' => $autoload,
        ]);
    }

    protected function validateExtension(PackageInterface $package)
    {
        if (!array_key_exists('class', $package->getExtra())) {
            throw new Exception(sprintf(
                'Unable to install extension %s. Boots extensions '
                .'require an extra property of "class" '
                .'in its composer.json file.',
                $package->getPrettyName()
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prettyName = $package->getPrettyName();

        $prefix = substr($prettyName, 0, 16);
        if ('boots/extension-' !== $prefix) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to install extension %s. Boots extensions '
                .'should always start their package name with '
                .'"boots/extension-"',
                $package->getPrettyName()
            ));
        }

        $name = substr($prettyName, 16);
        if (!preg_match('/^(([a-z]+-)*[a-z]+)$/', $name)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to install extension %s. Boots extensions '
                .'should only contain lowercase alphabets and '
                .'hyphens; and should not start or end with hyphens.',
                $package->getPrettyName()
            ));
        }

        return "{$this->extDir}/{$name}";
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === $this->extType;
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if (!$this->supports($package->getType())) {
            parent::install($repo, $package);
        } else
            $this->validateExtension($package);
            parent::install($repo, $package);
            $this->mountExtension($package);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        if (!$this->supports($package->getType())) {
            parent::update($repo, $initial, $target);
        } else
            $this->validateExtension($target);
            parent::update($repo, $initial, $target);
            $this->mountExtension($target);
        }
    }
}
