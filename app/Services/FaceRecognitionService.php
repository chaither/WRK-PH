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
        // Support both a single descriptor (flat array) or an array of descriptors (array of arrays)
        if (empty($storedDescriptor)) {
            return false;
        }

        // If stored descriptor appears to be a single flat descriptor (same length as probe and numeric), compare directly
        if (self::isFlatDescriptor($storedDescriptor, $probeDescriptor)) {
            $distance = self::distance($storedDescriptor, $probeDescriptor);
            return $distance <= $threshold;
        }

        // Otherwise, treat storedDescriptor as an array of descriptors and return true if any match under threshold
        foreach ($storedDescriptor as $sd) {
            if (!is_array($sd)) continue;
            if (count($sd) !== count($probeDescriptor)) continue;
            $distance = self::distance($sd, $probeDescriptor);
            if ($distance <= $threshold) return true;
        }
        return false;
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

    /**
     * Return the minimum Euclidean distance between the probe and stored descriptors.
     * Accepts stored descriptor as either a single flat descriptor or an array of descriptors.
     */
    public static function minDistance(array $storedDescriptor, array $probeDescriptor): float
    {
        if (empty($storedDescriptor)) return PHP_FLOAT_MAX;
        if (self::isFlatDescriptor($storedDescriptor, $probeDescriptor)) {
            return self::distance($storedDescriptor, $probeDescriptor);
        }
        $min = PHP_FLOAT_MAX;
        foreach ($storedDescriptor as $sd) {
            if (!is_array($sd) || count($sd) !== count($probeDescriptor)) continue;
            $d = self::distance($sd, $probeDescriptor);
            if ($d < $min) $min = $d;
        }
        return $min;
    }

    private static function isFlatDescriptor(array $stored, array $probe): bool
    {
        // Heuristic: if counts match and elements are scalar numeric
        if (count($stored) !== count($probe)) return false;
        foreach ($stored as $v) {
            if (!is_numeric($v)) return false;
        }
        return true;
    }
}
