<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="h4 mb-0 fw-bold text-dark">Site Allotment</h2>
            <p class="text-muted mb-0">Assign panels exclusively to Branch Managers.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center shadow-sm" onclick="openAllotmentModal()">
                <i class="ph ph-plus-circle me-2 fs-5"></i> Assign Panels
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="ph ph-magnifying-glass"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="filterSearch" placeholder="Search mappings...">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Sr No</th>
                        <th>Assigned Panel</th>
                        <th>Branch Manager</th>
                        <th>Branch Code</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody id="allotmentTableBody">
                    <tr>
                        <td colspan="5" class="p-5 text-center text-muted">
                            <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                            Loading mappings...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="allotmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold d-flex align-items-center">
                    <i class="ph ph-link me-2"></i>Assign Panels
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="allotmentForm">
                    <div class="mb-4">
                        <label class="form-label fw-semibold small">Branch Manager <span class="text-danger">*</span></label>
                        <select class="form-select" id="branch_manager_id" name="branch_manager_id" required>
                            <option value="">Select a Manager...</option>
                        </select>
                    </div>

                    <div class="mb-2 position-relative">
                        <label class="form-label fw-semibold small">Select Panels <span class="text-danger">*</span></label>
                        <div class="text-muted small mb-2">Assign available panels to this manager. (Multiple selection supported)</div>
                        
                        <select id="panel_ids" name="panel_ids[]" required class="d-none" multiple></select>
                        
                        <div id="panelSelector" class="form-control min-vh-auto d-flex flex-wrap gap-2 align-items-center p-2 cursor-pointer position-relative">
                            <div id="chosenPanelsContainer" class="d-flex flex-wrap gap-1 w-100">
                                <span class="text-muted small italic px-1" id="panelSelectorPlaceholder">Select panels...</span>
                            </div>

                            <div id="panelDropdownMenu" class="position-absolute start-0 end-0 top-100 mt-1 bg-white border rounded shadow-lg overflow-auto d-none z-3" style="max-height: 200px;">
                                <div class="sticky-top bg-white p-2 border-bottom">
                                    <input type="text" id="panelDropdownSearch" class="form-control form-control-sm" placeholder="Search unassigned panels...">
                                </div>
                                <div id="panelDropdownList" class="p-1">
                                    </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="save_btn" onclick="saveAllotment()">Assign Panels</button>
            </div>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
    .cursor-pointer { cursor: pointer; }
    #panelSelector { min-height: 45px; }
    .panel-option:hover { background-color: #f8f9fa; }
    .badge-panel { background-color: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe; }
</style>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
    const apiUrl = BASE_API_URL + "/site_allotment.php";
    const sessionUserId = "<?php echo $_user_id; ?>";
    
    let unassignedPanels = [];
    let selectedPanels = new Set();
    const allotmentModal = new bootstrap.Modal(document.getElementById('allotmentModal'));

    document.addEventListener("DOMContentLoaded", function () {
        loadManagers();
        loadPanels();
        loadMappings();

        // Custom Multi-Select Logic
        const selector = document.getElementById('panelSelector');
        const popup = document.getElementById('panelDropdownMenu');
        const search = document.getElementById('panelDropdownSearch');

        selector.addEventListener('click', (e) => {
            if(e.target.closest('.remove-pill')) return;
            popup.classList.toggle('d-none');
            if(!popup.classList.contains('d-none')) search.focus();
        });

        document.addEventListener('click', (e) => {
            if(!selector.contains(e.target)) popup.classList.add('d-none');
        });

        search.addEventListener('input', (e) => renderPanelDropdown(e.target.value));
        
        document.getElementById("filterSearch").addEventListener("keyup", function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll("#allotmentTableBody tr").forEach(row => {
               row.style.display = row.innerText.toLowerCase().includes(term) ? '' : 'none';
            });
        });
    });

    function loadManagers() {
        const fd = new FormData(); fd.append("get_active_managers", "1");
        fetch(apiUrl, { method: "POST", body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.Code === 200) {
                const select = document.getElementById('branch_manager_id');
                let options = '<option value="">Select a Manager...</option>';
                data.data.forEach(m => {
                    options += `<option value="${m.id}">${m.branch_name} (${m.branch_code})</option>`;
                });
                select.innerHTML = options;
            }
        });
    }

    function loadPanels() {
        const fd = new FormData(); fd.append("get_available_panels", "1");
        fetch(apiUrl, { method: "POST", body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.Code === 200) {
                unassignedPanels = data.data.map(p => p.panel_id);
                renderPanelDropdown();
            }
        });
    }

    function renderPanelDropdown(searchQuery = '') {
        const list = document.getElementById('panelDropdownList');
        let html = '';
        const filtered = unassignedPanels.filter(p => p.toLowerCase().includes(searchQuery.toLowerCase()));

        if(filtered.length === 0) {
            html = '<div class="small text-muted p-2 text-center">No panels found.</div>';
        } else {
            filtered.forEach(id => {
                if(!selectedPanels.has(id)) {
                    html += `<div class="p-2 small cursor-pointer panel-option rounded" onclick="addPanelPill('${id}', event)">
                                <i class="ph ph-monitor me-2 text-muted"></i>${id}
                             </div>`;
                }
            });
        }
        list.innerHTML = html;
    }

    function addPanelPill(id, e) {
        e.stopPropagation();
        selectedPanels.add(id);
        renderPills();
        renderPanelDropdown();
    }

    window.removePanelPill = function(id) {
        selectedPanels.delete(id);
        renderPills();
        renderPanelDropdown();
    }

    function renderPills() {
        const container = document.getElementById('chosenPanelsContainer');
        if(selectedPanels.size === 0) {
            container.innerHTML = '<span class="text-muted small italic px-1">Select panels...</span>';
            return;
        }

        let html = '';
        selectedPanels.forEach(id => {
            html += `<span class="badge badge-panel d-inline-flex align-items-center gap-1 p-2 rounded">
                        ${id} <i class="ph ph-x cursor-pointer remove-pill" onclick="removePanelPill('${id}')"></i>
                     </span>`;
        });
        container.innerHTML = html;
        document.getElementById('panel_ids').innerHTML = Array.from(selectedPanels).map(id => `<option value="${id}" selected>${id}</option>`).join('');
    }

    function loadMappings() {
        const container = document.getElementById("allotmentTableBody");
        container.innerHTML = '<tr><td colspan="5" class="p-5 text-center text-muted"><div class="spinner-border spinner-border-sm text-primary"></div></td></tr>';

        const fd = new FormData(); fd.append("get_all_mappings", "1");
        fetch(apiUrl, { method: "POST", body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.Code === 200 && data.data.length > 0) {
                let html = '';
                data.data.forEach((row, index) => {
                    let isAssigned = row.manager_id !== null;
                    html += `
                        <tr>
                            <td class="ps-4 text-muted small">${index + 1}</td>
                            <td><i class="ph ph-monitor me-1"></i>${row.panel_id}</td>
                            <td class="fw-semibold">${isAssigned ? row.branch_name : '<span class="text-muted small">Unassigned</span>'}</td>
                            <td class="text-muted font-monospace small">${isAssigned ? row.branch_code : '-'}</td>
                            <td class="text-end pe-4">
                                ${isAssigned ? `
                                    <button class="btn btn-outline-danger btn-sm border-0" onclick="deleteMapping(${row.mapping_id}, '${row.panel_id}')">
                                        <i class="ph ph-trash fs-5"></i>
                                    </button>` : `
                                    <button class="btn btn-link btn-sm text-success text-decoration-none fw-bold" onclick="inlineAssign('${row.panel_id}')">
                                        <i class="ph ph-plus-circle"></i> Assign
                                    </button>`}
                            </td>
                        </tr>`;
                });
                container.innerHTML = html;
            } else {
                container.innerHTML = '<tr><td colspan="5" class="p-5 text-center text-muted">No mappings found.</td></tr>';
            }
        });
    }

    function inlineAssign(panel_id) {
        let optionsHtml = document.getElementById('branch_manager_id').innerHTML;
        Swal.fire({
            title: 'Quick Assign',
            html: `<p class="small text-muted text-start mb-2">Assign Panel: <b>${panel_id}</b></p>
                   <select id="inline_manager_id" class="form-select form-select-sm">${optionsHtml}</select>`,
            showCancelButton: true,
            confirmButtonText: 'Assign',
            preConfirm: () => {
                const mid = document.getElementById('inline_manager_id').value;
                if (!mid) return Swal.showValidationMessage('Please select a manager');
                return mid;
            }
        }).then((result) => {
            if (result.isConfirmed) submitAssignment(result.value, [panel_id]);
        });
    }

    function openAllotmentModal() {
        selectedPanels.clear();
        renderPills();
        document.getElementById('allotmentForm').reset();
        allotmentModal.show();
    }

    function saveAllotment() {
        const mid = document.getElementById('branch_manager_id').value;
        if (!mid || selectedPanels.size === 0) return Swal.fire('Error', 'Manager and Panels are required', 'error');
        submitAssignment(mid, Array.from(selectedPanels));
    }

    function submitAssignment(mid, pids) {
        const fd = new FormData();
        fd.append("assign_panels", "1");
        fd.append("branch_manager_id", mid);
        fd.append("user_id", sessionUserId);
        fd.append("panel_ids", JSON.stringify(pids));

        fetch(apiUrl, { method: "POST", body: fd })
        .then(res => res.json())
        .then(data => {
            if (data.Code === 200 || data.Code === 206) {
                allotmentModal.hide();
                Swal.fire({ icon: 'success', title: 'Action Successful', timer: 1500, toast: true, position: 'top-end', showConfirmButton: false });
                loadMappings(); loadPanels();
            } else {
                Swal.fire('Error', data.msg, 'error');
            }
        });
    }

    function deleteMapping(id, pid) {
        Swal.fire({
            title: 'Unassign Panel?',
            text: `Remove ${pid} from this manager?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData(); fd.append('mapping_id', id); fd.append('delete_mapping', '1');
                fetch(apiUrl, { method: 'POST', body: fd })
                .then(res => res.json()).then(data => {
                    if (data.Code === 200) { loadMappings(); loadPanels(); }
                });
            }
        });
    }
</script>

<?php include('./footer.php'); ?>