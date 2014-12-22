<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class HealthFormMedicalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('doctor', 'text');
        
        $builder->add('doctorPhone', 'text', array(
            'label' => 'Phone'
        ));
        
        $builder->add('doctorAddress', 'textarea', array(
            'label' => 'Address'
        ));
        
        $builder->add('allergies', 'textarea');
        $builder->add('dietary', 'textarea');
    }

    public function getName()
    {
        return 'healthFormMedical';
    }
}
