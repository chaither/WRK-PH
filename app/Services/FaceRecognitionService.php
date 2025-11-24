<?php

namespace App\Services;

class FaceRecognitionService
{
    /**
     * Compare two descriptors (arrays of floats) using Euclidean distance.
     * Returns true if distance is below threshold.
     */
    public static function matches(array $storedDescriptor, array $probeDescriptor, float $threshold = 0.6): bool
    {
        if (count($storedDescriptor) !== count($probeDescriptor) || count($storedDescriptor) === 0) {
            return false;
        }

        $sum = 0.0;
        for ($i = 0, $len = count($storedDescriptor); $i < $len; $i++) {
            $diff = (float) $storedDescriptor[$i] - (float) $probeDescriptor[$i];
            $sum += $diff * $diff;
        }

        $distance = sqrt($sum);

        return $distance <= $threshold;
    }
}
