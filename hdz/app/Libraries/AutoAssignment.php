<?php
/**
 * @package HelpDeskZ Auto Assignment
 * @author: HelpDeskZ Team
 * @Copyright (c) 2025, HelpDeskZ.com
 * @description: Sistema de asignación automática y balanceada de tickets
 */

namespace App\Libraries;

use App\Models\Staff;
use Config\Database;
use Config\Services;

class AutoAssignment
{
    protected $db;
    protected $settings;
    
    public function __construct()
    {
        $this->db = Database::connect();
        $this->settings = Services::settings();
    }
    
    /**
     * Asignar ticket automáticamente usando el método configurado
     * 
     * @param int $ticket_id ID del ticket
     * @param int $department_id ID del departamento
     * @return int|false ID del staff asignado o false si falla
     */
    public function assignTicket($ticket_id, $department_id)
    {
        // Verificar si la asignación automática está habilitada
        if (!$this->isAutoAssignmentEnabled()) {
            return false;
        }
        
        $method = $this->getAssignmentMethod();
        
        switch ($method) {
            case 'random':
                return $this->assignRandom($ticket_id, $department_id);
            case 'balanced':
                return $this->assignBalanced($ticket_id, $department_id);
            case 'weighted':
                return $this->assignWeighted($ticket_id, $department_id);
            default:
                return $this->assignBalanced($ticket_id, $department_id);
        }
    }
    
    /**
     * Asignación aleatoria
     */
    protected function assignRandom($ticket_id, $department_id)
    {
        $available_staff = $this->getAvailableStaff($department_id);
        
        if (empty($available_staff)) {
            return false;
        }
        
        // Selección aleatoria
        $selected_staff = $available_staff[array_rand($available_staff)];
        
        return $this->performAssignment($ticket_id, $selected_staff['id'], $department_id);
    }
    
    /**
     * Asignación balanceada (round-robin)
     */
    protected function assignBalanced($ticket_id, $department_id)
    {
        $available_staff = $this->getAvailableStaff($department_id);
        
        if (empty($available_staff)) {
            return false;
        }
        
        // Obtener conteos actuales de asignación
        $assignment_counts = $this->getAssignmentCounts($department_id);
        
        // Encontrar el staff con menor número de asignaciones
        $min_assignments = PHP_INT_MAX;
        $selected_staff = null;
        
        foreach ($available_staff as $staff) {
            $count = $assignment_counts[$staff['id']] ?? 0;
            if ($count < $min_assignments) {
                $min_assignments = $count;
                $selected_staff = $staff;
            }
        }
        
        if (!$selected_staff) {
            return false;
        }
        
        return $this->performAssignment($ticket_id, $selected_staff['id'], $department_id);
    }
    
    /**
     * Asignación ponderada por prioridad
     */
    protected function assignWeighted($ticket_id, $department_id)
    {
        $available_staff = $this->getAvailableStaffWithWeights($department_id);
        
        if (empty($available_staff)) {
            return false;
        }
        
        // Calcular probabilidades basadas en peso
        $total_weight = array_sum(array_column($available_staff, 'weight'));
        $random = mt_rand(1, $total_weight);
        
        $current_weight = 0;
        foreach ($available_staff as $staff) {
            $current_weight += $staff['weight'];
            if ($random <= $current_weight) {
                return $this->performAssignment($ticket_id, $staff['id'], $department_id);
            }
        }
        
        return false;
    }
    
    /**
     * Realizar la asignación efectiva del ticket
     */
    protected function performAssignment($ticket_id, $staff_id, $department_id)
    {
        // Actualizar el ticket con el staff asignado
        $update_ticket = $this->db->table('hdzfv_tickets')
            ->where('id', $ticket_id)
            ->update(['staff_id' => $staff_id]);
            
        if (!$update_ticket) {
            return false;
        }
        
        // Actualizar contador de asignaciones
        $this->updateAssignmentCount($department_id, $staff_id);
        
        // Log de la asignación
        log_message('info', "Ticket #{$ticket_id} asignado automáticamente al staff #{$staff_id} en departamento #{$department_id}");
        
        return $staff_id;
    }
    
    /**
     * Obtener staff disponible para un departamento
     */
    protected function getAvailableStaff($department_id)
    {
        $query = "
            SELECT DISTINCT s.id, s.fullname, s.username, s.email
            FROM hdzfv_staff s
            INNER JOIN hdzfv_staff_departments sd ON s.id = sd.staff_id
            WHERE s.active = 1 
            AND sd.department_id = ? 
            AND sd.active = 1
            ORDER BY s.fullname ASC
        ";
        
        $result = $this->db->query($query, [$department_id]);
        $staff_list = $result->getResultArray();
        
        // Si no hay agentes específicos configurados, usar agentes con acceso general al departamento
        if (empty($staff_list)) {
            $query_fallback = "
                SELECT DISTINCT s.id, s.fullname, s.username, s.email
                FROM hdzfv_staff s
                WHERE s.active = 1 
                AND s.admin = 0
                AND (
                    s.department LIKE '%\"$department_id\"%'
                    OR s.department = ''
                )
                ORDER BY s.fullname ASC
            ";
            
            $result = $this->db->query($query_fallback);
            $staff_list = $result->getResultArray();
        }
        
        return $staff_list;
    }
    
    /**
     * Obtener staff con pesos de prioridad
     */
    protected function getAvailableStaffWithWeights($department_id)
    {
        $query = "
            SELECT DISTINCT s.id, s.fullname, s.username, s.email,
                   COALESCE(sd.priority_weight, 1) as weight
            FROM hdzfv_staff s
            INNER JOIN hdzfv_staff_departments sd ON s.id = sd.staff_id
            WHERE s.active = 1 
            AND sd.department_id = ? 
            AND sd.active = 1
            ORDER BY weight DESC, s.fullname ASC
        ";
        
        $result = $this->db->query($query, [$department_id]);
        $staff_list = $result->getResultArray();
        
        // Si no hay agentes específicos configurados, usar agentes con acceso general al departamento
        if (empty($staff_list)) {
            $query_fallback = "
                SELECT DISTINCT s.id, s.fullname, s.username, s.email, 1 as weight
                FROM hdzfv_staff s
                WHERE s.active = 1 
                AND s.admin = 0
                AND (
                    s.department LIKE '%\"$department_id\"%'
                    OR s.department = ''
                )
                ORDER BY s.fullname ASC
            ";
            
            $result = $this->db->query($query_fallback);
            $staff_list = $result->getResultArray();
        }
        
        return $staff_list;
    }
    
    /**
     * Obtener conteos de asignación actuales
     */
    protected function getAssignmentCounts($department_id)
    {
        $query = "
            SELECT staff_id, assignment_count
            FROM hdzfv_department_assignments
            WHERE department_id = ?
        ";
        
        $result = $this->db->query($query, [$department_id]);
        $counts = [];
        
        foreach ($result->getResultArray() as $row) {
            $counts[$row['staff_id']] = $row['assignment_count'];
        }
        
        return $counts;
    }
    
    /**
     * Actualizar contador de asignaciones
     */
    protected function updateAssignmentCount($department_id, $staff_id)
    {
        $query = "
            INSERT INTO hdzfv_department_assignments 
            (department_id, staff_id, assignment_count, last_assignment)
            VALUES (?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE 
                assignment_count = assignment_count + 1,
                last_assignment = ?
        ";
        
        $timestamp = time();
        return $this->db->query($query, [$department_id, $staff_id, $timestamp, $timestamp]);
    }
    
    /**
     * Verificar si la asignación automática está habilitada
     */
    public function isAutoAssignmentEnabled()
    {
        // Verificar en la tabla config (forma principal)
        $query = "SELECT auto_assignment FROM hdzfv_config WHERE id = 1";
        $result = $this->db->query($query);
        
        if ($result->getNumRows() > 0) {
            $row = $result->getRow();
            return (bool) $row->auto_assignment;
        }
        
        // Fallback: verificar en settings si no existe en config
        $query = "SELECT value FROM hdzfv_settings WHERE var = 'auto_assignment_enabled'";
        $result = $this->db->query($query);
        
        if ($result->getNumRows() > 0) {
            $row = $result->getRow();
            return (bool) $row->value;
        }
        
        // Si no encuentra nada, asumir deshabilitado
        return false;
    }
    
    /**
     * Obtener método de asignación configurado
     */
    public function getAssignmentMethod()
    {
        $query = "SELECT auto_assignment_method FROM hdzfv_config WHERE id = 1";
        $result = $this->db->query($query);
        
        if ($result->getNumRows() > 0) {
            $row = $result->getRow();
            return $row->auto_assignment_method ?: 'balanced';
        }
        
        return 'balanced';
    }
    
    /**
     * Reasignar ticket manualmente
     */
    public function reassignTicket($ticket_id, $staff_id)
    {
        $update = $this->db->table('hdzfv_tickets')
            ->where('id', $ticket_id)
            ->update(['staff_id' => $staff_id]);
            
        if ($update) {
            log_message('info', "Ticket #{$ticket_id} reasignado manualmente al staff #{$staff_id}");
        }
        
        return $update;
    }
    
    /**
     * Obtener estadísticas de asignación por departamento
     */
    public function getAssignmentStats($department_id)
    {
        $query = "
            SELECT s.id, s.fullname, s.username,
                   COALESCE(da.assignment_count, 0) as assignments,
                   da.last_assignment
            FROM hdzfv_staff s
            LEFT JOIN hdzfv_department_assignments da ON s.id = da.staff_id AND da.department_id = ?
            LEFT JOIN hdzfv_staff_departments sd ON s.id = sd.staff_id AND sd.department_id = ?
            WHERE s.active = 1 
            AND (
                s.admin = 1 
                OR (sd.department_id = ? AND sd.active = 1)
                OR NOT EXISTS (SELECT 1 FROM hdzfv_staff_departments WHERE staff_id = s.id)
            )
            ORDER BY assignments DESC, s.fullname ASC
        ";
        
        $result = $this->db->query($query, [$department_id, $department_id, $department_id]);
        return $result->getResultArray();
    }
    
    /**
     * Resetear contadores de asignación
     */
    public function resetAssignmentCounters($department_id = null)
    {
        $query = "DELETE FROM hdzfv_department_assignments";
        $params = [];
        
        if ($department_id) {
            $query .= " WHERE department_id = ?";
            $params[] = $department_id;
        }
        
        return $this->db->query($query, $params);
    }
}
