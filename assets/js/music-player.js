// ==============================================
// assets/js/music-player.js - PASTI JALAN DI SEMUA HALAMAN
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
    // CEK ELEMEN - JIKA TIDAK ADA, TUNGGU 1 DETIK
    // ==========================================
    if (!musicToggle || !musicControls) {
        console.log('⚠️ Music Player elements not found, retrying...');
        setTimeout(function() {
            location.reload();
        }, 1000);
        return;
    }

    // ==========================================
    // BUAT AUDIO - PAKAI MP3 DARI INTERNET (PASTI JALAN)
    // ==========================================
    var audio = new Audio();

    // DAFTAR MP3 DARI INTERNET
    var playlists = [
        'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
        'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
        'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3'
    ];

    var songNames = [
        'SoundHelix - Song 1',
        'SoundHelix - Song 2',
        'SoundHelix - Song 3'
    ];

    var currentTrack = 0;
    var isPlaying = false;
    var volume = 40;
    var noteInterval = null;

    // ==========================================
    // FUNGSI MEMUAT LAGU
    // ==========================================
    function loadTrack(index) {
        if (index < 0) index = playlists.length - 1;
        if (index >= playlists.length) index = 0;
        currentTrack = index;

        audio.src = playlists[currentTrack];
        audio.load();

        if (songName) {
            songName.textContent = songNames[currentTrack];
        }

        console.log('🎵 Loading: ' + songNames[currentTrack]);
        console.log('🎵 URL: ' + playlists[currentTrack]);
    }

    // ==========================================
    // UPDATE UI - PASTI BERUBAH
    // ==========================================
    function updateUI() {
        // Status di navbar
        if (musicStatus) {
            musicStatus.textContent = isPlaying ? 'On' : 'Off';
            musicStatus.style.color = isPlaying ? '#667eea' : '#9ca3af';
            musicStatus.style.fontWeight = isPlaying ? 'bold' : 'normal';
        }

        // Status di mobile menu
        if (musicStatusMobile) {
            musicStatusMobile.textContent = isPlaying ? 'Music: On' : 'Music: Off';
        }

        // Dot indicator
        if (musicDot) {
            if (isPlaying) {
                musicDot.className = 'music-dot';
                musicDot.style.background = '#22c55e';
            } else {
                musicDot.className = 'music-dot off';
                musicDot.style.background = '#9ca3af';
            }
        }

        // Live dot
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

        // Music toggle icon
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

        console.log('🎵 UI Updated: isPlaying = ' + isPlaying);
    }

    // ==========================================
    // UPDATE TIME & PROGRESS
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

    function updateProgress() {
        if (progressFill && audio.duration) {
            var percent = (audio.currentTime / audio.duration) * 100;
            progressFill.style.width = percent + '%';
        }
        updateTimeDisplay();
    }

    function animateNotes() {
        if (noteInterval) clearInterval(noteInterval);
        if (!isPlaying) return;
        var notes = ['🎵', '🎶', '🎧', '🎸', '🎹', '🎤', '🎼'];
        var i = 0;
        noteInterval = setInterval(function() {
            if (!isPlaying) { clearInterval(noteInterval); return; }
            if (musicNoteAnim) {
                musicNoteAnim.textContent = notes[i % notes.length];
                i++;
            }
        }, 800);
    }

    // ==========================================
    // FUNGSI PLAY / PAUSE
    // ==========================================
    function playMusic() {
        console.log('🎵 Trying to play music...');

        // Jika audio belum di-load, load dulu
        if (!audio.src || audio.src === '') {
            loadTrack(currentTrack);
        }

        audio.volume = volume / 100;

        audio.play().then(function() {
            isPlaying = true;
            updateUI();
            animateNotes();
            console.log('🎵 Music is PLAYING!');
        }).catch(function(error) {
            console.log('❌ Play error:', error);
            // Coba reload dan play ulang
            audio.load();
            setTimeout(function() {
                audio.play().then(function() {
                    isPlaying = true;
                    updateUI();
                    animateNotes();
                    console.log('🎵 Music is PLAYING after reload!');
                }).catch(function(e) {
                    console.log('❌ Still cannot play:', e);
                    alert('⚠️ Tidak bisa memutar musik. Coba refresh halaman.');
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
        console.log('🎵 Toggle Play: isPlaying = ' + isPlaying);
        if (isPlaying) {
            pauseMusic();
        } else {
            playMusic();
        }
    }

    // ==========================================
    // LOAD LAGU PERTAMA
    // ==========================================
    loadTrack(0);

    // ==========================================
    // EVENT LISTENERS
    // ==========================================

    // 1. Toggle controls panel
    function toggleControls(e) {
        if (e) e.stopPropagation();
        if (musicControls) {
            musicControls.classList.toggle('show');
            console.log('🎵 Controls toggled');
        }
    }

    if (musicToggle) {
        musicToggle.addEventListener('click', toggleControls);
        console.log('✅ musicToggle event attached');
    }
    if (musicToggleBtn) {
        musicToggleBtn.addEventListener('click', toggleControls);
        console.log('✅ musicToggleBtn event attached');
    }
    if (musicToggleMobile) {
        musicToggleMobile.addEventListener('click', toggleControls);
        console.log('✅ musicToggleMobile event attached');
    }

    // 2. Close button
    if (closeMusicBtn) {
        closeMusicBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (musicControls) {
                musicControls.classList.remove('show');
            }
        });
    }

    // 3. Close on outside click
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

    // 4. Play button
    if (playBtn) {
        playBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            console.log('🎵 Play button clicked!');
            togglePlay();
        });
        console.log('✅ playBtn event attached');
    }

    // 5. Previous / Next
    if (prevBtn) {
        prevBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentTrack = (currentTrack - 1 + playlists.length) % playlists.length;
            loadTrack(currentTrack);
            if (isPlaying) {
                audio.play().catch(function() {});
            }
            updateProgress();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentTrack = (currentTrack + 1) % playlists.length;
            loadTrack(currentTrack);
            if (isPlaying) {
                audio.play().catch(function() {});
            }
            updateProgress();
        });
    }

    // 6. Progress track
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

    // 7. Volume
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
            localStorage.setItem('staynest_musicVolume', volume);
        });
    }

    // Restore volume
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

    // 8. Audio events
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
        loadTrack(currentTrack);
        if (isPlaying) {
            audio.play().catch(function() {});
        }
        console.log('⏭️ Auto next: ' + songNames[currentTrack]);
    });

    audio.addEventListener('error', function(e) {
        console.log('❌ Audio error:', e);
        console.log('🔄 Trying next song...');
        currentTrack = (currentTrack + 1) % playlists.length;
        loadTrack(currentTrack);
    });

    // 9. Keyboard shortcut
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName !== 'INPUT' && e.key === ' ') {
            e.preventDefault();
            togglePlay();
        }
    });

    // ==========================================
    // UPDATE UI AWAL
    // ==========================================
    updateUI();
    updateTimeDisplay();

    console.log('🎵 StayNest Music Player ready!');
    console.log('🎵 Current song: ' + songNames[currentTrack]);
    console.log('🎵 Is playing: ' + isPlaying);
});