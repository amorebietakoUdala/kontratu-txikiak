<?php

namespace App\Form;

use App\Entity\Contract;
use App\Entity\ContractType;
use App\Entity\DurationType;
use App\Entity\IdentificationType;
use App\Validator\IsValidDNI;
use App\Validator\IsValidExpCode;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

class ContractFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $disabled = $options['disabled'];
        $builder
            ->add('code', null,[
                'label' => 'contract.code',
                'disabled' => $disabled,
                // 'constraints' => [
                //     new IsValidExpCode(),
                // ]
            ])
            ->add('subjectEs', null, [
                'label' => 'contract.subjectEs',
                'disabled' => $disabled,
            ])
            ->add('subjectEu', null,[
                'label' => 'contract.subjectEu',
                'disabled' => $disabled,
            ])
            ->add('amountWithVAT', NumberType::class,[
                'label' => 'contract.amountWithVAT',
                'disabled' => $disabled,
                'constraints' => [
                    new Positive(),
                    new LessThanOrEqual('48400'),
                ]
            ])
            ->add('duration', NumberType::class,[
                'label' => 'contract.duration',
                'disabled' => $disabled,
                'constraints' => [
                    new Positive(),
                ]
            ])
            ->add('idNumber', null,[
                'label' => 'contract.idNumber',
                'disabled' => $disabled,
                'constraints' => [
                    new IsValidDNI(),
                ]
            ])
            ->add('enterprise', null,[
                'label' => 'contract.enterprise',
                'disabled' => $disabled,
            ])
            ->add('awardDate', DateTimeType::class,[
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'yyyy-MM-dd',
                'attr' => ['class' => 'js-datepicker'],
                'label' => 'contract.awardDate',
                'disabled' => $disabled,
            ])
            ->add('type', EntityType::class,[
                'label' => 'contract.type',
                'class' => ContractType::class,
                'choice_label' =>  function ($type) {
                    return $type->getName();
                },
            ])
            ->add('durationType', EntityType::class,[
                'label' => 'contract.durationType',
                'class' => DurationType::class,
                'choice_label' =>  function ($type) {
                    return $type->getName();
                },
            ])
            ->add('identificationType', EntityType::class,[
                'label' => 'contract.identificationType',
                'class' => IdentificationType::class,
                'choice_label' =>  function ($type) {
                    return $type->getName();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contract::class,
            'locale' => 'es',
            'disabled' => false,
        ]);
    }
}
