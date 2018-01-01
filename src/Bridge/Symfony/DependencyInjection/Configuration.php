<?php

namespace Translation\PlatformAdapter\Sheet\Bridge\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $root = $treeBuilder->root('translation_adapter_sheet');

        $root->children()
            ->scalarNode('httplug_client')->defaultNull()->end()
            ->scalarNode('httplug_message_factory')->defaultNull()->end()
            ->scalarNode('httplug_uri_factory')->defaultNull()->end()
            ->append($this->getSheetNode())
        ->end();

        return $treeBuilder;
    }

    /**
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getSheetNode()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('sheet');

        return $node;
    }
}
