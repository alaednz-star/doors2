<?php

namespace App\Controllers\Admin;

class UsageController extends LookupController
{
    // "Door Usage" is stored in the existing door_types table.
    protected function table(): string     { return 'door_types'; }
    protected function routeBase(): string { return '/door-showroom/admin/usages'; }
    protected function singular(): string  { return 'Door Usage'; }
    protected function plural(): string    { return 'Door Usages'; }
    protected function navKey(): string    { return 'usages'; }
    protected function viewDir(): string   { return 'lookup'; }

    protected function blockingReference(int $id): ?string
    {
        $n = (int) $this->scalar(
            'SELECT COUNT(*) FROM price_rules WHERE door_type_id = ?', [$id]
        );
        return $n > 0
            ? "This usage is used in {$n} pricing rule(s). Deactivate it instead, or remove those rules first."
            : null;
    }
}
