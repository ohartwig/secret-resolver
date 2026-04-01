<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Tests\Unit\Application\Service;

use Moselwal\SecretResolver\Application\Service\SecretResolverService;
use Moselwal\SecretResolver\Domain\Contract\SecretProviderInterface;
use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecretResolverService::class)]
final class SecretResolverServiceTest extends TestCase
{
    #[Test]
    public function resolveReturnsNullWhenNoProviders(): void
    {
        $service = new SecretResolverService([]);
        $key = new SecretKey('DB_PASSWORD');

        self::assertNull($service->resolve($key));
    }

    #[Test]
    public function resolveReturnsValueFromFirstSupportingProvider(): void
    {
        $provider1 = $this->createMock(SecretProviderInterface::class);
        $provider1->method('supports')->willReturn(true);
        $provider1->method('resolve')->willReturn('secret-value');

        $provider2 = $this->createMock(SecretProviderInterface::class);
        $provider2->method('supports')->willReturn(true);
        $provider2->method('resolve')->willReturn('other-value');

        $service = new SecretResolverService([$provider1, $provider2]);
        $key = new SecretKey('DB_PASSWORD');

        self::assertSame('secret-value', $service->resolve($key));
    }

    #[Test]
    public function resolveSkipsUnsupportedProviders(): void
    {
        $unsupported = $this->createMock(SecretProviderInterface::class);
        $unsupported->method('supports')->willReturn(false);
        $unsupported->expects(self::never())->method('resolve');

        $supported = $this->createMock(SecretProviderInterface::class);
        $supported->method('supports')->willReturn(true);
        $supported->method('resolve')->willReturn('found');

        $service = new SecretResolverService([$unsupported, $supported]);
        $key = new SecretKey('API_KEY');

        self::assertSame('found', $service->resolve($key));
    }

    #[Test]
    public function resolveSkipsProviderReturningNull(): void
    {
        $nullProvider = $this->createMock(SecretProviderInterface::class);
        $nullProvider->method('supports')->willReturn(true);
        $nullProvider->method('resolve')->willReturn(null);

        $valueProvider = $this->createMock(SecretProviderInterface::class);
        $valueProvider->method('supports')->willReturn(true);
        $valueProvider->method('resolve')->willReturn('fallback-value');

        $service = new SecretResolverService([$nullProvider, $valueProvider]);
        $key = new SecretKey('SOME_SECRET');

        self::assertSame('fallback-value', $service->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenAllProvidersReturnNull(): void
    {
        $provider = $this->createMock(SecretProviderInterface::class);
        $provider->method('supports')->willReturn(true);
        $provider->method('resolve')->willReturn(null);

        $service = new SecretResolverService([$provider]);
        $key = new SecretKey('MISSING');

        self::assertNull($service->resolve($key));
    }
}
