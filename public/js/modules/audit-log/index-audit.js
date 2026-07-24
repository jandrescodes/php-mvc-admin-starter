/**
 * index-audit.js — Audit Log page
 *
 * Responsibilities:
 *   1. Initialize DataTables on #tableAuditLog (columns 0-5 exported, column 6 excluded).
 *   2. Open #modalLogDetail and populate it from the single data-log JSON attribute on <tr>.
 *   3. Render the details object as a readable key-value table (no raw JSON exposed).
 *   4. Show a dynamic subtitle in the modal header (module badge + action).
 *
 * No AJAX calls — all data arrives server-rendered.
 *
 * @package ProyectoBase
 * @subpackage JavaScript\Modules\AuditLog
 * @author jandrescodes
 * @version 1.2
 */

$(document).ready(function () {

    // -------------------------------------------------------------------------
    // DataTables — columns 0-5 exported, column 6 (detail btn) excluded
    // -------------------------------------------------------------------------

    const config = createTableConfig('AuditLog', [0, 1, 2, 3, 4, 5], {
        pageLength: 25,
        order: [[0, 'desc']],
        columnDefs: [
            { targets: 6, orderable: false, searchable: false }
        ]
    });

    const table = $('#tableAuditLog').DataTable(config);
    table.buttons().container().appendTo('#tableAuditLog_wrapper .col-md-6:eq(0)');

    // -------------------------------------------------------------------------
    // Date filter — calendar trigger button opens the native picker
    // -------------------------------------------------------------------------

    $(document).on('click', '.btn-date-picker', function () {
        const targetId = $(this).data('picker-target');
        const input = document.getElementById(targetId);
        if (input && typeof input.showPicker === 'function') {
            input.showPicker();
        }
    });

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Badge classes per module — mirrors the PHP match() in the view */
    const MODULE_BADGES = {
        auth:        'badge-info',
        users:       'badge-primary',
        roles:       'badge-warning',
        permissions: 'badge-secondary',
    };

    /**
     * Returns a Bootstrap badge HTML string for the given module.
     *
     * @param {string} module
     * @returns {string}
     */
    function moduleBadge(module) {
        const cls = MODULE_BADGES[module] || 'badge-dark';
        const label = module.charAt(0).toUpperCase() + module.slice(1);
        return `<span class="badge ${cls} badge-pill px-2">${label}</span>`;
    }

    /**
     * Converts a snake_case or camelCase key into a human-readable label.
     * Examples: "identifier_type" → "Identifier type"
     *
     * @param {string} key
     * @returns {string}
     */
    function humanizeKey(key) {
        return key
            .replace(/_/g, ' ')
            .replace(/([a-z])([A-Z])/g, '$1 $2')
            .replace(/^./, c => c.toUpperCase());
    }

    /**
     * Builds the rows of #detailTableBody from a flat or nested details object.
     * Nested objects are serialized to a readable "key: value, …" string.
     *
     * @param {object} details
     */
    function renderDetailsTable(details) {
        const $tbody = $('#detailTableBody').empty();

        Object.entries(details).forEach(([key, value]) => {
            let display;
            if (value === null || value === undefined) {
                display = '<span class="text-muted">—</span>';
            } else if (typeof value === 'object') {
                display = Object.entries(value)
                    .map(([k, v]) => `${humanizeKey(k)}: ${v}`)
                    .join(', ');
            } else if (typeof value === 'boolean') {
                display = value
                    ? '<span class="text-success"><i class="fas fa-check mr-1" aria-hidden="true"></i>Yes</span>'
                    : '<span class="text-muted"><i class="fas fa-times mr-1" aria-hidden="true"></i>No</span>';
            } else {
                display = $('<span>').text(String(value)).html(); // XSS-safe
            }

            $tbody.append(
                `<tr>
                    <td class="text-muted small text-uppercase text-nowrap pr-3" style="width:35%;vertical-align:middle">
                        ${humanizeKey(key)}
                    </td>
                    <td style="vertical-align:middle">${display}</td>
                </tr>`
            );
        });
    }

    // -------------------------------------------------------------------------
    // Detail modal — populate from data-log on the parent <tr>
    // -------------------------------------------------------------------------

    /**
     * Parses the single data-log JSON attribute from the row and populates
     * #modalLogDetail fields, then opens the modal.
     *
     * @param {jQuery} $btn  The clicked .btn-detail button
     */
    function openDetailModal($btn) {
        // In DataTables responsive mode, hidden columns are moved into a "child" <tr>.
        // The data-log attribute lives on the original (parent) row, not the child.
        let $tr = $btn.closest('tr');
        if ($tr.hasClass('child')) {
            $tr = $tr.prev('tr.parent');
        }

        const raw = $tr.data('log');
        if (!raw) return; // Guard: row not found (should not happen)

        /** @type {{id:number, created_at:string, actor_id:number|null, actor_label:string,
         *           module:string, action:string, description:string,
         *           ip:string, user_agent:string, details:object|null}} */
        const log = (typeof raw === 'string') ? JSON.parse(raw) : raw;

        // Dynamic header subtitle: badge + action
        $('#detailSubtitle').html(`${moduleBadge(log.module)}&nbsp;<code class="text-white-50 small">${log.action || ''}</code>`);

        // Meta fields
        const actorText = log.actor_id
            ? `${log.actor_label || '—'} <span class="text-muted">(ID ${log.actor_id})</span>`
            : (log.actor_label || '<span class="text-muted">Anonymous</span>');

        $('#detailId').text(log.id || '—');
        $('#detailCreatedAt').text(log.created_at || '—');
        $('#detailModule').html(moduleBadge(log.module));
        $('#detailAction').text(log.action || '—');
        $('#detailActor').html(actorText);
        $('#detailIp').text(log.ip || '—');
        $('#detailUserAgent').text(log.user_agent || '—');

        // Description — show callout only when there is content
        if (log.description) {
            $('#detailDescription').text(log.description);
            $('#descriptionSection').removeClass('d-none');
        } else {
            $('#descriptionSection').addClass('d-none');
        }

        // Details table — show only when there is data
        const hasDetails = log.details
            && typeof log.details === 'object'
            && Object.keys(log.details).length > 0;

        if (hasDetails) {
            renderDetailsTable(log.details);
            $('#detailsSection').removeClass('d-none');
        } else {
            $('#detailsSection').addClass('d-none');
        }

        $('#modalLogDetail').modal('show');
    }

    // Event delegation — works for DataTables rows on any page
    $(document).on('click', '.btn-detail', function () {
        openDetailModal($(this));
    });

});
