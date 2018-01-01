<?php

namespace Translation\PlatformAdapter\Sheet\Bridge\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Translation\PlatformAdapter\Sheet\Sheet;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class TranslationAdapterSheetExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $sheetConf = $config['sheet'];

        $adapterDef = $container->register('php_translation.adapter.sheet');
        $adapterDef
            ->setClass(Sheet::class)
            ->addArgument($sheetConf);
    }
}
