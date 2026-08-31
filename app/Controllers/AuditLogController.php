<?php

/**
 * AuditLog Controller
 *
 * Read-only module. Renders the activity log index with server-side filters.
 * No POST/AJAX endpoints — the log is append-only and cannot be edited from the UI.
 *
 * @package ProyectoBase
 * @subpackage App\Controllers
 * @author jandrescodes
 * @version 1.0
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ActivityLog;

class AuditLogController extends Controller
{
    private ActivityLog $logModel;

    public function __construct()
    {
        $this->logModel = new ActivityLog();
    }

    /**
     * Renders the audit log index page.
     *
     * Reads optional filters from $_GET, fetches matching rows, and passes
     * both data and active filter values back to the view so controls are
     * repopulated when the page loads from a filtered URL.
     */
    public function index(): void
    {
        $filters = [
            'module'    => trim($_GET['module']    ?? ''),
            'action'    => trim($_GET['action']    ?? ''),
            'actor_id'  => trim($_GET['actor_id']  ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to']   ?? ''),
        ];

        // Remove empty values so the model only applies active filters
        $activeFilters = array_filter($filters, fn ($v) => $v !== '');

        $logs    = $this->logModel->getAll($activeFilters);
        $modules = $this->logModel->getDistinctModules();
        $actions = $this->logModel->getDistinctActions();
        $actors  = $this->logModel->getActorsWithLogs();

        $this->render(
            'audit-log/index',
            compact('logs', 'modules', 'actions', 'actors', 'filters'),
            ['datatables', 'datatables-export', 'select2'],
            ['audit-log/index-audit'],
            ['audit-log/audit-log']
        );
    }
}
