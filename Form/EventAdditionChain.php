<?php

namespace ScoutEvent\ManagementBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

class EventAdditionChain
{

    private $additions;

    public function __construct()
    {
        $this->additions = array();
    }

    public function addStage(EventAdditionalStepProvider $addition)
    {
        $this->additions[] = $addition;
    }
    
    public function addAdditional(FormBuilderInterface $builder)
    {
        foreach ($this->additions as $stage)
        {
            $stage->addAdditional($builder);
        }
    }
    
    public function persist($form, $event)
    {
        foreach ($this->additions as $stage)
        {
            $stage->persist($form, $event);
        }
    }
    
    public function remove($event)
    {
        foreach ($this->additions as $stage)
        {
            $stage->remove($event);
        }
    }

}