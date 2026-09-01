// assets/js/music-player.js - Music Player untuk SEMUA HALAMAN

document.addEventListener('DOMContentLoaded', function() {
    console.log('🎵 StayNest Music Player Initialized!');
    
    // Elements
    const musicToggle = document.getElementById('musicToggle');
    const musicToggleBtn = document.getElementById('musicToggleBtn');
    const musicToggleMobile = document.getElementById('musicToggleMobile');
    const musicControls = document.getElementById('musicControls');
    const closeMusicBtn = document.getElementById('closeMusicBtn');
    const playBtn = document.getElementById('playBtn');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const progressTrack = document.getElementById('progressTrack');
    const progressFill = document.getElementById('progressFill');
    const volumeSlider = document.getElementById('volumeSlider');
    const volumePercent = document.getElementById('volumePercent');
    const currentTime = document.getElementById('currentTime');
    const totalTime = document.getElementById('totalTime');
    const pulseRing = document.getElementById('pulseRing');
    const musicToggleIcon = document.getElementById('musicToggleIcon');
    const musicStatus = document.getElementById('musicStatus');
    const musicStatusMobile = document.getElementById('musicStatusMobile');
    const musicNoteAnim = document.getElementById('musicNoteAnim');
    
    // Check if elements exist
    if (!musicToggle || !musicControls) {
        console.log('⚠️ Music Player elements not found on this page');
        return;
    }
    
    let isPlaying = false;
    let progress = 0;
    let progressInterval = null;
    let volume = 40;
    const totalDuration = 225;
    let noteInterval = null;
    
    // ==========================================
    // TOGGLE CONTROLS
    // ==========================================
    function toggleControls(e) {
        if (e) e.stopPropagation();
        musicControls.classList.toggle('show');
    }
    
    if (musicToggle) musicToggle.addEventListener('click', toggleControls);
    if (musicToggleBtn) musicToggleBtn.addEventListener('click', toggleControls);
    if (musicToggleMobile) musicToggleMobile.addEventListener('click', toggleControls);
    
    if (closeMusicBtn) {
        closeMusicBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            musicControls.classList.remove('show');
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
    // PLAY/PAUSE
    // ==========================================
    if (playBtn) {
        playBtn.addEventListener('click', function() {
            isPlaying = !isPlaying;
            const icon = this.querySelector('i');
            
            if (isPlaying) {
                icon.className = 'fas fa-pause';
                this.style.background = 'linear-gradient(135deg, #f093fb, #f5576c)';
                if (pulseRing) pulseRing.classList.add('active');
                if (musicToggleIcon) musicToggleIcon.className = 'fas fa-stop';
                if (musicStatus) { musicStatus.textContent = 'On'; musicStatus.style.color = '#667eea'; }
                if (musicStatusMobile) musicStatusMobile.textContent = 'Music: On';
                simulateProgress();
                animateNotes();
                console.log('🎵 Music Playing');
            } else {
                icon.className = 'fas fa-play';
                this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                if (pulseRing) pulseRing.classList.remove('active');
                if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music';
                if (musicStatus) { musicStatus.textContent = 'Off'; musicStatus.style.color = 'gray'; }
                if (musicStatusMobile) musicStatusMobile.textContent = 'Music: Off';
                if (progressInterval) { clearTimeout(progressInterval); progressInterval = null; }
                if (noteInterval) { clearInterval(noteInterval); }
                console.log('⏸️ Music Paused');
            }
        });
    }
    
    // ==========================================
    // ANIMATE NOTES
    // ==========================================
    function animateNotes() {
        if (noteInterval) clearInterval(noteInterval);
        if (!isPlaying) return;
        const notes = ['🎵', '🎶', '🎧', '🎸', '🎹', '🎤', '🎼'];
        let i = 0;
        noteInterval = setInterval(function() {
            if (!isPlaying) { clearInterval(noteInterval); return; }
            if (musicNoteAnim) {
                musicNoteAnim.textContent = notes[i % notes.length];
                i++;
            }
        }, 800);
    }
    
    // ==========================================
    // PROGRESS SIMULATION
    // ==========================================
    function simulateProgress() {
        if (!isPlaying) return;
        if (progress >= 100) {
            progress = 0;
            if (playBtn) {
                const icon = playBtn.querySelector('i');
                icon.className = 'fas fa-play';
                playBtn.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                isPlaying = false;
                if (pulseRing) pulseRing.classList.remove('active');
                if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music';
                if (musicStatus) { musicStatus.textContent = 'Off'; musicStatus.style.color = 'gray'; }
                if (musicStatusMobile) musicStatusMobile.textContent = 'Music: Off';
                if (noteInterval) clearInterval(noteInterval);
            }
            return;
        }
        progress += 0.5;
        if (progressFill) progressFill.style.width = progress + '%';
        updateTimeDisplay();
        progressInterval = setTimeout(simulateProgress, 100);
    }
    
    // ==========================================
    // UPDATE TIME DISPLAY
    // ==========================================
    function updateTimeDisplay() {
        if (currentTime) {
            const currentSeconds = Math.floor((progress / 100) * totalDuration);
            const mins = Math.floor(currentSeconds / 60);
            const secs = currentSeconds % 60;
            currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
    }
    
    // ==========================================
    // PROGRESS TRACK CLICK
    // ==========================================
    if (progressTrack) {
        progressTrack.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const percent = (x / rect.width) * 100;
            progress = Math.min(100, Math.max(0, percent));
            if (progressFill) progressFill.style.width = progress + '%';
            updateTimeDisplay();
        });
    }
    
    // ==========================================
    // PREVIOUS / NEXT
    // ==========================================
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            progress = Math.max(0, progress - 10);
            if (progressFill) progressFill.style.width = progress + '%';
            updateTimeDisplay();
        });
    }
    
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
            if (volume === 0) {
                document.querySelector('.music-volume i').className = 'fas fa-volume-mute';
            } else {
                document.querySelector('.music-volume i').className = 'fas fa-volume-down';
            }
            localStorage.setItem('staynest_musicVolume', volume);
        });
    }
    
    // Restore volume
    const savedVolume = localStorage.getItem('staynest_musicVolume');
    if (savedVolume !== null && volumeSlider) {
        volume = parseFloat(savedVolume);
        volumeSlider.value = volume;
        if (volumePercent) volumePercent.textContent = volume + '%';
    }
    
    // ==========================================
    // SET TOTAL TIME
    // ==========================================
    if (totalTime) {
        const mins = Math.floor(totalDuration / 60);
        const secs = totalDuration % 60;
        totalTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
    }
    
    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName !== 'INPUT' && e.key === ' ') {
            e.preventDefault();
            if (playBtn) playBtn.click();
        }
    });
    
    console.log('🎵 StayNest Music Player ready!');
});