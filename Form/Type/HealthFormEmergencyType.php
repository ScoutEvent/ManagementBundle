<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HealthFormEmergencyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('emergencyContact', 'text');
        
        $builder->add('emergencyPhone', 'text', array(
            'label' => 'Phone'
        ));
    }

    public function getName()
    {
        return 'healthFormEmergency';
    }
}
