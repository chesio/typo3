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

namespace TYPO3\CMS\Form\Tests\Unit\Mvc;

use Prophecy\PhpUnit\ProphecyTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;
use TYPO3\CMS\Form\Mvc\ProcessingRule;
use TYPO3\CMS\Form\Tests\Unit\Mvc\Fixtures\AnotherTestValidator;
use TYPO3\CMS\Form\Tests\Unit\Mvc\Validation\Fixtures\TestValidator;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Test case
 */
class ProcessingRuleTest extends UnitTestCase
{
    use ProphecyTrait;

    /**
     * @test
     */
    public function addValidatorAddsValidator(): void
    {
        GeneralUtility::addInstance(ConjunctionValidator::class, new ConjunctionValidator([]));
        $subject = new ProcessingRule($this->prophesize(PropertyMapper::class)->reveal());
        $subject->addValidator(new TestValidator());
        $validators = $subject->getValidators();
        $validators->rewind();
        self::assertInstanceOf(AbstractValidator::class, $validators->current());
    }

    /**
     * @test
     */
    public function removeAllRemovesAllValidators(): void
    {
        $subject = new ProcessingRule($this->prophesize(PropertyMapper::class)->reveal());
        $subject->addValidator(new TestValidator());
        $subject->addValidator(new AnotherTestValidator());
        $subject->addValidator(new TestValidator());
        $subject->removeAllValidators();
        self::assertCount(0, $subject->getValidators());
    }

    /**
     * @test
     */
    public function filterValidatorRemovesValidatorsDependingOnClosure(): void
    {
        $subject = new ProcessingRule($this->prophesize(PropertyMapper::class)->reveal());
        $subject->addValidator(new TestValidator());
        $subject->addValidator(new AnotherTestValidator());
        $subject->addValidator(new TestValidator());
        $subject->filterValidators(static function ($validator) {
            return $validator instanceof AnotherTestValidator;
        });
        $validators = $subject->getValidators();
        self::assertCount(1, $validators);
        self::assertInstanceOf(AnotherTestValidator::class, $validators->current());
    }

    /**
     * @test
     */
    public function processNoPropertyMappingReturnsNotModifiedValue(): void
    {
        GeneralUtility::addInstance(ConjunctionValidator::class, new ConjunctionValidator([]));
        $processingRule = new ProcessingRule($this->prophesize(PropertyMapper::class)->reveal());
        $input = 'someValue';
        self::assertSame($input, $processingRule->process($input));
    }

    /**
     * @test
     */
    public function processNoPropertyMappingAndHasErrorsIfValidatorContainsErrors(): void
    {
        GeneralUtility::addInstance(ConjunctionValidator::class, new ConjunctionValidator([]));
        $processingRule = new ProcessingRule($this->prophesize(PropertyMapper::class)->reveal());
        $processingRule->addValidator(new TestValidator());
        $input = 'addError';
        $processingRule->process($input);
        self::assertTrue($processingRule->getProcessingMessages()->hasErrors());
    }
}
