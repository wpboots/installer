<?php

namespace Boots\Installer;

/**
 * This file is part of the Boots\Installer package.
 *
 * @package    Boots\Installer
 * @subpackage FrameworkInstaller
 * @author     Kamal Khan <shout@bhittani.com>
 * @version    1.x
 * @see        http://wpboots.com
 * @link       https://github.com/wpboots/installer
 * @copyright  2014-2017 Kamal Khan
 * @license    https://github.com/wpboots/installer/blob/master/LICENSE
 */

use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

/**
 * @package Boots\Installer
 * @subpackage FrameworkInstaller
 */
class FrameworkInstaller extends Installer
{
    /**
     * Extension type.
     * @var string
     */
    protected $framework = 'boots/boots';

    // public function cascade(InstalledRepositoryInterface $repo)
    // {
    //     $installationManager = $this->composer->getInstallationManager();
    //     $localRepos = $this->composer->getRepositoryManager()->getRepositories();

    //     $extensionInstaller = new ExtensionInstaller($this->io, $this->composer);
    //     foreach ($localRepos as $repository) {
    //         foreach ($repository->getPackages() as $package) {
    //             if ($extensionInstaller->supports($package->getType())) {
    //                 $extensionInstaller->install($repo, $package, true);
    //             }
    //         }
    //     }
    // }

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        if ($package->getPrettyName() !== $this->framework) {
            return parent::getInstallPath($package);
        }

        return $this->frameworkDir;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        return $packageType === 'framework';
    }

    /**
     * {@inheritDoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        if ($package->getPrettyName() !== $this->framework) {
            return parent::install($repo, $package);
        }

        $extra = $package->getExtra();
        $mount = isset($extra['mount']) ? $extra['mount'] : true;

        $path = $this->getAbsolutePath($package);
        $configPath = "{$path}/{$this->configFile}";
        if (is_file($configPath)) {
            $config = $this->readConfig($configPath);
        }

        parent::install($repo, $package);

        $this->mount($package, [], $mount === false);

        if (!isset($config)) {
            $config = $this->readConfig($configPath);
        }
        $config['mounted'] = $mount;
        $config['version'] = $package->getPrettyVersion();
        $config['autoload'] = $package->getAutoload();
        $this->writeConfig($configPath, $config);

        // Update all extensions
        // $this->cascade($repo);
    }
}
