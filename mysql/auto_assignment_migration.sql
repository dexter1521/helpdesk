-- =================================
-- MIGRACIÓN PARA ASIGNACIÓN AUTOMÁTICA DE TICKETS
-- Fecha: 7 de julio 2025
-- =================================

-- Agregar campo staff_id a la tabla tickets para asignación
ALTER TABLE `hdzfv_tickets` 
ADD COLUMN `staff_id` int NOT NULL DEFAULT '0' AFTER `user_id`,
ADD INDEX `idx_staff_id` (`staff_id`);

-- Agregar configuración para habilitar/deshabilitar asignación automática
ALTER TABLE `hdzfv_config` 
ADD COLUMN `auto_assignment` tinyint(1) NOT NULL DEFAULT '0' AFTER `kb_latest`,
ADD COLUMN `auto_assignment_method` varchar(20) NOT NULL DEFAULT 'balanced' AFTER `auto_assignment`;

-- Insertar configuraciones por defecto
UPDATE `hdzfv_config` SET 
    `auto_assignment` = 0,
    `auto_assignment_method` = 'balanced'
WHERE `id` = 1;

-- Agregar tabla para tracking de asignaciones por departamento
CREATE TABLE `hdzfv_department_assignments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `staff_id` int NOT NULL,
  `assignment_count` int NOT NULL DEFAULT 0,
  `last_assignment` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dept_staff_unique` (`department_id`, `staff_id`),
  KEY `idx_department_id` (`department_id`),
  KEY `idx_staff_id` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- Agregar tabla para configuración de staff por departamento
CREATE TABLE `hdzfv_staff_departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `staff_id` int NOT NULL,
  `department_id` int NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `priority_weight` int NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_dept_unique` (`staff_id`, `department_id`),
  KEY `idx_staff_id` (`staff_id`),
  KEY `idx_department_id` (`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
