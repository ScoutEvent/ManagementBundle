<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('startTime', 'datetime', array(
            'date_format' => 'dd-MM-yyyy'
        ));
        $builder->add('endTime', 'datetime', array(
            'date_format' => 'dd-MM-yyyy'
        ));
        $builder->add('coordinator', 'text');
        $builder->add('owner', 'entity', array(
            'class' => 'ScoutEventBaseBundle:User',
            'label' => 'User'
        ));
        $builder->add('location', 'textarea');
        $builder->add('summary', 'textarea');
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $eventEntity = $event->getData();
            $form = $event->getForm();

            if (!$eventEntity || null === $eventEntity->getId()) {
                // New event
                $form->add('Create', 'submit', array(
                    'attr'=> array(
                        'class' => 'btn btn-success pull-right'
                    )
                ));
            } else {
                // Existing event
                $form->add('Edit', 'submit', array(
                    'attr' => array(
                        'class' => 'btn btn-success pull-right'
                    )
                ));
                $form->add('Delete', 'submit', array(
                    'attr' => array(
                        'class' => 'btn btn-warning pull-right',
                        'onclick' => 'return confirm("Are you sure?")'
                    )
                ));
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'ScoutEvent\DataBundle\Entity\Event'
        ));
    }

    public function getName()
    {
        return 'event';
    }
}
