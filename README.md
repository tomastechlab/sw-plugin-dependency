# Shopware Plugin Dependency Resolver

Composer plugin to automatically activate Shopware plugins in the correct dependency order after `composer install` or `composer update`.

## Features

- Automatically resolves plugin dependencies from composer.json
- Performs topological sort to determine correct activation order
- Activates plugins in dependency order to prevent activation failures
- Handles circular dependency detection
- Skips already installed/activated plugins
- Optional disabling via environment variable or configuration


## Installation

```bash
composer require tomastechlab/shopware-plugin-dependency
```

## Usage

The plugin automatically runs after `composer install` and `composer update`. It will:

1. Scan all installed Shopware plugins (type: `shopware-platform-plugin`)
2. Parse their composer.json `require` section for plugin dependencies
3. Build a dependency graph and perform topological sort
4. Activate plugins in the correct order using `php bin/console plugin:install --activate`

## Disabling the Plugin

You can disable the automatic plugin activation in two ways:

### Option 1: Environment Variable (Temporary Disabling)

Set the `SW_PLUGIN_DEP_SKIP` environment variable:

```bash
# Disable for a single command
SW_PLUGIN_DEP_SKIP=1 composer install

# Disable for a single command (alternative values)
SW_PLUGIN_DEP_SKIP=true composer install
SW_PLUGIN_DEP_SKIP=yes composer install

# Export globally to disable for all commands
export SW_PLUGIN_DEP_SKIP=1
composer install
composer update
```

This is useful when you want to temporarily disable the plugin without modifying your project's composer.json.

### Option 2: Composer Configuration (Permanent Disabling)

Add this configuration to your project's composer.json:

```json
{
    "extra": {
        "shopware-plugin-dependency": {
            "enabled": false
        }
    }
}
```

This is useful when you want to permanently disable the plugin for a specific project.

## Configuration

The plugin looks for plugins in the default Shopware locations:
- `custom/plugins/`
- `custom/static-plugins/`
- `vendor/` (for composer-installed plugins)

## Requirements

- PHP 7.4 or higher
- Composer 2.0 or higher
- Shopware 6

## License
MIT
