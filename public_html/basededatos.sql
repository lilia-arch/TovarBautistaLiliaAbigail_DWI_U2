-- Crear la base de datos para la tienda
CREATE DATABASE IF NOT EXISTS gamestore_db;
USE gamestore_db;

-- Crear la tabla de usuarios para gestionar el registro y login
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);