<?php

namespace ScoutEvent\ManagementBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class HealthFormSignatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('picture', new StoredFileType(), array(
            'required' => false
        ));
        
        $builder->add('signature', 'text');
    }

    public function getName()
    {
        return 'healthFormSignature';
    }
}
