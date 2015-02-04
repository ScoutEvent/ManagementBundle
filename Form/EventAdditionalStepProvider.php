<?php

namespace ScoutEvent\ManagementBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

abstract class EventAdditionalStepProvider
{

    abstract public function addAdditional(FormBuilderInterface $builder);
    abstract public function remove($event);
    abstract public function persist($form, $event);

}
