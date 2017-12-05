<?php

namespace Phug\Partial;

use Phug\Renderer;
use Phug\Util\ModuleInterface;

trait ExtensionsTrait
{
    /**
     * List of global extensions. Class names that add custom behaviors to the engine.
     *
     * @var array
     */
    private static $extensions = [];

    private static function normalizeExtensionClassName($name)
    {
        return ltrim('\\', strtolower($name));
    }

    private static function getExtensionsGetters()
    {
        return [
            'includes'            => 'getIncludes',
            'scanners'            => 'getScanners',
            'token_handlers'      => 'getTokenHandlers',
            'node_compilers'      => 'getCompilers',
            'formats'             => 'getFormats',
            'patterns'            => 'getPatterns',
            'filters'             => 'getFilters',
            'keywords'            => 'getKeywords',
            'element_handlers'    => 'getElementHandlers',
            'php_token_handlers'  => 'getPhpTokenHandlers',
            'assignment_handlers' => 'getAssignmentHandlers',
        ];
    }

    private static function removeExtensionFromCurrentRenderer($extensionClassName)
    {
        /* @var Renderer $renderer */
        $renderer = self::$renderer;

        if (is_a($extensionClassName, ModuleInterface::class, true)) {
            $renderer->setOption(
                'modules',
                array_filter($renderer->getOption('modules'), function ($module) use ($extensionClassName) {
                    return $module !== $extensionClassName;
                })
            );

            return;
        }

        $extension = new $extensionClassName();
        foreach (['getOptions', 'getEvents'] as $method) {
            static::removeOptions([], $extension->$method());
        }
        foreach (static::getExtensionsGetters() as $option => $method) {
            static::removeOptions([$option], $extension->$method());
        }
        $rendererClassName = self::getRendererClassName();
        $renderer->setOptionsDefaults((new $rendererClassName())->getOptions());
    }

    private static function extractExtensionOptions(&$options, $extensionClassName, $methods)
    {
        $extension = is_string($extensionClassName)
            ? new $extensionClassName()
            : $extensionClassName;
        foreach (['getOptions', 'getEvents'] as $method) {
            $value = $extension->$method();
            if (!empty($value)) {
                $options = array_merge_recursive($options, $value);
            }
        }
        foreach ($methods as $option => $method) {
            $value = $extension->$method();
            if (!empty($value)) {
                $options = array_merge_recursive($options, [$option => $value]);
            }
        }
    }

    /**
     * Get options from extensions list and default options.
     *
     * @param array $extensions list of extensions instances of class names
     * @param array $options    optional default options to merge with
     *
     * @return array
     */
    public static function getExtensionsOptions(array $extensions, array $options = [])
    {
        $methods = static::getExtensionsGetters();
        foreach ($extensions as $extensionClassName) {
            if (is_a($extensionClassName, ModuleInterface::class, true)) {
                if (!isset($options['modules'])) {
                    $options['modules'] = [];
                }
                $options['modules'][] = $extensionClassName;

                continue;
            }

            static::extractExtensionOptions($options, $extensionClassName, $methods);
        }

        return $options;
    }
}