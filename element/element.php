<?php

namespace YOOtheme;

return [
    'transforms' => [
        'render' => function ($node) {
            // Initialize tracks array
            $tracks = [];

            // Process repeater items (tracks)
            if (!empty($node->children)) {
                foreach ($node->children as $child) {
                    if ($child->type === 'advanced_audio_track') {
                        $props = $child->props;

                        // Skip if no audio source
                        if (empty($props['audio_src'])) {
                            continue;
                        }

                        $tracks[] = [
                            'title' => $props['title'] ?? 'Unknown Title',
                            'artist' => $props['artist'] ?? 'Unknown Artist',
                            'audio' => $props['audio_src'],
                            'cover' => $props['cover_image'] ?? '',
                            'duration' => $props['duration'] ?? '',
                            'external_link' => $props['external_link'] ?? '',
                            'external_link_label' => $props['external_link_label'] ?? '',
                            'link_apple' => $props['link_apple'] ?? '',
                            'link_amazon' => $props['link_amazon'] ?? '',
                            'link_spotify' => $props['link_spotify'] ?? '',
                        ];
                    }
                }
            }

            // Pass tracks to the node props for the template
            $node->props['tracks_data'] = $tracks;
            $node->props['_debug_children'] = count($node->children ?? []);
            $node->props['_debug_tracks'] = count($tracks);
            
            // Album Details
            $node->props['album_title'] = $node->props['album_title'] ?? '';
            $node->props['album_artist'] = $node->props['album_artist'] ?? '';
            $node->props['album_cover'] = $node->props['album_cover'] ?? '';
            $node->props['album_description'] = $node->props['album_description'] ?? '';

            // Ensure default values for settings
            $node->props['layout'] = $node->props['layout'] ?? 'simple';
            $node->props['primary_color'] = $node->props['primary_color'] ?? '#1e87f0';
            
            // Defaults for new features
            $node->props['show_download'] = $node->props['show_download'] ?? false;
            $node->props['show_speed'] = $node->props['show_speed'] ?? false;
            $node->props['show_external_link'] = $node->props['show_external_link'] ?? true;
            $node->props['show_platform_links'] = $node->props['show_platform_links'] ?? true;
            $node->props['show_waveform'] = $node->props['show_waveform'] ?? true;

            // Render only if we have tracks
            return !empty($tracks);
        },
    ],
];
