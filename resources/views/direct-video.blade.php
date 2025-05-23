<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon/favicon-16x16.png') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon/favicon.ico') }}">
    <link rel="manifest" href="{{ asset('images/favicon/site.webmanifest') }}">
    <link rel="mask-icon" href="{{ asset('images/favicon/safari-pinned-tab.svg') }}" color="#fe2c55">
    <meta name="msapplication-TileColor" content="#fe2c55">
    <meta name="msapplication-config" content="{{ asset('images/favicon/browserconfig.xml') }}">
    <meta name="theme-color" content="#fe2c55">
    
    <!-- Primary Meta Tags -->
    <title>{{ $video['title'] }} - {{ $video['author']['nickname'] }} (@{{ $username }}) TikTok Video</title>
    <meta name="description" content="Watch {{ $video['author']['nickname'] }}'s TikTok video: {{ $video['title'] }}. View anonymously without an account.">
    <meta name="keywords" content="{{ $username }} tiktok, {{ $video['author']['nickname'] }} videos, watch tiktok anonymously, tiktok video viewer, tiktok without account, private tiktok viewing">
    
    <!-- Canonical Tag -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="video.other">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $video['title'] }} - {{ $video['author']['nickname'] }} TikTok Video">
    <meta property="og:description" content="Watch {{ $video['author']['nickname'] }}'s TikTok video anonymously without logging in.">
    <meta property="og:image" content="{{ $video['cover'] }}">
    <meta property="og:video" content="{{ $video['play_url'] }}">
    <meta property="og:video:type" content="video/mp4">
    <meta property="og:video:width" content="720">
    <meta property="og:video:height" content="1280">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="player">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ $video['title'] }} - {{ $video['author']['nickname'] }} TikTok Video">
    <meta name="twitter:description" content="Watch {{ $video['author']['nickname'] }}'s TikTok video anonymously without an account.">
    <meta name="twitter:image" content="{{ $video['cover'] }}">
    <meta name="twitter:player" content="{{ $video['play_url'] }}">
    <meta name="twitter:player:width" content="720">
    <meta name="twitter:player:height" content="1280">
    
    <meta name="google-analytics-id" content="{{ config('analytics.google_id') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #000;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }
        
        .navbar {
            background-color: rgba(0, 0, 0, 0.9);
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #fe2c55;
        }
        
        .logo i {
            margin-right: 5px;
        }
        
        .video-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 80px 20px 20px;
            position: relative;
        }
        
        .video-player-wrapper {
            max-width: 450px;
            width: 100%;
            background: #000;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
            position: relative;
        }
        
        .video-player {
            width: 100%;
            height: auto;
            aspect-ratio: 9/16;
            max-height: 80vh;
            object-fit: cover;
            display: block;
        }
        
        .video-info {
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            padding: 20px;
            color: white;
        }
        
        .video-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        
        .author-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .author-details {
            flex: 1;
        }
        
        .author-name {
            font-weight: 600;
            margin: 0;
        }
        
        .author-username {
            color: #aaa;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .video-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ccc;
        }
        
        .stat-item i {
            color: #fe2c55;
        }
        
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            background-color: rgba(254, 44, 85, 0.1);
            border: 1px solid #fe2c55;
            color: #fe2c55;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-action:hover {
            background-color: #fe2c55;
            color: white;
        }
        
        .controls-overlay {
            position: absolute;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        
        .control-btn {
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .control-btn:hover {
            background-color: #fe2c55;
            transform: scale(1.1);
        }
        
        @media (max-width: 768px) {
            .video-container {
                padding: 70px 10px 10px;
            }
            
            .video-player-wrapper {
                max-width: 100%;
            }
            
            .video-stats {
                gap: 15px;
            }
            
            .action-buttons {
                gap: 8px;
            }
        }
        
        @media (max-width: 576px) {
            .video-player {
                max-height: 70vh;
            }
            
            .video-info {
                padding: 15px;
            }
        }
    </style>

    <!-- Google Analytics -->
    @include('components.google-analytics')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand logo" href="{{ route('home') }}">
                <i class="fab fa-tiktok"></i> TikTok Viewer
            </a>
            <div class="d-flex">
                <a href="{{ route('user.profile', ['username' => $username]) }}" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-user"></i> View Profile
                </a>
            </div>
        </div>
    </nav>

    <!-- Video Container -->
    <div class="video-container">
        <div class="video-player-wrapper">
            <video id="videoPlayer" class="video-player" controls autoplay playsinline poster="{{ $video['cover'] }}">
                <source src="{{ $video['play_url'] }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            
            <div class="controls-overlay">
                <button class="control-btn" id="fullscreenBtn" title="Fullscreen">
                    <i class="fas fa-expand"></i>
                </button>
                <button class="control-btn" id="shareBtn" title="Share">
                    <i class="fas fa-share-alt"></i>
                </button>
            </div>
            
            <div class="video-info">
                <h1 class="video-title">{{ $video['title'] }}</h1>
                
                <div class="author-info">
                    @if($video['author']['avatar'])
                        <img src="{{ $video['author']['avatar'] }}" alt="{{ $video['author']['nickname'] }}" class="author-avatar">
                    @else
                        <div class="author-avatar" style="background: #fe2c55; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user" style="color: white;"></i>
                        </div>
                    @endif
                    <div class="author-details">
                        <p class="author-name">{{ $video['author']['nickname'] }}</p>
                        <p class="author-username">@{{ $video['author']['unique_id'] }}</p>
                    </div>
                </div>
                
                <div class="video-stats">
                    <div class="stat-item">
                        <i class="fas fa-eye"></i>
                        <span>{{ number_format($video['play_count']) }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-heart"></i>
                        <span>{{ number_format($video['digg_count']) }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-comment"></i>
                        <span>{{ number_format($video['comment_count']) }}</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-share"></i>
                        <span>{{ number_format($video['share_count']) }}</span>
                    </div>
                </div>
                
                <div class="action-buttons">
                    <a href="{{ route('user.profile', ['username' => $username]) }}" class="btn-action">
                        <i class="fas fa-user"></i> View Profile
                    </a>
                    <a href="{{ route('home') }}" class="btn-action">
                        <i class="fas fa-home"></i> Home
                    </a>
                    <button class="btn-action" id="downloadBtn">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const videoPlayer = document.getElementById('videoPlayer');
            const fullscreenBtn = document.getElementById('fullscreenBtn');
            const shareBtn = document.getElementById('shareBtn');
            const downloadBtn = document.getElementById('downloadBtn');
            
            // Fullscreen functionality
            fullscreenBtn.addEventListener('click', function() {
                if (videoPlayer.requestFullscreen) {
                    videoPlayer.requestFullscreen();
                } else if (videoPlayer.webkitRequestFullscreen) {
                    videoPlayer.webkitRequestFullscreen();
                } else if (videoPlayer.msRequestFullscreen) {
                    videoPlayer.msRequestFullscreen();
                }
            });
            
            // Share functionality
            shareBtn.addEventListener('click', function() {
                if (navigator.share) {
                    navigator.share({
                        title: '{{ $video["title"] }}',
                        text: 'Check out this TikTok video by {{ $video["author"]["nickname"] }}',
                        url: window.location.href
                    }).catch(console.error);
                } else {
                    // Fallback - copy to clipboard
                    const dummy = document.createElement('input');
                    document.body.appendChild(dummy);
                    dummy.value = window.location.href;
                    dummy.select();
                    document.execCommand('copy');
                    document.body.removeChild(dummy);
                    
                    // Show feedback
                    const originalText = shareBtn.innerHTML;
                    shareBtn.innerHTML = '<i class="fas fa-check"></i>';
                    setTimeout(() => {
                        shareBtn.innerHTML = originalText;
                    }, 2000);
                }
            });
            
            // Download functionality
            downloadBtn.addEventListener('click', function() {
                const link = document.createElement('a');
                link.href = '{{ $video["play_url"] }}';
                link.download = '{{ $video["author"]["unique_id"] }}_{{ $video["id"] }}.mp4';
                link.target = '_blank';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            });
            
            // Handle video load errors
            videoPlayer.onerror = function() {
                console.error('Video failed to load');
                const errorDiv = document.createElement('div');
                errorDiv.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: white;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 48px; color: #fe2c55; margin-bottom: 20px;"></i>
                        <h3>Video Unavailable</h3>
                        <p>This video is currently unavailable or has been removed.</p>
                        <a href="{{ route('user.profile', ['username' => $username]) }}" class="btn-action">View Profile Instead</a>
                    </div>
                `;
                videoPlayer.parentNode.replaceChild(errorDiv, videoPlayer);
            };
        });
    </script>

    <!-- Schema.org VideoObject Markup -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "VideoObject",
        "name": "{{ $video['title'] }}",
        "description": "TikTok video by {{ $video['author']['nickname'] }} (@{{ $video['author']['unique_id'] }})",
        "thumbnailUrl": "{{ $video['cover'] }}",
        "contentUrl": "{{ $video['play_url'] }}",
        "uploadDate": "{{ \Carbon\Carbon::createFromTimestamp($video['create_time'])->toIso8601String() }}",
        "duration": "PT{{ $video['duration'] }}S",
        "interactionStatistic": [
            {
                "@type": "InteractionCounter",
                "interactionType": "https://schema.org/WatchAction",
                "userInteractionCount": "{{ $video['play_count'] }}"
            },
            {
                "@type": "InteractionCounter",
                "interactionType": "https://schema.org/LikeAction",
                "userInteractionCount": "{{ $video['digg_count'] }}"
            },
            {
                "@type": "InteractionCounter",
                "interactionType": "https://schema.org/CommentAction",
                "userInteractionCount": "{{ $video['comment_count'] }}"
            },
            {
                "@type": "InteractionCounter",
                "interactionType": "https://schema.org/ShareAction",
                "userInteractionCount": "{{ $video['share_count'] }}"
            }
        ],
        "author": {
            "@type": "Person",
            "name": "{{ $video['author']['nickname'] }}",
            "alternateName": "@{{ $video['author']['unique_id'] }}",
            "url": "https://www.tiktok.com/@{{ $video['author']['unique_id'] }}"
        }
    }
    </script>
</body>
</html> 