<?php
function install_schema(PDO $pdo, string $adminName, string $adminUser, string $adminPassword): void {
    $sql = [];
    $sql[] = "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(120) NOT NULL, username VARCHAR(80) NOT NULL UNIQUE, password VARCHAR(255) NOT NULL, role ENUM('admin','kepala_sekolah','supervisor','guru') NOT NULL DEFAULT 'guru', teacher_id INT NULL, active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS teachers (id INT AUTO_INCREMENT PRIMARY KEY, nip VARCHAR(40), name VARCHAR(140) NOT NULL, gender ENUM('L','P') DEFAULT 'L', phone VARCHAR(40), email VARCHAR(120), expertise VARCHAR(120), status VARCHAR(80) DEFAULT 'Aktif', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS subjects (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(140) NOT NULL, phase VARCHAR(20) DEFAULT 'E/F', area VARCHAR(120), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS classes (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(80) NOT NULL, major VARCHAR(120), level VARCHAR(20), created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS instruments (id INT AUTO_INCREMENT PRIMARY KEY, aspect VARCHAR(160) NOT NULL, indicator TEXT NOT NULL, weight INT NOT NULL DEFAULT 1, active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS schedules (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NOT NULL, subject_id INT NOT NULL, class_id INT NOT NULL, supervisor_id INT NULL, scheduled_at DATETIME NOT NULL, status ENUM('Direncanakan','Berlangsung','Selesai','Dibatalkan') DEFAULT 'Direncanakan', notes TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS observations (id INT AUTO_INCREMENT PRIMARY KEY, schedule_id INT NOT NULL, teacher_id INT NOT NULL, observer_user_id INT NOT NULL, learning_objectives TEXT, good_practices TEXT, recommendations TEXT, final_score DECIMAL(5,2) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS observation_scores (id INT AUTO_INCREMENT PRIMARY KEY, observation_id INT NOT NULL, instrument_id INT NOT NULL, score INT NOT NULL DEFAULT 0, notes TEXT) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS followups (id INT AUTO_INCREMENT PRIMARY KEY, observation_id INT NOT NULL, action_plan TEXT NOT NULL, due_date DATE, status ENUM('Belum Mulai','Proses','Selesai') DEFAULT 'Belum Mulai', result_notes TEXT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS documents (id INT AUTO_INCREMENT PRIMARY KEY, teacher_id INT NULL, instrument_id INT NULL, title VARCHAR(180) NOT NULL, category VARCHAR(80), file_name VARCHAR(255), notes TEXT, uploaded_by INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS instrument_downloads (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL, description TEXT NULL, category VARCHAR(80) NULL, file_path VARCHAR(255) NOT NULL, original_name VARCHAR(255) NULL, file_ext VARCHAR(20) NULL, file_size INT NULL, button_label VARCHAR(80) NULL, active TINYINT(1) NOT NULL DEFAULT 1, sort_order INT NOT NULL DEFAULT 0, uploaded_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS academic_supervision_forms (id INT AUTO_INCREMENT PRIMARY KEY, stage ENUM('pra_mapel','observasi_mapel','pasca_mapel','pra_bk','observasi_bk','pasca_bk') NOT NULL, teacher_type ENUM('Mapel','BK') NOT NULL DEFAULT 'Mapel', teacher_id INT NOT NULL, subject_id INT NULL, class_id INT NULL, supervisor_user_id INT NULL, supervision_date DATE NULL, focus TEXT NULL, strengths TEXT NULL, notes TEXT NULL, recommendations TEXT NULL, score DECIMAL(5,2) NULL, created_by INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    $sql[] = "CREATE TABLE IF NOT EXISTS school_identity (id INT PRIMARY KEY DEFAULT 1, school_name VARCHAR(180) NOT NULL DEFAULT 'SMK Negeri Contoh', npsn VARCHAR(40) NULL, address TEXT NULL, phone VARCHAR(60) NULL, email VARCHAR(120) NULL, website VARCHAR(120) NULL, logo_path VARCHAR(255) NULL, principal_name VARCHAR(140) NULL, principal_nip VARCHAR(60) NULL, principal_signature_path VARCHAR(255) NULL, supervisor_name VARCHAR(140) NULL, supervisor_nip VARCHAR(60) NULL, supervisor_signature_path VARCHAR(255) NULL, city VARCHAR(80) NULL, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    foreach($sql as $q){ $pdo->exec($q); }
    $pdo->exec("INSERT INTO school_identity(id,school_name,npsn,address,phone,email,website,city) VALUES(1,'SMK Negeri Contoh','','Alamat sekolah belum diatur','','','','') ON DUPLICATE KEY UPDATE id=id");

    $count = (int)$pdo->query("SELECT COUNT(*) FROM instruments")->fetchColumn();
    if($count===0){
        $items = [
            ['Perencanaan Pembelajaran','Tujuan pembelajaran, ATP, modul ajar, asesmen diagnostik, dan alur kegiatan sesuai Kurikulum Merdeka.',2],
            ['Pembelajaran Berdiferensiasi','Guru menyesuaikan proses, konten, dan produk belajar berdasarkan kebutuhan murid.',2],
            ['Budaya Positif & Manajemen Kelas','Kelas aman, inklusif, disiplin positif, dan mendorong partisipasi aktif.',1],
            ['Pemanfaatan Teknologi/Teaching Factory','Penggunaan media, perangkat praktik, industri, dan teknologi sesuai karakter SMK.',1],
            ['Asesmen Formatif & Sumatif','Asesmen digunakan untuk umpan balik, perbaikan proses, dan pengukuran capaian kompetensi.',2],
            ['Refleksi dan Tindak Lanjut','Guru melakukan refleksi, menerima umpan balik, dan menyusun perbaikan pembelajaran.',2],
        ];
        $st=$pdo->prepare("INSERT INTO instruments(aspect,indicator,weight) VALUES(?,?,?)");
        foreach($items as $it){$st->execute($it);}    
    }
    foreach(['X TKJ 1|Teknik Jaringan Komputer|X','XI RPL 1|Rekayasa Perangkat Lunak|XI','XII TKR 1|Teknik Kendaraan Ringan|XII'] as $row){
        [$n,$m,$l]=explode('|',$row); $st=$pdo->prepare("INSERT IGNORE INTO classes(name,major,level) VALUES(?,?,?)"); $st->execute([$n,$m,$l]);
    }
    foreach(['Produktif RPL|F|Kejuruan','Dasar-Dasar TJKT|E|Kejuruan','Projek Kreatif dan Kewirausahaan|F|Kejuruan'] as $row){
        [$n,$p,$a]=explode('|',$row); $st=$pdo->prepare("INSERT IGNORE INTO subjects(name,phase,area) VALUES(?,?,?)"); $st->execute([$n,$p,$a]);
    }
    $hash = password_hash($adminPassword, PASSWORD_DEFAULT);
    $st=$pdo->prepare("INSERT INTO users(name,username,password,role,active) VALUES(?,?,?,?,1) ON DUPLICATE KEY UPDATE password=VALUES(password), name=VALUES(name), role='admin', active=1");
    $st->execute([$adminName,$adminUser,$hash,'admin']);
}
