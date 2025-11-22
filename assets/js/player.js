/**
 * YOOtheme Advanced Audio Player - WaveSurfer.js Implementation
 */
(function () {
    'use strict';

    // Global registry to track active players
    const activePlayers = new Set();

    class YTAAPlayer {
        constructor(element) {
            this.element = element;
            activePlayers.add(this);

            // Read tracks from global variable
            const id = element.id.replace(/-/g, '_');
            const tracksVar = window[`ytaa_tracks_${id}`];
            
            if (Array.isArray(tracksVar)) {
                this.tracks = tracksVar;
            } else {
                // Fallback or empty
                this.tracks = [];
                console.error('YTAA: No tracks found for', id);
            }

            this.config = {
                autoplay: element.dataset.autoplay === 'true',
                loop: element.dataset.loop === 'true',
                shuffle: element.dataset.shuffle === 'true',
                waveform: element.dataset.waveform === 'true'
            };

            this.currentIndex = 0;
            this.wavesurfer = null;
            this.isPlaying = false;
            this.shouldAutoplay = false;
            this.hasAutoplayed = false;

            // UI Elements
            this.ui = {
                playBtn: element.querySelector('.ytaa-btn-play'),
                prevBtn: element.querySelector('.ytaa-btn-prev'),
                nextBtn: element.querySelector('.ytaa-btn-next'),
                progressBar: element.querySelector('.ytaa-progress-bar'),
                progressFill: element.querySelector('.ytaa-progress-fill'),
                currentTime: element.querySelector('.ytaa-current-time'),
                duration: element.querySelector('.ytaa-duration'),
                title: element.querySelector('.ytaa-title'),
                artist: element.querySelector('.ytaa-artist'),
                coverImgs: element.querySelectorAll('.ytaa-cover-img'),
                coverPlaceholders: element.querySelectorAll('.ytaa-cover-placeholder'),
                volumeSlider: element.querySelector('.ytaa-volume-slider'),
                playlist: element.querySelector('.ytaa-playlist ul'),
                downloadBtns: element.querySelectorAll('.ytaa-btn-download'),
                linkBtns: element.querySelectorAll('.ytaa-btn-link'),
                spotifyBtns: element.querySelectorAll('.ytaa-btn-spotify'),
                appleBtns: element.querySelectorAll('.ytaa-btn-apple'),
                amazonBtns: element.querySelectorAll('.ytaa-btn-amazon'),
                speedBtns: element.querySelectorAll('.ytaa-btn-speed'),
                waveform: element.querySelector('.ytaa-waveform'),
                headerPlayBtn: element.querySelector('.ytaa-btn-header-play'),
                playText: element.querySelector('.ytaa-play-text')
            };

            // Create hidden container if needed for Simple layout or if waveform disabled but we use WS engine
            if (!this.ui.waveform) {
                this.ui.waveform = document.createElement('div');
                this.ui.waveform.style.display = 'none';
                this.element.appendChild(this.ui.waveform);
            }

            this.init();
        }

        destroy() {
            if (this.wavesurfer) {
                this.wavesurfer.destroy();
            }
            activePlayers.delete(this);
        }

        init() {
            if (typeof WaveSurfer === 'undefined') {
                console.error('YTAA: WaveSurfer.js not loaded!');
                return;
            }

            if (this.tracks.length === 0) {
                return;
            }

            // Bind Events
            if (this.ui.playBtn) this.ui.playBtn.addEventListener('click', () => this.togglePlay());
            if (this.ui.headerPlayBtn) this.ui.headerPlayBtn.addEventListener('click', () => this.togglePlay());
            
            if (this.ui.prevBtn) this.ui.prevBtn.addEventListener('click', () => this.prev());
            if (this.ui.nextBtn) this.ui.nextBtn.addEventListener('click', () => this.next());

            if (this.ui.progressBar) {
                this.ui.progressBar.addEventListener('click', (e) => {
                    if (!this.wavesurfer) return;
                    const rect = this.ui.progressBar.getBoundingClientRect();
                    const percent = (e.clientX - rect.left) / rect.width;
                    this.seek(percent);
                });
            }

            if (this.ui.volumeSlider) {
                this.ui.volumeSlider.addEventListener('input', (e) => this.setVolume(e.target.value));
            }

            // Speed Control
            this.ui.speedBtns.forEach(btn => {
                btn.addEventListener('click', () => this.toggleSpeed(btn));
            });

            // Render Playlist
            if (this.ui.playlist) {
                this.renderPlaylist();
            }

            // Initialize WaveSurfer
            this.initWaveSurfer();
        }

        initWaveSurfer() {
            // Get color from CSS variable or default
            const style = getComputedStyle(this.element);
            const primaryColor = style.getPropertyValue('--ytaa-primary-color').trim() || '#1e87f0';

            this.wavesurfer = WaveSurfer.create({
                container: this.ui.waveform,
                waveColor: 'rgba(0,0,0,0.1)',
                progressColor: primaryColor,
                cursorColor: primaryColor,
                height: 60,
                responsive: true,
                normalize: true,
                backend: 'MediaElement'
            });

            // Events
            this.wavesurfer.on('play', () => {
                this.isPlaying = true;
                this.updatePlayBtn();
                this.stopOtherPlayers();
                requestAnimationFrame(this.step.bind(this));
            });
            
            this.wavesurfer.on('pause', () => {
                this.isPlaying = false;
                this.updatePlayBtn();
            });
            
            this.wavesurfer.on('finish', () => {
                if (this.config.loop && this.tracks.length === 1) {
                    this.play();
                } else {
                    this.next();
                }
            });

            this.wavesurfer.on('ready', () => {
                if (this.ui.duration) this.ui.duration.textContent = this.formatTime(this.wavesurfer.getDuration());
                
                // Autoplay logic
                if (this.shouldAutoplay) {
                     this.play();
                     this.shouldAutoplay = false;
                } else if (this.config.autoplay && !this.hasAutoplayed) {
                    this.play();
                    this.hasAutoplayed = true;
                }
            });
            
            // Error handling
            this.wavesurfer.on('error', (e) => {
                console.error('YTAA: WaveSurfer error', e);
            });

            // Initial Load
            this.loadTrack(this.currentIndex);

            // Enable Draggable if Mini Layout
            if (this.element.classList.contains('ytaa-layout-mini')) {
                this.enableDraggable();
            }
        }

        loadTrack(index) {
            const track = this.tracks[index];
            if (!track) return;

            // Update UI Text
            if (this.ui.title) this.ui.title.textContent = track.title;
            if (this.ui.artist) this.ui.artist.textContent = track.artist;

            // Update Cover
            if (this.ui.coverImgs && this.ui.coverImgs.length > 0) {
                this.ui.coverImgs.forEach(img => {
                    if (track.cover) {
                        this.element.style.removeProperty('--ytaa-primary-color');
                        img.removeAttribute('crossorigin');
                        img.src = track.cover;
                        img.style.display = 'block';
                    } else {
                        img.style.display = 'none';
                    }
                });

                if (track.cover) {
                    if (this.ui.coverPlaceholders) {
                        this.ui.coverPlaceholders.forEach(el => el.style.display = 'none');
                    }
                    this.extractColor(track.cover);
                } else {
                    if (this.ui.coverPlaceholders) {
                        this.ui.coverPlaceholders.forEach(el => {
                            el.style.display = 'flex'; // Use flex to center content
                        });
                    }
                    this.element.style.removeProperty('--ytaa-primary-color');
                    if (this.wavesurfer) {
                         this.wavesurfer.setOptions({
                            progressColor: '#1e87f0',
                            cursorColor: '#1e87f0'
                        });
                    }
                }
            }

            // Update Download Button
            this.ui.downloadBtns.forEach(btn => { btn.href = track.audio; });

            // Update External Link
            this.ui.linkBtns.forEach(btn => {
                if (track.external_link) {
                    btn.href = track.external_link;
                    btn.style.display = 'inline-flex';
                    if (track.external_link_label) {
                        btn.title = track.external_link_label;
                    }
                } else {
                    btn.style.display = 'none';
                }
            });

            // Update Social Links
            this.ui.spotifyBtns.forEach(btn => {
                if (track.link_spotify) {
                    btn.href = track.link_spotify;
                    btn.style.display = 'inline-flex';
                } else {
                    btn.style.display = 'none';
                }
            });
            this.ui.appleBtns.forEach(btn => {
                if (track.link_apple) {
                    btn.href = track.link_apple;
                    btn.style.display = 'inline-flex';
                } else {
                    btn.style.display = 'none';
                }
            });
            this.ui.amazonBtns.forEach(btn => {
                if (track.link_amazon) {
                    btn.href = track.link_amazon;
                    btn.style.display = 'inline-flex';
                } else {
                    btn.style.display = 'none';
                }
            });

            // Highlight playlist
            if (this.ui.playlist) {
                const items = this.ui.playlist.querySelectorAll('li');
                items.forEach(item => item.classList.remove('uk-active'));
                if (items[index]) items[index].classList.add('uk-active');
            }

            // Load Audio
            if (this.wavesurfer) {
                // If we are already playing (switching tracks), set flag for autoplay
                if (this.isPlaying) {
                    this.shouldAutoplay = true;
                }
                this.wavesurfer.load(track.audio);
            }
        }

        extractColor(url) {
            if (typeof ColorThief === 'undefined' || !url) return;
            
            const img = new Image();
            img.crossOrigin = "Anonymous";
            img.src = url;

            const applyColor = () => {
                try {
                    const colorThief = new ColorThief();
                    const color = colorThief.getColor(img);
                    if (color) {
                        const rgb = `rgb(${color[0]}, ${color[1]}, ${color[2]})`;
                        this.element.style.setProperty('--ytaa-primary-color', rgb);
                        // Update Waveform Color
                        if (this.wavesurfer) {
                            this.wavesurfer.setOptions({
                                progressColor: rgb,
                                cursorColor: rgb
                            });
                        }
                    }
                } catch (e) {
                    // Silently fail if CORS blocks canvas access
                }
            };
            
            if (img.complete) applyColor();
            else img.addEventListener('load', applyColor);
        }

        stopOtherPlayers() {
            activePlayers.forEach(player => {
                if (player !== this && player.isPlaying) {
                    player.pause();
                }
            });
        }

        togglePlay() {
            if (!this.wavesurfer) return;
            this.wavesurfer.playPause();
        }

        play() {
            if (this.wavesurfer) this.wavesurfer.play();
        }

        pause() {
            if (this.wavesurfer) this.wavesurfer.pause();
        }

        prev() {
            let index = this.currentIndex - 1;
            if (index < 0) index = this.tracks.length - 1;
            this.currentIndex = index;
            this.shouldAutoplay = true; // Always play when changing track manually
            this.loadTrack(index);
        }

        next() {
            let index = this.currentIndex + 1;
            if (index >= this.tracks.length) {
                if (this.config.loop) {
                    index = 0;
                } else {
                    this.pause();
                    return;
                }
            }
            this.currentIndex = index;
            this.shouldAutoplay = true; // Always play when changing track manually
            this.loadTrack(index);
        }

        seek(percent) {
            if (this.wavesurfer) {
                this.wavesurfer.seekTo(percent);
            }
        }

        step() {
            if (!this.wavesurfer || !this.isPlaying) return;

            const seek = this.wavesurfer.getCurrentTime() || 0;
            const duration = this.wavesurfer.getDuration() || 0;

            // Update Standard Progress Bar (for simple layout or fallback)
            if (this.ui.progressFill) {
                const percent = (seek / duration) * 100 || 0;
                this.ui.progressFill.style.width = `${percent}%`;
            }

            if (this.ui.currentTime) this.ui.currentTime.textContent = this.formatTime(seek);
            // Duration is usually updated on ready, but can update here if changed
            
            if (this.isPlaying) {
                requestAnimationFrame(this.step.bind(this));
            }
        }

        updatePlayBtn() {
            // Update Header Button Text
            if (this.ui.playText) {
                this.ui.playText.textContent = this.isPlaying ? 'Pausa' : 'Reproducir';
            }

            if (!this.ui.playBtn) return;
            
            const playSVG = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="5,4 15,10 5,16"></polygon></svg>';
            const pauseSVG = '<svg width="20" height="20" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><rect fill="currentColor" x="5" y="4" width="3" height="12"></rect><rect fill="currentColor" x="12" y="4" width="3" height="12"></rect></svg>';
            
            this.ui.playBtn.innerHTML = this.isPlaying ? pauseSVG : playSVG;
            
            // Ensure UIkit doesn't try to overwrite it if it was observing
            this.ui.playBtn.removeAttribute('uk-icon');
        }

        renderPlaylist() {
            this.ui.playlist.innerHTML = '';
            const isAppleLayout = this.element.classList.contains('ytaa-layout-apple');

            this.tracks.forEach((track, i) => {
                const li = document.createElement('li');
                
                if (isAppleLayout) {
                    // Apple Style List Item
                    li.innerHTML = `
                        <div class="ytaa-track-num">${i + 1}</div>
                        <div class="ytaa-track-play-icon"><svg width="12" height="12" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><polygon fill="currentColor" points="5,4 15,10 5,16"></polygon></svg></div>
                        <div class="ytaa-track-title uk-text-truncate">${track.title}</div>
                        <div class="ytaa-track-duration">${track.duration || ''}</div>
                        <div class="ytaa-track-menu"><span uk-icon="icon: more"></span></div>
                    `;
                } else {
                    // Standard Style
                    li.innerHTML = `
                        <div class="uk-grid-small uk-flex-middle" uk-grid>
                            <div class="uk-width-expand">
                                <div class="uk-text-truncate"><strong>${track.title}</strong></div>
                                <div class="uk-text-small uk-text-muted uk-text-truncate">${track.artist}</div>
                            </div>
                            <div class="uk-width-auto">
                                <span class="uk-text-small uk-text-muted">${track.duration || ''}</span>
                            </div>
                        </div>
                    `;
                }

                li.style.cursor = 'pointer';
                li.addEventListener('click', () => {
                    this.currentIndex = i;
                    this.shouldAutoplay = true;
                    this.loadTrack(i);
                });
                this.ui.playlist.appendChild(li);
            });
        }

        toggleSpeed(btn) {
            if (!this.wavesurfer) return;
            const speeds = [1.0, 1.5, 2.0, 0.5];
            let currentSpeed = this.wavesurfer.getPlaybackRate();
            // Match nearest speed
            let nextSpeed = speeds[0];
            for (let i = 0; i < speeds.length; i++) {
                if (Math.abs(speeds[i] - currentSpeed) < 0.1) {
                    nextSpeed = speeds[(i + 1) % speeds.length];
                    break;
                }
            }
            this.wavesurfer.setPlaybackRate(nextSpeed);
            this.ui.speedBtns.forEach(b => {
                b.textContent = nextSpeed + 'x';
            });
        }

        setVolume(val) {
            if (this.wavesurfer) this.wavesurfer.setVolume(val);
        }

        formatTime(secs) {
            const minutes = Math.floor(secs / 60) || 0;
            const seconds = Math.floor(secs - minutes * 60) || 0;
            return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }

        enableDraggable() {
            const el = this.element;
            let isDragging = false;
            let startX, startY, initialLeft, initialTop;

            const onMouseDown = (e) => {
                if (e.target.closest('button') || e.target.closest('input') || e.target.closest('.ytaa-playlist') || e.target.closest('wave')) return;
                
                isDragging = true;
                el.style.cursor = 'grabbing';
                
                const rect = el.getBoundingClientRect();
                if (!el.style.left) {
                    el.style.left = rect.left + 'px';
                    el.style.top = rect.top + 'px';
                    el.style.bottom = 'auto';
                    el.style.right = 'auto';
                    el.style.margin = '0';
                }

                startX = e.clientX || e.touches[0].clientX;
                startY = e.clientY || e.touches[0].clientY;
                initialLeft = parseFloat(el.style.left) || rect.left;
                initialTop = parseFloat(el.style.top) || rect.top;

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
                document.addEventListener('touchmove', onMouseMove, { passive: false });
                document.addEventListener('touchend', onMouseUp);
            };

            const onMouseMove = (e) => {
                if (!isDragging) return;
                e.preventDefault(); // Prevent scrolling on touch

                const clientX = e.clientX || (e.touches && e.touches[0].clientX);
                const clientY = e.clientY || (e.touches && e.touches[0].clientY);

                const dx = clientX - startX;
                const dy = clientY - startY;

                el.style.left = `${initialLeft + dx}px`;
                el.style.top = `${initialTop + dy}px`;
            };

            const onMouseUp = () => {
                isDragging = false;
                el.style.cursor = 'grab'; // Revert cursor
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
                document.removeEventListener('touchmove', onMouseMove);
                document.removeEventListener('touchend', onMouseUp);
            };

            el.addEventListener('mousedown', onMouseDown);
            el.addEventListener('touchstart', onMouseDown, { passive: false });
            
            // Set initial cursor
            el.style.cursor = 'grab';
        }
    }

    // Cleanup function for orphaned players
    function cleanupOrphans() {
        activePlayers.forEach(player => {
            if (!document.body.contains(player.element)) {
                player.destroy();
            }
        });
    }

    // Initialize on DOM Ready
    document.addEventListener('DOMContentLoaded', () => {
        cleanupOrphans(); // Clean any pre-existing
        const players = document.querySelectorAll('.ytaa-player');
        players.forEach(el => new YTAAPlayer(el));
    });

    // Re-init on YOOtheme Builder updates
    const observer = new MutationObserver((mutations) => {
        let needsCleanup = false;
        mutations.forEach((mutation) => {
            if (mutation.type === 'childList') {
                if (mutation.removedNodes.length > 0) {
                    needsCleanup = true;
                }
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        if (node.classList && node.classList.contains('ytaa-player') && !node.dataset.initialized) {
                            new YTAAPlayer(node);
                            node.dataset.initialized = 'true';
                        }
                        const players = node.querySelectorAll('.ytaa-player');
                        players.forEach(el => {
                            if (!el.dataset.initialized) {
                                new YTAAPlayer(el);
                                el.dataset.initialized = 'true';
                            }
                        });
                    }
                });
            }
        });
        
        if (needsCleanup) {
            cleanupOrphans();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

})();
