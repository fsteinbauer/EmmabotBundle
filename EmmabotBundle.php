<?php

namespace EmmabotBundle;

use EmmabotBundle\DependencyInjection\InputProcessorCompilerPass;
use EmmabotBundle\DependencyInjection\IntentCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EmmabotBundle
 *
 * @package EmmabotBundle
 */
class EmmabotBundle extends Bundle
{
    /**
     * Builds the bundle.
     *
     * It is only ever called once when the cache is empty.
     *
     * This method can be overridden to register compilation passes,
     * other extensions, ...
     *
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new InputProcessorCompilerPass());
        $container->addCompilerPass(new IntentCompilerPass());
    }
}
