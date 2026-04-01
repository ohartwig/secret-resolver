<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2026 Moselwal Digitalagentur <info@moselwal.de>
 * SPDX-License-Identifier: GPL-2.0-or-later
 */

namespace Moselwal\SecretResolver\Domain\Enum;

enum SecretSource: string
{
    case FileEnv = 'file_env';
    case RunSecrets = 'run_secrets';
}
