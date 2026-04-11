<?php

namespace App\Enums\Concerns;

/**
 * Marker interface for enums that should serialize as {key, value} objects
 * when returned from Eloquent models (matching BenSampo enum behavior).
 */
interface HasKeyValueSerialization {}
