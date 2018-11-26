<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 19:53
 */

namespace EmmabotBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class InputProcessorCompilerPass
 *
 * This class is used for Input processors to be registered
 * by services. All Input processors to be registered need to have the tag
 * "emmabot.processor.input"
 *
 * Example:
 * ```yml
 * emmabot.processor.ner:
 *   public: false
 *   class: EmmabotBundle\InputProcessor\EntityExtractionInputProcessor
 *   arguments:
 *   - "@core_nlp"
 *   tags:
 *   - { name: emmabot.processor.input, priority: 100 }
 * ```
 *
 * @package EmmabotBundle\DependencyInjection
 */
class InputProcessorCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        // always first check if the primary service is defined
        if (!$container->has('emmabot.input_chain')) {
            return;
        }

        $definition = $container->findDefinition('emmabot.input_chain');

        // find all service IDs with the emmabot.processor.input tag
        $taggedServices = $container->findTaggedServiceIds('emmabot.processor.input');

        foreach ($taggedServices as $id => $tags) {
            // add the processor service to the ChainTransport service
            foreach ($tags as $attributes) {

                $definition->addMethodCall('addProcessor', array(
                    new Reference($id),
                    $attributes["priority"]
                ));
            }
        }
    }
}