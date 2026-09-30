<?php

namespace App\Exceptions;

use RuntimeException;

class AuditRestoreException extends RuntimeException
{
    // Expected failures that prevent an audit entry from being restored safely.
}
