<?php

namespace Boots\Installer;

use Composer\Installer\PackageEvent;

require __DIR__ . '/../../vendor/autoload.php';

class PluginLoader
{
    public static function load(PackageEvent $event)
    {
        $composer = $event->getComposer();
        $io = $event->getIO();

        (new Plugin)->activate($composer, $io);
    }
}
