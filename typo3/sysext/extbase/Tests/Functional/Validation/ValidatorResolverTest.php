<?php

declare(strict_types=1);

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace TYPO3\CMS\Extbase\Tests\Functional\Validation;

use ExtbaseTeam\TestValidators\Domain\Model\AnotherModel;
use ExtbaseTeam\TestValidators\Domain\Model\Model;
use ExtbaseTeam\TestValidators\Validation\Validator\CustomValidator;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Extbase\Reflection\ReflectionService;
use TYPO3\CMS\Extbase\Validation\Validator\CollectionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class ValidatorResolverTest extends FunctionalTestCase
{
    /**
     * @var bool Speed up this test case, it needs no database
     */
    protected bool $initializeDatabase = false;

    protected array $testExtensionsToLoad = [
        'typo3/sysext/extbase/Tests/Functional/Fixtures/Extensions/test_validators',
    ];

    /**
     * @test
     */
    public function createValidatorSetsOptionsAndDependenciesAreInjected(): void
    {
        $subject = $this->get(ValidatorResolver::class);
        $options = ['foo' => 'bar'];
        /** @var CustomValidator $validator */
        $validator = $subject->createValidator(CustomValidator::class, $options);
        self::assertSame($options, $validator->getOptions());
        self::assertInstanceOf(IconFactory::class, $validator->iconFactory);
    }

    /**
     * @test
     */
    public function buildBaseValidatorConjunctionAddsValidatorsDefinedByAnnotationsInTheClassToTheReturnedConjunction(): void
    {
        $subject = $this->getAccessibleMock(
            ValidatorResolver::class,
            ['dummy'],
            [$this->get(ReflectionService::class)]
        );

        $subject->_call(
            'buildBaseValidatorConjunction',
            Model::class,
            Model::class
        );

        /** @var array $baseValidatorConjunctions */
        $baseValidatorConjunctions = $subject->_get('baseValidatorConjunctions');
        self::assertIsArray($baseValidatorConjunctions);
        self::assertCount(2, $baseValidatorConjunctions);
        self::assertArrayHasKey(Model::class, $baseValidatorConjunctions);
        self::assertArrayHasKey(AnotherModel::class, $baseValidatorConjunctions);

        /** @var ConjunctionValidator $conjunctionValidator */
        $conjunctionValidator = $baseValidatorConjunctions[Model::class];
        self::assertInstanceOf(ConjunctionValidator::class, $conjunctionValidator);

        $baseValidators = $conjunctionValidator->getValidators();
        self::assertInstanceOf(\SplObjectStorage::class, $baseValidators);
        $baseValidators->rewind();

        /** @var GenericObjectValidator $validator */
        $validator = $baseValidators->current();
        self::assertInstanceOf(GenericObjectValidator::class, $validator);

        $propertyValidators = $validator->getPropertyValidators();
        self::assertCount(4, $propertyValidators);
        self::assertArrayHasKey('foo', $propertyValidators);
        self::assertArrayHasKey('bar', $propertyValidators);
        self::assertArrayHasKey('baz', $propertyValidators);
        self::assertArrayHasKey('qux', $propertyValidators);

        /** @var \SplObjectStorage $fooPropertyValidators */
        $fooPropertyValidators = $propertyValidators['foo'];
        self::assertInstanceOf(\SplObjectStorage::class, $fooPropertyValidators);
        self::assertCount(3, $fooPropertyValidators);

        $fooPropertyValidators->rewind();
        /** @var StringLengthValidator $propertyValidator */
        $propertyValidator = $fooPropertyValidators->current();
        self::assertInstanceOf(StringLengthValidator::class, $propertyValidator);
        self::assertSame(['minimum' => 1, 'maximum' => PHP_INT_MAX], $propertyValidator->getOptions());

        $fooPropertyValidators->next();
        /** @var StringLengthValidator $propertyValidator */
        $propertyValidator = $fooPropertyValidators->current();
        self::assertInstanceOf(StringLengthValidator::class, $propertyValidator);
        self::assertSame(['minimum' => 0, 'maximum' => 10], $propertyValidator->getOptions());

        $fooPropertyValidators->next();
        $propertyValidator = $fooPropertyValidators->current();
        self::assertInstanceOf(NotEmptyValidator::class, $propertyValidator);

        /** @var \SplObjectStorage $barPropertyValidators */
        $barPropertyValidators = $propertyValidators['bar'];
        self::assertInstanceOf(\SplObjectStorage::class, $barPropertyValidators);
        self::assertCount(1, $barPropertyValidators);

        $barPropertyValidators->rewind();
        $propertyValidator = $barPropertyValidators->current();
        self::assertInstanceOf(CustomValidator::class, $propertyValidator);

        /** @var \SplObjectStorage $bazPropertyValidators */
        $bazPropertyValidators = $propertyValidators['baz'];
        self::assertInstanceOf(\SplObjectStorage::class, $bazPropertyValidators);
        self::assertCount(1, $bazPropertyValidators);

        $bazPropertyValidators->rewind();
        $propertyValidator = $bazPropertyValidators->current();
        self::assertInstanceOf(CollectionValidator::class, $propertyValidator);

        /** @var \SplObjectStorage $quxPropertyValidators */
        $quxPropertyValidators = $propertyValidators['qux'];
        self::assertInstanceOf(\SplObjectStorage::class, $quxPropertyValidators);
        self::assertCount(1, $quxPropertyValidators);

        $quxPropertyValidators->rewind();
        $propertyValidator = $quxPropertyValidators->current();
        self::assertInstanceOf(ConjunctionValidator::class, $propertyValidator);
        self::assertSame(
            $baseValidatorConjunctions[AnotherModel::class],
            $propertyValidator
        );
    }
}
