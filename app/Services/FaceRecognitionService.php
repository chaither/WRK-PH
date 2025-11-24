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

        $distance = self::distance($storedDescriptor, $probeDescriptor);

        return $distance <= $threshold;
    }

    /**
     * Compute Euclidean distance between two face descriptor arrays.
     * Returns a float distance (lower = more similar).
     */
    public static function distance(array $a, array $b): float
    {
        $sum = 0.0;
        $len = count($a);
        for ($i = 0; $i < $len; $i++) {
            $diff = (float) $a[$i] - (float) $b[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }
}
