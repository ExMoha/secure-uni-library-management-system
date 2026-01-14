DROP DATABASE IF EXISTS library;
CREATE DATABASE library;
USE library;

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student'
);

CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    cover_image VARCHAR(255) DEFAULT 'default.jpg',
    status ENUM('available', 'borrowed') DEFAULT 'available'
);

CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    borrow_date DATE NOT NULL DEFAULT CURRENT_DATE,
    return_date DATE DEFAULT NULL,
    status ENUM('active', 'returned') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

CREATE TABLE suggestions (
    suggestion_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_title VARCHAR(200) NOT NULL,
    author VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

INSERT INTO books (title, author, category, description, status, cover_image) VALUES 
('MATH 101', 'Department of Mathematics', 'Mathematics', 'Introduction to Math. A comprehensive guide to fundamental mathematical concepts.', 'available', 'default.jpg'),
('PHYS 101', 'Department of Physics', 'Physics', 'Physics Basics. Covers mechanics, thermodynamics, and electromagnetism.', 'available', 'default.jpg'),
('CYB 325', 'Faculty of Computing', 'Cybersecurity', 'Web Application Development.', 'available', 'default.jpg'),
('CYB 333', 'Faculty of Computing', 'Cybersecurity', 'An in-depth analysis of modern malware, phishing, and network vulnerabilities.', 'available', 'default.jpg');