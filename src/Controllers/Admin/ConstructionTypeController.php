<?php

namespace App\Controllers\Admin;

use App\Core\Database;

class ConstructionTypeController extends LookupController
{
    protected function table(): string     { return 'construction_types'; }
    protected function routeBase(): string { return '/door-showroom/admin/construction-types'; }
    protected function singular(): string  { return 'Construction Type'; }
    protected function plural(): string    { return 'Construction Types'; }
    protected function navKey(): string    { return 'construction-types'; }
    protected function viewDir(): string   { return 'lookup'; }

    protected function hasImage(): bool       { return true; }
    protected function uploadDir(): string    { return APP_ROOT . '/public/uploads/construction'; }
    protected function uploadWebPath(): string{ return '/door-showroom/uploads/construction'; }
    protected function imageField(): string   { return 'construction_image'; }

    protected function blockingReference(int $id): ?string
    {
        $n = (int) $this->scalar(
            'SELECT COUNT(*) FROM price_rules WHERE construction_type_id = ?', [$id]
        );
        return $n > 0
            ? "This construction type is used in {$n} pricing rule(s). Deactivate it instead, or remove those rules first."
            : null;
    }
}
