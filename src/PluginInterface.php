<?php

namespace Robo;

use Composer\IO\IOInterface;

interface PluginInterface {

    /**
     * This method is called every time that the plugin is installed or updated.
     *
     * @param \Composer\IO\IOInterface $io
     * @param array $extra
     *   The extra.robo array from the root project's composer.json.
     *
     * @return mixed
     */
    public function install(IOInterface $io, array $extra);
}