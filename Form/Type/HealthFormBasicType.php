<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class HealthFormBasicType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('knownAs', 'text', array(
            'required' => false,
            'label' => 'Known As (optional)'
        ));
        
        $builder->add('dateOfBirth', 'birthday', array(
            'format' => 'dd-MM-yyyy'
        ));
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $healthForm = $event->getData();
            $form = $event->getForm();
            
            $form->add('membershipNumber', 'text', array(
                'required' => !$healthForm->getParticipant()->getYoungPerson()
            ));
        });
        
        $builder->add('address', 'textarea');
        
        $builder->add('phone', 'text');
    }

    public function getName()
    {
        return 'healthFormBasic';
    }
}
