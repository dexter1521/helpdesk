-- Script de inicialización para la base de datos del HelpDesk
-- Este archivo se ejecuta automáticamente cuando se crea el contenedor de MySQL

-- Configurar zona horaria
SET time_zone = '+00:00';

-- Configuraciones básicas de MySQL
SET GLOBAL sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO';

-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS helpdesk;
USE helpdesk;

-- Ejecutar el script principal de HelpDeskZ
SOURCE /docker-entrypoint-initdb.d/helpdeskz.sql;

-- Mensaje de confirmación
SELECT 'Base de datos HelpDesk inicializada correctamente con el esquema de HelpDeskZ' AS mensaje;
