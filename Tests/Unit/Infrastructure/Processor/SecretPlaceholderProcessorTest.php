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

    #[Test]
    public function resolvesTheSecretThroughTheFileEnvProvider(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'secret');
        self::assertIsString($file);
        // Trailing newline on purpose: files written by an editor or by
        // `echo` carry one, and the value must arrive without it.
        file_put_contents($file, "s3cr3t\n");
        putenv('SECRET_PROCESSOR_TEST_FILE=' . $file);

        try {
            self::assertSame('s3cr3t', $this->processor->process('SECRET_PROCESSOR_TEST', []));
        } finally {
            putenv('SECRET_PROCESSOR_TEST_FILE');
            unlink($file);
        }
    }

    /**
     * The empty string is the documented fallback, not an oversight — see the
     * note on reportMiss(). It is pinned here because the value is silent by
     * nature: nothing downstream can tell it apart from a secret that really
     * is empty, so a change of mind about it has to be a deliberate one.
     */
    #[Test]
    public function anUnresolvableSecretYieldsAnEmptyStringRatherThanFailing(): void
    {
        putenv('SECRET_PROCESSOR_ABSENT_FILE');

        self::assertSame('', $this->processor->process('SECRET_PROCESSOR_ABSENT', []));
    }

    #[Test]
    public function anInvalidKeyIsRejectedInsteadOfSilentlyResolvingToNothing(): void
    {
        // Path traversal must not reach the /run/secrets provider. Failing
        // loudly here is right: the key is malformed, not merely absent.
        $this->expectException(\InvalidArgumentException::class);

        $this->processor->process('runsecrets:../../etc/passwd', []);
    }
}
