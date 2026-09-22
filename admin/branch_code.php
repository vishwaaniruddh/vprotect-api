<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/@phosphor-icons/web"></script>

<div class="container-fluid py-4 bg-light min-vh-100">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="h4 mb-0 fw-bold text-dark">Branch Management</h2>
            <p class="text-muted mb-0">Manage branch codes, names, and office addresses.</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <button type="button" class="btn btn-indigo d-inline-flex align-items-center shadow-sm text-white" style="background-color: #4f46e5;" onclick="openBranchModal()">
                <i class="ph ph-plus-circle me-2 fs-5"></i> Add Branch
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="table-responsive">
            <div id="branchTableContainer">
                <div class="p-5 text-center text-muted">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <p>Loading branches...</p>
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
                    <i class="ph ph-storefront me-2"></i>Add Branch Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="branchForm">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="E.g. Downtown Office" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="branch_code" name="branch_code" placeholder="E.g. NYC-001" required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3" placeholder="Enter full address..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-top-0">
                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary px-4" id="save_btn">Save Branch</button>
                <button type="button" class="btn btn-success px-4" id="update_btn" style="display:none;">Update Branch</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
    const apiUrl = BASE_API_URL + "/branch_code.php";
    const sessionUserId = "<?php echo $_user_id; ?>";
    
    let currentPage = 1;
    const limitPerPage = 10;
    
    // Initialize Bootstrap Modal
    const bModal = new bootstrap.Modal(document.getElementById('branchModal'));

    document.addEventListener("DOMContentLoaded", function () {
        loadBranches(currentPage);
        document.getElementById("save_btn").addEventListener("click", saveBranch);
        document.getElementById("update_btn").addEventListener("click", updateBranch);
    });

    function openBranchModal() {
        clearForm();
        bModal.show();
    }

    function closeBranchModal() {
        bModal.hide();
    }

    function loadBranches(page) {
        const container = document.getElementById("branchTableContainer");
        const paginationControls = document.getElementById("paginationControls");

        container.innerHTML = '<div class="p-5 text-center"><div class="spinner-border text-primary mb-2"></div><p>Loading...</p></div>';

        const formData = new FormData();
        formData.append("get_all_branch", "1");
        formData.append("page", page);
        formData.append("limit", limitPerPage);

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data?.Code === 200 && data.data?.length > 0) {
                let table = `
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Sr No</th>
                                <th>Branch Code</th>
                                <th>Branch Name</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                let sr = (page - 1) * limitPerPage + 1;

                data.data.forEach(row => {
                    let statusBadge = row.status == 1 
                        ? '<span class="badge bg-success-subtle text-success border border-success-subtle px-2">Active</span>' 
                        : '<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2">Inactive</span>';
                    
                    const safeName = (row.branch_name || '').replace(/'/g, "\\'");
                    const safeCode = (row.branch_code || '').replace(/'/g, "\\'");
                    const safeAddress = (row.address || '').replace(/'/g, "\\'").replace(/\n/g, " ");

                    table += `
                    <tr>
                        <td class="ps-4 text-muted small">${sr++}</td>
                        <td><span class="badge bg-indigo-subtle text-indigo border border-indigo-subtle font-monospace">${row.branch_code ?? 'N/A'}</span></td>
                        <td class="fw-semibold text-dark">${row.branch_name ?? ''}</td>
                        <td class="text-muted small text-truncate" style="max-width: 200px;">${row.address ?? ''}</td>
                        <td>${statusBadge}</td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary border-0" onclick="openEditModal('${row.id}', '${safeName}', '${safeCode}', '${safeAddress}')" title="Edit">
                                    <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn btn-outline-danger border-0" onclick="deleteBranch('${row.id}')" title="Delete">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>`;
                });

                table += `</tbody></table>`;
                container.innerHTML = table;
                renderPagination(data.pagination);
            } else {
                container.innerHTML = `<div class="p-5 text-center text-muted"><i class="ph ph-warning-circle fs-2 text-warning mb-2"></i><p>No branches found.</p></div>`;
                paginationControls.innerHTML = '';
            }
        })
        .catch(() => {
            container.innerHTML = `<div class="alert alert-danger m-3">Failed to load data.</div>`;
        });
    }

    function renderPagination(pagination) {
        const controls = document.getElementById("paginationControls");
        if (!pagination || pagination.total_pages <= 1) {
            controls.innerHTML = "";
            return;
        }

        const currPage = parseInt(pagination.current_page);
        const totalPages = parseInt(pagination.total_pages);
        
        let html = `<div class="small text-muted">Showing <b>${(currPage - 1) * limitPerPage + 1}</b> to <b>${Math.min(currPage * limitPerPage, pagination.total_records)}</b> of ${pagination.total_records}</div>`;
        
        html += `<nav><ul class="pagination pagination-sm mb-0">`;
        html += `<li class="page-item ${currPage === 1 ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadBranches(${currPage - 1})"><i class="ph ph-caret-left"></i></a></li>`;

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currPage - 1 && i <= currPage + 1)) {
                html += `<li class="page-item ${currPage === i ? 'active' : ''}"><a class="page-link" href="#" onclick="loadBranches(${i})">${i}</a></li>`;
            } else if (i === currPage - 2 || i === currPage + 2) {
                html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }
        }

        html += `<li class="page-item ${currPage === totalPages ? 'disabled' : ''}"><a class="page-link" href="#" onclick="loadBranches(${currPage + 1})"><i class="ph ph-caret-right"></i></a></li>`;
        html += `</ul></nav>`;
        
        controls.innerHTML = html;
        currentPage = currPage;
    }

    function saveBranch() {
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
            btn.disabled = false; btn.innerHTML = 'Save Branch';
            if (data.Code == 200) {
                bModal.hide();
                Swal.fire({ icon: 'success', title: 'Saved!', text: data.msg, timer: 1500, toast: true, position: 'top-end', showConfirmButton: false });
                loadBranches(1);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.msg });
            }
        });
    }

    function openEditModal(id, name, code, address) {
        clearForm();
        document.getElementById("modalTitle").innerHTML = '<i class="ph ph-pencil-simple me-2"></i>Edit Branch Details';
        document.getElementById("edit_id").value = id;
        document.getElementById("branch_name").value = name;
        document.getElementById("branch_code").value = code;
        document.getElementById("address").value = address;

        document.getElementById("save_btn").style.display = 'none';
        document.getElementById("update_btn").style.display = 'block';
        bModal.show();
    }

    function updateBranch() {
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
                Swal.fire({ icon: 'success', title: 'Updated!', timer: 1500, toast: true, position: 'top-end', showConfirmButton: false });
                loadBranches(currentPage);
            }
        });
    }

    function deleteBranch(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const fd = new FormData();
                fd.append('id', id); fd.append('delete', '1');
                fetch(apiUrl, { method: 'POST', body: fd })
                .then(res => res.json())
                .then(data => {
                    if (data.Code === 200) {
                        loadBranches(currentPage);
                        Swal.fire('Deleted!', '', 'success');
                    }
                });
            }
        });
    }

    function clearForm() {
        document.getElementById("branchForm").reset();
        document.getElementById("edit_id").value = '';
        document.getElementById("modalTitle").innerHTML = '<i class="ph ph-storefront me-2"></i>Add Branch Details';
        document.getElementById("save_btn").style.display = 'block';
        document.getElementById("update_btn").style.display = 'none';
    }
</script>

<style>
    .btn-indigo:hover { background-color: #4338ca !important; }
    .badge.bg-indigo-subtle { background-color: #e0e7ff; color: #4338ca; }
</style>

<?php include('./footer.php'); ?>