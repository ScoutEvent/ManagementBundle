<?php

namespace ScoutEvent\ManagementBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

use ScoutEvent\ManagementBundle\Form\Type\HealthFormBasicType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormMedicalType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormEmergencyType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormSignatureType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormSwimmingType;

class HealthFormFlow extends FormFlow {

    private $swimming;

    public function __construct() {
        $this->swimming = false;
    }

    public function setSwimming($swimming) {
        $this->swimming = $swimming;
    }

    public function getName() {
        return 'healthForm';
    }

    protected function loadStepsConfig() {
        return array(
            array(
                'label' => 'Basic Details',
                'type' => new HealthFormBasicType()
            ),
            array(
                'label' => 'Emergency Contact',
                'type' => new HealthFormEmergencyType()
            ),
            array(
                'label' => 'Medical Details',
                'type' => new HealthFormMedicalType()
            ),
            array(
                'label' => 'Swimming',
                'type' => new HealthFormSwimmingType(),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return !$this->swimming;
                }
            ),
            array(
                'label' => 'Signature',
                'type' => new HealthFormSignatureType()
            ),
        );
    }
}
