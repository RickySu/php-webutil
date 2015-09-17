<?php

namespace WebUtil\Script;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class EnvCheck
{

    static public function preAutoloadDump(Event $event)
    {
        $composer = $event->getComposer();
        $package = $composer->getPackage();
        $autoload = $package->getAutoload();
        if (defined('HHVM_VERSION')) {
            $autoload['psr-4']['WebUtil\\'] = 'hacksrc';
            $package->setAutoload($autoload);
        }
    }

}
