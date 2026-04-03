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
    // --- Legacy cascade tests ---

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
        $provider1 = $this->createStub(SecretProviderInterface::class);
        $provider1->method('supports')->willReturn(true);
        $provider1->method('resolve')->willReturn('secret-value');
        $provider1->method('getName')->willReturn('');

        $provider2 = $this->createStub(SecretProviderInterface::class);
        $provider2->method('supports')->willReturn(true);
        $provider2->method('resolve')->willReturn('other-value');
        $provider2->method('getName')->willReturn('');

        $service = new SecretResolverService([$provider1, $provider2]);
        $key = new SecretKey('DB_PASSWORD');

        self::assertSame('secret-value', $service->resolve($key));
    }

    #[Test]
    public function resolveSkipsUnsupportedProviders(): void
    {
        $unsupported = $this->createMock(SecretProviderInterface::class);
        $unsupported->method('supports')->willReturn(false);
        $unsupported->method('getName')->willReturn('');
        $unsupported->expects(self::never())->method('resolve');

        $supported = $this->createStub(SecretProviderInterface::class);
        $supported->method('supports')->willReturn(true);
        $supported->method('resolve')->willReturn('found');
        $supported->method('getName')->willReturn('');

        $service = new SecretResolverService([$unsupported, $supported]);
        $key = new SecretKey('API_KEY');

        self::assertSame('found', $service->resolve($key));
    }

    #[Test]
    public function resolveSkipsProviderReturningNull(): void
    {
        $nullProvider = $this->createStub(SecretProviderInterface::class);
        $nullProvider->method('supports')->willReturn(true);
        $nullProvider->method('resolve')->willReturn(null);
        $nullProvider->method('getName')->willReturn('');

        $valueProvider = $this->createStub(SecretProviderInterface::class);
        $valueProvider->method('supports')->willReturn(true);
        $valueProvider->method('resolve')->willReturn('fallback-value');
        $valueProvider->method('getName')->willReturn('');

        $service = new SecretResolverService([$nullProvider, $valueProvider]);
        $key = new SecretKey('SOME_SECRET');

        self::assertSame('fallback-value', $service->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenAllProvidersReturnNull(): void
    {
        $provider = $this->createStub(SecretProviderInterface::class);
        $provider->method('supports')->willReturn(true);
        $provider->method('resolve')->willReturn(null);
        $provider->method('getName')->willReturn('');

        $service = new SecretResolverService([$provider]);
        $key = new SecretKey('MISSING');

        self::assertNull($service->resolve($key));
    }

    // --- Extended format: targeted provider resolution ---

    #[Test]
    public function resolveTargetsProviderByName(): void
    {
        $fileEnv = $this->createStub(SecretProviderInterface::class);
        $fileEnv->method('getName')->willReturn('');
        $fileEnv->method('supports')->willReturn(true);
        $fileEnv->method('resolve')->willReturn('file-value');

        $vault = $this->createStub(SecretProviderInterface::class);
        $vault->method('getName')->willReturn('vault');
        $vault->method('supports')->willReturn(true);
        $vault->method('resolve')->willReturn('vault-value');

        $service = new SecretResolverService([$fileEnv, $vault]);
        $key = new SecretKey('vault:DB_PASSWORD');

        self::assertSame('vault-value', $service->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenTargetedProviderNotFound(): void
    {
        $fileEnv = $this->createStub(SecretProviderInterface::class);
        $fileEnv->method('getName')->willReturn('');

        $service = new SecretResolverService([$fileEnv]);
        $key = new SecretKey('vault:DB_PASSWORD');

        self::assertNull($service->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenTargetedProviderDoesNotSupport(): void
    {
        $vault = $this->createStub(SecretProviderInterface::class);
        $vault->method('getName')->willReturn('vault');
        $vault->method('supports')->willReturn(false);

        $service = new SecretResolverService([$vault]);
        $key = new SecretKey('vault:MISSING_SECRET');

        self::assertNull($service->resolve($key));
    }

    // --- SubKey extraction ---

    #[Test]
    public function resolveExtractsSubKeyFromJsonResponse(): void
    {
        $vault = $this->createStub(SecretProviderInterface::class);
        $vault->method('getName')->willReturn('vault');
        $vault->method('supports')->willReturn(true);
        $vault->method('resolve')->willReturn('{"password":"s3cret","username":"admin"}');

        $service = new SecretResolverService([$vault]);
        $key = new SecretKey('vault:kv-v2/db.password');

        self::assertSame('s3cret', $service->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenSubKeyNotInJson(): void
    {
        $vault = $this->createStub(SecretProviderInterface::class);
        $vault->method('getName')->willReturn('vault');
        $vault->method('supports')->willReturn(true);
        $vault->method('resolve')->willReturn('{"other":"value"}');

        $service = new SecretResolverService([$vault]);
        $key = new SecretKey('vault:kv-v2/db.password');

        self::assertNull($service->resolve($key));
    }

    #[Test]
    public function resolveReturnsNullWhenResponseIsNotJson(): void
    {
        $vault = $this->createStub(SecretProviderInterface::class);
        $vault->method('getName')->willReturn('vault');
        $vault->method('supports')->willReturn(true);
        $vault->method('resolve')->willReturn('plain-text-value');

        $service = new SecretResolverService([$vault]);
        $key = new SecretKey('vault:kv-v2/db.password');

        self::assertNull($service->resolve($key));
    }
}
