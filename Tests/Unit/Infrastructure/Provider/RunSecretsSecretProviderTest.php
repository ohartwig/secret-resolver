<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Tests\Unit\Infrastructure\Provider;

use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use Moselwal\SecretResolver\Infrastructure\Provider\RunSecretsSecretProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RunSecretsSecretProvider::class)]
final class RunSecretsSecretProviderTest extends TestCase
{
    private RunSecretsSecretProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new RunSecretsSecretProvider();
    }

    #[Test]
    public function priorityIs20(): void
    {
        self::assertSame(20, $this->provider->priority());
    }

    #[Test]
    public function supportsReturnsFalseWhenPathNotReadable(): void
    {
        $key = new SecretKey('NONEXISTENT_SECRET_XYZ_12345');

        self::assertFalse($this->provider->supports($key));
    }

    #[Test]
    public function resolveReturnsNullWhenPathNotReadable(): void
    {
        $key = new SecretKey('NONEXISTENT_SECRET_XYZ_12345');

        self::assertNull($this->provider->resolve($key));
    }
}
