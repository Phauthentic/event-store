<?php

declare(strict_types=1);

namespace Phauthentic\EventStore;

/**
 *
 */
enum SQLDialect: string
{
    case Standard = 'standard';

    case MSSQL = 'sqlite';
}
