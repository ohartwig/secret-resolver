<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Domain\ValueObject;

final readonly class SecretKey
{
    public string $upperCase;
    public string $lowerCase;
    public string $fileEnvKey;
    public string $runSecretsPath;

    public function __construct(
        public string $raw,
    ) {
        $this->upperCase = strtoupper($raw);
        $this->lowerCase = strtolower($raw);
        $this->fileEnvKey = $this->upperCase . '_FILE';
        $this->runSecretsPath = '/run/secrets/' . $this->lowerCase;
    }
}
