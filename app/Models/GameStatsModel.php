<?php
// /app/Models/GameStatsModel.php

class GameStatsModel {
    private $cacheFile;
    private $cacheExpiry = 300; // 5 minutes

    public function __construct() {
        $this->cacheFile = __DIR__ . '/../../cache/game_stats.json';
    }

    /**
     * Get Minecraft server statistics
     * Uses caching to avoid hitting API rate limits
     */
    public function getMinecraftStats() {
        $cached = $this->getCachedData('minecraft');
        if ($cached) {
            return $cached;
        }

        // Mock data voor MVP - later echte API integratie
        // Voor echte data: https://api.mcsrvstat.us/2/jouwserver.nl
        $stats = [
            'server_name' => 'JouwServer.nl',
            'server_ip' => 'play.jouwserver.nl',
            'online' => true,
            'online_players' => rand(8, 25),
            'max_players' => 50,
            'uptime' => '99.8%',
            'version' => '1.20.1',
            'motd' => 'Welkom op JouwServer!',
            'top_players' => [
                ['name' => 'PlayerOne', 'playtime' => '245h', 'rank' => 'Admin'],
                ['name' => 'BuilderPro', 'playtime' => '198h', 'rank' => 'Moderator'],
                ['name' => 'RedstoneGuru', 'playtime' => '156h', 'rank' => 'VIP'],
                ['name' => 'PvPMaster', 'playtime' => '134h', 'rank' => 'Member'],
                ['name' => 'Miner2023', 'playtime' => '112h', 'rank' => 'Member']
            ],
            'last_update' => time()
        ];

        $this->cacheData('minecraft', $stats);
        return $stats;
    }

    /**
     * Get Rainbow Six Siege statistics
     * Uses caching to avoid hitting API rate limits
     */
    public function getR6Stats() {
        $cached = $this->getCachedData('r6siege');
        if ($cached) {
            return $cached;
        }

        // Mock data voor MVP
        // Voor echte data: R6Stats API of Ubisoft API
        $stats = [
            'username' => 'JouwGamertag',
            'platform' => 'PC',
            'uplay_id' => 'jouw-uplay-id',
            'current_rank' => 'Gold II',
            'max_rank' => 'Platinum III',
            'mmr' => '2567',
            'kd_ratio' => '1.23',
            'win_rate' => '54.2%',
            'kills' => '3456',
            'deaths' => '2801',
            'wins' => '543',
            'losses' => '459',
            'level' => 156,
            'playtime' => '487h',
            'favorite_operator' => 'Ash',
            'last_update' => time()
        ];

        $this->cacheData('r6siege', $stats);
        return $stats;
    }

    /**
     * Get cached data if it exists and hasn't expired
     * @param string $type Type of stats (minecraft or r6siege)
     * @return array|null Cached data or null if not found/expired
     */
    private function getCachedData($type) {
        if (!file_exists($this->cacheFile)) {
            return null;
        }

        $cache = json_decode(file_get_contents($this->cacheFile), true);
        if (!$cache || !isset($cache[$type])) {
            return null;
        }

        // Check if cache is expired
        if (time() - $cache[$type]['last_update'] > $this->cacheExpiry) {
            return null;
        }

        return $cache[$type];
    }

    /**
     * Cache data to JSON file
     * @param string $type Type of stats (minecraft or r6siege)
     * @param array $data Data to cache
     */
    private function cacheData($type, $data) {
        $cache = [];
        if (file_exists($this->cacheFile)) {
            $cache = json_decode(file_get_contents($this->cacheFile), true) ?? [];
        }

        // Ensure cache directory exists
        $cacheDir = dirname($this->cacheFile);
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cache[$type] = $data;
        file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
    }

    /**
     * Clear cache for specific type or all
     * @param string|null $type Type to clear, or null for all
     */
    public function clearCache($type = null) {
        if (!file_exists($this->cacheFile)) {
            return;
        }

        if ($type === null) {
            // Clear all cache
            unlink($this->cacheFile);
        } else {
            // Clear specific cache
            $cache = json_decode(file_get_contents($this->cacheFile), true);
            if (isset($cache[$type])) {
                unset($cache[$type]);
                file_put_contents($this->cacheFile, json_encode($cache, JSON_PRETTY_PRINT));
            }
        }
    }

    /**
     * Force refresh stats (bypass cache)
     * @param string $type Type of stats to refresh
     * @return array Fresh stats
     */
    public function forceRefresh($type) {
        $this->clearCache($type);

        if ($type === 'minecraft') {
            return $this->getMinecraftStats();
        } elseif ($type === 'r6siege') {
            return $this->getR6Stats();
        }

        return [];
    }

    /**
     * Fetch live Minecraft server stats from API
     * @param string $serverIp Server IP address
     * @return array|null Server stats or null on failure
     */
    public function fetchLiveMinecraftStats($serverIp) {
        $apiUrl = "https://api.mcsrvstat.us/2/{$serverIp}";

        // Use cURL for better error handling
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (!$data || !isset($data['online'])) {
            return null;
        }

        // Transform API data to our format
        return [
            'server_name' => $data['hostname'] ?? $serverIp,
            'server_ip' => $serverIp,
            'online' => $data['online'] ?? false,
            'online_players' => $data['players']['online'] ?? 0,
            'max_players' => $data['players']['max'] ?? 0,
            'version' => $data['version'] ?? 'Unknown',
            'motd' => $data['motd']['clean'][0] ?? '',
            'uptime' => '99.8%', // This would need separate tracking
            'top_players' => [], // Would need separate API/database
            'last_update' => time()
        ];
    }

    /**
     * Fetch live Rainbow Six Siege stats from API
     * Note: Requires R6Stats API key or Ubisoft API credentials
     * @param string $username Player username
     * @param string $platform Platform (uplay, psn, xbl)
     * @return array|null Player stats or null on failure
     */
    public function fetchLiveR6Stats($username, $platform = 'uplay') {
        // Example implementation with R6Stats API
        // You'll need to sign up for API key at: https://r6stats.com/

        $apiKey = 'YOUR_R6STATS_API_KEY'; // Add your API key here
        $apiUrl = "https://api2.r6stats.com/public-api/stats/{$username}/{$platform}/generic";

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$apiKey}"
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            return null;
        }

        $data = json_decode($response, true);

        if (!$data) {
            return null;
        }

        // Transform API data to our format
        // Note: Actual structure depends on the API you use
        return [
            'username' => $username,
            'platform' => strtoupper($platform),
            'level' => $data['level'] ?? 0,
            'current_rank' => $data['rank'] ?? 'Unranked',
            'kd_ratio' => $data['kd'] ?? '0.00',
            'win_rate' => $data['win_rate'] ?? '0%',
            'kills' => $data['kills'] ?? 0,
            'deaths' => $data['deaths'] ?? 0,
            'wins' => $data['wins'] ?? 0,
            'losses' => $data['losses'] ?? 0,
            'playtime' => $data['playtime'] ?? '0h',
            'last_update' => time()
        ];
    }

    /**
     * Check if cache exists and is valid
     * @param string $type Type of stats
     * @return bool True if cache is valid
     */
    public function isCacheValid($type) {
        $cached = $this->getCachedData($type);
        return $cached !== null;
    }

    /**
     * Get cache age in seconds
     * @param string $type Type of stats
     * @return int|null Cache age or null if no cache
     */
    public function getCacheAge($type) {
        if (!file_exists($this->cacheFile)) {
            return null;
        }

        $cache = json_decode(file_get_contents($this->cacheFile), true);
        if (!$cache || !isset($cache[$type])) {
            return null;
        }

        return time() - $cache[$type]['last_update'];
    }
}
?>