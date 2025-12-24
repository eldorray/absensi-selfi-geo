<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait HasHaversineCalculation
 *
 * Provides methods for calculating the distance between two geographic coordinates
 * using the Haversine formula.
 */
trait HasHaversineCalculation
{
    /**
     * Earth's radius in meters.
     */
    private const float EARTH_RADIUS_METERS = 6371000.0;

    /**
     * Calculate the distance between two coordinates using the Haversine formula.
     *
     * @param float $lat1 Latitude of the first point (in degrees)
     * @param float $lon1 Longitude of the first point (in degrees)
     * @param float $lat2 Latitude of the second point (in degrees)
     * @param float $lon2 Longitude of the second point (in degrees)
     * @return float Distance in meters
     */
    protected function calculateHaversineDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        // Haversine formula
        $a = sin($deltaLat / 2) ** 2 +
             cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Check if a coordinate is within a specified radius of another coordinate.
     *
     * @param float $userLat User's latitude
     * @param float $userLon User's longitude
     * @param float $officeLat Office's latitude
     * @param float $officeLon Office's longitude
     * @param int $radiusMeters Allowed radius in meters
     * @return bool True if within radius, false otherwise
     */
    protected function isWithinRadius(
        float $userLat,
        float $userLon,
        float $officeLat,
        float $officeLon,
        int $radiusMeters
    ): bool {
        $distance = $this->calculateHaversineDistance($userLat, $userLon, $officeLat, $officeLon);
        return $distance <= $radiusMeters;
    }
}
