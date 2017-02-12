<?php

namespace Boots\Installer;

/**
 * This file is part of the Boots\Installer package.
 *
 * @package    Boots\Installer
 * @subpackage ExtensionInstaller
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
 * @subpackage ExtensionInstaller
 */
class ExtensionInstaller extends Installer
{
    /**
     * Extension slug (will be set dynamically for each extension).
     * @var string
     */
    protected $extSlug;

    /**
     * Extension prefix.
     * @var string
     */
    protected $extPrefix = 'boots/extension';

    /**
     * Extension type.
     * @var string
     */
    protected $extType = 'boots-extension';

    /**
     * {@inheritDoc}
     */
    public function getInstallPath(PackageInterface $package)
    {
        $prettyName = $package->getPrettyName();

        $unwantedLength = strlen($this->extPrefix) + 1;

        $prefix = substr($prettyName, 0, $unwantedLength);
        if ($prefix !== $this->extPrefix . '-') {
            throw new \InvalidArgumentException(sprintf(
                'Unable to install extension %s. Boots extensions '
                .'should always start their package name with '
                .'"boots/extension-"',
                $prettyName
            ));
        }

        $this->extSlug = substr($prettyName, $unwantedLength);
        if (!preg_match('/^(([a-z]+-)*[a-z]+)$/', $this->extSlug)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to install extension %s. Boots extensions '
                .'should only contain lowercase alphabets and '
                .'hyphens; and should not start or end with hyphens.',
                $prettyName
            ));
        }

        return "{$this->frameworkDir}/{$this->extDir}/{$this->extSlug}";
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
            return parent::install($repo, $package);
        }

        $extra = $package->getExtra();

        if (!array_key_exists('class', $extra)) {
            throw new \Exception(sprintf(
                'Unable to install extension %s. Boots extensions '
                .'require an extra property of "class" '
                .'in its composer.json file.',
                $package->getPrettyName()
            ));
        }

        $path = $this->getRootPath($this->frameworkDir);
        $configPath = "{$path}/{$this->configFile}";
        if (is_file($configPath)) {
            $config = $this->readConfig($configPath);
        }

        parent::install($repo, $package);

        if (!isset($config)) {
            $config = $this->readConfig($configPath);
        }

        $regexes = [];
        $suffix = $this->sanitizeVersion($config['version']);
        foreach (array_keys($config['autoload']['psr-4']) as $prefix) {
            $regexes['/^\\\\?' . preg_quote($prefix) . '/'] = $suffix;
        }
        $this->mount($package, $regexes);

        $config['extensions'][$this->extSlug] = [
            'version' => $package->getPrettyVersion(),
            'autoload' => $package->getAutoload(),
            'class' => $extra['class'],
        ];
        if (!isset($config['extensions'][$this->extSlug]['mounted'])) {
            $config['extensions'][$this->extSlug]['mounted'] = false;
        }

        $this->writeConfig($configPath, $config);
    }
}
