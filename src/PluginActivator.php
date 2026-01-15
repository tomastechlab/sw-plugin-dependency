<?php

declare(strict_types=1);

namespace TomasTechLab\ShopwarePluginDependency;

use Composer\IO\IOInterface;

class PluginActivator
{
    private IOInterface $io;
    private string $consolePath;
    private array $installedPlugins = [];

    public function __construct(IOInterface $io, ?string $consolePath = null)
    {
        $this->io = $io;
        $this->consolePath = $consolePath ?? 'bin/console';
        $this->loadInstalledPlugins();
    }

    public function activate(string $pluginName): bool
    {
        if ($this->isPluginActive($pluginName)) {
            $this->io->write(sprintf('  - Plugin <info>%s</info> already active, skipping', $pluginName), true, IOInterface::VERBOSE);
            return true;
        }

        $this->io->write(sprintf('  - Activating plugin: <info>%s</info>', $pluginName));

        $command = sprintf('php %s plugin:install --activate %s', $this->consolePath, escapeshellarg($pluginName));

        $output = $returnCode = null;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            $this->io->writeError(sprintf('  - Failed to activate plugin <error>%s</error>: %s', $pluginName, implode("\n", $output)), true, IOInterface::VERBOSE);
            return false;
        }

        $this->installedPlugins[] = $pluginName;

        return true;
    }

    private function loadInstalledPlugins(): void
    {
        if (!file_exists($this->consolePath)) {
            return;
        }

        $command = sprintf('php %s plugin:list --json', $this->consolePath);
        
        $output = $returnCode = null;
        exec($command . ' 2>&1', $output, $returnCode);

        if ($returnCode !== 0) {
            return;
        }

        $json = implode("\n", $output);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['elements'])) {
            return;
        }

        foreach ($data['elements'] as $plugin) {
            if (isset($plugin['active']) && $plugin['active'] === true) {
                $this->installedPlugins[] = $plugin['name'];
            }
        }
    }

    private function isPluginActive(string $pluginName): bool
    {
        return in_array($pluginName, $this->installedPlugins, true);
    }

    public function getConsolePath(): string
    {
        return $this->consolePath;
    }

    public function setConsolePath(string $consolePath): void
    {
        $this->consolePath = $consolePath;
    }
}
