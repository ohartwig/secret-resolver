<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Tests\Unit\Infrastructure\Provider;

use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use Moselwal\SecretResolver\Infrastructure\Provider\FileEnvSecretProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FileEnvSecretProvider::class)]
final class FileEnvSecretProviderTest extends TestCase
{
    private FileEnvSecretProvider $provider;
    private string $tempFile = '';

    protected function setUp(): void
    {
        $this->provider = new FileEnvSecretProvider();
    }

    protected function tearDown(): void
    {
        if ($this->tempFile !== '' && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }

        putenv('TEST_SECRET_FILE');
    }

    #[Test]
    public function priorityIs30(): void
    {
        self::assertSame(30, $this->provider->priority());
    }

    #[Test]
    public function supportsReturnsFalseWhenEnvNotSet(): void
    {
        $key = new SecretKey('TEST_SECRET');

        self::assertFalse($this->provider->supports($key));
    }

    #[Test]
    public function supportsReturnsTrueWhenEnvPointsToReadableFile(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'secret_test_');
        file_put_contents($this->tempFile, 'secret-value');
        putenv('TEST_SECRET_FILE=' . $this->tempFile);

        $key = new SecretKey('TEST_SECRET');

        self::assertTrue($this->provider->supports($key));
    }

    #[Test]
    public function resolveReturnsFileContent(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'secret_test_');
        file_put_contents($this->tempFile, "  my-secret-value  \n");
        putenv('TEST_SECRET_FILE=' . $this->tempFile);

        $key = new SecretKey('TEST_SECRET');

        self::assertSame('my-secret-value', $this->provider->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullForEmptyFile(): void
    {
        $this->tempFile = tempnam(sys_get_temp_dir(), 'secret_test_');
        file_put_contents($this->tempFile, '   ');
        putenv('TEST_SECRET_FILE=' . $this->tempFile);

        $key = new SecretKey('TEST_SECRET');

        self::assertNull($this->provider->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenEnvNotSet(): void
    {
        $key = new SecretKey('TEST_SECRET');

        self::assertNull($this->provider->resolve($key));
    }
}
