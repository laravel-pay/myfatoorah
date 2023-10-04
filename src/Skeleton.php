<?php

namespace VendorName\Skeleton;

use VendorName\Skeleton\Common\Contracts\RequiredFields;
use VendorName\Skeleton\Common\Traits\HasHandelRedirects;
use VendorName\Skeleton\Common\Traits\HasRequiredFields;

class Skeleton implements RequiredFields
{
    use HasHandelRedirects;
    use HasRequiredFields;

    public function __construct()
    {
        $this->handelRedirects();
    }

    public function requiredFields(): array
    {
        return [];
    }
}
