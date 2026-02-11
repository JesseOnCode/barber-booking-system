-- =============================================
-- BarberShop - Tietokantarakenne
-- =============================================

-- Luo tietokanta jos ei ole olemassa
CREATE DATABASE IF NOT EXISTS barbershop 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Käytä tietokantaa
USE barbershop;

-- =============================================
-- Käyttäjätaulu
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indeksit nopeampaan hakuun
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Varaustaulu
-- =============================================
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    duration INT NOT NULL COMMENT 'Kesto minuutteina',
    notes TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Viiteavain käyttäjään
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- Indeksit nopeampaan hakuun
    INDEX idx_date_time (date, time),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Palvelutaulu (valinnainen, tulevaisuutta varten)
-- =============================================
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    duration INT NOT NULL COMMENT 'Kesto minuutteina',
    price DECIMAL(10, 2) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_active (active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- Lisää peruspalvelut
-- =============================================
INSERT INTO services (name, description, duration, price) VALUES
('Hiustenleikkaus', 'Miesten hiustenleikkaus ammattilaiselta', 30, 25.00),
('Parranleikkaus', 'Parran muotoilu ja trimmaus', 15, 15.00),
('Koneajo', 'Täysi koneajo', 20, 20.00),
('Hiustenleikkaus + Parranleikkaus', 'Täydellinen grooming-paketti', 45, 35.00);