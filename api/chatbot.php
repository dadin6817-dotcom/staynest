<?php
// api/chatbot.php - API untuk chatbot
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Koneksi database (opsional, untuk menyimpan history chat)
try {
    require_once dirname(__DIR__) . '/config/database.php';
} catch(Exception $e) {
    // Jika database error, tetap lanjutkan
}

// Mulai session untuk tracking chat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate atau ambil session ID
if (!isset($_SESSION['chat_session_id'])) {
    $_SESSION['chat_session_id'] = session_id();
}
$session_id = $_SESSION['chat_session_id'];

// ==============================================
// FUNGSI CEK JAM OPERASIONAL
// Senin - Sabtu: 08:00 - 17:00 WIB
// Minggu: OFF (Tidak bisa chat)
// ==============================================
function isOperationalHours() {
    $currentHour = (int)date('H');
    $currentMinute = (int)date('i');
    $currentTime = $currentHour + ($currentMinute / 60);
    $currentDay = (int)date('N'); // 1=Senin, 2=Selasa, 3=Rabu, 4=Kamis, 5=Jumat, 6=Sabtu, 7=Minggu
    
    // Minggu (hari ke-7) - OFF sepanjang hari
    if ($currentDay == 7) {
        return false;
    }
    
    // Senin - Sabtu (hari ke-1 sampai ke-6)
    // Jam operasional: 08:00 - 17:00
    $startTime = 8.0;  // 08:00
    $endTime = 17.0;   // 17:00
    
    return ($currentTime >= $startTime && $currentTime <= $endTime);
}

// Fungsi untuk mendapatkan pesan di luar jam operasional
function getOfflineMessage() {
    $currentHour = (int)date('H');
    $currentMinute = (int)date('i');
    $currentDay = (int)date('N');
    
    $dayNames = ['', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    $dayName = $dayNames[$currentDay];
    
    // Cek apakah hari Minggu
    if ($currentDay == 7) {
        return "⏰ *MAAF, HARI MINGGU TUTUP* ⏰\n\n" .
               "Hari ini adalah hari *Minggu* - *Libur Operasional*\n\n" .
               "📅 *Jam operasional kami:*\n" .
               "• Senin - Sabtu: 08:00 - 17:00 WIB\n" .
               "• Minggu: *TUTUP / LIBUR*\n\n" .
               "Silakan kembali pada hari Senin - Sabtu jam 08:00 - 17:00 WIB ya! 😊\n\n" .
               "📞 Atau hubungi langsung:\n" .
               "WA: 0858-1117-7617\n" .
               "Email: adinda.auliap24@gmail.com";
    }
    
    return "⏰ *MAAF, DI LUAR JAM OPERASIONAL* ⏰\n\n" .
           "Saat ini jam " . sprintf("%02d:%02d", $currentHour, $currentMinute) . " WIB (Hari " . $dayName . ")\n\n" .
           "📅 *Jam operasional kami:*\n" .
           "• Senin - Sabtu: 08:00 - 17:00 WIB\n" .
           "• Minggu: *TUTUP / LIBUR*\n\n" .
           "Silakan kembali saat jam operasional ya! 😊\n\n" .
           "📞 Atau hubungi langsung:\n" .
           "WA: 0858-1117-7617\n" .
           "Email: adinda.auliap24@gmail.com";
}

// Fungsi untuk mendapatkan informasi jam operasional
function getOperationalHoursInfo() {
    return "📅 *JAM OPERASIONAL STAYNEST*\n\n" .
           "⏰ *Senin - Sabtu:* 08:00 - 17:00 WIB\n" .
           "❌ *Minggu:* TUTUP / LIBUR\n\n" .
           "✅ Chat akan langsung dibalas selama jam operasional\n" .
           "💬 Di luar jam operasional atau hari Minggu, pesan akan dibalas saat jam kerja berikutnya\n\n" .
           "📞 *Atau hubungi langsung:*\n" .
           "📱 WhatsApp: 0858-1117-7617\n" .
           "📧 Email: adinda.auliap24@gmail.com";
}

// Fungsi untuk menyimpan pesan (opsional)
function saveMessage($pdo, $session_id, $user_message, $bot_response) {
    if (!$pdo) return;
    try {
        // Cek apakah tabel chat_messages ada
        $stmt = $pdo->prepare("CREATE TABLE IF NOT EXISTS chat_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            session_id VARCHAR(100) NOT NULL,
            user_message TEXT NOT NULL,
            bot_response TEXT NOT NULL,
            is_offline INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $stmt->execute();
        
        $is_offline = isOperationalHours() ? 0 : 1;
        $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, user_message, bot_response, is_offline) VALUES (?, ?, ?, ?)");
        $stmt->execute([$session_id, $user_message, $bot_response, $is_offline]);
    } catch(Exception $e) {}
}

// Fungsi untuk mendapatkan respons dari chatbot (LENGKAP BAHASA INDONESIA)
function getBotResponse($message) {
    $message = strtolower(trim($message));
    $originalMessage = $message;
    
    // Hapus karakter khusus untuk pencarian keyword
    $message = preg_replace('/[^a-z0-9\s]/', '', $message);
    
    // ============== RESPONSE UNTUK JAM OPERASIONAL ==============
    if (preg_match('/(jam operasional|jam kerja|operasional|jam layanan)/i', $message)) {
        return getOperationalHoursInfo();
    }
    
    // ============== RESPONSE UNTUK SAPAAN ==============
    if (preg_match('/(halo|hai|hello|hey|haii|hallo|helo)/i', $message)) {
        $operationalInfo = getOperationalHoursInfo();
        return "👋 Halo juga! Selamat datang di StayNest!\n\n" .
               "Ada yang bisa saya bantu tentang kontrakan? 😊\n\n" .
               "💬 *Saya bisa bantu:*\n" .
               "• Info harga kontrakan\n" .
               "• Lokasi properti\n" .
               "• Fasilitas yang tersedia\n" .
               "• Cara booking\n" .
               "• Ketersediaan unit\n" .
               "• Info unit VIP\n\n" .
               $operationalInfo . "\n\n" .
               "Atau ketik *bantuan* untuk menu lengkap!";
    }
    
    if (preg_match('/(makasih|terima kasih|thank|thanks)/i', $message)) {
        return "Sama-sama! 🙏\n\n" .
               "Senang bisa membantu Anda!\n\n" .
               "Ada pertanyaan lain tentang kontrakan StayNest? 😊\n\n" .
               "📅 *Jam operasional:*\n" .
               "Senin - Sabtu: 08:00 - 17:00 WIB\n" .
               "Minggu: TUTUP\n\n" .
               "Jangan ragu untuk bertanya lagi ya! ✨";
    }
    
    if (preg_match('/(assalamualaikum|assalam|salam)/i', $message)) {
        return "Wa'alaikumsalam warahmatullahi wabarakatuh! 👋\n\n" .
               "Selamat datang di StayNest Support!\n\n" .
               "Ada yang bisa saya bantu tentang kontrakan? 😊\n\n" .
               getOperationalHoursInfo();
    }
    
    // ============== RESPONSE UNTUK KONTAK ==============
    if (preg_match('/(kontak|contact|whatsapp|wa|telepon|hubungi|no wa|nomor)/i', $message)) {
        return "📞 *KONTAK STAYNEST*\n\n" .
               "📱 WhatsApp: *0858-1117-7617*\n" .
               "📧 Email: *adinda.auliap24@gmail.com*\n" .
               "🌐 Website: *www.staynest.com*\n\n" .
               "🏢 *Kantor Pusat:*\n" .
               "Jl. Raya Babelan No. 123, Bekasi\n\n" .
               getOperationalHoursInfo() . "\n\n" .
               "🤝 Tim kami siap membantu Anda!";
    }
    
    // ============== RESPONSE UNTUK BANTUAN / MENU ==============
    if (preg_match('/(bantuan|help|menu|tolong)/i', $message)) {
        return "🆘 *MENU BANTUAN STAYNEST*\n\n" .
               "Ketik kata kunci berikut:\n\n" .
               "🏠 *Lihat properti* - Daftar properti\n" .
               "📝 *Cara booking* - Cara booking\n" .
               "💰 *Harga* - Info harga kontrakan\n" .
               "📍 *Lokasi* - Alamat properti\n" .
               "🏠 *Fasilitas* - Fasilitas tersedia\n" .
               "✅ *Ketersediaan* - Unit available\n" .
               "👑 *VIP* - Info unit VIP\n" .
               "📞 *Kontak* - Nomor kontak kami\n" .
               "⏰ *Jam operasional* - Info jam kerja\n\n" .
               "Atau ketik pertanyaan Anda langsung! 😊";
    }
    
    // ============== RESPONSE UNTUK LIHAT PROPERTI ==============
    if (preg_match('/(lihat properti|properties|daftar properti|semua properti)/i', $message)) {
        return "🏠 *DAFTAR PROPERTI STAYNEST*\n\n" .
               "1️⃣ *StayNest Vela*\n" .
               "📍 Babelan, Bekasi\n" .
               "💰 Rp 700.000/bulan\n" .
               "✅ 2 unit tersedia\n\n" .
               "2️⃣ *StayNest Aera (VIP)* ⭐\n" .
               "📍 Tambun Utara, Bekasi\n" .
               "💰 Rp 700.000/bulan\n" .
               "✅ 4 unit tersedia\n\n" .
               "3️⃣ *StayNest Elora (VIP)* ⭐\n" .
               "📍 Babelan, Bekasi\n" .
               "💰 Lantai 1: Rp 1.200.000/bulan\n" .
               "💰 Lantai 2: Rp 1.000.000/bulan\n" .
               "✅ 10 unit tersedia\n\n" .
               "🔗 Kunjungi halaman Properties untuk detail lengkap!\n\n" .
               getOperationalHoursInfo();
    }
    
    // ============== RESPONSE UNTUK CARA BOOKING ==============
    if (preg_match('/(cara booking|booking|pesan|menyewa|sewa)/i', $message)) {
        return "📝 *CARA BOOKING KONTRAKAN STAYNEST*\n\n" .
               "1️⃣ Pilih properti favorit Anda\n" .
               "2️⃣ Klik tombol 'View Details'\n" .
               "3️⃣ Pilih unit yang tersedia (Available)\n" .
               "4️⃣ Klik tombol 'Book Now'\n" .
               "5️⃣ Isi formulir pemesanan\n" .
               "6️⃣ Konfirmasi booking\n\n" .
               "✅ Tim kami akan menghubungi Anda dalam 1x24 jam!\n\n" .
               "📞 Atau hubungi langsung:\n" .
               "WA: 0858-1117-7617";
    }
    
    // ============== RESPONSE UNTUK HARGA ==============
    if (preg_match('/(harga|price|biaya|berapa)/i', $message)) {
        return "💰 *HARGA KONTRAKAN STAYNEST*\n\n" .
               "🏠 *StayNest Vela*\n" .
               "Rp 700.000/bulan\n\n" .
               "🏠 *StayNest Aera*\n" .
               "Rp 700.000/bulan\n\n" .
               "🏠 *StayNest Elora Lantai 1*\n" .
               "Rp 1.200.000/bulan\n\n" .
               "🏠 *StayNest Elora Lantai 2*\n" .
               "Rp 1.000.000/bulan\n\n" .
               "✅ Sudah termasuk listrik token & air tanah jetpump!\n" .
               "💡 Minimal sewa 3 bulan\n" .
               "🎉 Bayar 3 bulan langsung dapat diskon 5%!";
    }
    
    // ============== RESPONSE UNTUK LOKASI ==============
    if (preg_match('/(lokasi|location|dimana|alamat)/i', $message)) {
        return "📍 *LOKASI KONTRAKAN STAYNEST*\n\n" .
               "🏠 *StayNest Vela*\n" .
               "Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi\n\n" .
               "🏠 *StayNest Aera*\n" .
               "Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi\n\n" .
               "🏠 *StayNest Elora*\n" .
               "Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi\n\n" .
               "✅ Akses mobil sampai depan kontrakan\n" .
               "✅ Bebas banjir\n" .
               "✅ Dekat dengan pusat kota";
    }
    
    // ============== RESPONSE UNTUK FASILITAS ==============
    if (preg_match('/(fasilitas|facility|fitur)/i', $message)) {
        return "🏠 *FASILITAS KONTRAKAN STAYNEST*\n\n" .
               "✅ Ruang Tengah\n" .
               "✅ Kamar Mandi Dalam\n" .
               "✅ Dapur dengan Westafel\n" .
               "✅ Ruang Jemur\n" .
               "✅ Listrik Token (900-1300 kWh)\n" .
               "✅ Air Tanah Jetpump\n" .
               "✅ Parkir Luas\n" .
               "✅ Akses Mobil Depan Kontrakan\n" .
               "✅ Bebas Banjir\n" .
               "✅ CCTV 24 Jam\n" .
               "✅ Lingkungan Asri dan Aman\n" .
               "✅ Penerangan yang baik";
    }
    
    // ============== RESPONSE UNTUK KETERSEDIAAN ==============
    if (preg_match('/(ketersediaan|tersedia|available|unit kosong)/i', $message)) {
        return "✅ *UNIT TERSEDIA SAAT INI*\n\n" .
               "🏠 StayNest Vela: *2 unit* tersedia\n" .
               "🏠 StayNest Aera: *4 unit* tersedia\n" .
               "🏠 StayNest Elora Lantai 1: *5 unit* tersedia\n" .
               "🏠 StayNest Elora Lantai 2: *5 unit* tersedia\n\n" .
               "📊 *Total: 16 unit siap huni!*\n\n" .
               "🔥 Cepat booking sebelum kehabisan!";
    }
    
    // ============== RESPONSE UNTUK VIP ==============
    if (preg_match('/(vip|premium|mewah)/i', $message)) {
        return "👑 *INFO UNIT VIP STAYNEST*\n\n" .
               "Unit VIP tersedia di:\n" .
               "⭐ *StayNest Aera*\n" .
               "⭐ *StayNest Elora*\n\n" .
               "✨ *KEUNGGULAN UNIT VIP:*\n" .
               "✅ Interior lebih modern & mewah\n" .
               "✅ Kamar lebih luas\n" .
               "✅ Pencahayaan lebih baik\n" .
               "✅ Desain instagramable\n" .
               "✅ Fasilitas premium\n" .
               "✅ Baru direnovasi\n\n" .
               "💰 Harga mulai Rp 1.000.000 - Rp 1.200.000/bulan\n\n" .
               "🔥 Buruan booking sebelum kehabisan!";
    }
    
    // ============== RESPONSE UNTUK PROPERTI SPESIFIK ==============
    if (preg_match('/(vela|babelan)/i', $message)) {
        return "🏠 *STAYNEST VELA*\n\n" .
               "📍 Lokasi: Kavling Harapan Manunggal Utara, Kec. Bahagia, Babelan, Bekasi\n" .
               "💰 Harga: Rp 700.000/bulan\n" .
               "🚪 Total Unit: 2 unit\n" .
               "✅ Tersedia: 2 unit\n\n" .
               "📝 *FASILITAS:*\n" .
               "✅ 1 Ruang Tengah\n" .
               "✅ 1 Kamar Tidur\n" .
               "✅ 1 Kamar Mandi\n" .
               "✅ Dapur (Westafel)\n" .
               "✅ Listrik Token (900 kWh)\n" .
               "✅ Air Tanah Jetpump\n" .
               "✅ Akses Mobil Depan Kontrakan\n" .
               "✅ Bebas Banjir\n\n" .
               "Tertarik? Booking sekarang juga! 😊";
    }
    
    if (preg_match('/(aera|alamanda)/i', $message)) {
        return "🏠 *STAYNEST AERA (VIP)* ⭐\n\n" .
               "📍 Lokasi: Jl. Pandawa 15, Kp. Gebang, Karang Satria, Tambun Utara, Bekasi\n" .
               "💰 Harga: Rp 700.000/bulan\n" .
               "🚪 Total Unit: 4 unit\n" .
               "✅ Tersedia: 4 unit\n\n" .
               "📝 *FASILITAS:*\n" .
               "✅ 3 Sekat\n" .
               "✅ Dapur (Westafel)\n" .
               "✅ Listrik Token (900 kWh)\n" .
               "✅ Air Tanah Jetpump\n" .
               "✅ Baru Direnovasi\n" .
               "✅ Akses Mobil Depan Kontrakan\n" .
               "✅ Bebas Banjir\n" .
               "✅ 50 m dari Jalan Raya\n\n" .
               "✨ Unit VIP dengan fasilitas premium!";
    }
    
    if (preg_match('/(elora|vip village)/i', $message)) {
        return "🏠 *STAYNEST ELORA (VIP)* ⭐\n\n" .
               "📍 Lokasi: Kavling Bumi Mas 2, Kec. Bahagia, Babelan, Bekasi\n" .
               "💰 Harga Lantai 1: Rp 1.200.000/bulan\n" .
               "💰 Harga Lantai 2: Rp 1.000.000/bulan\n" .
               "🚪 Total Unit: 12 unit\n" .
               "✅ Tersedia: 10 unit\n\n" .
               "📝 *FASILITAS:*\n" .
               "✅ Ruang Tengah\n" .
               "✅ 1 Kamar Mandi\n" .
               "✅ Dapur Westafel\n" .
               "✅ Ruang Jemur\n" .
               "✅ Listrik Token (1.300 kWh)\n" .
               "✅ Air Tanah Jetpump\n" .
               "✅ Lantai 1: 2 Kamar Tidur\n" .
               "✅ Lantai 2: 1 Kamar Tidur\n" .
               "✅ Akses Mobil Depan Kontrakan\n" .
               "✅ Tidak Banjir\n\n" .
               "🏠 Rumah minimalis 2 lantai dengan desain modern!";
    }
    
    // ============== RESPONSE UNTUK LANTAI ==============
    if (preg_match('/(lantai 1|lt 1|lantai satu)/i', $message)) {
        return "💰 *HARGA LANTAI 1 (StayNest Elora)*\n\n" .
               "Harga sewa untuk unit Lantai 1 adalah:\n" .
               "*Rp 1.200.000/bulan*\n\n" .
               "✅ Termasuk:\n" .
               "- Listrik token 1.300 kWh\n" .
               "- Air tanah jetpump\n" .
               "- 2 Kamar Tidur\n" .
               "- Fasilitas lengkap\n\n" .
               "🏠 Minimal sewa 3 bulan. Booking sekarang!";
    }
    
    if (preg_match('/(lantai 2|lt 2|lantai dua)/i', $message)) {
        return "💰 *HARGA LANTAI 2 (StayNest Elora)*\n\n" .
               "Harga sewa untuk unit Lantai 2 adalah:\n" .
               "*Rp 1.000.000/bulan*\n\n" .
               "✅ Termasuk:\n" .
               "- Listrik token 1.300 kWh\n" .
               "- Air tanah jetpump\n" .
               "- 1 Kamar Tidur\n" .
               "- Fasilitas lengkap\n\n" .
               "🏠 Minimal sewa 3 bulan. Booking sekarang!";
    }
    
    // ============== RESPONSE UNTUK UNIT TERTENTU ==============
    if (preg_match('/unit\s*(\d+)/i', $message, $matches)) {
        $unit_num = $matches[1];
        return "🔍 *INFO UNIT $unit_num*\n\n" .
               "Untuk informasi ketersediaan unit $unit_num, silakan:\n\n" .
               "1️⃣ Kunjungi halaman detail properti\n" .
               "2️⃣ Atau hubungi admin kami\n\n" .
               "📱 WhatsApp: 0858-1117-7617\n" .
               "📧 Email: adinda.auliap24@gmail.com\n\n" .
               getOperationalHoursInfo() . "\n\n" .
               "Tim kami akan dengan senang hati membantu Anda! 😊";
    }
    
    // ============== RESPONSE DEFAULT ==============
    return "Maaf, saya kurang paham dengan pertanyaan Anda. 😅\n\n" .
           "💬 *Coba tanyakan ini:*\n" .
           "• *Harga* - Info harga kontrakan\n" .
           "• *Lokasi* - Alamat properti\n" .
           "• *Fasilitas* - Fasilitas tersedia\n" .
           "• *Cara booking* - Proses booking\n" .
           "• *Ketersediaan* - Unit available\n" .
           "• *VIP* - Info unit VIP\n" .
           "• *Kontak* - Hubungi kami\n" .
           "• *Jam operasional* - Info jam kerja\n" .
           "• *Bantuan* - Menu lengkap\n\n" .
           "📞 Atau hubungi langsung:\n" .
           "WA: 0858-1117-7617\n" .
           "Email: adinda.auliap24@gmail.com";
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $message = isset($input['message']) ? trim($input['message']) : '';
    
    if (empty($message)) {
        echo json_encode(['response' => 'Silakan ketik pesan Anda! 😊', 'success' => true]);
        exit;
    }
    
    // Cek jam operasional
    $isOperational = isOperationalHours();
    $response = '';
    
    if (!$isOperational) {
        $response = getOfflineMessage();
    } else {
        $response = getBotResponse($message);
    }
    
    // Simpan ke database jika koneksi ada
    if (isset($pdo) && $pdo) {
        saveMessage($pdo, $session_id, $message, $response);
    }
    
    echo json_encode([
        'response' => $response, 
        'success' => true,
        'is_operational' => $isOperational
    ]);
    exit;
}

// Handle GET request (testing)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo json_encode([
        'success' => true, 
        'message' => 'Chatbot API is ready!',
        'version' => '1.0',
        'operational_hours' => [
            'days' => 'Senin - Sabtu',
            'start' => '08:00',
            'end' => '17:00',
            'closed_on' => 'Minggu (TUTUP)',
            'current_status' => isOperationalHours() ? 'Online' : 'Offline'
        ],
        'contact' => [
            'whatsapp' => '0858-1117-7617',
            'email' => 'adinda.auliap24@gmail.com'
        ]
    ]);
    exit;
}
?>