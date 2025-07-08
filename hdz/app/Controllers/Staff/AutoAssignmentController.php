<?php
/**
 * @package HelpDeskZ Auto Assignment
 * @author: HelpDeskZ Team
 * @Copyright (c) 2025, HelpDeskZ.com
 * @description: Controlador para gestión de asignación automática
 */

namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Libraries\AutoAssignment;
use Config\Services;

class AutoAssignmentController extends BaseController
{
    
    public function index()
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $autoAssignment = new AutoAssignment();
        $departments = Services::departments();
        
        return view('staff/auto_assignment', [
            'auto_assignment_enabled' => $autoAssignment->isAutoAssignmentEnabled(),
            'assignment_method' => $autoAssignment->getAssignmentMethod(),
            'departments' => $departments->getAll(),
            'error_msg' => $this->session->getFlashdata('error_msg'),
            'success_msg' => $this->session->getFlashdata('success_msg')
        ]);
    }
    
    public function updateSettings()
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getMethod() === 'post' && $this->request->getPost('do') == 'update_settings'){
            $validation = Services::validation();
            $validation->setRules([
                'auto_assignment' => 'required|in_list[0,1]',
                'auto_assignment_method' => 'required|in_list[random,balanced,weighted]'
            ], [
                'auto_assignment' => [
                    'required' => 'Debe seleccionar si habilitar la asignación automática',
                    'in_list' => 'Valor inválido para asignación automática'
                ],
                'auto_assignment_method' => [
                    'required' => 'Debe seleccionar un método de asignación',
                    'in_list' => 'Método de asignación inválido'
                ]
            ]);

            if($validation->withRequest($this->request)->run() == false){
                $this->session->setFlashdata('error_msg', $validation->listErrors());
            } else {
                try {
                    // Guardar configuraciones usando conexión directa a la base de datos
                    $db = \Config\Database::connect();
                    
                    $auto_assignment = $this->request->getPost('auto_assignment');
                    $auto_assignment_method = $this->request->getPost('auto_assignment_method');
                    
                    // Verificar columna auto_assignment
                    $columnAuto = $db->query("SHOW COLUMNS FROM hdzfv_config LIKE 'auto_assignment'")->getRowArray();
                    if (!$columnAuto) {
                        try {
                            $db->query(
                                "ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment` tinyint(1) NOT NULL DEFAULT '0' AFTER `kb_latest`"
                            );
                        } catch (\Exception $e) {
                            if (!str_contains($e->getMessage(), 'Duplicate column')) {
                                throw $e;
                            }
                        }
                    }

                    // Verificar columna auto_assignment_method
                    $columnMethod = $db->query("SHOW COLUMNS FROM hdzfv_config LIKE 'auto_assignment_method'")->getRowArray();
                    if (!$columnMethod) {
                        try {
                            $db->query(
                                "ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment_method` varchar(20) NOT NULL DEFAULT 'balanced' AFTER `auto_assignment`"
                            );
                        } catch (\Exception $e) {
                            if (!str_contains($e->getMessage(), 'Duplicate column')) {
                                throw $e;
                            }
                        }
                    }
                    
                    // Actualizar la configuración
                    $result = $db->table('hdzfv_config')
                        ->where('id', 1)
                        ->update([
                            'auto_assignment' => $auto_assignment,
                            'auto_assignment_method' => $auto_assignment_method
                        ]);
                    
                    if ($result) {
                        $this->session->setFlashdata('success_msg', 'Configuración de asignación automática actualizada correctamente');
                    } else {
                        $this->session->setFlashdata('error_msg', 'Error al actualizar la configuración en la base de datos');
                    }
                    
                } catch (\Exception $e) {
                    log_message('error', 'Error al guardar configuración de auto_assignment: ' . $e->getMessage());
                    $this->session->setFlashdata('error_msg', 'Error interno: ' . $e->getMessage());
                }
                
                return redirect()->route('staff_auto_assignment');
            }
        }

        // Si llegamos aquí, mostrar la vista con los datos actuales
        $autoAssignment = new AutoAssignment();
        $departments = Services::departments();
        
        return view('staff/auto_assignment', [
            'auto_assignment_enabled' => $autoAssignment->isAutoAssignmentEnabled(),
            'assignment_method' => $autoAssignment->getAssignmentMethod(),
            'departments' => $departments->getAll(),
            'error_msg' => $this->session->getFlashdata('error_msg'),
            'success_msg' => $this->session->getFlashdata('success_msg')
        ]);
    }
    
    public function departmentStats($department_id)
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $autoAssignment = new AutoAssignment();
        $departments = Services::departments();
        
        if(!$department = $departments->getByID($department_id)){
            $this->session->setFlashdata('error_msg', 'Departamento no encontrado');
            return redirect()->back();
        }
        
        $stats = $autoAssignment->getAssignmentStats($department_id);
        
        return view('staff/auto_assignment_stats', [
            'department' => $department,
            'stats' => $stats,
            'error_msg' => $this->session->getFlashdata('error_msg'),
            'success_msg' => $this->session->getFlashdata('success_msg')
        ]);
    }
    
    public function resetCounters()
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'reset_counters'){
            $department_id = $this->request->getPost('department_id');
            
            $autoAssignment = new AutoAssignment();
            
            if($autoAssignment->resetAssignmentCounters($department_id)){
                $this->session->setFlashdata('success_msg', 'Contadores de asignación reiniciados correctamente');
            } else {
                $this->session->setFlashdata('error_msg', 'Error al reiniciar los contadores');
            }
        }
        
        return redirect()->back();
    }
    
    public function staffDepartments()
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'save_assignments'){
            $this->saveStaffDepartmentAssignments();
        }
        
        $staff = new \App\Models\Staff();
        $departments = Services::departments();
        
        // Obtener configuración actual
        $db = \Config\Database::connect();
        $current_assignments = $db->table('hdzfv_staff_departments')
            ->select('staff_id, department_id, active, priority_weight')
            ->get()
            ->getResultArray();
            
        $assignments = [];
        foreach($current_assignments as $assignment){
            $assignments[$assignment['staff_id']][$assignment['department_id']] = [
                'active' => $assignment['active'],
                'weight' => $assignment['priority_weight']
            ];
        }
        
        return view('staff/auto_assignment_staff', [
            'staff_list' => $staff->where('active', 1)->findAll(),
            'departments' => $departments->getAll(),
            'assignments' => $assignments,
            'error_msg' => $this->session->getFlashdata('error_msg'),
            'success_msg' => $this->session->getFlashdata('success_msg')
        ]);
    }
    
    protected function saveStaffDepartmentAssignments()
    {
        $db = \Config\Database::connect();
        
        // Limpiar asignaciones existentes
        $db->table('hdzfv_staff_departments')->truncate();
        
        $staff_assignments = $this->request->getPost('staff_departments');
        
        if($staff_assignments){
            foreach($staff_assignments as $staff_id => $departments){
                foreach($departments as $dept_id => $config){
                    if(isset($config['active']) && $config['active'] == '1'){
                        $weight = max(1, intval($config['weight'] ?? 1));
                        
                        $db->table('hdzfv_staff_departments')->insert([
                            'staff_id' => $staff_id,
                            'department_id' => $dept_id,
                            'active' => 1,
                            'priority_weight' => $weight
                        ]);
                    }
                }
            }
        }
        
        $this->session->setFlashdata('success_msg', 'Asignaciones de staff a departamentos guardadas correctamente');
    }
    
    public function debugStatus()
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        $db = \Config\Database::connect();
        $debug_info = [];
        
        try {
            // Verificar columnas en hdzfv_config
            $columns = $db->query("SHOW COLUMNS FROM hdzfv_config LIKE 'auto_assignment%'")->getResultArray();
            $debug_info['config_columns'] = $columns;
            
            // Verificar valores actuales en hdzfv_config
            $config_values = $db->query("SELECT auto_assignment, auto_assignment_method FROM hdzfv_config WHERE id = 1")->getRowArray();
            $debug_info['config_values'] = $config_values;
            
            // Verificar existencia de tablas
            $tables = [
                'hdzfv_staff_departments' => $db->query("SHOW TABLES LIKE 'hdzfv_staff_departments'")->getNumRows() > 0,
                'hdzfv_department_assignments' => $db->query("SHOW TABLES LIKE 'hdzfv_department_assignments'")->getNumRows() > 0
            ];
            $debug_info['tables'] = $tables;
            
            // Verificar columna staff_id en tickets
            $staff_id_column = $db->query("SHOW COLUMNS FROM hdzfv_tickets LIKE 'staff_id'")->getNumRows() > 0;
            $debug_info['tickets_staff_id'] = $staff_id_column;
            
        } catch (\Exception $e) {
            $debug_info['error'] = $e->getMessage();
        }
        
        // Retornar JSON para debug
        return $this->response->setJSON($debug_info);
    }
    
    public function runMigration()
    {
        // Solo administradores pueden acceder
        if($this->staff->getData('admin') != 1){
            return redirect()->route('staff_dashboard');
        }

        if($this->request->getPost('do') == 'run_migration'){
            try {
                $db = \Config\Database::connect();
                $executed = 0;
                $errors = [];
                
                // Lista de comandos SQL para ejecutar
                $migration_commands = [
                    // Agregar campo staff_id a la tabla tickets
                    "ALTER TABLE `hdzfv_tickets` ADD COLUMN `staff_id` int NOT NULL DEFAULT '0' AFTER `user_id`",
                    "ALTER TABLE `hdzfv_tickets` ADD INDEX `idx_staff_id` (`staff_id`)",
                    
                    // Agregar configuración de auto assignment
                    "ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment` tinyint(1) NOT NULL DEFAULT '0' AFTER `kb_latest`",
                    "ALTER TABLE `hdzfv_config` ADD COLUMN `auto_assignment_method` varchar(20) NOT NULL DEFAULT 'balanced' AFTER `auto_assignment`",
                    
                    // Configurar valores por defecto
                    "UPDATE `hdzfv_config` SET `auto_assignment` = 0, `auto_assignment_method` = 'balanced' WHERE `id` = 1",
                    
                    // Crear tabla de asignaciones por departamento
                    "CREATE TABLE IF NOT EXISTS `hdzfv_department_assignments` (
                        `id` int NOT NULL AUTO_INCREMENT,
                        `department_id` int NOT NULL,
                        `staff_id` int NOT NULL,
                        `assignment_count` int NOT NULL DEFAULT 0,
                        `last_assignment` int NOT NULL DEFAULT 0,
                        PRIMARY KEY (`id`),
                        UNIQUE KEY `dept_staff_unique` (`department_id`, `staff_id`),
                        KEY `idx_department_id` (`department_id`),
                        KEY `idx_staff_id` (`staff_id`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3",
                    
                    // Crear tabla de staff por departamento
                    "CREATE TABLE IF NOT EXISTS `hdzfv_staff_departments` (
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
                ];
                
                // Ejecutar cada comando
                foreach ($migration_commands as $command) {
                    try {
                        $db->query($command);
                        $executed++;
                        log_message('info', 'Comando de migración ejecutado: ' . substr($command, 0, 50) . '...');
                    } catch (\Exception $e) {
                        // Si el error es por columna/tabla/índice existente, no es un error real
                        if (str_contains($e->getMessage(), 'Duplicate column') || 
                            str_contains($e->getMessage(), 'already exists') ||
                            str_contains($e->getMessage(), 'Duplicate key')) {
                            log_message('info', 'Comando ya aplicado previamente: ' . substr($command, 0, 50) . '...');
                        } else {
                            $errors[] = 'Error en comando: ' . $e->getMessage();
                            log_message('error', 'Error en migración: ' . $e->getMessage());
                        }
                    }
                }
                
                if (empty($errors)) {
                    $this->session->setFlashdata('success_msg', "Migración ejecutada correctamente. {$executed} comandos procesados.");
                } else {
                    $this->session->setFlashdata('error_msg', 'Algunos errores durante la migración: ' . implode(', ', $errors));
                }
                
            } catch (\Exception $e) {
                $this->session->setFlashdata('error_msg', 'Error al ejecutar migración: ' . $e->getMessage());
                log_message('error', 'Error general en migración: ' . $e->getMessage());
            }
        }
        
        return redirect()->route('staff_auto_assignment');
    }
}
