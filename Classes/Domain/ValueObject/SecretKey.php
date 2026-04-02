<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Domain\ValueObject;

final readonly class SecretKey
{
    private const MAX_LENGTH = 128;
    private const VALID_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';

    public string $upperCase;
    public string $lowerCase;

    public function __construct(
        public string $raw,
    ) {
        if ($raw === '' || strlen($raw) > self::MAX_LENGTH || preg_match(self::VALID_PATTERN, $raw) !== 1) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Secret key must match %s and not exceed %d characters, got: "%s"',
                    self::VALID_PATTERN,
                    self::MAX_LENGTH,
                    $raw,
                ),
                1743500100,
            );
        }

        $this->upperCase = strtoupper($raw);
        $this->lowerCase = strtolower($raw);
    }
}
