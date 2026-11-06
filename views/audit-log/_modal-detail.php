<div class="modal fade" id="modalLogDetail" tabindex="-1" aria-labelledby="modalLogDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <!-- Header: ícono fijo + subtítulo «Módulo · acción» que rellena el JS -->
            <div class="modal-header text-bg-info py-2">
                <div>
                    <h5 class="modal-title mb-0" id="modalLogDetailLabel">
                        <i class="bi bi-clock-history me-1" aria-hidden="true"></i> Event Detail
                    </h5>
                    <small id="detailSubtitle"></small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body py-3">

                <!-- Metadatos: 2 columnas desde md, apilados en móvil -->
                <div class="row">
                    <div class="col-12 col-md-6 mb-2 mb-md-0">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-body-secondary">ID</dt>
                            <dd class="col-7 mb-1" id="detailId">—</dd>

                            <dt class="col-5 text-body-secondary">Date</dt>
                            <dd class="col-7 mb-1" id="detailCreatedAt">—</dd>

                            <dt class="col-5 text-body-secondary">Module</dt>
                            <dd class="col-7 mb-1" id="detailModule">—</dd>

                            <dt class="col-5 text-body-secondary">Action</dt>
                            <dd class="col-7 mb-0"><code id="detailAction">—</code></dd>
                        </dl>
                    </div>
                    <div class="col-12 col-md-6">
                        <dl class="row mb-0 small">
                            <dt class="col-5 text-body-secondary">User</dt>
                            <dd class="col-7 mb-1" id="detailActor">—</dd>

                            <dt class="col-5 text-body-secondary">IP</dt>
                            <dd class="col-7 mb-1 font-monospace" id="detailIp">—</dd>

                            <dt class="col-5 text-body-secondary">User Agent</dt>
                            <dd class="col-7 mb-0 text-break" id="detailUserAgent">—</dd>
                        </dl>
                    </div>
                </div>

                <hr class="mt-2 mb-3">

                <!-- Descripción: el JS la oculta si está vacía -->
                <div id="descriptionSection" class="mb-3 d-none">
                    <p class="text-body-secondary small fw-bold text-uppercase mb-1">Description</p>
                    <div class="bg-body-secondary rounded p-2">
                        <p id="detailDescription" class="mb-0"></p>
                    </div>
                </div>

                <!-- Detalles adicionales: tabla clave/valor, oculta si no hay -->
                <div id="detailsSection" class="d-none">
                    <p class="text-body-secondary small fw-bold text-uppercase mb-1">Additional details</p>
                    <table class="table table-sm table-borderless mb-0">
                        <tbody id="detailTableBody"></tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1" aria-hidden="true"></i> Close
                </button>
            </div>

        </div>
    </div>
</div>
