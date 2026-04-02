<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Tests\Unit\Domain\ValueObject;

use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SecretKey::class)]
final class SecretKeyTest extends TestCase
{
    #[Test]
    public function constructorNormalizesUpperCase(): void
    {
        $key = new SecretKey('db_password');

        self::assertSame('DB_PASSWORD', $key->upperCase);
    }

    #[Test]
    public function constructorNormalizesLowerCase(): void
    {
        $key = new SecretKey('DB_PASSWORD');

        self::assertSame('db_password', $key->lowerCase);
    }

    #[Test]
    public function rawValueIsPreserved(): void
    {
        $key = new SecretKey('My_Mixed_Case_Key');

        self::assertSame('My_Mixed_Case_Key', $key->raw);
    }

    /** @return array<string, array{string, string, string}> */
    public static function keyVariantsProvider(): array
    {
        return [
            'lower input' => ['db_password', 'DB_PASSWORD', 'db_password'],
            'upper input' => ['DB_PASSWORD', 'DB_PASSWORD', 'db_password'],
            'mixed input' => ['Api_Key', 'API_KEY', 'api_key'],
        ];
    }

    #[Test]
    #[DataProvider('keyVariantsProvider')]
    public function allDerivedValuesAreCorrect(
        string $raw,
        string $expectedUpper,
        string $expectedLower,
    ): void {
        $key = new SecretKey($raw);

        self::assertSame($expectedUpper, $key->upperCase);
        self::assertSame($expectedLower, $key->lowerCase);
    }

    /** @return array<string, array{string}> */
    public static function invalidKeyProvider(): array
    {
        return [
            'empty string' => [''],
            'starts with digit' => ['1PASSWORD'],
            'starts with underscore' => ['_SECRET'],
            'contains dot' => ['DB.PASSWORD'],
            'contains slash' => ['../etc/passwd'],
            'contains space' => ['DB PASSWORD'],
            'contains hyphen' => ['db-password'],
            'path traversal' => ['../../etc/shadow'],
            'null byte simulation' => ["KEY\x00"],
        ];
    }

    #[Test]
    #[DataProvider('invalidKeyProvider')]
    public function constructorRejectsInvalidKeys(string $raw): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1743500100);

        new SecretKey($raw);
    }

    #[Test]
    public function constructorRejectsKeyExceedingMaxLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1743500100);

        new SecretKey(str_repeat('A', 129));
    }

    #[Test]
    public function constructorAcceptsKeyAtMaxLength(): void
    {
        $key = new SecretKey(str_repeat('A', 128));

        self::assertSame(str_repeat('A', 128), $key->raw);
    }
}
