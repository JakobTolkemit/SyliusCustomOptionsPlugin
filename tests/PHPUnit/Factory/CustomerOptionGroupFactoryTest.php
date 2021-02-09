<?php

declare(strict_types=1);

namespace Tests\Brille24\SyliusCustomerOptionsPlugin\PHPUnit\Factory;

use Brille24\SyliusCustomerOptionsPlugin\Entity\CustomerOptions\CustomerOption;
use Brille24\SyliusCustomerOptionsPlugin\Entity\CustomerOptions\CustomerOptionGroup;
use Brille24\SyliusCustomerOptionsPlugin\Entity\CustomerOptions\Validator\ValidatorInterface;
use Brille24\SyliusCustomerOptionsPlugin\Entity\Product;
use Brille24\SyliusCustomerOptionsPlugin\Factory\CustomerOptionGroupFactory;
use Brille24\SyliusCustomerOptionsPlugin\Repository\CustomerOptionRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

class CustomerOptionGroupFactoryTest extends TestCase
{
    /** @var CustomerOptionGroupFactory */
    private $customerOptionGroupFactory;

    /** @var MockObject */
    private $customerOptionRepositoryMock;

    /** @var MockObject */
    private $productRepositoryMock;

    public function setUp(): void
    {
        $this->customerOptionRepositoryMock = $this->createMock(CustomerOptionRepositoryInterface::class);
        $this->customerOptionRepositoryMock->method('findAll')->willReturn([]);

        $this->productRepositoryMock = $this->createMock(ProductRepositoryInterface::class);
        $this->productRepositoryMock->method('findAll')->willReturn([]);

        $this->customerOptionGroupFactory = new CustomerOptionGroupFactory(
            $this->customerOptionRepositoryMock,
            $this->productRepositoryMock
        );
    }

    /**
     * @test
     */
    public function testGenerateRandom()
    {
        $this->productRepositoryMock
            ->expects($this->any())
            ->method('findBy')
            ->with(['code' => []])
            ->willReturn([])
        ;

        $amount = 7;

        $customerOptionGroups = $this->customerOptionGroupFactory->generateRandom($amount);

        $this->assertCount($amount, $customerOptionGroups);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateWithValidOptions()
    {
        $this->productRepositoryMock
            ->expects($this->any())
            ->method('findBy')
            ->with(['code' => []])
            ->willReturn([])
        ;

        $options = [
            'code'         => 'some_group',
            'translations' => [
                'en_US' => 'Some Group',
            ],
            'options'  => [],
            'products' => [],
        ];

        $customerOptionGroup = $this->customerOptionGroupFactory->createFromConfig($options);

        $this->assertInstanceOf(CustomerOptionGroup::class, $customerOptionGroup);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateWithoutCode()
    {
        $this->productRepositoryMock
            ->expects($this->any())
            ->method('findBy')
            ->with(['code' => []])
            ->willReturn([])
        ;

        $options = [
            'translations' => [
                'en_US' => 'Abc',
            ],
        ];

        $customerOptionGroup = $this->customerOptionGroupFactory->createFromConfig($options);

        $this->assertInstanceOf(CustomerOptionGroup::class, $customerOptionGroup);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateWithoutTranslations()
    {
        $this->productRepositoryMock
            ->expects($this->any())
            ->method('findBy')
            ->with(['code' => []])
            ->willReturn([])
        ;

        $options = [
            'code' => 'some_group',
        ];

        $this->expectException(\Exception::class);

        $customerOptionGroup = $this->customerOptionGroupFactory->createFromConfig($options);

        $this->assertNull($customerOptionGroup);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateWithOptions()
    {
        $this->productRepositoryMock
            ->expects($this->any())
            ->method('findBy')
            ->with(['code' => []])
            ->willReturn([])
        ;

        $optionCodes = [
            'option_1',
            'option_2',
            'option_3',
        ];

        $args = [];
        $vals = [];
        foreach ($optionCodes as $index => $code) {
            $option = new CustomerOption();
            $option->setCode($code);

            $args[] = [$code];
            $vals[] = $option;
        }

        $this->customerOptionRepositoryMock
            ->method('findOneByCode')
            ->withConsecutive(...$args)
            ->willReturnOnConsecutiveCalls(...$vals)
        ;

        $options = [
            'code'         => 'some_group',
            'translations' => [
                'en_US' => 'Some Group',
            ],
            'options' => $optionCodes,
        ];

        $customerOptionGroup = $this->customerOptionGroupFactory->createFromConfig($options);

        $this->assertCount(3, $customerOptionGroup->getOptions());
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateWithProducts()
    {
        $productCodes = [
            'product_1',
            'product_2',
            'product_3',
        ];

        $mockProducts = [];
        foreach ($productCodes as $code) {
            $product = new Product();
            $product->setCode($code);
            $mockProducts[] = $product;
        }

        $this->productRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['code' => $productCodes])
            ->willReturn($mockProducts)
        ;

        $options = [
            'code'         => 'some_group',
            'translations' => [
                'en_US' => 'Some Group',
            ],
            'products' => $productCodes,
        ];

        $customerOptionGroup = $this->customerOptionGroupFactory->createFromConfig($options);

        $this->assertCount(3, $customerOptionGroup->getProducts());
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateWithConditionalConstraint()
    {
        $this->productRepositoryMock
            ->expects($this->any())
            ->method('findBy')
            ->with(['code' => []])
            ->willReturn([])
        ;

        $optionCodes = [
            'option_1',
            'option_2',
            'option_3',
        ];

        $customerOptions = [];

        foreach ($optionCodes as $index => $code) {
            $option = new CustomerOption();
            $option->setCode($code);

            $customerOptions[] = $option;
        }

        $this->customerOptionRepositoryMock
            ->method('findOneByCode')
            ->withConsecutive(
                ['option_1'],
                ['option_2'],
                ['option_3'],
                ['option_1'],
                ['option_2'],
                ['option_2'],
                ['option_3']
            )
            ->willReturnOnConsecutiveCalls(
                $customerOptions[0],
                $customerOptions[1],
                $customerOptions[2],
                $customerOptions[0],
                $customerOptions[1],
                $customerOptions[1],
                $customerOptions[2]
            )
        ;

        $options = [
            'code'         => 'some_group',
            'translations' => [
                'en_US' => 'Some Group',
            ],
            'options'    => $optionCodes,
            'validators' => [
                [
                    'conditions' => [
                        [
                            'customer_option' => 'option_1',
                            'comparator'      => 'greater',
                            'value'           => '4',
                        ],
                    ],
                    'constraints' => [
                        [
                            'customer_option' => 'option_2',
                            'comparator'      => 'lesser',
                            'value'           => '10',
                        ],
                        [
                            'customer_option' => 'option_2',
                            'comparator'      => 'greater',
                            'value'           => '0',
                        ],
                        [
                            'customer_option' => 'option_3',
                            'comparator'      => 'equal',
                            'value'           => '5',
                        ],
                    ],
                    'error_messages' => [
                        'en_US' => 'Test',
                        'de_DE' => 'Test2',
                    ],
                ],
            ],
        ];

        $customerOptionGroup = $this->customerOptionGroupFactory->createFromConfig($options);

        $this->assertCount(1, $customerOptionGroup->getValidators());

        /** @var ValidatorInterface $validator */
        $validator = $customerOptionGroup->getValidators()[0];

        $this->assertCount(1, $validator->getConditions());
        $this->assertCount(3, $validator->getConstraints());
    }
}
