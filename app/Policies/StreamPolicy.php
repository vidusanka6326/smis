<?php

namespace App\Policies;

use App\Concerns\ChecksSystemConfigPermission;

class StreamPolicy
{
    use ChecksSystemConfigPermission;
}
