<?php

return [
    // Empty locally. IPs/CIDRs separated by commas, or an explicit '*' only
    // behind an edge that overwrites forwarded headers and cannot be bypassed.
    'proxies' => env('TRUSTED_PROXIES'),
];
