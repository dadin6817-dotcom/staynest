<?php
// includes/footer.php - Footer dengan Chatbot di Semua Halaman
?>
<footer class="bg-gray-900 text-white mt-12">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <div class="grid md:grid-cols-4 gap-8">
            <div>
                <h3 class="text-xl font-bold gradient-text mb-4">🏠 StayNest</h3>
                <p class="text-gray-400 text-sm">Find your cozy home today.</p>
                <div class="flex gap-4 mt-4">
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="text-gray-400 hover:text-white transition text-xl"><i class="fab fa-facebook"></i></a>
                </div>
            </div>
            
            <div>
                <h4 class="font-semibold mb-3">Quick Links</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/staynest/welcome.php" class="hover:text-white transition">Welcome</a></li>
                    <li><a href="/staynest/index.php" class="hover:text-white transition">Home</a></li>
                    <li><a href="/staynest/properties.php" class="hover:text-white transition">Properties</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/staynest/bookings/my_bookings.php" class="hover:text-white transition">My Bookings</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold mb-3">Support</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                    <li><a href="#" class="hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="font-semibold mb-3">Contact</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><i class="fas fa-envelope mr-2"></i> info@staynest.com</li>
                    <li><i class="fas fa-phone mr-2"></i> +62 812 3456 7890</li>
                    <li><i class="fas fa-map-marker-alt mr-2"></i> Jakarta, Indonesia</li>
                </ul>
            </div>
        </div>
        
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> StayNest. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- ========================================== -->
<!-- MUSIC PLAYER -->
<!-- ========================================== -->
<div id="musicPlayerContainer" class="fixed bottom-6 left-6 z-[9998]">
    <button id="musicToggle" class="w-14 h-14 rounded-full shadow-lg hover:shadow-xl transition transform hover:scale-110 flex items-center justify-center relative" 
            style="background: linear-gradient(135deg, #667eea, #764ba2); border: none; cursor: pointer;">
        <span class="pulse-ring" id="pulseRing"></span>
        <i class="fas fa-music text-white text-xl" id="musicToggleIcon"></i>
    </button>
    
    <div id="musicControls" class="hidden absolute bottom-20 left-0 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl p-5 w-80 border border-white/20">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-headphones text-lg"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800">StayNest Radio</p>
                <p class="text-xs text-gray-400" id="songTitle">Loading...</p>
            </div>
            <button id="closeMusicBtn" class="text-gray-400 hover:text-gray-600 transition" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="mb-3">
            <input type="range" id="progressSlider" class="music-progress" min="0" max="100" value="0">
            <div class="flex justify-between text-xs text-gray-400 mt-1">
                <span id="currentTime">0:00</span>
                <span id="totalTime">0:00</span>
            </div>
        </div>
        
        <div class="flex items-center justify-between">
            <button id="prevBtn" class="text-gray-500 hover:text-purple-600 transition text-lg w-10 h-10 rounded-full hover:bg-purple-50 flex items-center justify-center" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-step-backward"></i>
            </button>
            <button id="playBtn" class="w-14 h-14 rounded-full flex items-center justify-center text-white transition transform hover:scale-105 shadow-lg" 
                    style="background: linear-gradient(135deg, #667eea, #764ba2); border: none; cursor: pointer;">
                <i class="fas fa-play text-xl"></i>
            </button>
            <button id="nextBtn" class="text-gray-500 hover:text-purple-600 transition text-lg w-10 h-10 rounded-full hover:bg-purple-50 flex items-center justify-center" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-step-forward"></i>
            </button>
            <button id="volumeBtn" class="text-gray-500 hover:text-purple-600 transition text-lg w-10 h-10 rounded-full hover:bg-purple-50 flex items-center justify-center" style="background: none; border: none; cursor: pointer;">
                <i class="fas fa-volume-up"></i>
            </button>
        </div>
        
        <div class="mt-3 hidden" id="volumeContainer">
            <input type="range" id="volumeSlider" class="music-progress" min="0" max="100" value="70">
        </div>
    </div>
</div>

<audio id="audioPlayer" loop>
    <source src="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3" type="audio/mpeg">
</audio>

<!-- ========================================== -->
<!-- CHATBOT - MUNCUL DI SEMUA HALAMAN -->
<!-- ========================================== -->
<?php include_once dirname(__FILE__) . '/chatbot.php'; ?>

<style>
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    #musicControls {
        backdrop-filter: blur(20px);
        background: rgba(255, 255, 255, 0.98);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    }
    
    #musicToggle {
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.4);
        transition: all 0.3s ease;
    }
    
    #musicToggle:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 40px rgba(102, 126, 234, 0.6);
    }
    
    .pulse-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.4);
        animation: pulseRing 1.5s ease-in-out infinite;
        display: none;
    }
    
    .pulse-ring.active { display: block; }
    
    @keyframes pulseRing {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.3); opacity: 0.1; }
    }
    
    .music-progress {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 5px;
        border-radius: 3px;
        background: #e5e7eb;
        outline: none;
        cursor: pointer;
    }
    
    .music-progress::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        cursor: pointer;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Music Player
    var musicToggle = document.getElementById('musicToggle');
    var musicControls = document.getElementById('musicControls');
    var closeMusicBtn = document.getElementById('closeMusicBtn');
    var playBtn = document.getElementById('playBtn');
    var prevBtn = document.getElementById('prevBtn');
    var nextBtn = document.getElementById('nextBtn');
    var volumeBtn = document.getElementById('volumeBtn');
    var progressSlider = document.getElementById('progressSlider');
    var volumeSlider = document.getElementById('volumeSlider');
    var currentTimeEl = document.getElementById('currentTime');
    var totalTimeEl = document.getElementById('totalTime');
    var volumeContainer = document.getElementById('volumeContainer');
    var pulseRing = document.getElementById('pulseRing');
    var musicToggleIcon = document.getElementById('musicToggleIcon');
    var songTitle = document.getElementById('songTitle');
    var audio = document.getElementById('audioPlayer');
    
    if (!audio) return;
    
    var songs = [
        { title: 'SoundHelix - Song 1', src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3' },
        { title: 'SoundHelix - Song 2', src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3' },
        { title: 'SoundHelix - Song 3', src: 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3' }
    ];
    
    var currentSongIndex = 0;
    var isPlaying = false;
    
    if (songTitle) songTitle.textContent = songs[currentSongIndex].title;
    
    if (musicToggle) {
        musicToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            musicControls.classList.toggle('hidden');
        });
    }
    
    if (closeMusicBtn) {
        closeMusicBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            musicControls.classList.add('hidden');
        });
    }
    
    document.addEventListener('click', function(e) {
        if (musicControls && !musicControls.classList.contains('hidden')) {
            if (!musicControls.contains(e.target) && !musicToggle.contains(e.target)) {
                musicControls.classList.add('hidden');
            }
        }
    });
    
    if (playBtn) {
        playBtn.addEventListener('click', function() {
            if (isPlaying) {
                audio.pause();
                isPlaying = false;
                this.querySelector('i').className = 'fas fa-play text-xl';
                this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                if (musicToggleIcon) musicToggleIcon.className = 'fas fa-music text-white text-xl';
                if (pulseRing) pulseRing.classList.remove('active');
            } else {
                audio.play().then(function() {
                    isPlaying = true;
                    playBtn.querySelector('i').className = 'fas fa-pause text-xl';
                    playBtn.style.background = 'linear-gradient(135deg, #f093fb, #f5576c)';
                    if (musicToggleIcon) musicToggleIcon.className = 'fas fa-stop text-white text-xl';
                    if (pulseRing) pulseRing.classList.add('active');
                }).catch(function(err) {
                    console.error('Audio error:', err);
                });
            }
        });
    }
    
    if (audio) {
        audio.addEventListener('timeupdate', function() {
            if (audio.duration) {
                var progress = (audio.currentTime / audio.duration) * 100;
                if (progressSlider) progressSlider.value = progress;
                if (currentTimeEl) {
                    var mins = Math.floor(audio.currentTime / 60);
                    var secs = Math.floor(audio.currentTime % 60);
                    currentTimeEl.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
                }
                if (totalTimeEl) {
                    var tm = Math.floor(audio.duration / 60);
                    var ts = Math.floor(audio.duration % 60);
                    totalTimeEl.textContent = tm + ':' + (ts < 10 ? '0' : '') + ts;
                }
            }
        });
    }
    
    if (progressSlider) {
        progressSlider.addEventListener('input', function() {
            if (audio.duration) audio.currentTime = (this.value / 100) * audio.duration;
        });
    }
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
            audio.src = songs[currentSongIndex].src;
            if (songTitle) songTitle.textContent = songs[currentSongIndex].title;
            if (isPlaying) audio.play();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            currentSongIndex = (currentSongIndex + 1) % songs.length;
            audio.src = songs[currentSongIndex].src;
            if (songTitle) songTitle.textContent = songs[currentSongIndex].title;
            if (isPlaying) audio.play();
        });
    }
    
    if (volumeBtn) {
        volumeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            volumeContainer.classList.toggle('hidden');
        });
    }
    
    if (volumeSlider) {
        volumeSlider.addEventListener('input', function() {
            audio.volume = this.value / 100;
        });
        audio.volume = volumeSlider.value / 100;
    }
});
</script>

</body>
</html>