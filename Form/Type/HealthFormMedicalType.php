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
        
        $builder->add('allergies', 'textarea', array(
            'label' => 'Allergies (leave blank if not applicable)',
            'required' => false
        ));
        $builder->add('dietary', 'textarea', array(
            'label' => 'Dietary (leave blank if not applicable)',
            'required' => false
        ));
        $builder->add('medical', 'textarea', array(
            'label' => 'Medical (leave blank if not applicable)',
            'required' => false
        ));
    }

    public function getName()
    {
        return 'healthFormMedical';
    }
}
