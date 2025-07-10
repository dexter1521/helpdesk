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
        $staff = $this->staff;
        $isAdmin = $staff->getData('admin') == 1;
        $staffId = $staff->getData('id');
        $departments = $staff->getData('department');

        if ($isAdmin) {
            // Admin: resumen global
            $total = $db->table('tickets')->countAllResults();
            $statusCounts = [];
            foreach ($tickets->statusList() as $statusId => $statusName) {
                $statusCounts[$statusName] = $db->table('tickets')->where('status', $statusId)->countAllResults();
            }
            $departmentsList = $db->table('departments')->select('id, name')->get()->getResult();
            $ticketsByDept = [];
            foreach ($departmentsList as $dept) {
                $ticketsByDept[$dept->name] = $db->table('tickets')->where('department_id', $dept->id)->countAllResults();
            }
            $agents = $db->table('staff')->select('id, fullname')->get()->getResult();
            $ticketsByAgent = [];
            foreach ($agents as $agent) {
                $ticketsByAgent[$agent->fullname] = $db->table('tickets')->where('staff_id', $agent->id)->countAllResults();
            }
        } else {
            // Agente: solo sus tickets
            $total = $db->table('tickets')->where('staff_id', $staffId)->countAllResults();
            $statusCounts = [];
            foreach ($tickets->statusList() as $statusId => $statusName) {
                $statusCounts[$statusName] = $db->table('tickets')->where('staff_id', $staffId)->where('status', $statusId)->countAllResults();
            }
            $ticketsByDept = [];
            if (is_array($departments)) {
                foreach ($departments as $deptId) {
                    $deptName = $db->table('departments')->select('name')->where('id', $deptId)->get()->getRow('name');
                    $ticketsByDept[$deptName] = $db->table('tickets')->where('staff_id', $staffId)->where('department_id', $deptId)->countAllResults();
                }
            }
            $ticketsByAgent = [ $staff->getData('fullname') => $total ];
        }

        return view('staff/dashboard', [
            'total_tickets' => $total,
            'status_counts' => $statusCounts,
            'tickets_by_dept' => $ticketsByDept,
            'tickets_by_agent' => $ticketsByAgent
        ]);
    }
}
