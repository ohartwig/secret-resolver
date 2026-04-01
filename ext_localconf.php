<?php

declare(strict_types=1);

use Moselwal\SecretResolver\Infrastructure\Processor\SecretPlaceholderProcessor;
use TYPO3\CMS\Core\Configuration\Processor\Placeholder\EnvVariableProcessor;
use TYPO3\CMS\Core\Configuration\Processor\Placeholder\ValueFromReferenceArrayProcessor;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['yamlLoader']['placeholderProcessors'][SecretPlaceholderProcessor::class] = [
    'after' => [
        EnvVariableProcessor::class,
    ],
    'before' => [
        ValueFromReferenceArrayProcessor::class,
    ],
];
