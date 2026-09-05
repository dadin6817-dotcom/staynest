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
    var musicDot = document.getElementById('musicDot');
    var liveDot = document.getElementById('liveDot');

    // ==========================================
    // CEK APAKAH ELEMEN ADA
    // ==========================================
    if (!musicToggle || !musicControls) {
        console.log('⚠️ Music Player elements not found on this page');
        return;
    }

    // ==========================================
    // CREATE AUDIO PLAYER
    // ==========================================
    var audio = new Audio();

    // DAFTAR LAGU (Ganti dengan file MP3 kamu)
    var playlists = [
        '/staynest/assets/music/nastelbom-elegant.mp3',
        '/staynest/assets/music/song2.mp3',
        '/staynest/assets/music/song3.mp3'
    ];

    var songNames = [
        'Nastelbom Elegant',
        'Relaxing Piano',
        'Chill Vibes'
    ];

    var currentTrack = 0;

    // Set lagu pertama
    audio.src = playlists[0];
    audio.load();
    if (songName) {
        songName.textContent = songNames[0];
    }

    // ==========================================
    // STATE / VARIABEL
    // ==========================================
    var isPlaying = false;
    var progress = 0;
    var progressInterval = null;
    var noteInterval = null;
    var volume = 40;
    var totalDuration = 225;

    // ==========================================
    // FUNGSI UPDATE UI
    // ==========================================
    function updateUI() {
        // Music status di navbar
        if (musicStatus) {
            musicStatus.textContent = isPlaying ? 'On' : 'Off';
            musicStatus.style.color = isPlaying ? '#667eea' : 'gray';
        }
        if (musicStatusMobile) {
            musicStatusMobile.textContent = isPlaying ? 'Music: On' : 'Music: Off';
        }
        // Dot indicator
        if (musicDot) {
            musicDot.className = isPlaying ? 'music-dot' : 'music-dot off';
            musicDot.style.background = isPlaying ? '#22c55e' : '#9ca3af';
        }
        if (liveDot) {
            liveDot.style.background = isPlaying ? '#22c55e' : '#9ca3af';
        }
        // Pulse ring
        if (pulseRing) {
            if (isPlaying) {
                pulseRing.classList.add('active');
            } else {
                pulseRing.classList.remove('active');
            }
        }
        // Main toggle icon
        if (musicToggleIcon) {
            musicToggleIcon.className = isPlaying ? 'fas fa-stop' : 'fas fa-music';
        }
        // Play button
        if (playBtn) {
            var icon = playBtn.querySelector('i');
            if (icon) {
                icon.className = isPlaying ? 'fas fa-pause' : 'fas fa-play';
            }
            playBtn.style.background = isPlaying ?
                'linear-gradient(135deg, #f093fb, #f5576c)' :
                'linear-gradient(135deg, #667eea, #764ba2)';
        }
    }

    // ==========================================
    // UPDATE TIME DISPLAY
    // ==========================================
    function updateTimeDisplay() {
        if (currentTime && audio.duration) {
            var currentSeconds = Math.floor(audio.currentTime);
            var mins = Math.floor(currentSeconds / 60);
            var secs = currentSeconds % 60;
            currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        if (totalTime && audio.duration) {
            var totalSeconds = Math.floor(audio.duration);
            var mins = Math.floor(totalSeconds / 60);
            var secs = totalSeconds % 60;
            totalTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    }

    // ==========================================
    // UPDATE PROGRESS BAR
    // ==========================================
    function updateProgress() {
        if (progressFill && audio.duration) {
            var percent = (audio.currentTime / audio.duration) * 100;
            progressFill.style.width = percent + '%';
        }
        updateTimeDisplay();
    }

    // ==========================================
    // ANIMASI NOT MUSIK
    // ==========================================
    function animateNotes() {
        if (noteInterval) {
            clearInterval(noteInterval);
            noteInterval = null;
        }
        if (!isPlaying) return;
        var notes = ['🎵', '🎶', '🎧', '🎸', '🎹', '🎤', '🎼'];
        var i = 0;
        noteInterval = setInterval(function() {
            if (!isPlaying) {
                clearInterval(noteInterval);
                noteInterval = null;
                return;
            }
            if (musicNoteAnim) {
                musicNoteAnim.textContent = notes[i % notes.length];
                i++;
            }
        }, 800);
    }

    // ==========================================
    // FUNGSI TOGGLE CONTROLS
    // ==========================================
    function toggleControls(e) {
        if (e) e.stopPropagation();
        if (musicControls) {
            musicControls.classList.toggle('show');
        }
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
            if (musicControls) {
                musicControls.classList.remove('show');
            }
        });
    }

    // ==========================================
    // TUTUP PANEL KETIKA KLIK DI LUAR
    // ==========================================
    document.addEventListener('click', function(e) {
        if (musicControls && musicControls.classList.contains('show')) {
            if (!musicControls.contains(e.target) &&
                !musicToggle.contains(e.target) &&
                !musicToggleBtn?.contains(e.target) &&
                !musicToggleMobile?.contains(e.target)) {
                musicControls.classList.remove('show');
            }
        }
    });

    // ==========================================
    // PLAY / PAUSE
    // ==========================================
    function playMusic() {
        if (!audio.src || audio.src === '') {
            audio.src = playlists[currentTrack];
            audio.load();
        }
        audio.play().then(function() {
            isPlaying = true;
            updateUI();
            animateNotes();
            console.log('🎵 Music Playing: ' + songNames[currentTrack]);
        }).catch(function(error) {
            console.log('⚠️ Play error:', error);
            audio.src = playlists[currentTrack];
            audio.load();
            setTimeout(function() {
                audio.play().catch(function(e) {
                    console.log('❌ Still cannot play:', e);
                });
            }, 500);
        });
    }

    function pauseMusic() {
        audio.pause();
        isPlaying = false;
        updateUI();
        if (noteInterval) {
            clearInterval(noteInterval);
            noteInterval = null;
        }
        console.log('⏸️ Music Paused');
    }

    function togglePlay() {
        if (isPlaying) {
            pauseMusic();
        } else {
            playMusic();
        }
    }

    if (playBtn) {
        playBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            togglePlay();
        });
    }

    // ==========================================
    // PREVIOUS / NEXT
    // ==========================================
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentTrack = (currentTrack - 1 + playlists.length) % playlists.length;
            audio.src = playlists[currentTrack];
            audio.load();
            if (isPlaying) {
                audio.play().catch(function() {});
            }
            if (songName) {
                songName.textContent = songNames[currentTrack] || 'Nastelbom Elegant';
            }
            updateTimeDisplay();
            console.log('⏮️ Previous: ' + songNames[currentTrack]);
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentTrack = (currentTrack + 1) % playlists.length;
            audio.src = playlists[currentTrack];
            audio.load();
            if (isPlaying) {
                audio.play().catch(function() {});
            }
            if (songName) {
                songName.textContent = songNames[currentTrack] || 'Nastelbom Elegant';
            }
            updateTimeDisplay();
            console.log('⏭️ Next: ' + songNames[currentTrack]);
        });
    }

    // ==========================================
    // KLIK PROGRESS TRACK (SEEK)
    // ==========================================
    if (progressTrack) {
        progressTrack.addEventListener('click', function(e) {
            if (audio.duration) {
                var rect = this.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var percent = x / rect.width;
                audio.currentTime = percent * audio.duration;
                updateProgress();
            }
        });
    }

    // ==========================================
    // VOLUME CONTROL
    // ==========================================
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            volume = parseFloat(this.value);
            audio.volume = volume / 100;
            if (volumePercent) {
                volumePercent.textContent = volume + '%';
            }
            var volumeIcon = document.querySelector('.music-volume i');
            if (volumeIcon) {
                if (volume === 0) {
                    volumeIcon.className = 'fas fa-volume-mute';
                } else {
                    volumeIcon.className = 'fas fa-volume-down';
                }
            }
            try {
                localStorage.setItem('staynest_musicVolume', volume);
            } catch(e) {}
        });
    }

    // ==========================================
    // RESTORE VOLUME DARI LOCALSTORAGE
    // ==========================================
    try {
        var savedVolume = localStorage.getItem('staynest_musicVolume');
        if (savedVolume !== null && volumeSlider) {
            volume = parseFloat(savedVolume);
            volumeSlider.value = volume;
            audio.volume = volume / 100;
            if (volumePercent) {
                volumePercent.textContent = volume + '%';
            }
        }
    } catch(e) {}

    // ==========================================
    // AUDIO EVENT LISTENERS
    // ==========================================
    audio.addEventListener('timeupdate', function() {
        updateProgress();
    });

    audio.addEventListener('loadedmetadata', function() {
        updateTimeDisplay();
        updateProgress();
        console.log('✅ Audio loaded: ' + audio.src);
    });

    audio.addEventListener('ended', function() {
        currentTrack = (currentTrack + 1) % playlists.length;
        audio.src = playlists[currentTrack];
        audio.load();
        if (isPlaying) {
            audio.play().catch(function() {});
        }
        if (songName) {
            songName.textContent = songNames[currentTrack] || 'Nastelbom Elegant';
        }
        console.log('⏭️ Auto next: ' + songNames[currentTrack]);
    });

    audio.addEventListener('error', function(e) {
        console.log('❌ Audio error:', e);
        console.log('❌ Please check if music files exist in /assets/music/');
        console.log('❌ File path: ' + audio.src);
    });

    // ==========================================
    // KEYBOARD SHORTCUT: SPACE UNTUK PLAY/PAUSE
    // ==========================================
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName !== 'INPUT' && e.key === ' ') {
            e.preventDefault();
            togglePlay();
        }
    });

    // ==========================================
    // SET SONG NAME
    // ==========================================
    if (songName) {
        songName.textContent = songNames[currentTrack] || 'Nastelbom Elegant';
    }

    // ==========================================
    // INISIALISASI
    // ==========================================
    updateUI();
    updateTimeDisplay();
    console.log('🎵 StayNest Music Player ready!');
    console.log('🎵 Current song: ' + songNames[currentTrack]);
});