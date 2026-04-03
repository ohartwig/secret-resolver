<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Domain\Contract;

use Moselwal\SecretResolver\Domain\ValueObject\SecretKey;

interface SecretProviderInterface
{
    public function supports(SecretKey $key): bool;

    public function resolve(SecretKey $key): ?string;

    public static function priority(): int;

    /**
     * Provider name for targeted resolution via extended key format.
     * Return empty string to participate only in the cascade (default behavior).
     * Example: 'vault', 'aws', 'azure'
     */
    public function getName(): string;
}
