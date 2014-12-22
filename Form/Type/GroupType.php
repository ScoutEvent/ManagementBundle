<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text');
        $builder->add('contact', 'text');
        $builder->add('owner', 'entity', array(
            'class' => 'ScoutEventBaseBundle:User',
            'label' => 'User'
        ));
        $builder->add('phone', 'text');
        
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $entity = $event->getData();
            $form = $event->getForm();

            if (!$entity || null === $entity->getId()) {
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
            'data_class' => 'ScoutEvent\DataBundle\Entity\GroupUnit'
        ));
    }

    public function getName()
    {
        return 'group';
    }
}
