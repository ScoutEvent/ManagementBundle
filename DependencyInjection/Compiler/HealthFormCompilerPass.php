<?php

namespace ScoutEvent\ManagementBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class HealthFormCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('scout.form.flow.healthForm.additionalChain')) {
            return;
        }

        $definition = $container->getDefinition(
            'scout.form.flow.healthForm.additionalChain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'scout.form.flow.healthForm.stage'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addStage',
                array(new Reference($id))
            );
        }
    }
}
