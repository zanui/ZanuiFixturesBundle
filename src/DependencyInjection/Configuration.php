<?php
/**
 * Zanui (http://www.zanui.com.au/)
 *
 * @link      http://github.com/zanui/shop for the canonical source repository
 * @copyright Copyright (c) 2011-2014 Internet Services Australia 3 Pty Limited (http://www.zanui.com.au)
 * @license   The MIT License (MIT)
 */

namespace Zanui\FixturesBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zanui_fixtures');

        $rootNode
            ->children()
                ->scalarNode('entity_namespace_fallback')
                    ->info('Defines a namespace to load entities from when one is not declared in the fixture class.')
                    ->example('Acme\HelloBundle\Entity')
                ->end()
                ->scalarNode('base_order_fallback')
                    ->info('Defines a base order for loading fixtures when one is not declared in the fixture class.')
                    ->defaultValue(1)
                ->end()
            ->end();

        return $treeBuilder;
    }
}
