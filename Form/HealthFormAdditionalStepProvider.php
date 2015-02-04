<?php

namespace ScoutEvent\ManagementBundle\Form;

abstract class HealthFormAdditionalStepProvider
{

    abstract public function getSteps();
    abstract public function additionalProcess($form, $healthFormEntity);

}
