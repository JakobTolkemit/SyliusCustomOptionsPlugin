<?php

declare(strict_types=1);

namespace Brille24\SyliusCustomerOptionsPlugin\Form\PriceImport;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductListPriceImportType extends AbstractType
{
    /** {@inheritdoc} */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('products', TextType::class)
            ->add('customer_option_value_price', CustomerOptionValuePriceType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class)
        ;

        $builder->get('products')->addModelTransformer(new CallbackTransformer(
            static function ($productArray) {
                if (!is_array($productArray)) {
                    return '';
                }

                return implode(', ', $productArray);
            },
            static function (string $productsAsString) {
                return explode(', ', $productsAsString);
            }
        ));
    }
}