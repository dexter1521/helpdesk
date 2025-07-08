<?php
/**
 * Script de migración para Asignación Automática de Tickets
 * Este script puede ejecutarse desde la línea de comandos o desde el navegador
 * para configurar las tablas y columnas necesarias para la asignación automática
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cargar la configuración de la base de datos desde variables de entorno (Docker)
// O usar configuración manual si no están disponibles

// Configuración de la base de datos (ajustar según tu configuración)
$config = [
    'hostname' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USER') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'database' => getenv('DB_NAME') ?: 'helpdesk', // Cambiar por el nombre de tu base de datos
    'charset' => 'utf8'
];

try {
    // Conectar a la base de datos
    $pdo = new PDO(
        "mysql:host={$config['hostname']};dbname={$config['database']};charset={$config['charset']}", 
        $config['username'], 
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<h2>Script de Migración - Asignación Automática de Tickets</h2>\n";
    echo "<pre>\n";
    
    $executed = 0;
    $errors = [];
    
    // Lista de comandos SQL para ejecutar
    $migration_commands = [
        [
            'description' => 'Agregar campo staff_id a la tabla tickets',
            'sql' => "ALTER TABLE `hdzfv_tickets` ADD COLUMN `staff_id` int NOT NULL DEFAULT '0' AFTER `user_id`"
        ],
        [
            'description' => 'Agregar índice para staff_id en tickets',
            'sql' => "ALTER TABLE `hdzfv_tickets` ADD INDEX `idx_staff_id` (`staff_id`)"
        ],
        [
            'description' => 'Agregar columna auto_assignment a config',
            'sql' => "ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment` tinyint(1) NOT NULL DEFAULT '0' AFTER `kb_latest`"
        ],
        [
            'description' => 'Agregar columna auto_assignment_method a config',
            'sql' => "ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment_method` varchar(20) NOT NULL DEFAULT 'balanced' AFTER `auto_assignment`"
        ],
        [
            'description' => 'Configurar valores por defecto en config',
            'sql' => "UPDATE `hdzfv_config` SET `auto_assignment` = 0, `auto_assignment_method` = 'balanced' WHERE `id` = 1"
        ],
        [
            'description' => 'Crear tabla de asignaciones por departamento',
            'sql' => "CREATE TABLE IF NOT EXISTS `hdzfv_department_assignments` (
                `id` int NOT NULL AUTO_INCREMENT,
                `department_id` int NOT NULL,
                `staff_id` int NOT NULL,
                `assignment_count` int NOT NULL DEFAULT 0,
                `last_assignment` int NOT NULL DEFAULT 0,
                PRIMARY KEY (`id`),
                UNIQUE KEY `dept_staff_unique` (`department_id`, `staff_id`),
                KEY `idx_department_id` (`department_id`),
                KEY `idx_staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        ],
        [
            'description' => 'Crear tabla de staff por departamento',
            'sql' => "CREATE TABLE IF NOT EXISTS `hdzfv_staff_departments` (
                `id` int NOT NULL AUTO_INCREMENT,
                `staff_id` int NOT NULL,
                `department_id` int NOT NULL,
                `active` tinyint(1) NOT NULL DEFAULT 1,
                `priority_weight` int NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `staff_dept_unique` (`staff_id`, `department_id`),
                KEY `idx_staff_id` (`staff_id`),
                KEY `idx_department_id` (`department_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3"
        ]
    ];
    
    // Ejecutar cada comando
    foreach ($migration_commands as $command) {
        echo "Ejecutando: {$command['description']}\n";
        try {
            $pdo->exec($command['sql']);
            echo "✓ ÉXITO: {$command['description']}\n";
            $executed++;
        } catch (PDOException $e) {
            // Si el error es por columna/tabla/índice existente, no es un error real
            if (strpos($e->getMessage(), 'Duplicate column') !== false || 
                strpos($e->getMessage(), 'already exists') !== false ||
                strpos($e->getMessage(), 'Duplicate key') !== false) {
                echo "⚠ YA EXISTE: {$command['description']}\n";
            } else {
                echo "✗ ERROR: {$command['description']} - {$e->getMessage()}\n";
                $errors[] = $e->getMessage();
            }
        }
        echo "\n";
    }
    
    echo "\n--- RESUMEN ---\n";
    echo "Comandos ejecutados exitosamente: {$executed}\n";
    echo "Errores encontrados: " . count($errors) . "\n";
    
    if (!empty($errors)) {
        echo "\nErrores:\n";
        foreach ($errors as $error) {
            echo "- {$error}\n";
        }
    }
    
    // Verificar el estado actual
    echo "\n--- VERIFICACIÓN FINAL ---\n";
    
    // Verificar columnas en hdzfv_config
    $stmt = $pdo->query("SHOW COLUMNS FROM hdzfv_config LIKE 'auto_assignment%'");
    $columns = $stmt->fetchAll();
    echo "Columnas de auto_assignment en hdzfv_config: " . count($columns) . "\n";
    foreach ($columns as $column) {
        echo "  - {$column['Field']} ({$column['Type']})\n";
    }
    
    // Verificar campo staff_id en tickets
    $stmt = $pdo->query("SHOW COLUMNS FROM hdzfv_tickets LIKE 'staff_id'");
    $staff_id_exists = $stmt->rowCount() > 0;
    echo "Campo staff_id en tickets: " . ($staff_id_exists ? "✓ Existe" : "✗ No existe") . "\n";
    
    // Verificar tablas
    $tables_to_check = ['hdzfv_staff_departments', 'hdzfv_department_assignments'];
    foreach ($tables_to_check as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        $exists = $stmt->rowCount() > 0;
        echo "Tabla {$table}: " . ($exists ? "✓ Existe" : "✗ No existe") . "\n";
    }
    
    echo "\n✓ Migración completada. Ahora puede configurar la asignación automática desde el panel de administración.\n";
    echo "</pre>\n";
    
} catch (PDOException $e) {
    echo "<h2>Error de Conexión</h2>\n";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Verifique la configuración de la base de datos en la parte superior del archivo.</p>\n";
}
?>
