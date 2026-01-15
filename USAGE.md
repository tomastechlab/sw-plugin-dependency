# Example Usage

## Installation

Add this plugin to your Shopware project's composer.json:

```bash
composer require tomastechlab/shopware-plugin-dependency
```

## How It Works

After running `composer install` or `composer update`, the plugin will automatically:

1. Scan all installed Shopware plugins (those with `type: "shopware-platform-plugin"` in their composer.json)
2. Parse their `require` section to identify plugin dependencies
3. Build a dependency graph
4. Perform topological sort to determine the correct activation order
5. Execute `php bin/console plugin:install --activate` for each plugin in order

## Example Plugin Setup

### Plugin A (No dependencies)
```json
{
    "name": "myshop/plugin-a",
    "type": "shopware-platform-plugin",
    "require": {
        "shopware/core": "6.4.*"
    }
}
```

### Plugin B (Depends on A)
```json
{
    "name": "myshop/plugin-b",
    "type": "shopware-platform-plugin",
    "require": {
        "shopware/core": "6.4.*",
        "myshop/plugin-a": "^1.0"
    }
}
```

### Plugin C (Depends on A and B)
```json
{
    "name": "myshop/plugin-c",
    "type": "shopware-platform-plugin",
    "require": {
        "shopware/core": "6.4.*",
        "myshop/plugin-a": "^1.0",
        "myshop/plugin-b": "^1.0"
    }
}
```

## Activation Order

The plugin will activate them in this order:

1. `myshop/plugin-a` (no dependencies)
2. `myshop/plugin-b` (depends on A)
3. `myshop/plugin-c` (depends on A and B)

This ensures that when `plugin-b` is activated, `plugin-a` is already active, and when `plugin-c` is activated, both `plugin-a` and `plugin-b` are already active.

## Output Example

```
> Shopware Plugin Dependency Resolver: Activating plugins...
  - Activating plugin: myshop/plugin-a
  - Activating plugin: myshop/plugin-b
  - Activating plugin: myshop/plugin-c
> Shopware Plugin Dependency Resolver: All plugins activated successfully.
```

## Circular Dependencies

If a circular dependency is detected (e.g., A depends on B, and B depends on A), the plugin will:

1. Detect the circular dependency
2. Display a warning message
3. Fall back to activating plugins in the order they were discovered

## Already Active Plugins

Plugins that are already installed and active will be skipped.

<<<<<<< HEAD
## Disabling Automatic Activation

You can disable the plugin's automatic activation behavior in two ways:

### Method 1: Using Environment Variable (Temporary)

```bash
# Skip plugin activation for a single composer install
SW_PLUGIN_DEP_SKIP=1 composer install

# Skip plugin activation for a single composer update
SW_PLUGIN_DEP_SKIP=true composer update

# Alternative accepted values: "yes", "true", "1"

# Export globally to skip for all subsequent composer commands
export SW_PLUGIN_DEP_SKIP=1
composer install
composer update
```

**Example output when disabled:**
```
> Shopware Plugin Dependency Resolver: Plugin is disabled, skipping activation.
```


### Method 2: Using Composer Configuration (Permanent)

For projects where you never want automatic plugin activation. Add this to your project's `composer.json`:

```json
{
    "extra": {
        "shopware-plugin-dependency": {
            "enabled": false
        }
    }
}
```
### Combining Both Methods

Both methods can be used together. The environment variable takes precedence over the composer.json configuration:

```json
// composer.json has plugin enabled
{
    "extra": {
        "shopware-plugin-dependency": {
            "enabled": true
        }
    }
}
```

```bash
# But environment variable can override it for a single command
SW_PLUGIN_DEP_SKIP=1 composer install  # Plugin will be skipped
composer update  # Plugin will run normally
```
=======
## Manual Activation

If you prefer not to use automatic activation, you can manually activate plugins in dependency order using the topological sort algorithm provided by this plugin.
>>>>>>> fe55a25 (initial version of plugin dependency resolver)

## Configuration

The plugin looks for Shopware plugins in these locations:
- `custom/plugins/` (standard Shopware location)
- `custom/static-plugins/` (static plugins)
- `vendor/` (composer-installed plugins)

## Troubleshooting

### Plugin not activating?

Check the verbose output by running with the `-v` flag:

```bash
composer update -v
```

<<<<<<< HEAD
=======
### Wrong activation order?

Ensure your plugin dependencies are correctly specified in the `require` section of each plugin's `composer.json`.

>>>>>>> fe55a25 (initial version of plugin dependency resolver)
### Shopware console not found?

The plugin expects the Shopware console at `bin/console`. If your console is in a different location, you can modify the `PluginActivator` class to use a different path.
