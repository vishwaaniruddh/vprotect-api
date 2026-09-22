<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function renderHeader($text, $level = 1, $classes = '') {
    $baseClasses = 'font-semibold text-slate-800 dark:text-white tracking-tight ';
    $sizes = [
        1 => 'text-3xl mb-4',
        2 => 'text-2xl mb-3',
        3 => 'text-xl mb-2',
        4 => 'text-lg mb-2',
        5 => 'text-base mb-1',
        6 => 'text-sm mb-1',
    ];
    $sizeClass = $sizes[$level] ?? $sizes[1];
    return "<h{$level} class=\"{$baseClasses}{$sizeClass} {$classes}\">{$text}</h{$level}>";
}

function renderParagraph($text, $classes = '') {
    return "<p class=\"text-sm text-slate-600 dark:text-slate-300 leading-relaxed mb-4 {$classes}\">{$text}</p>";
}

function renderLabel($text, $for = '', $classes = '') {
    $forAttr = $for ? "for=\"{$for}\"" : '';
    return "<label {$forAttr} class=\"block text-sm font-medium text-slate-700 dark:text-slate-200 mb-1 {$classes}\">{$text}</label>";
}

?>


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="h4 mb-0 fw-bold text-dark">Branch Managers</h2>
            <p class="text-muted mb-0">Manage branch managers and their site associations.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-primary d-inline-flex align-items-center shadow-sm" onclick="openBranchManagerModal()">
                <i class="ph ph-plus-circle me-2 fs-5"></i> Add Branch Manager
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="ph ph-magnifying-glass"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0" id="filterSearch" placeholder="Search managers...">
                    </div>
                </div>
                <div class="col-md-8 d-flex justify-content-md-end align-items-center gap-2">
                    <label class="text-muted small text-nowrap">Show entries:</label>
                    <select class="form-select form-select-sm w-auto" id="entriesPerPage" onchange="changeLimit(this.value)">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div class="table-responsive">
            <div id="branchTableContainer">
                <div class="p-5 text-center text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p>Loading managers...</p>
                </div>
            </div>
        </div>
        
        <div class="card-footer bg-white py-3 border-top-0">
            <div id="paginationControls" class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="branchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="modalTitle">
                    <i class="ph ph-user me-2"></i>Add Branch Manager
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="branchForm">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch Code <span class="text-danger">*</span></label>
                        <select class="form-select" id="branch_code" name="branch_code" required>
                            <option value="">Select a Branch...</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Manager Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="Full Name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="email@example.com" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Contact No <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="contact" name="contact" placeholder="10 Digit Number" maxlength="10" 
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="save_btn">Save Manager</button>
                <button type="button" class="btn btn-success px-4" id="update_btn" style="display:none;">Update Manager</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
    const apiUrl = BASE_API_URL + "/branch_manager.php";
    const sessionUserId = "<?php echo $_user_id; ?>";
    
    let currentPage = 1;
    let limitPerPage = 10;
    let searchQuery = '';
    
    // Initialize Bootstrap Modal
    const bModal = new bootstrap.Modal(document.getElementById('branchModal'));

    document.addEventListener("DOMContentLoaded", function () {
        loadBranchOptions();
        loadManagers(currentPage);

        document.getElementById("save_btn").addEventListener("click", saveManager);
        document.getElementById("update_btn").addEventListener("click", updateManager);

        let searchTimeout;
        document.getElementById("filterSearch").addEventListener("keyup", function() {
            clearTimeout(searchTimeout);
            searchQuery = this.value;
            searchTimeout = setTimeout(() => loadManagers(1), 500);
        });
    });

    function changeLimit(limit) {
        limitPerPage = parseInt(limit);
        loadManagers(1);
    }

    function openBranchManagerModal() {
        clearForm();
        bModal.show();
    }

    function closeBranchManagerModal() {
        bModal.hide();
    }

    function loadBranchOptions() {
        const formData = new FormData();
        formData.append("get_active_branch_codes", "1");

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data?.Code === 200 && data.data) {
                const select = document.getElementById('branch_code');
                let options = '<option value="">Select a Branch...</option>';
                data.data.forEach(branch => {
                    options += `<option value="${branch.branch_code}">${branch.branch_code} - ${branch.branch_name}</option>`;
                });
                select.innerHTML = options;
            }
        });
    }

    function loadManagers(page) {
        const container = document.getElementById("branchTableContainer");
        const paginationControls = document.getElementById("paginationControls");

        container.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary mb-2"></div><p>Loading...</p></div>';

        const formData = new FormData();
        formData.append("get_all_branch", "1");
        formData.append("start", (page - 1) * limitPerPage);
        formData.append("length", limitPerPage);
        if (searchQuery) formData.append("search[value]", searchQuery);

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data?.Code === 200 && data.data?.length > 0) {
                let table = `
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Branch Code</th>
                                <th>Manager Name</th>
                                <th>Contact Details</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                data.data.forEach(row => {
                    table += `
                    <tr>
                        <td class="ps-4 text-muted small">${row.sr_no}</td>
                        <td>${row.branch_code || 'N/A'}</td>
                        <td class="fw-semibold text-dark">${row.branch_name ?? ''}</td>
                        <td>
                            <div class="small"><i class="ph ph-envelope text-muted me-1"></i>${row.email}</div>
                            <div class="small"><i class="ph ph-phone text-muted me-1"></i>${row.contact}</div>
                        </td>
                        <td>${row.status}</td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary border-0" onclick="openEditModal('${row.id}', '${row.branch_name}', '${row.branch_code_raw}', '${row.email}', '${row.contact}')">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn btn-outline-danger border-0" onclick="deleteManager('${row.id}')">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });

                table += `</tbody></table>`;
                container.innerHTML = table;
                renderPagination(page, data.recordsFiltered);
            } else {
                container.innerHTML = `<div class="p-5 text-center text-muted"><i class="ph ph-warning-circle fs-2 text-warning mb-2"></i><p>No records found.</p></div>`;
                paginationControls.innerHTML = "";
            }
        });
    }

    function renderPagination(currPage, totalRecords) {
        const controls = document.getElementById("paginationControls");
        const totalPages = Math.ceil(totalRecords / limitPerPage);
        
        let html = `<div class="small text-muted">Showing <b>${(currPage - 1) * limitPerPage + 1}</b> to <b>${Math.min(currPage * limitPerPage, totalRecords)}</b> of ${totalRecords}</div>`;
        
        html += `<nav><ul class="pagination pagination-sm mb-0">`;
        html += `<li class="page-item ${currPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadManagers(${currPage - 1})"><i class="ph ph-caret-left"></i></a></li>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currPage - 1 && i <= currPage + 1)) {
                html += `<li class="page-item ${currPage === i ? 'active' : ''}"><a class="page-link" href="#" onclick="loadManagers(${i})">${i}</a></li>`;
            } else if (i === currPage - 2 || i === currPage + 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += `<li class="page-item ${currPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadManagers(${currPage + 1})"><i class="ph ph-caret-right"></i></a></li>`;
        html += `</ul></nav>`;
        
        controls.innerHTML = html;
        currentPage = currPage;
    }

    function saveManager() {
        const form = document.getElementById("branchForm");
        if(!form.checkValidity()) { form.reportValidity(); return; }

        const btn = document.getElementById("save_btn");
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';

        const formData = new FormData(form);
        formData.append("user_id", sessionUserId);
        formData.append("save_branch", "1");

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false; btn.innerHTML = 'Save Manager';
            if (data.Code == 200) {
                bModal.hide();
                Swal.fire({ icon: 'success', title: 'Saved!', text: data.msg, timer: 1500 });
                loadManagers(1);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.msg });
            }
        });
    }

    function openEditModal(id, name, code, email, contact) {
        clearForm();
        document.getElementById("modalTitle").innerHTML = '<i class="ph ph-pencil-simple me-2"></i>Edit Branch Manager';
        document.getElementById("edit_id").value = id;
        document.getElementById("branch_name").value = name;
        document.getElementById("branch_code").value = code;
        document.getElementById("email").value = email;
        document.getElementById("contact").value = contact;

        document.getElementById("save_btn").style.display = 'none';
        document.getElementById("update_btn").style.display = 'block';
        bModal.show();
    }

    function updateManager() {
        const btn = document.getElementById("update_btn");
        const formData = new FormData(document.getElementById("branchForm"));
        formData.append("user_id", sessionUserId);
        formData.append("update_branch", "1");

        btn.disabled = true;
        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.Code == 200) {
                bModal.hide();
                Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500 });
                loadManagers(currentPage);
            }
        });
    }

    function deleteManager(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0d6efd',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id', id); fd.append('delete', '1');
                fetch(apiUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.Code === 200) {
                        loadManagers(currentPage);
                        Swal.fire('Deleted!', '', 'success');
                    }
                });
            }
        });
    }

    function clearForm() {
        document.getElementById("branchForm").reset();
        document.getElementById("edit_id").value = '';
        document.getElementById("modalTitle").innerHTML = '<i class="ph ph-user me-2"></i>Add Branch Manager';
        document.getElementById("save_btn").style.display = 'block';
        document.getElementById("update_btn").style.display = 'none';
    }
</script>

<?php include('./footer.php'); ?>