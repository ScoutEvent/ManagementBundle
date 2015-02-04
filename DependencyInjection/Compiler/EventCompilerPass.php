<?php

namespace ScoutEvent\ManagementBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class EventCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scout.event.additionChain')) {
            return;
        }

        $definition = $container->getDefinition(
            'scout.event.additionChain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'scout.event.addition'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addStage',
                array(new Reference($id))
            );
        }
    }
}
