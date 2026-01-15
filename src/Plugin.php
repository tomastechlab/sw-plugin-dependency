<?php

declare(strict_types=1);

namespace TomasTechLab\ShopwarePluginDependency;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;

class Plugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;
    private IOInterface $io;

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->composer = $composer;
        $this->io = $io;
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => 'activatePlugins',
            ScriptEvents::POST_UPDATE_CMD => 'activatePlugins',
        ];
    }

    public function activatePlugins(Event $event): void
    {
<<<<<<< HEAD
        if (!$this->isPluginEnabled()) {
            $this->io->write('<info>Shopware Plugin Dependency Resolver: Plugin is disabled, skipping activation.</info>');
            return;
        }

=======
>>>>>>> fe55a25 (initial version of plugin dependency resolver)
        $this->io->write('<info>Shopware Plugin Dependency Resolver: Activating plugins...</info>');

        try {
            $resolver = new DependencyResolver($this->composer, $this->io);
            $activator = new PluginActivator($this->io);

            $activationOrder = $resolver->resolveActivationOrder();

            if (empty($activationOrder)) {
                $this->io->write('<info>No Shopware plugins to activate.</info>');
                return;
            }

            foreach ($activationOrder as $pluginName) {
                $activator->activate($pluginName);
            }

            $this->io->write('<info>Shopware Plugin Dependency Resolver: All plugins activated successfully.</info>');
        } catch (\Exception $e) {
            $this->io->writeError('<error>Shopware Plugin Dependency Resolver: ' . $e->getMessage() . '</error>');
        }
    }
<<<<<<< HEAD

    private function isPluginEnabled(): bool
    {
        if ($this->isDisabledByEnvironmentVariable()) {
            return false;
        }

        if ($this->isDisabledByComposerConfig()) {
            return false;
        }

        return true;
    }

    private function isDisabledByEnvironmentVariable(): bool
    {
        $skip = (string) getenv('SW_PLUGIN_DEP_SKIP');
        $disabled = strtolower($skip);

        return in_array($disabled, ['1', 'true', 'yes', 'on'], true);
    }

    private function isDisabledByComposerConfig(): bool
    {
        $extra = $this->composer->getPackage()->getExtra();

        $config = $extra['shopware-plugin-dependency'] ?? null;

        if (!is_array($config)) {
            return false;
        }

        return ($config['enabled'] ?? true) === false;
    }
=======
>>>>>>> fe55a25 (initial version of plugin dependency resolver)
}
