CREATE DATABASE IF NOT EXISTS graffiti_blog;
USE graffiti_blog;

CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(255),
    location VARCHAR(100),
    risk_level ENUM('BAJO', 'MEDIO', 'ALTO') DEFAULT 'BAJO',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE
);

CREATE TABLE comments (
    id_comment INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    id_user INT NOT NULL,
    id_post INT NOT NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE,
    FOREIGN KEY (id_post) REFERENCES posts(id_post)
        ON DELETE CASCADE
);

CREATE TABLE likes (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_post INT NOT NULL,
    UNIQUE (id_user, id_post),
    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE,
    FOREIGN KEY (id_post) REFERENCES posts(id_post)
        ON DELETE CASCADE
);

CREATE TABLE reposts (
    id_repost INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_post INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_user, id_post),
    FOREIGN KEY (id_user) REFERENCES users(id_user)
        ON DELETE CASCADE,
    FOREIGN KEY (id_post) REFERENCES posts(id_post)
        ON DELETE CASCADE
);
