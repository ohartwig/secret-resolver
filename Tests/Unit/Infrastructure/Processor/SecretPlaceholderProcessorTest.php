<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Tests\Unit\Infrastructure\Processor;

use Moselwal\SecretResolver\Infrastructure\Processor\SecretPlaceholderProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecretPlaceholderProcessor::class)]
final class SecretPlaceholderProcessorTest extends TestCase
{
    private SecretPlaceholderProcessor $processor;

    protected function setUp(): void
    {
        $this->processor = new SecretPlaceholderProcessor();
    }

    #[Test]
    public function canProcessReturnsTrueForSecretPlaceholder(): void
    {
        self::assertTrue(
            $this->processor->canProcess('%secret(DB_PASSWORD)%', [])
        );
    }

    #[Test]
    public function canProcessReturnsFalseForEnvPlaceholder(): void
    {
        self::assertFalse(
            $this->processor->canProcess('%env(DB_PASSWORD)%', [])
        );
    }

    #[Test]
    public function canProcessReturnsFalseForPlainPlaceholder(): void
    {
        self::assertFalse(
            $this->processor->canProcess('%someValue%', [])
        );
    }
}
