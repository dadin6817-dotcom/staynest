// ==============================================
// assets/js/music-player.js - Music Player PASTI JALAN!
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

    if (!musicToggle || !musicControls) {
        console.log('⚠️ Music Player elements not found');
        return;
    }

    // ==========================================
    // BUAT AUDIO PLAYER - PAKAI MP3 DARI INTERNET
    // ==========================================
    var audio = new Audio();

    // ==========================================
    // DAFTAR MP3 DARI INTERNET (PASTI BISA DIPUTAR)
    // ==========================================
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

        console.log('🎵 Loading: ' + playlists[currentTrack]);
        console.log('🎵 Song: ' + songNames[currentTrack]);
    }

    // Muat lagu pertama
    loadTrack(0);

    // ==========================================
    // STATE
    // ==========================================
    var isPlaying = false;
    var volume = 40;
    var noteInterval = null;

    // ==========================================
    // UPDATE UI
    // ==========================================
    function updateUI() {
        if (musicStatus) {
            musicStatus.textContent = isPlaying ? 'On' : 'Off';
            musicStatus.style.color = isPlaying ? '#667eea' : 'gray';
        }
        if (musicStatusMobile) {
            musicStatusMobile.textContent = isPlaying ? 'Music: On' : 'Music: Off';
        }
        if (musicDot) {
            musicDot.className = isPlaying ? 'music-dot' : 'music-dot off';
            musicDot.style.background = isPlaying ? '#22c55e' : '#9ca3af';
        }
        if (liveDot) {
            liveDot.style.background = isPlaying ? '#22c55e' : '#9ca3af';
        }
        if (pulseRing) {
            if (isPlaying) {
                pulseRing.classList.add('active');
            } else {
                pulseRing.classList.remove('active');
            }
        }
        if (musicToggleIcon) {
            musicToggleIcon.className = isPlaying ? 'fas fa-stop' : 'fas fa-music';
        }
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
    // TOGGLE CONTROLS
    // ==========================================
    function toggleControls(e) {
        if (e) e.stopPropagation();
        if (musicControls) {
            musicControls.classList.toggle('show');
        }
    }

    if (musicToggle) musicToggle.addEventListener('click', toggleControls);
    if (musicToggleBtn) musicToggleBtn.addEventListener('click', toggleControls);
    if (musicToggleMobile) musicToggleMobile.addEventListener('click', toggleControls);

    if (closeMusicBtn) {
        closeMusicBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (musicControls) {
                musicControls.classList.remove('show');
            }
        });
    }

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
        console.log('🎵 Trying to play music...');
        console.log('🎵 Current song: ' + songNames[currentTrack]);

        audio.volume = volume / 100;

        audio.play().then(function() {
            isPlaying = true;
            updateUI();
            animateNotes();
            console.log('🎵 Music is playing! 🎵');
        }).catch(function(error) {
            console.log('❌ Play error:', error);
            console.log('🔄 Trying next song...');
            // Coba lagu berikutnya
            currentTrack = (currentTrack + 1) % playlists.length;
            loadTrack(currentTrack);
            setTimeout(function() {
                audio.play().then(function() {
                    isPlaying = true;
                    updateUI();
                    animateNotes();
                    console.log('🎵 Music is playing! 🎵');
                }).catch(function(e) {
                    console.log('❌ Still cannot play:', e);
                    alert('⚠️ Tidak bisa memutar musik. Coba refresh halaman.');
                    if (musicStatus) {
                        musicStatus.textContent = 'Error';
                        musicStatus.style.color = 'red';
                    }
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

    // ==========================================
    // PROGRESS TRACK
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
    // VOLUME
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
            localStorage.setItem('staynest_musicVolume', volume);
        });
    }

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
    // AUDIO EVENTS
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

    // ==========================================
    // KEYBOARD SHORTCUT
    // ==========================================
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName !== 'INPUT' && e.key === ' ') {
            e.preventDefault();
            togglePlay();
        }
    });

    // ==========================================
    // INISIALISASI
    // ==========================================
    updateUI();
    updateTimeDisplay();
    console.log('🎵 StayNest Music Player ready!');
    console.log('🎵 Current song: ' + songNames[currentTrack]);
});