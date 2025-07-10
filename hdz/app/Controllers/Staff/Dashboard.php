<?php
/**
 * Dashboard para Staff - Admin
 */
namespace App\Controllers\Staff;

use App\Controllers\BaseController;
use App\Libraries\Tickets;

class Dashboard extends BaseController
{
    public function index()
    {
        $tickets = new Tickets();
        $db = \Config\Database::connect();
        $builder = $db->table('tickets');
        $total = $builder->countAllResults();

        // Contar por estado
        $statusCounts = [];
        foreach ($tickets->statusList() as $statusId => $statusName) {
            $statusCounts[$statusName] = $db->table('tickets')->where('status', $statusId)->countAllResults();
        }

        // Métricas por departamento
        $departments = $db->table('departments')->select('id, name')->get()->getResult();
        $ticketsByDept = [];
        foreach ($departments as $dept) {
            $ticketsByDept[$dept->name] = $db->table('tickets')->where('department_id', $dept->id)->countAllResults();
        }

        // Métricas por agente
        $agents = $db->table('staff')->select('id, name')->get()->getResult();
        $ticketsByAgent = [];
        foreach ($agents as $agent) {
            $ticketsByAgent[$agent->name] = $db->table('tickets')->where('staff_id', $agent->id)->countAllResults();
        }

        return view('staff/dashboard', [
            'total_tickets' => $total,
            'status_counts' => $statusCounts,
            'tickets_by_dept' => $ticketsByDept,
            'tickets_by_agent' => $ticketsByAgent
        ]);
    }
}
