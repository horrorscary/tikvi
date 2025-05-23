<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CacheService
{
    /**
     * Cache durations in seconds
     * PROFILE_CACHE_DURATION: 6 hours (user profile info)
     * VIDEOS_CACHE_DURATION: 3 hours (user's videos)
     * TRENDING_CACHE_DURATION: 30 minutes (trending videos)
     */
    const PROFILE_CACHE_DURATION = 21600; // 6 hours
    const VIDEOS_CACHE_DURATION = 10800;  // 3 hours
    const TRENDING_CACHE_DURATION = 1800; // 30 minutes
    const VIDEO_CACHE_DURATION = 86400;   // 24 hours
    
    /**
     * Fetch profile from cache or source
     *
     * @param string $username
     * @param callable $fetchCallback
     * @param bool $forceRefresh
     * @return array
     */
    public function getProfile(string $username, callable $fetchCallback, bool $forceRefresh = false)
    {
        $cacheKey = "profile:{$username}";
        
        // Force refresh if requested
        if ($forceRefresh) {
            return $this->refreshProfileCache($username, $fetchCallback, $cacheKey);
        }
        
        // Try to get from cache first
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                Log::info("Cache hit for profile: {$username}");
                // Mark as not stale since it's from cache
                $cached['is_stale'] = false;
                return $cached;
            }
            
            Log::info("Cache miss for profile: {$username} - fetching fresh data");
            return $this->refreshProfileCache($username, $fetchCallback, $cacheKey);
            
        } catch (\Exception $e) {
            Log::error("Cache error for profile {$username}: " . $e->getMessage());
            // Fallback to direct API call
            try {
                $freshData = $fetchCallback();
                
                if (!$freshData || isset($freshData['code']) && $freshData['code'] !== 0) {
                    return ['code' => -1, 'msg' => 'Failed to fetch profile data from the API: ' . ($freshData['msg'] ?? 'Unknown error')];
                }
                
                $freshData['cached_at'] = Carbon::now()->toIso8601String();
                $freshData['is_stale'] = false;
                
                return $freshData;
            } catch (\Exception $e2) {
                Log::error("Complete failure for profile {$username}: " . $e2->getMessage());
                return ['code' => -1, 'msg' => 'Exception when fetching profile: ' . $e2->getMessage()];
            }
        }
    }
    
    /**
     * Fetch videos from cache or source
     *
     * @param string $username
     * @param callable $fetchCallback
     * @param string|null $cursor
     * @param bool $forceRefresh
     * @return array
     */
    public function getVideos(string $username, callable $fetchCallback, $cursor = null, bool $forceRefresh = false)
    {
        // For paginated results, include cursor in cache key
        $cacheKey = $cursor ? "videos:{$username}:cursor:{$cursor}" : "videos:{$username}";
        
        // Force refresh if requested
        if ($forceRefresh) {
            return $this->refreshVideosCache($username, $fetchCallback, $cacheKey, $cursor);
        }
        
        // Try to get from cache first
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                Log::info("Cache hit for videos: {$username}, cursor: {$cursor}");
                // Mark as not stale since it's from cache
                $cached['is_stale'] = false;
                return $cached;
            }
            
            Log::info("Cache miss for videos: {$username}, cursor: {$cursor} - fetching fresh data");
            return $this->refreshVideosCache($username, $fetchCallback, $cacheKey, $cursor);
            
        } catch (\Exception $e) {
            Log::error("Cache error for videos {$username}: " . $e->getMessage());
            // Fallback to direct API call
            try {
                $freshData = $fetchCallback();
                
                if (!$freshData || isset($freshData['code']) && $freshData['code'] !== 0) {
                    return ['code' => -1, 'msg' => 'Failed to fetch videos data from the API: ' . ($freshData['msg'] ?? 'Unknown error')];
                }
                
                $freshData['cached_at'] = Carbon::now()->toIso8601String();
                $freshData['is_stale'] = false;
                
                return $freshData;
            } catch (\Exception $e2) {
                Log::error("Complete failure for videos {$username}: " . $e2->getMessage());
                return ['code' => -1, 'msg' => 'Exception when fetching videos: ' . $e2->getMessage()];
            }
        }
    }
    
    /**
     * Fetch single video from cache or source
     *
     * @param string $username
     * @param string $videoId
     * @param callable $fetchCallback
     * @param bool $forceRefresh
     * @return array
     */
    public function getVideo(string $username, string $videoId, callable $fetchCallback, bool $forceRefresh = false)
    {
        $cacheKey = "video:{$videoId}:{$username}";
        
        // Force refresh if requested
        if ($forceRefresh) {
            return $this->refreshVideoCache($username, $videoId, $fetchCallback, $cacheKey);
        }
        
        // Try to get from cache first
        try {
            $cached = Cache::get($cacheKey);
            
            if ($cached) {
                Log::info("Cache hit for video: {$videoId}, username: {$username}");
                // Mark as not stale since it's from cache
                $cached['is_stale'] = false;
                return $cached;
            }
            
            Log::info("Cache miss for video: {$videoId}, username: {$username} - fetching fresh data");
            return $this->refreshVideoCache($username, $videoId, $fetchCallback, $cacheKey);
            
        } catch (\Exception $e) {
            Log::error("Cache error for video {$videoId}: " . $e->getMessage());
            // Fallback to direct API call
            try {
                $freshData = $fetchCallback();
                
                if (!$freshData || isset($freshData['code']) && $freshData['code'] !== 0) {
                    return ['code' => -1, 'msg' => 'Failed to fetch video data from the API: ' . ($freshData['msg'] ?? 'Unknown error')];
                }
                
                $freshData['cached_at'] = Carbon::now()->toIso8601String();
                $freshData['is_stale'] = false;
                
                return $freshData;
            } catch (\Exception $e2) {
                Log::error("Complete failure for video {$videoId}: " . $e2->getMessage());
                return ['code' => -1, 'msg' => 'Exception when fetching video: ' . $e2->getMessage()];
            }
        }
    }
    
    /**
     * Refresh profile cache with fresh data
     *
     * @param string $username
     * @param callable $fetchCallback
     * @param string $cacheKey
     * @return array
     */
    protected function refreshProfileCache(string $username, callable $fetchCallback, string $cacheKey)
    {
        try {
            $freshData = $fetchCallback();
            
            if (!$freshData || isset($freshData['code']) && $freshData['code'] !== 0) {
                return $this->handleFetchFailure($cacheKey, 'profile');
            }
            
            // Add cache metadata
            $freshData['cached_at'] = Carbon::now()->toIso8601String();
            $freshData['is_stale'] = false;
            
            // Store in cache
            Cache::put($cacheKey, $freshData, self::PROFILE_CACHE_DURATION);
            
            return $freshData;
        } catch (\Exception $e) {
            Log::error("Error refreshing profile cache: " . $e->getMessage(), [
                'username' => $username,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->handleFetchFailure($cacheKey, 'profile');
        }
    }
    
    /**
     * Refresh videos cache with fresh data
     *
     * @param string $username
     * @param callable $fetchCallback
     * @param string $cacheKey
     * @param string|null $cursor
     * @return array
     */
    protected function refreshVideosCache(string $username, callable $fetchCallback, string $cacheKey, $cursor = null)
    {
        try {
            $freshData = $fetchCallback();
            
            if (!$freshData || isset($freshData['code']) && $freshData['code'] !== 0) {
                return $this->handleFetchFailure($cacheKey, 'videos');
            }
            
            // Add cache metadata
            $freshData['cached_at'] = Carbon::now()->toIso8601String();
            $freshData['is_stale'] = false;
            
            // Store in cache
            Cache::put($cacheKey, $freshData, self::VIDEOS_CACHE_DURATION);
            
            return $freshData;
        } catch (\Exception $e) {
            Log::error("Error refreshing videos cache: " . $e->getMessage(), [
                'username' => $username,
                'cursor' => $cursor,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->handleFetchFailure($cacheKey, 'videos');
        }
    }
    
    /**
     * Refresh video cache with fresh data
     *
     * @param string $username
     * @param string $videoId
     * @param callable $fetchCallback
     * @param string $cacheKey
     * @return array
     */
    protected function refreshVideoCache(string $username, string $videoId, callable $fetchCallback, string $cacheKey)
    {
        try {
            $freshData = $fetchCallback();
            
            if (!$freshData || isset($freshData['code']) && $freshData['code'] !== 0) {
                return $this->handleFetchFailure($cacheKey, 'video');
            }
            
            // Add cache metadata
            $freshData['cached_at'] = Carbon::now()->toIso8601String();
            $freshData['is_stale'] = false;
            
            // Store in cache
            Cache::put($cacheKey, $freshData, self::VIDEO_CACHE_DURATION);
            
            return $freshData;
        } catch (\Exception $e) {
            Log::error("Error refreshing video cache: " . $e->getMessage(), [
                'username' => $username,
                'video_id' => $videoId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->handleFetchFailure($cacheKey, 'video');
        }
    }
    
    /**
     * Handle fetch failure by returning stale data if available
     *
     * @param string $cacheKey
     * @param string $type
     * @return array
     */
    protected function handleFetchFailure(string $cacheKey, string $type)
    {
        // Try to get stale data from cache
        $staleData = Cache::get("stale:{$cacheKey}");
        
        if ($staleData) {
            // Mark data as stale for UI indication
            $staleData['is_stale'] = true;
            Log::info("Serving stale {$type} data for key: {$cacheKey}");
            
            // Log detailed cache information for debugging
            Log::debug("Stale cache details", [
                'key' => $cacheKey,
                'type' => $type,
                'cached_at' => $staleData['cached_at'] ?? 'unknown',
                'cache_store' => config('cache.default'),
                'cache_path' => config('cache.stores.file.path', 'not_file_store')
            ]);
            
            return $staleData;
        }
        
        // Attempt to create the cache directory if it doesn't exist (file driver only)
        if (config('cache.default') === 'file') {
            $cachePath = config('cache.stores.file.path');
            if (!file_exists($cachePath)) {
                try {
                    mkdir($cachePath, 0775, true);
                    Log::info("Created missing cache directory: {$cachePath}");
                } catch (\Exception $e) {
                    Log::error("Failed to create cache directory: {$e->getMessage()}", [
                        'path' => $cachePath,
                        'exception' => get_class($e)
                    ]);
                }
            }
        }
        
        Log::error("No stale data available for key: {$cacheKey}");
        return ['code' => -1, 'msg' => 'Failed to fetch data and no stale data available. Please try again later.'];
    }
    
    /**
     * Schedule a background refresh for a cached resource
     * This is a stub method and would need a proper queue worker in production
     *
     * @param string $type
     * @param array $params
     */
    public function scheduleBackgroundRefresh(string $type, array $params)
    {
        // Log the refresh request for now
        Log::info("Background refresh scheduled for {$type}", $params);
        
        // In a real app, you would dispatch a job to a queue worker
        // e.g. RefreshCacheJob::dispatch($type, $params);
    }
    
    /**
     * Invalidate all cache for a user
     *
     * @param string $username
     */
    public function invalidateUserCache(string $username)
    {
        // Since we're using the file driver without tags, 
        // we need to manually clear the specific cache keys
        // This is less efficient than using tags for invalidation
        $profileKey = "profile:{$username}";
        $staleProfileKey = "stale:{$profileKey}";
        
        Cache::forget($profileKey);
        Cache::forget($staleProfileKey);
        
        Log::info("Invalidated cache for user: {$username}");
    }
    
    /**
     * Invalidate cache for a specific video
     *
     * @param string $username
     * @param string $videoId
     */
    public function invalidateVideoCache(string $username, string $videoId)
    {
        $videoKey = "video:{$username}:{$videoId}";
        $staleVideoKey = "stale:{$videoKey}";
        
        Cache::forget($videoKey);
        Cache::forget($staleVideoKey);
        
        Log::info("Invalidated cache for video: {$videoId}");
    }
    
    /**
     * Test cache connectivity and functionality
     *
     * @return array
     */
    public function testCacheConnection()
    {
        $result = [
            'success' => false,
            'cache_driver' => config('cache.default'),
            'cache_path' => config('cache.default') === 'file' ? config('cache.stores.file.path') : null,
            'path_exists' => false,
            'path_writable' => false,
            'write_test' => false,
            'read_test' => false,
            'error' => null
        ];
        
        try {
            // Check if cache path exists and is writable (if using file driver)
            if (config('cache.default') === 'file') {
                $cachePath = config('cache.stores.file.path');
                $result['path_exists'] = file_exists($cachePath);
                $result['path_writable'] = is_writable($cachePath);
                
                // Create directory if it doesn't exist
                if (!$result['path_exists']) {
                    try {
                        mkdir($cachePath, 0775, true);
                        $result['path_exists'] = true;
                        $result['path_writable'] = is_writable($cachePath);
                    } catch (\Exception $e) {
                        $result['error'] = "Failed to create cache directory: " . $e->getMessage();
                    }
                }
            }
            
            // Test writing to cache
            $testKey = 'test_cache_connection_' . time();
            $testValue = ['test' => true, 'timestamp' => now()->timestamp];
            
            Cache::put($testKey, $testValue, 60);
            $result['write_test'] = true;
            
            // Test reading from cache
            $readValue = Cache::get($testKey);
            $result['read_test'] = ($readValue && isset($readValue['test']) && $readValue['test'] === true);
            
            // Clean up
            Cache::forget($testKey);
            
            $result['success'] = $result['write_test'] && $result['read_test'];
        } catch (\Exception $e) {
            $result['error'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Warm cache for trending profiles
     *
     * @param array $usernames
     */
    public function warmTrendingProfilesCache(array $usernames)
    {
        // This is a placeholder method that would prefetch and cache profiles
        Log::info("Warming cache for trending profiles", ['usernames' => $usernames]);
    }
} 