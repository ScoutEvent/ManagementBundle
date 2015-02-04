<?php

namespace ScoutEvent\ManagementBundle\Form;

use Craue\FormFlowBundle\Form\FormFlow;

use ScoutEvent\ManagementBundle\Form\Type\HealthFormBasicType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormMedicalType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormEmergencyType;
use ScoutEvent\ManagementBundle\Form\Type\HealthFormSignatureType;

class HealthFormFlow extends FormFlow {

    private $additionProvider;

    public function __construct(HealthFormAdditionalChain $additionProvider)
    {
        $this->additionProvider = $additionProvider;
    }

    public function getName() {
        return 'healthForm';
    }

    protected function loadStepsConfig() {
        $basic = array(
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
            )
        );
        
        $additions = $this->additionProvider->getAdditions();
        
        $end = array(
            array(
                'label' => 'Signature',
                'type' => new HealthFormSignatureType()
            )
        );
        
        return array_merge($basic, $additions, $end);
    }
}
