-- Create auth database
CREATE DATABASE IF NOT EXISTS lms_auth;
USE lms_auth;

-- Database connections table
CREATE TABLE IF NOT EXISTS db_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    host VARCHAR(255) NOT NULL,
    database_name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Verification codes table
CREATE TABLE IF NOT EXISTS verification_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    connection_id INT NOT NULL,
    code VARCHAR(255) NOT NULL,
    expiry DATETIME NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (connection_id) REFERENCES db_connections(id)
);

-- Insert sample connection
INSERT INTO db_connections (name, host, database_name, username, password) VALUES 
('Main Branch', 'localhost', 'lms_main', 'root', 'password'),
('Branch 2', 'localhost', 'lms_branch2', 'root', 'password');
