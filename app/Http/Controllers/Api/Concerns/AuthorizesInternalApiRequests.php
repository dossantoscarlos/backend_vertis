<?php

namespace App\Http\Controllers\Api\Concerns;

use Illuminate\Http\Request;

trait AuthorizesInternalApiRequests
{
    private function authorizeInternalRequest(Request $request): void
    {
        $secret = config('acl.internal_secret');

        if (! is_string($secret) || $secret === '') {
            abort_if(app()->isProduction(), 403);

            return;
        }

        $providedSecret = $request->header('X-Internal-Secret', '');

        abort_unless(is_string($providedSecret) && hash_equals($secret, $providedSecret), 403);
    }
}
