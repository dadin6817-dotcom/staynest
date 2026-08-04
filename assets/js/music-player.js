// assets/js/music-player.js - Stay Alive Music Player
// Menggunakan file MP3: nastelbom-elegant.mp3
(function() {
    console.log('StayNest Music Player Loaded - nastelbom-elegant.mp3');
    
    // ============================================
    // KONFIGURASI LAGU (Sesuai dengan file MP3 Anda)
    // ============================================
    const musicUrls = [
        '/assets/music/nastelbom-elegant.mp3',  // File MP3 Anda
    ];
    
    const musicNames = [
        'Nastelbom Elegant'
    ];
    
    let audio = null;
    let isPlaying = false;
    let currentTrackIndex = 0;
    let volume = 0.4; // Volume 40%
    
    // ============================================
    // INISIALISASI AUDIO GLOBAL
    // ============================================
    function initAudio() {
        // Cek apakah audio sudah ada dari halaman sebelumnya
        if (window.stayNestAudio) {
            audio = window.stayNestAudio;
            console.log('Audio instance restored from previous page');
        } else {
            audio = new Audio();
            audio.loop = true;  // Ulang otomatis agar musik terus berjalan
            audio.volume = volume;
            window.stayNestAudio = audio;
            console.log('New audio instance created');
            
            // Debug event listeners untuk mengetahui status audio
            audio.addEventListener('canplay', function() {
                console.log('✅ Audio can play!');
            });
            audio.addEventListener('error', function(e) {
                console.log('❌ Audio error:', e);
                console.log('❌ Pastikan file nastelbom-elegant.mp3 ada di folder assets/music/');
                console.log('❌ Cek URL: ' + audio.src);
            });
            audio.addEventListener('loadeddata', function() {
                console.log('✅ Audio loaded successfully!');
                console.log('🎵 File: ' + audio.src);
            });
            audio.addEventListener('playing', function() {
                console.log('🎵 Music is playing!');
            });
        }
        
        // Restore saved state dari sessionStorage
        const savedTime = sessionStorage.getItem('staynest_audioCurrentTime');
        const savedIsPlaying = sessionStorage.getItem('staynest_isPlaying');
        const savedVolume = localStorage.getItem('staynest_musicVolume');
        
        if (savedVolume !== null) {
            volume = parseFloat(savedVolume);
            audio.volume = volume;
        }
        
        // Load track
        audio.src = musicUrls[0];
        audio.load();
        console.log('Loading music:', musicUrls[0]);
        
        // Restore playback position
        if (savedTime !== null && !isNaN(parseFloat(savedTime))) {
            audio.currentTime = parseFloat(savedTime);
        }
        
        // Auto-play attempt
        tryAutoPlay();
        
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
            if (audio && !audio.paused) {
                sessionStorage.setItem('staynest_audioCurrentTime', audio.currentTime);
                sessionStorage.setItem('staynest_isPlaying', true);
            }
        }, 2000);
    }
    
    function tryAutoPlay() {
        const playPromise = audio.play();
        if (playPromise !== undefined) {
            playPromise.then(function() {
                isPlaying = true;
                updateUI();
                console.log('✅ Auto-play SUCCESS! Music is playing.');
            }).catch(function(error) {
                console.log('⚠️ Auto-play blocked by browser:', error);
                showPlayOverlay();
            });
        }
    }
    
    // ============================================
    // OVERLAY YANG BISA DIKLIK
    // ============================================
    function showPlayOverlay() {
        // Hapus overlay lama jika ada
        const existingOverlay = document.getElementById('staynestPlayOverlay');
        if (existingOverlay) {
            existingOverlay.remove();
        }
        
        // Buat overlay baru
        const overlay = document.createElement('div');
        overlay.id = 'staynestPlayOverlay';
        overlay.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <div style="font-size: 70px; margin-bottom: 20px; animation: bounce 1s ease-in-out infinite;">🎵</div>
                <div style="font-size: 26px; font-weight: bold; margin-bottom: 15px; color: white;">Click anywhere to play music</div>
                <div style="font-size: 16px; opacity: 0.9; margin-bottom: 25px;">Tap to enable background music</div>
                <button id="staynestPlayButton" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border: none; padding: 14px 38px; border-radius: 50px; font-size: 18px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 5px 20px rgba(102,126,234,0.4);">
                    <i class="fas fa-play"></i> Play Music
                </button>
                <div style="margin-top: 30px; font-size: 12px; opacity: 0.6;">🎧 Nastelbom Elegant - Background Music</div>
            </div>
            <style>
                @keyframes bounce {
                    0%, 100% { transform: translateY(0); }
                    50% { transform: translateY(-15px); }
                }
                #staynestPlayButton:hover { 
                    transform: scale(1.05); 
                    box-shadow: 0 8px 25px rgba(102,126,234,0.6);
                }
            </style>
        `;
        
        // Style overlay
        overlay.style.position = 'fixed';
        overlay.style.top = '0';
        overlay.style.left = '0';
        overlay.style.width = '100%';
        overlay.style.height = '100%';
        overlay.style.backgroundColor = 'rgba(0,0,0,0.92)';
        overlay.style.zIndex = '999999';
        overlay.style.display = 'flex';
        overlay.style.alignItems = 'center';
        overlay.style.justifyContent = 'center';
        overlay.style.color = 'white';
        overlay.style.fontFamily = 'Inter, sans-serif';
        overlay.style.cursor = 'pointer';
        overlay.style.backdropFilter = 'blur(10px)';
        
        // Event click untuk seluruh overlay
        overlay.onclick = function(e) {
            e.stopPropagation();
            startMusicAndRemoveOverlay();
        };
        
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden'; // Mencegah scroll
        
        // Event untuk tombol
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
        }
        
        // Pastikan audio memiliki src
        if (!audio.src || audio.src === '') {
            audio.src = musicUrls[0];
            audio.load();
        }
        
        // Reset volume dan play
        audio.volume = volume;
        audio.play().then(() => {
            isPlaying = true;
            updateUI();
            console.log('✅ Music started after user click!');
            console.log('🎵 Now playing: nastelbom-elegant.mp3');
        }).catch(err => {
            console.log('❌ Failed to play:', err);
            alert('Unable to play music. Please check if nastelbom-elegant.mp3 exists in assets/music/ folder.');
        });
    }
    
    // ============================================
    // CREATE MUSIC PLAYER UI (Tombol Floating)
    // ============================================
    function createMusicPlayer() {
        if (document.getElementById('staynestElegantMusicPlayer')) return;
        
        const playerHTML = `
            <div id="staynestElegantMusicPlayer" style="position: fixed; bottom: 20px; left: 20px; z-index: 9999;">
                <div id="staynestMusicToggleBtn" style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea, #764ba2, #f093fb); border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 8px 20px rgba(102,126,234,0.3); transition: all 0.3s ease; position: relative;">
                    <div id="staynestMusicWave" style="position: absolute; width: 100%; height: 100%; border-radius: 50%; background: rgba(102,126,234,0.4); animation: staynestPulse 1.5s ease-in-out infinite; display: none;"></div>
                    <i id="staynestMusicIcon" class="fas fa-headphones" style="color: white; font-size: 22px;"></i>
                </div>
                
                <div id="staynestMusicPlayerPanel" style="position: absolute; bottom: 65px; left: 0; width: 280px; background: rgba(255,255,255,0.98); backdrop-filter: blur(20px); border-radius: 20px; padding: 15px; box-shadow: 0 20px 40px rgba(0,0,0,0.15); display: none; transition: all 0.3s ease; border: 1px solid rgba(102,126,234,0.1);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%; animation: staynestPulse 1.5s infinite;"></div>
                            <h3 style="font-size: 13px; font-weight: 600; color: #667eea;"><i class="fas fa-music"></i> StayNest Radio</h3>
                        </div>
                        <button id="staynestCloseMusicPanel" style="background: none; border: none; cursor: pointer; color: #999; font-size: 18px;">&times;</button>
                    </div>
                    
                    <div style="margin-bottom: 12px; padding: 10px; background: linear-gradient(135deg, #f5f0ff, #fdf2f8); border-radius: 14px; text-align: center;">
                        <div id="staynestMusicNoteAnim" style="font-size: 20px; margin-bottom: 5px;">🎧</div>
                        <p id="staynestSongName" style="font-size: 13px; font-weight: 600; color: #333;">Nastelbom Elegant</p>
                    </div>
                    
                    <div style="margin-bottom: 10px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span id="staynestCurrentTime" style="font-size: 9px; color: #999;">0:00</span>
                            <span id="staynestDuration" style="font-size: 9px; color: #999;">--:--</span>
                        </div>
                        <div style="width: 100%; height: 3px; background: #e8e8e8; border-radius: 3px; cursor: pointer;" id="staynestProgressBar">
                            <div id="staynestProgress" style="width: 0%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 3px;"></div>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 12px;">
                        <button id="staynestPlayPauseMusic" style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(102,126,234,0.3);">
                            <i id="staynestPlayPauseIcon" class="fas fa-play" style="color: white; font-size: 16px;"></i>
                        </button>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-volume-down" style="color: #667eea; font-size: 11px;"></i>
                        <input type="range" id="staynestVolumeSlider" min="0" max="100" value="40" style="flex: 1; height: 3px;">
                        <i class="fas fa-volume-up" style="color: #667eea; font-size: 11px;"></i>
                        <span id="staynestVolumePercent" style="font-size: 9px; color: #999;">40%</span>
                    </div>
                </div>
            </div>
            <style>
                @keyframes staynestPulse {
                    0%, 100% { transform: scale(1); opacity: 0.6; }
                    50% { transform: scale(1.2); opacity: 0.2; }
                }
                #staynestMusicToggleBtn:hover { transform: scale(1.05); }
            </style>
        `;
        
        document.body.insertAdjacentHTML('beforeend', playerHTML);
        attachEvents();
        updateUI();
        
        // Set duration when metadata loaded
        audio.addEventListener('loadedmetadata', function() {
            const durationElem = document.getElementById('staynestDuration');
            if (durationElem && audio.duration) {
                const minutes = Math.floor(audio.duration / 60);
                const seconds = Math.floor(audio.duration % 60);
                durationElem.textContent = minutes + ':' + (seconds < 10 ? '0' + seconds : seconds);
            }
        });
    }
    
    function updateUI() {
        const playIcon = document.getElementById('staynestPlayPauseIcon');
        const wave = document.getElementById('staynestMusicWave');
        const musicIcon = document.getElementById('staynestMusicIcon');
        
        if (isPlaying) {
            if (playIcon) playIcon.className = 'fas fa-pause';
            if (wave) wave.style.display = 'block';
            if (musicIcon) musicIcon.className = 'fas fa-stop';
        } else {
            if (playIcon) playIcon.className = 'fas fa-play';
            if (wave) wave.style.display = 'none';
            if (musicIcon) musicIcon.className = 'fas fa-headphones';
        }
    }
    
    function attachEvents() {
        const toggle = document.getElementById('staynestMusicToggleBtn');
        const panel = document.getElementById('staynestMusicPlayerPanel');
        const close = document.getElementById('staynestCloseMusicPanel');
        const playPause = document.getElementById('staynestPlayPauseMusic');
        const volume = document.getElementById('staynestVolumeSlider');
        const volumePercent = document.getElementById('staynestVolumePercent');
        const progressBar = document.getElementById('staynestProgressBar');
        
        if (toggle) {
            toggle.onclick = () => {
                panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
            };
        }
        if (close) close.onclick = () => panel.style.display = 'none';
        if (playPause) playPause.onclick = togglePlayPause;
        
        if (volume) {
            volume.oninput = (e) => {
                volume = e.target.value / 100;
                audio.volume = volume;
                if (volumePercent) volumePercent.textContent = e.target.value + '%';
                localStorage.setItem('staynest_musicVolume', volume);
            };
        }
        
        if (progressBar) progressBar.onclick = seek;
        
        audio.ontimeupdate = updateProgress;
        
        // Animate music notes
        setInterval(() => {
            if (isPlaying) {
                const anim = document.getElementById('staynestMusicNoteAnim');
                if (anim) {
                    const notes = ['🎵', '🎶', '🎧', '🎸', '🎹', '🎤', '🎼'];
                    anim.textContent = notes[Math.floor(Math.random() * notes.length)];
                }
            }
        }, 1000);
    }
    
    function playMusic() {
        audio.play().catch(e => console.log('Play error:', e));
        isPlaying = true;
        updateUI();
        saveState();
    }
    
    function pauseMusic() {
        audio.pause();
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
    
    function updateProgress() {
        const progress = document.getElementById('staynestProgress');
        const currentTime = document.getElementById('staynestCurrentTime');
        if (progress && currentTime && audio.duration && !isNaN(audio.duration)) {
            const percent = (audio.currentTime / audio.duration) * 100;
            progress.style.width = percent + '%';
            const minutes = Math.floor(audio.currentTime / 60);
            const seconds = Math.floor(audio.currentTime % 60);
            currentTime.textContent = minutes + ':' + (seconds < 10 ? '0' + seconds : seconds);
        }
    }
    
    function seek(e) {
        const rect = e.currentTarget.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const percent = x / rect.width;
        if (audio.duration && !isNaN(audio.duration)) {
            audio.currentTime = percent * audio.duration;
            saveState();
        }
    }
    
    function saveState() {
        if (audio) {
            sessionStorage.setItem('staynest_audioCurrentTime', audio.currentTime);
            sessionStorage.setItem('staynest_isPlaying', isPlaying);
        }
        localStorage.setItem('staynest_musicVolume', volume);
    }
    
    // ============================================
    // START
    // ============================================
    initAudio();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createMusicPlayer);
    } else {
        createMusicPlayer();
    }
})();
