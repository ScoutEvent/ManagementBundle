<?php

namespace ScoutEvent\ManagementBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use ScoutEvent\ManagementBundle\DependencyInjection\Compiler\HealthFormCompilerPass;
use ScoutEvent\ManagementBundle\DependencyInjection\Compiler\EventCompilerPass;


class ScoutEventManagementBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new HealthFormCompilerPass());
        $container->addCompilerPass(new EventCompilerPass());
    }
    
}
