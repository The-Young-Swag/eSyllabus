<!-- UNIVERSAL VALIDATION MODAL -->
<div class="modal fade" id="dynamicModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow">

            <!-- Dynamic Header -->
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold" id="dynamicModalTitle"></h5>
                    <small class="text-muted d-block">
                        Please verify the information before proceeding
                    </small>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3"
                        data-bs-dismiss="modal"></button>
            </div>

            <!-- Dynamic Body -->
            <div class="modal-body pt-3" id="dynamicModalBody">
            </div>

            <!-- Dynamic Footer -->
            <div class="modal-footer border-0 pt-0 justify-content-center gap-2"
                 id="dynamicModalFooter">
            </div>

        </div>
    </div>
</div>



<div class="modal fade" id="viewAllModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <div class="w-100 text-center">
                    <h5 class="modal-title fw-bold" id="viewAllModalTitle"></h5>
                    <small class="text-muted d-block" id="viewAllModalSubtitle"></small>
                </div>
                <button type="button" class="btn-close position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3" id="viewAllModalBody">
                <!-- Table injected here -->
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-center" id="viewAllModalFooter">
                <!-- Pagination injected here -->
            </div>
        </div>
    </div>
</div>