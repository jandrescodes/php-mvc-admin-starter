<div class="modal fade" id="modalLogDetail" tabindex="-1" role="dialog"
    aria-labelledby="modalLogDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <!-- Header: static icon + dynamic "Module · action" subtitle set by JS -->
            <div class="modal-header bg-info py-2">
                <div>
                    <h5 class="modal-title text-white mb-0" id="modalLogDetailLabel">
                        <i class="fas fa-history mr-1" aria-hidden="true"></i> Event Detail
                    </h5>
                    <small class="text-white-50" id="detailSubtitle"></small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body py-3">

                <!-- Meta grid: 2 cols on md+, stacked on mobile -->
                <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">ID</dt>
                            <dd class="col-7 mb-1" id="detailId">—</dd>

                            <dt class="col-5 text-muted">Date</dt>
                            <dd class="col-7 mb-1" id="detailCreatedAt">—</dd>

                            <dt class="col-5 text-muted">Module</dt>
                            <dd class="col-7 mb-1" id="detailModule">—</dd>

                            <dt class="col-5 text-muted">Action</dt>
                            <dd class="col-7 mb-0"><code id="detailAction">—</code></dd>
                        </dl>
                    </div>
                    <div class="col-12 col-md-6">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-muted">User</dt>
                            <dd class="col-7 mb-1" id="detailActor">—</dd>

                            <dt class="col-5 text-muted">IP</dt>
                            <dd class="col-7 mb-1 text-monospace" id="detailIp">—</dd>

                            <dt class="col-5 text-muted">User Agent</dt>
                            <dd class="col-7 mb-0 text-break" id="detailUserAgent">—</dd>
                        </dl>
                    </div>
                </div>

                <hr class="mt-2 mb-3">

                <!-- Description — hidden by JS when empty -->
                <div id="descriptionSection" class="mb-3 d-none">
                    <p class="text-muted small font-weight-bold text-uppercase mb-1">Description</p>
                    <div class="bg-light rounded p-2">
                        <p id="detailDescription" class="mb-0"></p>
                    </div>
                </div>

                <!-- Additional details — key/value table, hidden when empty -->
                <div id="detailsSection" class="d-none">
                    <p class="text-muted small font-weight-bold text-uppercase mb-1">Additional details</p>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody id="detailTableBody"></tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1" aria-hidden="true"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>