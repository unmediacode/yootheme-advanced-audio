<?php

// Get props
$tracks = $props['tracks_data'] ?? [];
$layout = $props['layout'] ?? 'simple';
$primary_color = $props['primary_color'] ?? '#1e87f0';
$autoplay = $props['autoplay'] ?? false;
$loop = $props['loop'] ?? false;
$shuffle = $props['shuffle'] ?? false;
$show_cover = $props['show_cover'] ?? true;
$cover_size = $props['cover_size'] ?? 'small';
$show_progress = $props['show_progress'] ?? true;
$show_volume = $props['show_volume'] ?? true;
$show_download = $props['show_download'] ?? false;
$show_speed = $props['show_speed'] ?? false;
$show_external_link = $props['show_external_link'] ?? true;
$show_platform_links = $props['show_platform_links'] ?? true;
$show_waveform = $props['show_waveform'] ?? true;

// Encode tracks for JS
$tracks_json = json_encode($tracks) ?: '[]';

// CSS Classes
$classes = ['ytaa-player', "ytaa-layout-{$layout}", "ytaa-cover-{$cover_size}"];
if ($layout === 'sticky') {
    $classes[] = 'uk-position-fixed uk-position-bottom uk-width-1-1';
}

// Styles
$style = "--ytaa-primary-color: {$primary_color};";

?>

<!-- Debug: Children: <?= $props['_debug_children'] ?? 0 ?>, Tracks: <?= $props['_debug_tracks'] ?? 0 ?> -->
<div id="<?= $attrs['id'] ?>" class="<?= implode(' ', $classes) ?>" style="<?= $style ?>"
    data-autoplay="<?= $autoplay && $autoplay !== 'false' ? 'true' : 'false' ?>" 
    data-loop="<?= $loop && $loop !== 'false' ? 'true' : 'false' ?>"
    data-shuffle="<?= $shuffle && $shuffle !== 'false' ? 'true' : 'false' ?>"
    data-waveform="<?= $show_waveform ? 'true' : 'false' ?>">

    <script>
        window.ytaa_tracks_<?= str_replace('-', '_', $attrs['id']) ?> = <?= $tracks_json ?>;
    </script>

    <?php if ($layout === 'simple'): ?>
        <!-- Simple Layout: Square with Centered Play Button -->
        <div class="ytaa-simple-interface uk-inline uk-background-muted uk-dark ytaa-cover-<?= $cover_size ?>">
            <!-- Cover Art (Background) -->
            <div class="ytaa-cover-container uk-position-relative uk-overflow-hidden" style="aspect-ratio: 1/1;">
                <img src="" alt="Cover" class="ytaa-cover-img uk-position-cover uk-object-cover" style="display:none;">
                
                <!-- Placeholder if no cover -->
                <div class="ytaa-cover-placeholder uk-position-cover uk-flex uk-flex-center uk-flex-middle uk-background-secondary">
                    <span uk-icon="icon: music; ratio: 3" class="uk-text-muted"></span>
                </div>

                <!-- Overlay with Controls -->
                <div class="uk-overlay uk-position-cover uk-flex uk-flex-center uk-flex-middle" style="background: rgba(0,0,0,0.3);">
                     <button class="ytaa-btn-play uk-icon-button uk-button-primary" style="width: 60px; height: 60px;">
                        <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="5,4 15,10 5,16"></polygon></svg>
                     </button>
                     
                     <!-- Simple Layout Extra Controls -->
                     <div class="uk-position-bottom-left uk-padding-small uk-flex uk-flex-middle ytaa-simple-extras">
                        <?php if ($show_speed): ?>
                            <button class="ytaa-btn-speed uk-icon-button uk-margin-small-right" type="button" style="width: 30px; height: 30px;">1x</button>
                        <?php endif ?>
                        
                        <?php if ($show_download): ?>
                            <a href="#" class="ytaa-btn-download uk-icon-button uk-margin-small-right" download style="width: 30px; height: 30px;" uk-icon="icon: download; ratio: 0.8"></a>
                        <?php endif ?>

                        <?php if ($show_external_link): ?>
                            <a href="#" class="ytaa-btn-link uk-icon-button" target="_blank" style="width: 30px; height: 30px;" uk-icon="icon: link; ratio: 0.8"></a>
                        <?php endif ?>
                        
                        <?php if ($show_platform_links): ?>
                        <!-- Social Links -->
                        <a href="#" target="_blank" class="ytaa-btn-spotify uk-icon-button uk-margin-small-right" style="display:none;" title="Spotify"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg></a>
                        <a href="#" target="_blank" class="ytaa-btn-apple uk-icon-button uk-margin-small-right" style="display:none;" uk-icon="icon: apple" title="Apple Music"></a>
                        <a href="#" target="_blank" class="ytaa-btn-amazon uk-icon-button uk-margin-small-right" style="display:none;" title="Amazon Music"><svg width="16" height="16" viewBox="0 0 512 512" fill="currentColor"><path d="M257.2 162.7c-48.7 1.8-169.5 15.5-169.5 117.5 0 109.5 138.3 114 183.5 43.2 6.5 10.2 35.4 37.5 45.3 46.8l56.8-56S341 288.9 341 261.4V114.3C341 89 316.5 32 228.7 32 140.7 32 94 87 94 136.3l73.5 6.8c16.3-49.5 54.2-49.5 54.2-49.5 40.7-.1 35.5 29.8 35.5 69.1zm0 86.8c0 80-84.2 68-84.2 17.2 0-47.2 50.5-56.7 84.2-57.8v40.6zm136 163.5c-7.7 10-70 67-174.5 67S34.2 408.5 9.7 379c-6.8-7.7 1-11.3 5.5-8.3C88.5 415.2 203 488.5 387.7 401c7.5-3.7 13.3 2 5.5 12zm39.8 2.2c-6.5 15.8-16 26.8-21.2 31-5.5 4.5-9.5 2.7-6.5-3.8s19.3-46.5 12.7-55c-6.5-8.3-37-4.3-48-3.2-10.8 1-13 2-14-.3-2.3-5.7 21.7-15.5 37.5-17.5 15.7-1.8 41-.8 46 5.7 3.7 5.1 0 27.1-6.5 43.1z"/></svg></a>
                        <?php endif ?>
                     </div>
                </div>
                
                <!-- Hidden elements for JS to populate/read if needed -->
                <div class="uk-hidden">
                     <div class="ytaa-title"></div>
                     <div class="ytaa-artist"></div>
                     <div class="ytaa-current-time"></div>
                     <div class="ytaa-duration"></div>
                     <div class="ytaa-progress-bar"><div class="ytaa-progress-fill"></div></div>
                </div>
            </div>
        </div>

    <?php elseif ($layout === 'apple'): ?>
        <!-- Apple Style Playlist Layout -->
        <div class="ytaa-apple-container uk-container uk-container-small uk-margin-medium-top">
            
            <!-- Album Header -->
            <div class="uk-grid-large uk-flex-middle" uk-grid>
                <!-- Cover -->
                <div class="uk-width-auto@s uk-flex uk-flex-center">
                    <div class="ytaa-album-cover uk-box-shadow-large uk-border-rounded uk-overflow-hidden" style="width: 260px; height: 260px; flex-shrink: 0; background-color: #f8f8f8; position: relative;">
                        <?php if ($props['album_cover']): ?>
                            <img src="<?= $props['album_cover'] ?>" alt="<?= $props['album_title'] ?>" class="ytaa-cover-img" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php elseif (!empty($tracks[0]['cover'])): ?>
                            <img src="<?= $tracks[0]['cover'] ?>" alt="Album Cover" class="ytaa-cover-img" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div class="ytaa-cover-placeholder uk-height-1-1 uk-flex uk-flex-center uk-flex-middle">
                                <span uk-icon="icon: music; ratio: 3" class="uk-text-muted"></span>
                            </div>
                        <?php endif ?>
                        <!-- Placeholder for when JS hides the image -->
                        <div class="ytaa-cover-placeholder uk-position-cover uk-background-muted uk-flex uk-flex-center uk-flex-middle" style="display: none;">
                            <span uk-icon="icon: music; ratio: 3" class="uk-text-muted"></span>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="uk-width-expand@s">
                    <h1 class="ytaa-album-title uk-heading-small uk-margin-remove uk-text-bold" style="font-size: 2.5rem; line-height: 1.1;">
                        <?= $props['album_title'] ?: ($tracks[0]['title'] ?? 'Unknown Album') ?>
                    </h1>
                    <h2 class="ytaa-album-artist uk-h3 uk-margin-small-top uk-text-danger uk-text-bold">
                        <?= $props['album_artist'] ?: ($tracks[0]['artist'] ?? 'Unknown Artist') ?>
                    </h2>
                    <div class="ytaa-album-meta uk-text-meta uk-margin-small-top uk-text-uppercase uk-text-small" style="font-weight: 600; letter-spacing: 1px;">
                        <?= strtoupper($tracks[0]['genre'] ?? 'Music') ?> • <?= date('Y') ?>
                    </div>
                    
                    <?php if ($props['album_description']): ?>
                        <p class="ytaa-album-desc uk-text-small uk-margin-medium-top uk-visible@s" style="max-width: 600px; color: #666; line-height: 1.5;">
                            <?= nl2br($props['album_description']) ?>
                        </p>
                    <?php endif ?>
                    
                    <div class="ytaa-header-actions uk-margin-medium-top uk-flex uk-flex-middle">
                        <button class="ytaa-btn-header-play uk-button uk-button-primary uk-button-large uk-border-pill uk-margin-small-right">
                            <span uk-icon="icon: play" class="uk-margin-small-right"></span> <span class="ytaa-play-text">Reproducir</span>
                        </button>
                        <button class="ytaa-btn-shuffle-all uk-button uk-button-default uk-button-large uk-border-pill">
                            <span uk-icon="icon: refresh" class="uk-margin-small-right"></span> Aleatorio
                        </button>
                    </div>
                </div>
            </div>

            <!-- Track List -->
            <div class="ytaa-playlist-list uk-margin-large-top">
                <div class="ytaa-playlist">
                    <ul class="uk-list uk-list-divider"></ul>
                </div>
            </div>

            <!-- Floating Mini Player (Sticky Bottom) -->
             <div class="ytaa-mini-player uk-card uk-card-default uk-card-body uk-padding-small uk-position-fixed uk-position-bottom-center uk-margin-bottom uk-box-shadow-xlarge uk-border-rounded" 
                 style="bottom: 20px; width: 90%; max-width: 700px; z-index: 980; backdrop-filter: blur(20px); background: rgba(255,255,255,0.9) !important; border: 1px solid rgba(0,0,0,0.05);">
                
                <div class="uk-grid-small uk-flex-middle" uk-grid>
                    <!-- Controls Left -->
                    <div class="uk-width-auto uk-flex uk-flex-middle">
                        <button class="ytaa-btn-prev uk-icon-button" style="background: transparent; border: none; color: #333; width: 32px; height: 32px;">
                            <span uk-icon="icon: chevron-left; ratio: 1.2"></span>
                        </button>
                        <button class="ytaa-btn-play uk-icon-button uk-margin-small-left uk-margin-small-right" style="background: #333 !important; color: #fff !important; width: 40px; height: 40px; border: none;">
                            <svg width="16" height="16" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="5,4 15,10 5,16"></polygon></svg>
                        </button>
                        <button class="ytaa-btn-next uk-icon-button" style="background: transparent; border: none; color: #333; width: 32px; height: 32px;">
                             <span uk-icon="icon: chevron-right; ratio: 1.2"></span>
                        </button>
                    </div>

                    <!-- Info Center -->
                    <div class="uk-width-expand uk-flex uk-flex-middle uk-flex-center uk-text-center uk-overflow-hidden">
                         <img src="" class="ytaa-cover-img uk-border-rounded uk-margin-small-right uk-box-shadow-small" style="width: 44px; height: 44px; object-fit: cover; display: none;">
                         <div class="uk-text-left" style="min-width: 0;">
                             <div class="ytaa-title uk-text-bold uk-text-truncate" style="font-size: 14px; color: #111; margin-bottom: 2px;"></div>
                             <div class="ytaa-artist uk-text-truncate" style="font-size: 12px; color: #666;"></div>
                         </div>
                    </div>

                    <!-- Extras Right -->
                    <div class="uk-width-auto uk-flex uk-flex-middle">
                        <?php if ($show_volume): ?>
                             <div class="ytaa-volume-control uk-flex uk-flex-middle uk-visible@s">
                                <span uk-icon="icon: receiver; ratio: 0.8" class="uk-margin-small-right" style="color: #666;"></span>
                                <input type="range" class="ytaa-volume-slider" min="0" max="1" step="0.1" value="1" style="width: 80px;">
                            </div>
                        <?php endif ?>
                        <div class="uk-margin-small-left">
                            <button class="uk-icon-button" uk-icon="icon: list" style="color: #333; background: transparent;"></button>
                        </div>
                    </div>
                </div>
                
                <!-- Hidden Progress for Logic -->
                 <div class="ytaa-progress-container uk-hidden">
                    <div class="ytaa-progress-bar"><div class="ytaa-progress-fill"></div></div>
                    <div class="ytaa-current-time"></div>
                    <div class="ytaa-duration"></div>
                 </div>
            </div>

        </div>

    <?php else: ?>
        <!-- Standard Interface (Card, Mini, Sticky) -->
        <div class="ytaa-main-interface uk-card uk-card-default uk-card-body uk-padding-small">

            <div class="uk-grid-small uk-flex-middle" uk-grid>

                <!-- Cover Art -->
                <?php if ($show_cover): ?>
                    <div class="uk-width-auto">
                        <div class="ytaa-cover-art uk-cover-container uk-border-rounded ytaa-cover-<?= $cover_size ?>">
                            <img src="" alt="Cover" class="ytaa-cover-img" width="80" height="80">
                            <div class="ytaa-cover-placeholder uk-background-muted uk-flex uk-flex-center uk-flex-middle">
                                <span uk-icon="icon: image; ratio: 2"></span>
                            </div>
                        </div>
                    </div>
                <?php endif ?>

                <!-- Controls & Info -->
                <div class="uk-width-expand">

                    <!-- Track Info -->
                    <div class="ytaa-track-info uk-margin-small-bottom">
                        <h4 class="ytaa-title uk-margin-remove uk-text-truncate">Select a track</h4>
                        <div class="ytaa-artist uk-text-meta uk-text-truncate"></div>
                    </div>

                    <!-- Progress Bar or Waveform -->
                    <?php if ($show_progress): ?>
                        <div class="ytaa-progress-container uk-margin-small-bottom">
                            <?php if ($show_waveform && $layout !== 'simple'): ?>
                                <div class="ytaa-waveform" id="waveform-<?= $attrs['id'] ?>"></div>
                            <?php else: ?>
                                <div class="ytaa-progress-bar">
                                    <div class="ytaa-progress-fill"></div>
                                </div>
                            <?php endif ?>
                            
                            <div class="uk-flex uk-flex-between uk-text-meta uk-text-small uk-margin-small-top">
                                <span class="ytaa-current-time">0:00</span>
                                <span class="ytaa-duration">0:00</span>
                            </div>
                        </div>
                    <?php endif ?>

                    <!-- Controls -->
                    <div class="ytaa-controls uk-flex uk-flex-middle uk-flex-between">

                        <div class="uk-flex uk-flex-middle">
                            <button class="ytaa-btn-prev uk-icon-button uk-margin-small-right"
                                uk-icon="icon: chevron-left"></button>
                            <button class="ytaa-btn-play uk-icon-button uk-button-primary">
                                <svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="5,4 15,10 5,16"></polygon></svg>
                            </button>
                            <button class="ytaa-btn-next uk-icon-button uk-margin-small-left"
                                uk-icon="icon: chevron-right"></button>
                        </div>
                        
                        <div class="uk-flex uk-flex-middle ytaa-extra-controls">
                             <?php if ($show_speed): ?>
                                <button class="ytaa-btn-speed uk-button uk-button-text uk-margin-small-right" type="button">1x</button>
                            <?php endif ?>

                            <?php if ($show_download): ?>
                            <a href="#" class="ytaa-btn-download uk-icon-button uk-margin-small-left" download uk-tooltip="Download" uk-icon="icon: download"></a>
                        <?php endif ?>
                        
                        <?php if ($show_platform_links): ?>
                        <a href="#" target="_blank" class="ytaa-btn-spotify uk-icon-button uk-margin-small-left" style="display:none;" title="Spotify"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.4 0 0 5.4 0 12s5.4 12 12 12 12-5.4 12-12S18.66 0 12 0zm5.521 17.34c-.24.359-.66.48-1.021.24-2.82-1.74-6.36-2.101-10.561-1.141-.418.122-.779-.179-.899-.539-.12-.421.18-.78.54-.9 4.56-1.021 8.52-.6 11.64 1.32.42.18.479.659.301 1.02zm1.44-3.3c-.301.42-.841.6-1.262.3-3.239-1.98-8.159-2.58-11.939-1.38-.479.12-1.02-.12-1.14-.6-.12-.48.12-1.021.6-1.141C9.6 9.9 15 10.561 18.72 12.84c.361.181.54.78.241 1.2zm.12-3.36C15.24 8.4 8.82 8.16 5.16 9.301c-.6.179-1.2-.181-1.38-.721-.18-.601.18-1.2.72-1.381 4.26-1.26 11.28-1.02 15.721 1.621.539.3.719 1.02.419 1.56-.299.421-1.02.599-1.559.3z"/></svg></a>
                        <a href="#" target="_blank" class="ytaa-btn-apple uk-icon-button uk-margin-small-left" style="display:none;" uk-icon="icon: apple" title="Apple Music"></a>
                        <a href="#" target="_blank" class="ytaa-btn-amazon uk-icon-button uk-margin-small-left" style="display:none;" title="Amazon Music"><svg width="16" height="16" viewBox="0 0 512 512" fill="currentColor"><path d="M257.2 162.7c-48.7 1.8-169.5 15.5-169.5 117.5 0 109.5 138.3 114 183.5 43.2 6.5 10.2 35.4 37.5 45.3 46.8l56.8-56S341 288.9 341 261.4V114.3C341 89 316.5 32 228.7 32 140.7 32 94 87 94 136.3l73.5 6.8c16.3-49.5 54.2-49.5 54.2-49.5 40.7-.1 35.5 29.8 35.5 69.1zm0 86.8c0 80-84.2 68-84.2 17.2 0-47.2 50.5-56.7 84.2-57.8v40.6zm136 163.5c-7.7 10-70 67-174.5 67S34.2 408.5 9.7 379c-6.8-7.7 1-11.3 5.5-8.3C88.5 415.2 203 488.5 387.7 401c7.5-3.7 13.3 2 5.5 12zm39.8 2.2c-6.5 15.8-16 26.8-21.2 31-5.5 4.5-9.5 2.7-6.5-3.8s19.3-46.5 12.7-55c-6.5-8.3-37-4.3-48-3.2-10.8 1-13 2-14-.3-2.3-5.7 21.7-15.5 37.5-17.5 15.7-1.8 41-.8 46 5.7 3.7 5.1 0 27.1-6.5 43.1z"/></svg></a>
                        <?php endif ?>

                        <?php if ($show_external_link): ?>
                                <a href="#" class="ytaa-btn-link uk-icon-button uk-margin-small-right" target="_blank" uk-icon="icon: link"></a>
                            <?php endif ?>

                            <?php if ($show_volume): ?>
                                <div class="ytaa-volume-control uk-flex uk-flex-middle uk-visible@s">
                                    <span uk-icon="icon: receiver" class="uk-margin-small-right"></span>
                                    <input type="range" class="ytaa-volume-slider" min="0" max="1" step="0.1" value="1">
                                </div>
                            <?php endif ?>
                        </div>

                    </div>

                </div>
            </div>

            <!-- Standard Playlist View (Legacy/Card fallback) -->
            <?php if ($layout === 'playlist'): ?>
                <div class="ytaa-playlist uk-margin-top uk-border-top uk-padding-small uk-padding-remove-horizontal">
                    <?php if (empty($tracks)): ?>
                        <div class="uk-alert uk-alert-warning">No audio tracks found. Please add tracks in the element settings.</div>
                    <?php else: ?>
                        <ul class="uk-list uk-list-divider">
                            <!-- Playlist items will be injected by JS -->
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif ?>
        </div>
    <?php endif; ?>

</div>