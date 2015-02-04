<?php

namespace ScoutEvent\ManagementBundle\Form;

use Symfony\Component\Form\AbstractType;

class HealthFormAdditionalChain
{

    private $additions;

    public function __construct()
    {
        $this->additions = array();
    }

    public function addStage(HealthFormAdditionalStepProvider $addition)
    {
        $this->additions[] = $addition;
    }
    
    public function getAdditions()
    {
        $stages = array();
        foreach ($this->additions as $stage)
        {
            $stages = array_merge($stages, $stage->getSteps());
        }
        return $stages;
    }
    
    public function additionalProcess($form, $healthFormEntity)
    {
        foreach ($this->additions as $stage)
        {
            $stage->additionalProcess($form, $healthFormEntity);
        }
    }

}