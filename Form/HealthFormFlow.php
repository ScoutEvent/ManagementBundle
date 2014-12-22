<?php

namespace ScoutEvent\ManagementBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

use ScoutEvent\ManagementBundle\Form\Type\HealthFormBasicType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormMedicalType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormEmergencyType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormSignatureType;

class HealthFormFlow extends FormFlow {

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
                'label' => 'Signature',
                'type' => new HealthFormSignatureType()
            ),
        );
    }
}
