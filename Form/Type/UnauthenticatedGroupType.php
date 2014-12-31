<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use ScoutEvent\ManagementBundle\Form\DataTransformer\EmailUserTransformer;

class UnauthenticatedGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'label' => 'Group name'
        ));
        $builder->add('contact', 'text');
        $transformer = new EmailUserTransformer($options['em']);
        $builder->add($builder->create('owner', 'email', array(
            'label' => 'Email'
        ))->addModelTransformer($transformer));
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
        ))->setRequired(array(
            'em'
        ))->setAllowedTypes(array(
            'em' => 'Doctrine\Common\Persistence\ObjectManager'
        ));
    }

    public function getName()
    {
        return 'group';
    }
}
