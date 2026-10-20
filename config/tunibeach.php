<?php

return [
    'cache_ttl' => max(0, (int) env('TUNIBEACH_CACHE_TTL', 300)),
];
