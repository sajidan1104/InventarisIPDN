// --- SISTEM TABS NAVIGATION DENGAN FITUR TOGGLE SAKELAR (OPEN/CLOSE) ---
function switchTab(tabName, shouldScroll = true) {
    const targetContent = document.getElementById('section-' + tabName);
    const targetCard = document.getElementById('card-' + tabName);

    if (!targetContent || !targetCard) return;

    // Deteksi apakah kartu yang diklik saat ini sudah berstatus aktif
    const isCurrentlyActive = targetCard.classList.contains('active');

    // 1. Reset / Tutup semua tab tabel dan nonaktifkan semua kartu terlebih dahulu
    document.querySelectorAll('.stat-card').forEach(card => {
        card.classList.remove('active');
    });
    document.querySelectorAll('.tab-content').forEach(content => {
        content.style.display = 'none';
    });

    // 2. Jika kartu sebelumnya TIDAK aktif, maka lakukan aksi BUKA (Expand)
    if (!isCurrentlyActive) {
        targetContent.style.display = 'block';
        targetCard.classList.add('active');

        // Gulirkan halaman ke bawah menuju area kerja tabel secara halus
        if (shouldScroll) {
            const targetElement = document.getElementById('management-area');
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    } 
    // 3. Jika kartu sebelumnya SUDAH aktif, maka lakukan aksi TUTUP (Collapse)
    else {
        // Gulirkan halaman kembali ke atas secara halus agar fokus ke menu statistik utama
        if (shouldScroll) {
            const statsGridElement = document.querySelector('.stats-grid');
            if (statsGridElement) {
                statsGridElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }
    }
}

// Inisialisasi DataTables dinamis pada semua tabel saat halaman siap
$(document).ready(function() {
    $('.dynamic-table').each(function() {
        $(this).DataTable({
            "language": {
                "lengthMenu": "Tampilkan _MENU_ entri per halaman",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(disaring dari total _MAX_ entri)",
                "search": "Cari Data:",
                "paginate": {
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            }
        });
    });

    // Ambil flag penanda dinamis dari variabel global PHP_FLAGS
    const flags = window.PHP_FLAGS || {};

    // 1. Tangani popup selamat datang saat login sukses
    if (flags.showWelcome) {
        const welcomeModal = document.querySelector('#welcomeModal');
        if (welcomeModal) {
            setTimeout(() => {
                welcomeModal.classList.add('active');
            }, 300);
        }
    }

    const closeWelcomeBtn = document.querySelector('#closeWelcomeBtn');
    if (closeWelcomeBtn) {
        closeWelcomeBtn.addEventListener('click', function() {
            const welcomeModal = document.querySelector('#welcomeModal');
            if (welcomeModal) welcomeModal.classList.remove('active');
        });
    }

    // 2. Deteksi parameter URL "tab" pasca manipulasi CRUD
    if (flags.activeTab) {
        setTimeout(() => {
            switchTab(flags.activeTab, true);
        }, 100);
    }
});

// --- SISTEM THEME TOGGLE (DARK/LIGHT MODE) ---
const checkboxTheme = document.querySelector('#checkboxTheme');
const currentTheme = localStorage.getItem('theme') ? localStorage.getItem('theme') : null;

// Terapkan tema yang tersimpan di localStorage saat pertama kali dimuat
if (currentTheme) {
    document.body.classList.add(currentTheme);
    if (currentTheme === 'dark-mode' && checkboxTheme) {
        checkboxTheme.checked = true;
    }
}

if (checkboxTheme) {
    checkboxTheme.addEventListener('change', function(e) {
        if (e.target.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light-mode');
        }
    });
}

// --- SAPAAN WAKTU DINAMIS (REAL-TIME CLIENT CLOCK) ---
function updateGreeting() {
    const greetingText = document.querySelector('#greetingText');
    if (!greetingText) return;

    // Ambil data username yang aktif dari bendera PHP_FLAGS
    const flags = window.PHP_FLAGS || {};
    const username = flags.currentUsername ? flags.currentUsername : 'Admin';

    const hour = new Date().getHours();
    let sapaan = "Selamat ";

    if (hour >= 5 && hour < 11) {
        sapaan += "Pagi";
    } else if (hour >= 11 && hour < 15) {
        sapaan += "Siang";
    } else if (hour >= 15 && hour < 18) {
        sapaan += "Sore";
    } else {
        sapaan += "Malam";
    }

    // Ubah nama sapaan secara dinamis sesuai username aktif
    greetingText.innerHTML = `<i class="far fa-clock"></i> ${sapaan}, ${username} 👋`;
}

// Jalankan fungsi sapaan saat halaman siap
document.addEventListener('DOMContentLoaded', updateGreeting);

// --- CONFIRMATION DIALOG UNTUK LOGOUT ---
const logoutBtn = document.querySelector('#logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function(e) {
        if (!confirm('Apakah Anda yakin ingin keluar dari aplikasi?')) {
            e.preventDefault();
        }
    });
}

// --- FLOATING SCROLL TO TOP BUTTON (ON TOP) ---
const scrollTopBtn = document.querySelector('#scrollTopBtn');

window.addEventListener('scroll', function() {
    if (window.scrollY > 300) {
        scrollTopBtn.classList.add('visible');
    } else {
        scrollTopBtn.classList.remove('visible');
    }
});

if (scrollTopBtn) {
    scrollTopBtn.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}