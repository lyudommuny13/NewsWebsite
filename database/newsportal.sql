-- Create database if not exists
CREATE DATABASE IF NOT EXISTS newsportal;
USE newsportal;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT 0,
    status BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Articles table
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author VARCHAR(100) NOT NULL,
    image_url VARCHAR(255),
    category_id INT,
    status BOOLEAN DEFAULT 1,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Insert default admin user
INSERT INTO users (name, email, password, is_admin, status) VALUES 
('Admin', 'admin@admin.com', '$2y$10$8WkWUP6QHh0kHEGGx8fhPOyOPdm.Qm4gfCqEPEGFgEGqjE5kxL.Gy', 1, 1);

-- Insert sample categories
INSERT INTO categories (name, slug) VALUES 
('Technology', 'technology'),
('Sports', 'sports'),
('Politics', 'politics'),
('Entertainment', 'entertainment'),
('Business', 'business'),
('Science', 'science');

-- Insert sample articles
INSERT INTO articles (title, content, author, category_id, status) VALUES 
('Welcome to NewsPortal', 'This is a sample article content.', 'Admin', 1, 1),
('Getting Started with Web Development', 'Learn the basics of web development.', 'Admin', 1, 1),
('Latest Sports Updates', 'Stay updated with sports news.', 'Admin', 2, 1);

-- Add indexes for better performance
ALTER TABLE articles ADD INDEX idx_category (category_id);
ALTER TABLE articles ADD INDEX idx_status (status);
ALTER TABLE articles ADD INDEX idx_created (created_at);
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE categories ADD INDEX idx_slug (slug); 