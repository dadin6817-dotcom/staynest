<?php
// includes/footer.php - Footer dengan Music Player di Bawah Kiri
?>
<footer class="bg-gray-900 text-white py-8 mt-12 relative">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid md:grid-cols-4 gap-8">
            <!-- Brand -->
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
            
            <!-- Quick Links -->
            <div>
                <h4 class="font-semibold mb-3">Quick Links</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="/staynest/index.php" class="hover:text-white transition">Home</a></li>
                    <li><a href="/staynest/properties.php" class="hover:text-white transition">Properties</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="/staynest/bookings/my_bookings.php" class="hover:text-white transition">My Bookings</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Support -->
            <div>
                <h4 class="font-semibold mb-3">Support</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><a href="#" class="hover:text-white transition">FAQ</a></li>
                    <li><a href="#" class="hover:text-white transition">Contact Us</a></li>
                    <li><a href="#" class="hover:text-white transition">Terms & Conditions</a></li>
                    <li><a href="#" class="hover:text-white transition">Privacy Policy</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div>
                <h4 class="font-semibold mb-3">Contact</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li><i class="fas fa-envelope mr-2"></i> info@staynest.com</li>
                    <li><i class="fas fa-phone mr-2"></i> +62 812 3456 7890</li>
                    <li><i class="fas fa-map-marker-alt mr-2"></i> Jakarta, Indonesia</li>
                </ul>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="border-t border-gray-800 mt-8 pt-6 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> StayNest. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- ========================================== -->
<!-- MUSIC PLAYER - FLOATING BOTTOM LEFT -->
<!-- ========================================== -->
<div id="musicPlayerContainer" class="fixed bottom-6 left-6 z-50">
    <!-- Tombol Utama -->
    <button id="musicToggle" class="w-14 h-14 rounded-full shadow-lg hover:shadow-xl transition transform hover:scale-110 flex items-center justify-center relative" 
            style="background: linear-gradient(135deg, #667eea, #764ba2);">
        <span class="pulse-ring" id="pulseRing"></span>
        <i class="fas fa-music text-white text-xl" id="musicToggleIcon"></i>
    </button>
    
    <!-- Kontrol Musik -->
    <div id="musicControls" class="hidden absolute bottom-20 left-0 bg-white/95 backdrop-blur-xl rounded-2xl shadow-2xl p-5 w-80 border border-white/20">
        <!-- Header -->
        <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full flex items-center justify-center text-white" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                <i class="fas fa-headphones text-lg"></i>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800">StayNest Radio</p>
                <p class="text-xs text-gray-400">Nastelbom Elegant</p>
            </div>
            <button id="closeMusicBtn" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- Progress Bar -->
        <div class="mb-3">
            <div class="relative">
                <input type="range" id="progressSlider" class="music-progress" min="0" max="100" value="0">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span id="currentTime">0:00</span>
                    <span id="totalTime">3:45</span>
                </div>
            </div>
        </div>
        
        <!-- Controls -->
        <div class="flex items-center justify-between">
            <button id="prevBtn" class="text-gray-500 hover:text-purple-600 transition text-lg w-10 h-10 rounded-full hover:bg-purple-50 flex items-center justify-center">
                <i class="fas fa-step-backward"></i>
            </button>
            <button id="playBtn" class="w-14 h-14 rounded-full flex items-center justify-center text-white transition transform hover:scale-105 shadow-lg" 
                    style="background: linear-gradient(135deg, #667eea, #764ba2); box-shadow: 0 4px 20px rgba(102,126,234,0.4);">
                <i class="fas fa-play text-xl"></i>
            </button>
            <button id="nextBtn" class="text-gray-500 hover:text-purple-600 transition text-lg w-10 h-10 rounded-full hover:bg-purple-50 flex items-center justify-center">
                <i class="fas fa-step-forward"></i>
            </button>
            <button id="volumeBtn" class="text-gray-500 hover:text-purple-600 transition text-lg w-10 h-10 rounded-full hover:bg-purple-50 flex items-center justify-center">
                <i class="fas fa-volume-up"></i>
            </button>
        </div>
        
        <!-- Volume Slider -->
        <div class="mt-3 hidden" id="volumeContainer">
            <input type="range" id="volumeSlider" class="music-progress" min="0" max="100" value="80">
        </div>
    </div>
</div>

<style>
    .gradient-text {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    #musicControls {
        backdrop-filter: blur(20px);
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
    }
    
    #musicToggle {
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    #musicToggle:hover {
        transform: scale(1.1);
        box-shadow: 0 8px 40px rgba(102, 126, 234, 0.5);
    }
    
    .pulse-ring {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.3);
        animation: pulseRing 1.5s ease-in-out infinite;
        display: none;
    }
    
    .pulse-ring.active {
        display: block;
    }
    
    @keyframes pulseRing {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.3); opacity: 0.1; }
    }
    
    .music-progress {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 4px;
        border-radius: 2px;
        background: #e5e7eb;
        outline: none;
        transition: all 0.2s ease;
    }
    
    .music-progress::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(102,126,234,0.4);
    }
    
    .music-progress::-moz-range-thumb {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea, #764ba2);
        cursor: pointer;
        border: none;
    }
    
    #musicControls .w-14 {
        min-width: 56px;
        min-height: 56px;
    }
</style>

<script>
    // ==========================================
    // MUSIC PLAYER SCRIPT
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        const musicToggle = document.getElementById('musicToggle');
        const musicControls = document.getElementById('musicControls');
        const closeMusicBtn = document.getElementById('closeMusicBtn');
        const playBtn = document.getElementById('playBtn');
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const volumeBtn = document.getElementById('volumeBtn');
        const progressSlider = document.getElementById('progressSlider');
        const volumeSlider = document.getElementById('volumeSlider');
        const currentTime = document.getElementById('currentTime');
        const totalTime = document.getElementById('totalTime');
        const volumeContainer = document.getElementById('volumeContainer');
        const pulseRing = document.getElementById('pulseRing');
        const musicToggleIcon = document.getElementById('musicToggleIcon');
        
        let isPlaying = false;
        let progress = 0;
        let progressInterval = null;
        let isMuted = false;
        let volume = 80;
        const totalDuration = 225; // 3:45 in seconds
        
        // Toggle controls
        function toggleControls(e) {
            if (e) e.stopPropagation();
            musicControls.classList.toggle('hidden');
            if (!musicControls.classList.contains('hidden')) {
                volumeContainer.classList.add('hidden');
            }
        }
        
        if (musicToggle) {
            musicToggle.addEventListener('click', toggleControls);
        }
        
        if (closeMusicBtn) {
            closeMusicBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                musicControls.classList.add('hidden');
            });
        }
        
        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (musicControls && !musicControls.classList.contains('hidden')) {
                if (!musicControls.contains(e.target) && !musicToggle.contains(e.target)) {
                    musicControls.classList.add('hidden');
                }
            }
        });
        
        // Play/Pause
        if (playBtn) {
            playBtn.addEventListener('click', function() {
                isPlaying = !isPlaying;
                const icon = this.querySelector('i');
                
                if (isPlaying) {
                    icon.className = 'fas fa-pause text-xl';
                    this.style.background = 'linear-gradient(135deg, #f093fb, #f5576c)';
                    if (musicToggleIcon) {
                        musicToggleIcon.className = 'fas fa-stop text-white text-xl';
                    }
                    if (pulseRing) {
                        pulseRing.classList.add('active');
                    }
                    simulateProgress();
                } else {
                    icon.className = 'fas fa-play text-xl';
                    this.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                    if (musicToggleIcon) {
                        musicToggleIcon.className = 'fas fa-music text-white text-xl';
                    }
                    if (pulseRing) {
                        pulseRing.classList.remove('active');
                    }
                    if (progressInterval) {
                        clearTimeout(progressInterval);
                        progressInterval = null;
                    }
                }
            });
        }
        
        function simulateProgress() {
            if (!isPlaying) return;
            if (progress >= 100) {
                progress = 0;
                if (playBtn) {
                    const icon = playBtn.querySelector('i');
                    icon.className = 'fas fa-play text-xl';
                    playBtn.style.background = 'linear-gradient(135deg, #667eea, #764ba2)';
                    isPlaying = false;
                    if (musicToggleIcon) {
                        musicToggleIcon.className = 'fas fa-music text-white text-xl';
                    }
                    if (pulseRing) {
                        pulseRing.classList.remove('active');
                    }
                }
                return;
            }
            progress += 0.5;
            if (progressSlider) progressSlider.value = progress;
            updateTimeDisplay();
            progressInterval = setTimeout(simulateProgress, 100);
        }
        
        function updateTimeDisplay() {
            if (currentTime) {
                const currentSeconds = Math.floor((progress / 100) * totalDuration);
                const mins = Math.floor(currentSeconds / 60);
                const secs = currentSeconds % 60;
                currentTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
            }
        }
        
        // Progress slider
        if (progressSlider) {
            progressSlider.addEventListener('input', function() {
                progress = parseFloat(this.value);
                updateTimeDisplay();
            });
        }
        
        // Prev button
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                progress = Math.max(0, progress - 10);
                if (progressSlider) progressSlider.value = progress;
                updateTimeDisplay();
            });
        }
        
        // Next button
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                progress = Math.min(100, progress + 10);
                if (progressSlider) progressSlider.value = progress;
                updateTimeDisplay();
            });
        }
        
        // Volume toggle
        if (volumeBtn) {
            volumeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                volumeContainer.classList.toggle('hidden');
                const icon = this.querySelector('i');
                if (isMuted) {
                    icon.className = 'fas fa-volume-up';
                    isMuted = false;
                    if (volumeSlider) volumeSlider.value = volume;
                } else {
                    icon.className = 'fas fa-volume-mute';
                    isMuted = true;
                    if (volumeSlider) volumeSlider.value = 0;
                }
            });
        }
        
        // Volume slider
        if (volumeSlider) {
            volumeSlider.addEventListener('input', function() {
                volume = parseFloat(this.value);
                const icon = volumeBtn?.querySelector('i');
                if (volume === 0) {
                    if (icon) icon.className = 'fas fa-volume-mute';
                    isMuted = true;
                } else {
                    if (icon) icon.className = 'fas fa-volume-up';
                    isMuted = false;
                }
            });
        }
        
        // Set total time
        if (totalTime) {
            const mins = Math.floor(totalDuration / 60);
            const secs = totalDuration % 60;
            totalTime.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        }
        
        // Keyboard shortcut (space bar)
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName !== 'INPUT' && e.key === ' ') {
                e.preventDefault();
                if (playBtn) playBtn.click();
            }
        });
    });
</script>

</body>
</html>