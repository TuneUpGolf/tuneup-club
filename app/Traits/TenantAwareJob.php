<?php

namespace App\Traits;

use Stancl\Tenancy\Tenancy;

trait TenantAwareJob
{
    public $tenantId;

    public function initializeTenantContext()
    {
        if ($this->tenantId) {
            tenancy()->initialize($this->tenantId);
        }
    }

    public function dispatchWithTenant()
    {
        $this->tenantId = tenant('id'); // save tenant id
        dispatch($this);
    }
}
