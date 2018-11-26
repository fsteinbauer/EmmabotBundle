<?php
/**
 * User: Florian Steinbauer <florian@acid-design.at>
 * Date: 15.05.2018
 * Time: 22:03
 */

namespace EmmabotBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class IntentCompilerPass
 *
 * This class is used to register intents as services.
 * To be registered, they need to have the tag
 * "emmabot.intent"
 *
 * Example:
 * ```yml
 * emmabot.intent.help:
 *   class: EmmabotBundle\Intent\HelpIntent
 *   tags:
 *   - { name: emmabot.intent, id: "help" }
 * ```
 *
 * @package EmmabotBundle\DependencyInjection
 */
class IntentCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {

        // always first check if the primary service is defined
        if (!$container->has('emmabot.intent_selector')) {
            return;
        }

        $definition = $container->findDefinition('emmabot.intent_selector');

        // find all service IDs with the emmabot.processor.input tag
        $taggedServices = $container->findTaggedServiceIds('emmabot.intent');

        foreach ($taggedServices as $id => $tags) {
            // add the processor service to the ChainTransport service
            foreach ($tags as $attributes) {

                $definition->addMethodCall('addIntent', array(
                    new Reference($id),
                    $attributes["id"]
                ));
            }
        }
    }
}