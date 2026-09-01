// ==============================================
// assets/js/music-player.js - Music Player untuk SEMUA HALAMAN
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎵 StayNest Music Player Loaded!');

    // ==========================================
    // AMBIL SEMUA ELEMEN
    // ==========================================
    var musicToggle = document.getElementById('musicToggle');
    var musicToggleBtn = document.getElementById('musicToggleBtn');
    var musicToggleMobile = document.getElementById('musicToggleMobile');
    var musicControls = document.getElementById('musicControls');
    var closeMusicBtn = document.getElementById('closeMusicBtn');
    var playBtn = document.getElementById('playBtn');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var progressTrack = document.getElementById('progressTrack');
    var progressFill = document.getElementById('progressFill');
    var volumeSlider = document.getElementById('volumeSlider');
    var volumePercent = document.getElementById('volumePercent');
    var currentTime = document.getElementById('currentTime');
    var totalTime = document.getElementById('totalTime');
    var pulseRing = document.getElementById('pulseRing');
    var musicToggleIcon = document.getElementById('musicToggleIcon');
    var musicStatus = document.getElementById('musicStatus');
    var musicStatusMobile = document.getElementById('musicStatusMobile');
    var musicNoteAnim = document.getElementById('musicNoteAnim');
    var songName = document.getElementById('songName');

    // ==========================================
    // CEK APAKAH ELEMEN ADA
    // ==========================================
    if (!musicToggle || !musicControls) {
        console.log('⚠️ Music Player elements not found on this page');
        return;
    }

    // ==========================================
    // STATE / VARIABEL
    // ==========================================
    var isPlaying = false;
    var progress = 0;
    var progressInterval = null;
    var noteInterval = null;
    var volume = 40;
    var totalDuration = 225; // 3:45 menit

    // ==========================================
    // FUNGSI TOGGLE CONTROLS
    // ==========================================
    function toggleControls(e) {
        if (e) e.stopPropagation();
        musicControls.classList.toggle('show');
    }

    // ==========================================
    // EVENT LISTENER TOGGLE
    // ==========================================
    if (musicToggle) {
        musicToggle.addEventListener('click', toggleControls);
    }
    if (musicToggleBtn) {
        musicToggleBtn.addEventListener('click', toggleControls);
    }
    if (musicToggleMobile) {
        musicToggleMobile.addEventListener('click', toggleControls);
    }

    // ==========================================
    // TUTUP PANEL DENGAN TOMBOL CLOSE
    // ==========================================
    if (closeMusicBtn) {
        closeMusicBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            musicControls.classList.remove('show');
        });
    }

    // ==========================================
    // TUTUP PANEL KETIKA KLIK DI LUAR
    // ==========================================
    document.addEventListener('click', function(e) {
        if (musicControls && musicControls.classList.contains('show')) {
            var isInside = musicControls.contains(e.target);
            var isToggle = musicToggle && musicToggle.contains(e.target);
            var isToggleBtn = musicToggleBtn && musicToggleBtn.contains(e.target);
            var isToggleMobile = musicToggleMobile && musicToggleMobile.contains(e.target);
            
            if (!isInside && !isToggle && !isToggleBtn && !isToggleMobile) {
                musicControls.classList.remove('show');
            }
        }
    });

    // ==========================================
    // UPDATE TIME DISPLAY
    // ==========================================
    function updateTimeDisplay() {
        if (currentTime) {
            var currentSeconds = Math.floor((progress / 100) * totalDuration);
            var mins = Math.floor(currentSeconds / 60);
            var secs = currentSeconds % 60;
            currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    }

    // ==========================================
    // ANIMASI NOT MUSIK
    // ==========================================
    function animateNotes() {
        if (noteInterval) clearInterval(noteInterval);
        if (!isPlaying) return;
        
        var notes = ['🎵', '🎶', '🎧', '🎸', '🎹', '🎤', '🎼'];
        var i = 0;
        
        noteInterval = setInterval(function() {
            if (!isPlaying) {
                clearInterval(noteInterval);
                return;
            }
            if (musicNoteAnim) {
                musicNoteAnim.textContent = notes[i % notes.length];
                i++;
            }
        }, 800);
    }

    // ==========================================
    // SIMULASI PROGRESS BAR
    // ==========================================
    function simulateProgress() {
        if (!isPlaying) return;

        if (progress >= 100) {
            progress = 0;
            
            if (playBtn) {
                var icon = playBtn.querySelector('i');
                icon.className = 'fas fa-play';
                playBtn.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
            }
            
            isPlaying = false;
            
            if (pulseRing) pulseRing.classList.remove('active');
            if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music';
            if (musicStatus) {
                musicStatus.textContent = 'Off';
                musicStatus.style.color = 'gray';
            }
            if (musicStatusMobile) {
                musicStatusMobile.textContent = 'Music: Off';
            }
            if (noteInterval) clearInterval(noteInterval);
            
            return;
        }

        progress += 0.5;
        if (progressFill) progressFill.style.width = progress + '%';
        updateTimeDisplay();
        
        progressInterval = setTimeout(simulateProgress, 100);
    }

    // ==========================================
    // PLAY / PAUSE
    // ==========================================
    if (playBtn) {
        playBtn.addEventListener('click', function() {
            isPlaying = !isPlaying;
            var icon = this.querySelector('i');

            if (isPlaying) {
                // PLAY
                icon.className = 'fas fa-pause';
                this.style.background = 'linear-gradient(135deg, #f093fb, #f5576c)';
                
                if (pulseRing) pulseRing.classList.add('active');
                if (musicToggleIcon) musicToggleIcon.className = 'fas fa-stop';
                
                if (musicStatus) {
                    musicStatus.textContent = 'On';
                    musicStatus.style.color = '#667eea';
                }
                if (musicStatusMobile) {
                    musicStatusMobile.textContent = 'Music: On';
                }
                
                simulateProgress();
                animateNotes();
                console.log('🎵 Music Playing');
                
            } else {
                // PAUSE
                icon.className = 'fas fa-play';
                this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                
                if (pulseRing) pulseRing.classList.remove('active');
                if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music';
                
                if (musicStatus) {
                    musicStatus.textContent = 'Off';
                    musicStatus.style.color = 'gray';
                }
                if (musicStatusMobile) {
                    musicStatusMobile.textContent = 'Music: Off';
                }
                
                if (progressInterval) {
                    clearTimeout(progressInterval);
                    progressInterval = null;
                }
                if (noteInterval) {
                    clearInterval(noteInterval);
                }
                
                console.log('⏸️ Music Paused');
            }
        });
    }

    // ==========================================
    // KLIK PROGRESS TRACK (SEEK)
    // ==========================================
    if (progressTrack) {
        progressTrack.addEventListener('click', function(e) {
            var rect = this.getBoundingClientRect();
            var x = e.clientX - rect.left;
            var percent = (x / rect.width) * 100;
            progress = Math.min(100, Math.max(0, percent));
            
            if (progressFill) progressFill.style.width = progress + '%';
            updateTimeDisplay();
        });
    }

    // ==========================================
    // TOMBOL PREVIOUS
    // ==========================================
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            progress = Math.max(0, progress - 10);
            if (progressFill) progressFill.style.width = progress + '%';
            updateTimeDisplay();
        });
    }

    // ==========================================
    // TOMBOL NEXT
    // ==========================================
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            progress = Math.min(100, progress + 10);
            if (progressFill) progressFill.style.width = progress + '%';
            updateTimeDisplay();
        });
    }

    // ==========================================
    // VOLUME CONTROL
    // ==========================================
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            volume = parseFloat(this.value);
            if (volumePercent) volumePercent.textContent = volume + '%';
            
            var volumeIcon = document.querySelector('.music-volume i');
            if (volumeIcon) {
                if (volume === 0) {
                    volumeIcon.className = 'fas fa-volume-mute';
                } else {
                    volumeIcon.className = 'fas fa-volume-down';
                }
            }
            
            localStorage.setItem('staynest_musicVolume', volume);
        });
    }

    // ==========================================
    // RESTORE VOLUME DARI LOCALSTORAGE
    // ==========================================
    var savedVolume = localStorage.getItem('staynest_musicVolume');
    if (savedVolume !== null && volumeSlider) {
        volume = parseFloat(savedVolume);
        volumeSlider.value = volume;
        if (volumePercent) volumePercent.textContent = volume + '%';
    }

    // ==========================================
    // SET TOTAL TIME (3:45)
    // ==========================================
    if (totalTime) {
        var mins = Math.floor(totalDuration / 60);
        var secs = totalDuration % 60;
        totalTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
    }

    // ==========================================
    // SET SONG NAME
    // ==========================================
    if (songName) {
        songName.textContent = 'Nastelbom Elegant';
    }

    // ==========================================
    // KEYBOARD SHORTCUT: SPACE UNTUK PLAY/PAUSE
    // ==========================================
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName !== 'INPUT' && e.key === ' ') {
            e.preventDefault();
            if (playBtn) playBtn.click();
        }
    });

    console.log('🎵 StayNest Music Player ready!');
});