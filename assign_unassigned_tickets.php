<?php

/**
 * Script para asignar automáticamente tickets que no tienen staff asignado
 * Este script debe ejecutarse desde la línea de comandos o navegador
 * para procesar los tickets legacy que se crearon antes de activar auto-assignment
 */

// Verificar si se está ejecutando desde CLI o desde navegador
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    // Si se ejecuta desde navegador, mostrar interfaz básica
    echo "<!DOCTYPE html>
    <html>
    <head>
        <title>Asignar Tickets Sin Asignar</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            .container { max-width: 800px; margin: 0 auto; }
            .button { background: #007cba; color: white; padding: 10px 20px; border: none; cursor: pointer; }
            .result { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
            .success { background: #d4edda; color: #155724; }
            .error { background: #f8d7da; color: #721c24; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Asignar Tickets Sin Asignar</h1>
            <p>Este script identificará y asignará automáticamente todos los tickets que no tienen staff asignado.</p>";

    if (isset($_POST['execute'])) {
        echo "<div class='result'>";
        processUnassignedTickets();
        echo "</div>";
    } else {
        echo "<form method='post'>
                <button type='submit' name='execute' class='button'>Ejecutar Asignación</button>
              </form>";
    }

    echo "</div></body></html>";
} else {
    // Ejecución desde CLI
    echo "Iniciando asignación de tickets sin asignar...\n";
    processUnassignedTickets();
}

function processUnassignedTickets() {
    try {
        // Cargar CodeIgniter
        require_once 'hdz/app/Config/Paths.php';
        $paths = new Config\Paths();
        require_once $paths->systemDirectory . '/bootstrap.php';

        // Inicializar servicios
        $db = \Config\Database::connect();
        $settings = \Config\Services::settings();

        // Verificar si auto-assignment está habilitado
        $autoAssignmentEnabled = ($settings->get('auto_assignment') == 1);
        
        if (!$autoAssignmentEnabled) {
            echo "⚠️ La auto-asignación no está habilitada. Activándola primero...\n";
            
            // Activar auto-assignment si no está activo
            $db->query("UPDATE " . db_prefix() . "config SET auto_assignment = 1");
            echo "✅ Auto-asignación activada.\n";
        }

        // Obtener tickets sin asignar
        $query = $db->query("
            SELECT t.id, t.subject, t.department_id, d.name as department_name 
            FROM " . db_prefix() . "tickets t 
            LEFT JOIN " . db_prefix() . "departments d ON t.department_id = d.id 
            WHERE t.staff_id = 0 OR t.staff_id IS NULL
            ORDER BY t.id
        ");
        
        $unassignedTickets = $query->getResult();
        
        if (empty($unassignedTickets)) {
            echo "✅ No se encontraron tickets sin asignar.\n";
            return;
        }

        echo "📋 Se encontraron " . count($unassignedTickets) . " tickets sin asignar:\n\n";

        // Cargar la librería de auto-asignación
        $autoAssignment = new \App\Libraries\AutoAssignment();
        
        $successCount = 0;
        $errorCount = 0;

        foreach ($unassignedTickets as $ticket) {
            try {
                echo "🎫 Procesando ticket #{$ticket->id}: {$ticket->subject}\n";
                echo "   Departamento: {$ticket->department_name} (ID: {$ticket->department_id})\n";
                
                // Intentar asignar el ticket automáticamente
                $result = $autoAssignment->assignTicket($ticket->id, $ticket->department_id);
                
                if ($result['success']) {
                    echo "   ✅ Asignado exitosamente al staff: {$result['staff_name']} (ID: {$result['staff_id']})\n";
                    $successCount++;
                } else {
                    echo "   ❌ Error al asignar: {$result['message']}\n";
                    $errorCount++;
                }
                
                echo "\n";
                
            } catch (Exception $e) {
                echo "   ❌ Error procesando ticket #{$ticket->id}: " . $e->getMessage() . "\n\n";
                $errorCount++;
            }
        }

        echo "📊 RESUMEN DE PROCESAMIENTO:\n";
        echo "   ✅ Tickets asignados exitosamente: {$successCount}\n";
        echo "   ❌ Tickets con errores: {$errorCount}\n";
        echo "   📋 Total procesados: " . count($unassignedTickets) . "\n\n";

        if ($successCount > 0) {
            echo "🎉 ¡Proceso completado! Los agentes ahora pueden ver todos los tickets asignados.\n";
        }

    } catch (Exception $e) {
        echo "❌ Error fatal: " . $e->getMessage() . "\n";
    }
}

function db_prefix() {
    return 'hdzfv_';
}
