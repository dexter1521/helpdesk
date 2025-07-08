# Solución: Asignación Automática de Tickets No Funciona

## Problema Identificado

La asignación automática de tickets no funciona desde la configuración (settings) debido a que faltan las columnas y tablas necesarias en la base de datos.

## Solución

### Opción 1: Migración desde el Panel de Administración (Recomendado)

1. Ve a **Staff Panel > Configuración > Asignación Automática**
2. Haz clic en el botón **"Ejecutar Migración"**
3. Confirma la ejecución
4. Verifica que aparezca el mensaje de éxito

### Opción 2: Ejecución Manual SQL

Si la migración automática falla, ejecuta los siguientes comandos SQL directamente en tu base de datos:

```sql
-- Agregar campo staff_id a la tabla tickets
ALTER TABLE `hdzfv_tickets` 
ADD COLUMN `staff_id` int NOT NULL DEFAULT '0' AFTER `user_id`,
ADD INDEX `idx_staff_id` (`staff_id`);

-- Agregar configuración de auto assignment
ALTER TABLE `hdzfv_config` 
ADD COLUMN `auto_assignment` tinyint(1) NOT NULL DEFAULT '0' AFTER `kb_latest`,
ADD COLUMN `auto_assignment_method` varchar(20) NOT NULL DEFAULT 'balanced' AFTER `auto_assignment`;

-- Configurar valores por defecto
UPDATE `hdzfv_config` SET 
    `auto_assignment` = 0,
    `auto_assignment_method` = 'balanced'
WHERE `id` = 1;

-- Crear tabla de asignaciones por departamento
CREATE TABLE IF NOT EXISTS `hdzfv_department_assignments` (
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

-- Crear tabla de staff por departamento
CREATE TABLE IF NOT EXISTS `hdzfv_staff_departments` (
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
```

## Verificación

Después de ejecutar cualquiera de las opciones, verifica que:

1. Las columnas `auto_assignment` y `auto_assignment_method` existen en la tabla `hdzfv_config`
2. La columna `staff_id` existe en la tabla `hdzfv_tickets`
3. Las tablas `hdzfv_staff_departments` y `hdzfv_department_assignments` existen

## Configuración Post-Migración

1. Ve a **Staff Panel > Configuración > Asignación Automática**
2. Habilita la asignación automática
3. Selecciona el método de asignación deseado:
   - **Balanceado**: Distribuye tickets equitativamente
   - **Aleatorio**: Asignación aleatoria
   - **Ponderado**: Basado en pesos de prioridad
4. Configura los agentes por departamento en **Gestionar Staff por Departamento**

## Archivos Modificados

Los siguientes archivos fueron corregidos:

1. **hdz/app/Views/staff/auto_assignment.php** - Corregido el formulario
2. **hdz/app/Controllers/Staff/AutoAssignmentController.php** - Mejorado manejo de errores
3. **hdz/app/Libraries/AutoAssignment.php** - Optimizado para usar Settings
4. **hdz/app/Libraries/Tickets.php** - Removido debug forzado
5. **hdz/app/Controllers/Staff/Tickets.php** - Removido debug forzado

## Solución Rápida

1. Ejecutar migración desde el panel de administración
2. Habilitar la asignación automática
3. Configurar método y agentes por departamento

La asignación automática funcionará correctamente después de ejecutar la migración.
