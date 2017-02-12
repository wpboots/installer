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
 * @copyright  2014-2017 Kamal Khan
 * @license    https://github.com/wpboots/installer/blob/master/LICENSE
 */

use PhpParser\Error;
use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Composer\Package\PackageInterface;
use PhpParser\NodeVisitor\NameResolver;
use Composer\Installer\LibraryInstaller;
use Bhittani\PhpParser\AppendSuffixVisitor;
use PhpParser\PrettyPrinter\Standard as PhpPrinter;

/**
 * @package Boots\Installer
 * @subpackage Installer
 */
abstract class Installer extends LibraryInstaller
{
    /**
     * Framework directory (relative to project).
     * @var string
     */
    protected $frameworkDir = 'boots';

    /**
     * Framework extension directory (relative to framework).
     * @var string
     */
    protected $extDir = 'extend';

    /**
     * Framework config file (relative to framework).
     * @var string
     */
    protected $configFile = 'boots.php';

    protected static function readConfig($path)
    {
        $config = [
            'version' => '',
            'autoload' => [],
            'mounted' => false,
            'extensions' => [],
        ];

        if (is_file($path)) {
            $config = require $path;
        }

        return $config;
    }

    protected static function writeConfig($path, array $config)
    {
        $contents = '<?php ' . PHP_EOL;
        $contents .= '// This file is automatically generated by the' . PHP_EOL;
        $contents .= '// boots framework and SHOULD NOT be modified directly.' . PHP_EOL;
        $contents .= 'return ' . var_export($config, true) . ';' . PHP_EOL;
        file_put_contents($path, $contents);
    }

    protected function getRootPath($path = '')
    {
        $root = dirname($this->composer->getConfig()->getConfigSource()->getName());
        return rtrim($root . '/' . trim($path, '/'), '/');
    }

    protected function getAbsolutePath(PackageInterface $package)
    {
        return $this->getRootPath() . '/' . $this->getInstallPath($package);
    }

    protected function sanitizeVersion($version)
    {
        $suffix = str_replace('.', '_', $version);
        $suffix = str_replace('-', '_', $suffix);
        return empty($suffix) ? '' : "_{$suffix}";
    }

    protected function mount(PackageInterface $package, $regexes = [])
    {
        $path = $this->getAbsolutePath($package);
        $version = $package->getPrettyVersion();
        $autoloads = $package->getAutoload();

        $suffix = $this->sanitizeVersion($version);

        $traverser = new NodeTraverser;
        if (isset($autoloads['psr-4'])) {
            foreach (array_keys($autoloads['psr-4']) as $prefix) {
                $regexes['/^\\\\?' . preg_quote($prefix) . '/'] = $suffix;
            }
        }
        $traverser->addVisitor(new AppendSuffixVisitor(null, $regexes));

        $printer = new PhpPrinter;
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        foreach (array_values($autoloads['psr-4']) as $rpath) {
            $dir = $path . '/' . trim($rpath, '/');
            if (!is_dir($dir)) {
                continue;
            }
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir));
            $files = new \RegexIterator($files, '/\.php$/');
            foreach ($files as $file) {
                try {
                    // read the file that should be converted
                    $code = file_get_contents($file);
                    // parse
                    $stmts = $parser->parse($code);
                    // traverse
                    $stmts = $traverser->traverse($stmts);
                    // pretty print
                    $code = $printer->prettyPrintFile($stmts);
                    // write the converted file to the target directory
                    file_put_contents($file, $code . PHP_EOL);
                } catch (Error $e) {
                    echo 'Parse Error: ', $e->getMessage();
                }
            }
        }
    }
}
