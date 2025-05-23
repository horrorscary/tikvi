<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GzipResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only compress if client supports gzip and response is compressible
        if ($this->shouldCompress($request, $response)) {
            $content = $response->getContent();
            
            if (strlen($content) > 1024) { // Only compress responses larger than 1KB
                $compressed = gzencode($content, 9); // Maximum compression
                
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', strlen($compressed));
                    $response->headers->set('Vary', 'Accept-Encoding');
                }
            }
        }
        
        return $response;
    }
    
    /**
     * Determine if the response should be compressed
     */
    private function shouldCompress(Request $request, Response $response): bool
    {
        // Check if client accepts gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (strpos($acceptEncoding, 'gzip') === false) {
            return false;
        }
        
        // Check if response is already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }
        
        // Check content type - only compress text-based responses
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = [
            'text/html',
            'text/plain',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml'
        ];
        
        foreach ($compressibleTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                return true;
            }
        }
        
        // Default to compressing if no content type is set (likely HTML)
        return empty($contentType);
    }
}
