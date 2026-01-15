<?php

declare(strict_types=1);

namespace TomasTechLab\ShopwarePluginDependency;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use MarcJ\TopSort\Element;
use MarcJ\TopSort\TopologicalSorter;

class DependencyResolver
{
    private Composer $composer;
    private IOInterface $io;
    private array $plugins = [];
    private array $pluginPaths = [
        'custom/plugins',
        'custom/static-plugins',
        'vendor',
    ];

    public function __construct(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function resolveActivationOrder(): array
    {
        $this->discoverPlugins();
        
        if (empty($this->plugins)) {
            return [];
        }

        return $this->topologicalSort();
    }

    private function discoverPlugins(): void
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $rootDir = dirname($vendorDir);

        foreach ($this->pluginPaths as $relativePath) {
            $pluginPath = $rootDir . '/' . $relativePath;
            
            if (!is_dir($pluginPath)) {
                continue;
            }

            $this->scanDirectory($pluginPath);
        }
    }

    private function scanDirectory(string $path): void
    {
        foreach (scandir($path) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path . '/' . $item;
            
            if (is_dir($itemPath)) {
                $composerJsonPath = $itemPath . '/composer.json';
                
                if (file_exists($composerJsonPath)) {
                    $this->parsePlugin($itemPath, $composerJsonPath);
                }
            }
        }
    }

    private function parsePlugin(string $pluginPath, string $composerJsonPath): void
    {
        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $type = $composerJson['type'] ?? '';
        
        if ($type !== 'shopware-platform-plugin') {
            return;
        }

        $pluginName = $composerJson['name'] ?? '';
        
        if (empty($pluginName)) {
            return;
        }

        $dependencies = [];
        $require = $composerJson['require'] ?? [];

        foreach ($require as $depName => $version) {
            if ($depName === 'shopware/core') {
                continue;
            }

            if (preg_match('/^[a-z0-9_-]+\/[a-z0-9_-]+$/i', $depName)) {
                $dependencies[] = $depName;
            }
        }

        $this->plugins[$pluginName] = [
            'name' => $pluginName,
            'dependencies' => $dependencies,
            'path' => $pluginPath,
        ];
    }

    private function topologicalSort(): array
    {
        $sorter = new TopologicalSorter();

        foreach ($this->plugins as $pluginName => $plugin) {
            $sorter->add($pluginName);
        }

        foreach ($this->plugins as $pluginName => $plugin) {
            foreach ($plugin['dependencies'] as $dependency) {
                if (isset($this->plugins[$dependency])) {
                    $sorter->add($dependency, $pluginName);
                }
            }
        }

        try {
            $sorted = $sorter->sort();
            return $sorted;
        } catch (\Exception $e) {
            $this->io->writeError('<warning>Circular dependency detected: ' . $e->getMessage() . '</warning>');
            $this->io->writeError('<warning>Falling back to installation order...</warning>');
            
            return array_keys($this->plugins);
        }
    }

    public function getPlugins(): array
    {
        return $this->plugins;
    }
}
