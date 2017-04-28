<?php

namespace Robo\Composer;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;
use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\DependencyResolver\Operation\UpdateOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Installer\PackageEvents;
use Composer\Script\ScriptEvents;
use Composer\Util\ProcessExecutor;
use Composer\Util\Filesystem;

/**
 * Composer plugin for managing Robo plugins.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

    /**
     * @var Composer
     */
    protected $composer;
    /**
     * @var IOInterface
     */
    protected $io;
    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;
    /**
     * @var ProcessExecutor
     */
    protected $executor;

    /**
     * @var
     */
    protected $roboPlugins = [];

    /**
     * Apply plugin modifications to Composer.
     *
     * @param Composer $composer
     * @param IOInterface $io
     */
    public function activate(Composer $composer, IOInterface $io) {
        $this->composer = $composer;
        $this->io = $io;
        $this->eventDispatcher = $composer->getEventDispatcher();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     */
    public static function getSubscribedEvents() {
        return array(
          PackageEvents::POST_PACKAGE_INSTALL => "onPostPackageEvent",
          PackageEvents::POST_PACKAGE_UPDATE => "onPostPackageEvent",
          ScriptEvents::POST_UPDATE_CMD => 'onPostCmdEvent',
        );
    }

    /**
     * Marks the Robo plugin to be processed after an install or update command.
     *
     * This event will be called for each composer package that is updated during
     * a given `composer install`. We use this hook to gather an array of all
     * Robo plugins, which will then be processed in onPostCmdEvent().
     *
     * @param \Composer\Installer\PackageEvent $event
     */
    public function onPostPackageEvent(PackageEvent $event) {
        $package = $this->getRoboPlugin($event->getOperation());
        if ($package) {
            $this->roboPlugins[] = $package;
        }
    }

    /**
     * Execute blt update after update command has been executed, if applicable.
     *
     * @param \Composer\Script\Event $event
     */
    public function onPostCmdEvent(Event $event) {
        if (!empty($this->roboPlugins)) {
            foreach ($this->roboPlugins as $plugin) {
                $this->installOrUpdateRoboPlugin($plugin);
            }
        }
    }

    /**
     * Gets the Robo Plugin, if it is the package that is being operated on.
     *
     * @param $operation
     *
     * @return mixed
     */
    protected function getRoboPlugin($operation) {
        if ($operation instanceof InstallOperation) {
            $package = $operation->getPackage();
        }
        elseif ($operation instanceof UpdateOperation) {
            $package = $operation->getTargetPackage();
        }

        if (isset($package) && $this->packageIsRoboPlugin($package)) {
            return $package;
        }

        return NULL;
    }

    /**
     * Determines if a given Composer package is a Robo plugin.
     *
     * @param $package
     *
     * @return bool
     */
    protected function packageIsRoboPlugin($package) {
        if ($package instanceof PackageInterface) {
            // A package must have extra.Robo defined in composer.json in order
            // to be considered a Robo plugin.
            $extra = $package->getExtra();
            if (!empty($extra['Robo'])) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @param $package
     */
    protected function installOrUpdateRoboPlugin($package) {
        // @todo Call the plugin's install method.
    }
}