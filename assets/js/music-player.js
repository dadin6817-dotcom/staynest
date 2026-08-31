// assets/js/music-player.js - Stay Alive Music Player
// Menggunakan file MP3: nastelbom-elegant.mp3

(function() {
    console.log('🎵 StayNest Music Player Loaded - nastelbom-elegant.mp3');
    
    // ============================================
    // KONFIGURASI LAGU
    // ============================================
    const musicUrls = [
        '/staynest/assets/music/nastelbom-elegant.mp3',
    ];
    
    const musicNames = [
        'Nastelbom Elegant'
    ];
    
    let audio = null;
    let isPlaying = false;
    let currentTrackIndex = 0;
    let volume = 0.4;
    let isOverlayShown = false;
    
    // ============================================
    // INISIALISASI AUDIO GLOBAL
    // ============================================
    function initAudio() {
        // Cek apakah audio sudah ada dari halaman sebelumnya
        if (window.stayNestAudio && window.stayNestAudio.src) {
            audio = window.stayNestAudio;
            console.log('✅ Audio instance restored from previous page');
            // Restore volume
            const savedVolume = localStorage.getItem('staynest_musicVolume');
            if (savedVolume !== null) {
                volume = parseFloat(savedVolume);
                audio.volume = volume;
            }
            // Restore playing state
            const savedIsPlaying = sessionStorage.getItem('staynest_isPlaying');
            if (savedIsPlaying === 'true') {
                isPlaying = true;
                // Try to resume playback
                try {
                    audio.play().catch(() => {});
                } catch(e) {}
            }
        } else {
            audio = new Audio();
            audio.loop = true;
            audio.volume = volume;
            window.stayNestAudio = audio;
            console.log('✅ New audio instance created');
            
            // Debug event listeners
            audio.addEventListener('canplay', function() {
                console.log('✅ Audio can play!');
            });
            audio.addEventListener('error', function(e) {
                console.log('❌ Audio error:', e);
                console.log('❌ Pastikan file nastelbom-elegant.mp3 ada di folder assets/music/');
            });
            audio.addEventListener('loadeddata', function() {
                console.log('✅ Audio loaded successfully!');
            });
            audio.addEventListener('playing', function() {
                console.log('🎵 Music is playing!');
                updateUI();
            });
            audio.addEventListener('pause', function() {
                console.log('⏸️ Music paused');
                updateUI();
            });
        }
        
        // Load track
        if (!audio.src || audio.src === '') {
            audio.src = musicUrls[0];
            audio.load();
            console.log('📁 Loading music:', musicUrls[0]);
        }
        
        // Restore playback position
        const savedTime = sessionStorage.getItem('staynest_audioCurrentTime');
        if (savedTime !== null && !isNaN(parseFloat(savedTime)) && audio.src) {
            audio.currentTime = parseFloat(savedTime);
        }
        
        // Restore volume
        const savedVolume = localStorage.getItem('staynest_musicVolume');
        if (savedVolume !== null) {
            volume = parseFloat(savedVolume);
            audio.volume = volume;
        }
        
        // Restore playing state
        const savedIsPlaying = sessionStorage.getItem('staynest_isPlaying');
        if (savedIsPlaying === 'true' && audio.src) {
            isPlaying = true;
            tryAutoPlay();
        }
        
        // Save state before page unload
        window.addEventListener('beforeunload', function() {
            if (audio) {
                sessionStorage.setItem('staynest_audioCurrentTime', audio.currentTime);
                sessionStorage.setItem('staynest_isPlaying', isPlaying);
            }
            localStorage.setItem('staynest_musicVolume', volume);
        });
        
        // Periodic save
        setInterval(function() {
            if (audio && !audio.paused && audio.src) {
                sessionStorage.setItem('staynest_audioCurrentTime', audio.currentTime);
                sessionStorage.setItem('staynest_isPlaying', true);
            }
        }, 3000);
    }
    
    function tryAutoPlay() {
        if (!audio || !audio.src) return;
        
        const playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise.then(function() {
                isPlaying = true;
                updateUI();
                console.log('✅ Auto-play SUCCESS! Music is playing.');
            }).catch(function(error) {
                console.log('⚠️ Auto-play blocked by browser:', error);
                if (!isOverlayShown) {
                    showPlayOverlay();
                }
            });
        }
    }
    
    // ============================================
    // OVERLAY YANG BISA DIKLIK
    // ============================================
    function showPlayOverlay() {
        if (isOverlayShown) return;
        isOverlayShown = true;
        
        const existingOverlay = document.getElementById('staynestPlayOverlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
        
        const overlay = document.createElement('div');
        overlay.id = 'staynestPlayOverlay';
        overlay.innerHTML = `
            <div style="text-align: center; padding: 20px; max-width: 400px;">
                <div style="font-size: 70px; margin-bottom: 20px; animation: staynestBounce 1s ease-in-out infinite;">🎵</div>
                <div style="font-size: 24px; font-weight: bold; margin-bottom: 10px; color: white;">Click to Play Music</div>
                <div style="font-size: 14px; opacity: 0.8; margin-bottom: 20px;">Tap to enable background music 🎧</div>
                <button id="staynestPlayButton" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 14px 40px; border-radius: 50px; font-size: 18px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 5px 25px rgba(102,126,234,0.5);">
                    <i class="fas fa-play"></i> Play Music
                </button>
                <div style="margin-top: 25px; font-size: 12px; opacity: 0.5;">🎧 Nastelbom Elegant</div>
            </div>
            <style>
                @keyframes staynestBounce {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-15px); }
                }
                #staynestPlayButton:hover { 
                    transform: scale(1.05); 
                    box-shadow: 0 8px 30px rgba(102,126,234,0.7);
                }
            </style>
        `;
        
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.9)';
        overlay.style.zIndex = '999999';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.color = 'white';
        overlay.style.fontFamily = 'Inter, sans-serif';
        overlay.style.cursor = 'pointer';
        overlay.style.backdropFilter = 'blur(10px)';
        
        overlay.onclick = function(e) {
            e.stopPropagation();
            startMusicAndRemoveOverlay();
        };
        
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';
        
        const playBtn = document.getElementById('staynestPlayButton');
        if (playBtn) {
            playBtn.onclick = function(e) {
                e.stopPropagation();
                startMusicAndRemoveOverlay();
            };
        }
    }
    
    function startMusicAndRemoveOverlay() {
        const overlay = document.getElementById('staynestPlayOverlay');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
            isOverlayShown = false;
        }
        
        if (!audio || !audio.src || audio.src === '') {
            audio.src = musicUrls[0];
            audio.load();
        }
        
        audio.volume = volume;
        audio.play().then(() => {
            isPlaying = true;
            updateUI();
            console.log('✅ Music started after user click!');
        }).catch(err => {
            console.log('❌ Failed to play:', err);
            // Try reloading audio
            audio.src = musicUrls[0];
            audio.load();
            setTimeout(() => {
                audio.play().catch(() => {});
            }, 500);
        });
    }
    
    // ============================================
    // CREATE MUSIC PLAYER UI (Tombol Floating)
    // ============================================
    function createMusicPlayer() {
        if (document.getElementById('staynestElegantMusicPlayer')) return;
        
        const playerHTML = `
            <div id="staynestElegantMusicPlayer" style="position: fixed; bottom: 20px; left: 20px; z-index: 9998;">
                <div id="staynestMusicToggleBtn" style="width: 52px; height: 52px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 8px 25px rgba(102,126,234,0.4); transition: all 0.3s ease; position: relative;">
                    <div id="staynestMusicWave" style="position: absolute; width: 100%; height: 100%; border-radius: 50%; background: rgba(102,126,234,0.3); animation: staynestPulse 1.5s ease-in-out infinite; display: none;"></div>
                    <i id="staynestMusicIcon" class="fas fa-headphones" style="color: white; font-size: 20px; z-index: 1;"></i>
                </div>
                
                <div id="staynestMusicPlayerPanel" style="position: absolute; bottom: 70px; left: 0; width: 280px; background: rgba(255,255,255,0.97); backdrop-filter: blur(20px); border-radius: 20px; padding: 18px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); display: none; transition: all 0.3s ease; border: 1px solid rgba(102,126,234,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 8px; height: 8px; background: #22c55e; border-radius: 50; animation: staynestPulse 1.5s infinite;"></div>
                            <h3 style="font-size: 13px; font-weight: 600; color: #667eea;"><i class="fas fa-music"></i> StayNest Radio</h3>
                        </div>
                        <button id="staynestCloseMusicPanel" style="background: none; border: none; cursor: pointer; color: #999; font-size: 20px; padding: 0 5px;">&times;</button>
                    </div>
                    
                    <div style="margin-bottom: 12px; padding: 10px; background: linear-gradient(135deg, #f5f0ff, #fdf2f8); border-radius: 14px; text-align: center;">
                        <div id="staynestMusicNoteAnim" style="font-size: 22px; margin-bottom: 4px;">🎧</div>
                        <p id="staynestSongName" style="font-size: 13px; font-weight: 600; color: #333;">Nastelbom Elegant</p>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span id="staynestCurrentTime" style="font-size: 10px; color: #999;">0:00</span>
                            <span id="staynestDuration" style="font-size: 10px; color: #999;">--:--</span>
                        </div>
                        <div style="width: 100%; height: 4px; background: #e8e8e8; border-radius: 4px; cursor: pointer; position: relative;" id="staynestProgressBar">
                            <div id="staynestProgress" style="width: 0%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 4px;"></div>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: center; align-items: center; gap: 20px; margin-bottom: 12px;">
                        <button id="staynestPlayPauseMusic" style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); border: none; cursor: pointer; box-shadow: 0 4px 15px rgba(102,126,234,0.3); transition: transform 0.2s ease;">
                            <i id="staynestPlayPauseIcon" class="fas fa-play" style="color: white; font-size: 16px;"></i>
                        </button>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-volume-down" style="color: #667eea; font-size: 12px;"></i>
                        <input type="range" id="staynestVolumeSlider" min="0" max="100" value="40" style="flex: 1; height: 4px; border-radius: 4px; background: #e8e8e8; -webkit-appearance: none; appearance: none;">
                        <i class="fas fa-volume-up" style="color: #667eea; font-size: 12px;"></i>
                        <span id="staynestVolumePercent" style="font-size: 10px; color: #999; min-width: 35px; text-align: right;">40%</span>
                    </div>
                </div>
            </div>
            <style>
                @keyframes staynestPulse {
                    0%, 100% { transform: scale(1); opacity: 0.5; }
                    50% { transform: scale(1.3); opacity: 0.1; }
                }
                #staynestMusicToggleBtn:hover { 
                    transform: scale(1.05); 
                    box-shadow: 0 8px 30px rgba(102,126,234,0.6);
                }
                #staynestPlayPauseMusic:hover {
                    transform: scale(1.05);
                }
                #staynestVolumeSlider::-webkit-slider-thumb {
                    -webkit-appearance: none;
                    appearance: none;
                    width: 14px;
                    height: 14px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    cursor: pointer;
                    box-shadow: 0 2px 8px rgba(102,126,234,0.3);
                }
                #staynestVolumeSlider::-moz-range-thumb {
                    width: 14px;
                    height: 14px;
                    border-radius: 50%;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    cursor: pointer;
                    border: none;
                }
            </style>
        `;
        
        document.body.insertAdjacentHTML('beforeend', playerHTML);
        attachEvents();
        updateUI();
        
        // Set duration when metadata loaded
        if (audio) {
            audio.addEventListener('loadedmetadata', function() {
                const durationElem = document.getElementById('staynestDuration');
                if (durationElem && audio.duration && !isNaN(audio.duration)) {
                    const minutes = Math.floor(audio.duration / 60);
                    const seconds = Math.floor(audio.duration % 60);
                    durationElem.textContent = minutes + ':' + (seconds < 10 ? '0' + seconds : seconds);
                }
            });
            
            // Update progress when playing
            audio.addEventListener('timeupdate', updateProgress);
        }
    }
    
    function updateUI() {
        const playIcon = document.getElementById('staynestPlayPauseIcon');
        const wave = document.getElementById('staynestMusicWave');
        const musicIcon = document.getElementById('staynestMusicIcon');
        
        if (isPlaying) {
            if (playIcon) { 
                playIcon.className = 'fas fa-pause';
                playIcon.style.fontSize = '16px';
            }
            if (wave) wave.style.display = 'block';
            if (musicIcon) { 
                musicIcon.className = 'fas fa-stop';
                musicIcon.style.fontSize = '18px';
            }
        } else {
            if (playIcon) { 
                playIcon.className = 'fas fa-play';
                playIcon.style.fontSize = '16px';
            }
            if (wave) wave.style.display = 'none';
            if (musicIcon) { 
                musicIcon.className = 'fas fa-headphones';
                musicIcon.style.fontSize = '20px';
            }
        }
    }
    
    function attachEvents() {
        const toggle = document.getElementById('staynestMusicToggleBtn');
        const panel = document.getElementById('staynestMusicPlayerPanel');
        const close = document.getElementById('staynestCloseMusicPanel');
        const playPause = document.getElementById('staynestPlayPauseMusic');
        const volumeSlider = document.getElementById('staynestVolumeSlider');
        const volumePercent = document.getElementById('staynestVolumePercent');
        const progressBar = document.getElementById('staynestProgressBar');
        
        if (toggle) {
            toggle.onclick = function(e) {
                e.stopPropagation();
                if (panel) {
                    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
                }
            };
        }
        
        if (close) {
            close.onclick = function(e) {
                e.stopPropagation();
                if (panel) panel.style.display = 'none';
            };
        }
        
        if (playPause) {
            playPause.onclick = function(e) {
                e.stopPropagation();
                togglePlayPause();
            };
        }
        
        if (volumeSlider) {
            volumeSlider.oninput = function(e) {
                const val = parseFloat(e.target.value);
                volume = val / 100;
                if (audio) audio.volume = volume;
                if (volumePercent) volumePercent.textContent = val + '%';
                localStorage.setItem('staynest_musicVolume', volume);
                // Update volume icon
                const icon = document.querySelector('#staynestVolumeSlider + i');
                if (icon) {
                    if (val === 0) icon.className = 'fas fa-volume-mute';
                    else if (val < 50) icon.className = 'fas fa-volume-down';
                    else icon.className = 'fas fa-volume-up';
                }
            };
        }
        
        if (progressBar) {
            progressBar.onclick = function(e) {
                const rect = this.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const percent = x / rect.width;
                if (audio && audio.duration && !isNaN(audio.duration)) {
                    audio.currentTime = percent * audio.duration;
                }
            };
        }
    }
    
    function updateProgress() {
        if (!audio) return;
        const progress = document.getElementById('staynestProgress');
        const currentTime = document.getElementById('staynestCurrentTime');
        if (progress && currentTime && audio.duration && !isNaN(audio.duration)) {
            const percent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = Math.min(percent, 100) + '%';
            const minutes = Math.floor(audio.currentTime / 60);
            const seconds = Math.floor(audio.currentTime % 60);
            currentTime.textContent = minutes + ':' + (seconds < 10 ? '0' + seconds : seconds);
        }
    }
    
    function playMusic() {
        if (!audio) return;
        audio.play().catch(function(e) {
            console.log('Play error:', e);
            // Try reloading
            audio.src = musicUrls[0];
            audio.load();
            setTimeout(function() {
                audio.play().catch(function() {});
            }, 500);
        });
        isPlaying = true;
        updateUI();
        saveState();
    }
    
    function pauseMusic() {
        if (audio) audio.pause();
        isPlaying = false;
        updateUI();
        saveState();
    }
    
    function togglePlayPause() {
        if (isPlaying) {
            pauseMusic();
        } else {
            playMusic();
        }
    }
    
    function saveState() {
        if (audio) {
            try {
                sessionStorage.setItem('staynest_audioCurrentTime', audio.currentTime);
                sessionStorage.setItem('staynest_isPlaying', isPlaying);
            } catch(e) {}
        }
        try {
            localStorage.setItem('staynest_musicVolume', volume);
        } catch(e) {}
    }
    
    // ============================================
    // START
    // ============================================
    initAudio();
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            createMusicPlayer();
            // Check if audio should be playing
            const savedIsPlaying = sessionStorage.getItem('staynest_isPlaying');
            if (savedIsPlaying === 'true' && audio && audio.src) {
                tryAutoPlay();
            }
        });
    } else {
        createMusicPlayer();
        const savedIsPlaying = sessionStorage.getItem('staynest_isPlaying');
        if (savedIsPlaying === 'true' && audio && audio.src) {
            tryAutoPlay();
        }
    }
    
    console.log('🎵 StayNest Music Player ready!');
})();