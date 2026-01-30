-- Consultant Availability & Profile Management (MySQL 8)
-- Run this against the target database.

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('consultant','recruiter','admin') NOT NULL DEFAULT 'consultant',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  last_login_at DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS profiles (
  user_id INT PRIMARY KEY,
  workday_worker_id VARCHAR(64) NULL,
  first_name VARCHAR(120) NULL,
  last_name VARCHAR(120) NULL,
  location_country VARCHAR(120) NULL,
  location_city VARCHAR(120) NULL,

  availability_status ENUM('available_now','available_from','unavailable') NOT NULL DEFAULT 'available_now',
  available_from_date DATE NULL,
  available_in_months INT NULL,

  skills_json JSON NULL,
  skills_text TEXT NULL,
  recent_experience TEXT NULL,
  linkedin_url VARCHAR(512) NULL,

  cv_url VARCHAR(512) NULL,
  updated_at DATETIME NOT NULL,

  CONSTRAINT fk_profiles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_profiles_availability ON profiles(availability_status);
CREATE INDEX idx_profiles_location_country ON profiles(location_country);
CREATE INDEX idx_profiles_updated_at ON profiles(updated_at);

-- Optional FULLTEXT (MySQL 8+ with InnoDB supports fulltext)
ALTER TABLE profiles ADD FULLTEXT INDEX ft_profiles_text (skills_text, recent_experience);
