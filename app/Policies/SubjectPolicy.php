<?php

namespace App\Policies;

use App\Concerns\ChecksSystemConfigPermission;

class SubjectPolicy
{
    use ChecksSystemConfigPermission;
}
