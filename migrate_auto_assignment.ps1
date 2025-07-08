# Script de PowerShell para migración de Asignación Automática de Tickets
# Ejecutar como administrador para mejores resultados

param(
    [string]$DatabaseHost = "localhost",
    [string]$DatabaseUser = "root",
    [string]$DatabasePassword = "",
    [string]$DatabaseName = "helpdeskz"
)

Write-Host "=== Script de Migración - Asignación Automática de Tickets ===" -ForegroundColor Cyan
Write-Host ""

# Verificar si mysql está disponible
try {
    $null = Get-Command mysql -ErrorAction Stop
    Write-Host "✓ MySQL cliente encontrado" -ForegroundColor Green
} catch {
    Write-Host "✗ ERROR: MySQL cliente no encontrado. Asegúrese de que MySQL esté instalado y en el PATH." -ForegroundColor Red
    exit 1
}

# Crear archivo temporal con los comandos SQL
$sqlCommands = @"
-- Migración para Asignación Automática de Tickets

-- Agregar campo staff_id a la tabla tickets
ALTER TABLE `hdzfv_tickets` ADD COLUMN `staff_id` int NOT NULL DEFAULT '0' AFTER `user_id`;
ALTER TABLE `hdzfv_tickets` ADD INDEX `idx_staff_id` (`staff_id`);

-- Agregar configuración de auto assignment
ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment` tinyint(1) NOT NULL DEFAULT '0' AFTER `kb_latest`;
ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment_method` varchar(20) NOT NULL DEFAULT 'balanced' AFTER `auto_assignment`;

-- Configurar valores por defecto
UPDATE `hdzfv_config` SET `auto_assignment` = 0, `auto_assignment_method` = 'balanced' WHERE `id` = 1;

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
"@

$tempSqlFile = [System.IO.Path]::GetTempFileName() + ".sql"
$sqlCommands | Out-File -FilePath $tempSqlFile -Encoding UTF8

Write-Host "Ejecutando migración..." -ForegroundColor Yellow
Write-Host "Host: $DatabaseHost" -ForegroundColor Gray
Write-Host "Usuario: $DatabaseUser" -ForegroundColor Gray  
Write-Host "Base de datos: $DatabaseName" -ForegroundColor Gray
Write-Host ""

try {
    # Ejecutar comandos SQL
    if ($DatabasePassword -eq "") {
        & mysql -h $DatabaseHost -u $DatabaseUser $DatabaseName < $tempSqlFile
    } else {
        & mysql -h $DatabaseHost -u $DatabaseUser -p$DatabasePassword $DatabaseName < $tempSqlFile
    }
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Migración ejecutada exitosamente" -ForegroundColor Green
        
        # Verificar que las columnas se crearon
        Write-Host ""
        Write-Host "Verificando estructura..." -ForegroundColor Yellow
        
        $verificationSql = @"
SHOW COLUMNS FROM hdzfv_config LIKE 'auto_assignment%';
SHOW COLUMNS FROM hdzfv_tickets LIKE 'staff_id';
SHOW TABLES LIKE 'hdzfv_staff_departments';
SHOW TABLES LIKE 'hdzfv_department_assignments';
"@
        
        $tempVerifyFile = [System.IO.Path]::GetTempFileName() + ".sql"
        $verificationSql | Out-File -FilePath $tempVerifyFile -Encoding UTF8
        
        if ($DatabasePassword -eq "") {
            & mysql -h $DatabaseHost -u $DatabaseUser $DatabaseName < $tempVerifyFile
        } else {
            & mysql -h $DatabaseHost -u $DatabaseUser -p$DatabasePassword $DatabaseName < $tempVerifyFile
        }
        
        Remove-Item $tempVerifyFile -Force
        
    } else {
        Write-Host "✗ Error durante la ejecución de la migración" -ForegroundColor Red
        Write-Host "Código de salida: $LASTEXITCODE" -ForegroundColor Red
    }
    
} catch {
    Write-Host "✗ Error al ejecutar la migración: $($_.Exception.Message)" -ForegroundColor Red
} finally {
    # Limpiar archivo temporal
    Remove-Item $tempSqlFile -Force
}

Write-Host ""
Write-Host "Migración completada. Ahora puede configurar la asignación automática desde el panel de administración." -ForegroundColor Cyan
Write-Host ""
