<?php include('./header.php');

$_user_id = $_SESSION['user_id'];
?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="mb-0">Branch Managers</h5>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mt-4">
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0 text-primary"><i class="ph ph-identification-card me-2"></i>Branch Managers</h4>
      <button type="button" class="btn btn-primary btn-sm" id="branch_add_btn" data-bs-toggle="modal" data-bs-target="#managerModal">
        <i class="ph ph-plus-circle me-1"></i>Add Manager
      </button>
    </div>
    
    <div class="card-body p-0">
      <div class="table-responsive p-3">
        <table id="managersTable" class="table table-hover align-middle mb-0 w-100">
            <thead class="table-light">
                <tr>
                    <th>Sr No</th>
                    <th>Manager Name</th>
                    <th>Assigned Branch</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
        </table>
      </div>
    </div>
  </div>
</div>


<!-- Manager Modal -->
<div class="modal fade" id="managerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalTitle"><i class="ph ph-user me-2"></i>Manager Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="managerForm">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Manager Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="branch_name" name="branch_name" placeholder="E.g. John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Assign Branch Code <span class="text-danger">*</span></label>
                        <select class="form-select" id="branch_code" name="branch_code" required>
                            <option value="" disabled selected>Select an active branch...</option>
                            <!-- Options rendered dynamically -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="E.g. john@frutopia.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Contact <span class="text-danger">*</span></label>
                        <input type="tel" maxlength="10" class="form-control" id="contact" name="contact" placeholder="10 Digit Number" required>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save_btn">Save Manager</button>
                <button type="button" class="btn btn-success" id="update_btn" style="display:none;">Update Manager</button>
            </div>
            
        </div>
    </div>
</div>

<!-- SweetAlert2 CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables CSS & JS (Requires jQuery) -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<?php include('./footer.php'); ?>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
    const apiUrl = BASE_API_URL + "/branch_manager.php";
    const sessionUserId = "<?php echo $_user_id; ?>";
    
    let currentPage = 1;
    const limitPerPage = 10;
    
    let managerModalInstance;

    document.addEventListener("DOMContentLoaded", function () {
        managerModalInstance = new bootstrap.Modal(document.getElementById('managerModal'));
        
        // Fetch valid branch codes for dropdown immediately
        fetchActiveBranches();

        // Reset modal on hide
        document.getElementById('managerModal').addEventListener('hidden.bs.modal', function () {
            clearForm();
        });

        initDataTable();

        document.getElementById("save_btn").addEventListener("click", saveManager);
        document.getElementById("update_btn").addEventListener("click", updateManager);
    });

    let dataTable;

    function initDataTable() {
        if(dataTable) { dataTable.destroy(); }
        dataTable = $('#managersTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": apiUrl,
                "type": "POST",
                "data": function(d) {
                    d.get_all_branch = "1";
                }
            },
            "columns": [
                { "data": "sr_no" },
                { "data": "branch_name" },
                { "data": "branch_code" },
                { "data": "email" },
                { "data": "contact" },
                { "data": "status" },
                { "data": "action", "className": "text-end", "orderable": false }
            ],
            "pageLength": 10,
            "lengthMenu": [5, 10, 25, 50],
            "language": {
                "paginate": {
                    "previous": "Prev",
                    "next": "Next"
                }
            }
        });
    }

    function fetchActiveBranches() {
        const formData = new FormData();
        formData.append("get_active_branch_codes", "1");

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if(data && data.Code === 200 && data.data) {
                const selectElement = document.getElementById("branch_code");
                // keep the first disabled selected option
                let html = '<option value="" disabled selected>Select an active branch...</option>';
                data.data.forEach(branch => {
                    html += `<option value="${branch.branch_code}">${branch.branch_name} (${branch.branch_code})</option>`;
                });
                selectElement.innerHTML = html;
            }
        }).catch(err => console.error("Error fetching branches for dropdown:", err));
    }

    /* ---------- VALIDATION HELPER ---------- */
    function validateInput(name, email, contact, branch_code) {
        if (!name || !email || !contact || !branch_code) {
            Swal.fire({ icon: 'warning', title: 'Missing Data', text: "All fields are required!"});
            return false;
        }
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) {
            Swal.fire({ icon: 'warning', title: 'Invalid Email', text: "Please enter a valid email format."});
            return false;
        }
        const contactPattern = /^[0-9]{10}$/;
        if (!contactPattern.test(contact)) {
            Swal.fire({ icon: 'warning', title: 'Invalid Contact', text: "Contact must be 10 digits exactly."});
            return false;
        }
        return true;
    }

    /* ---------- SAVE ROW ---------- */
    function saveManager() {
        const form = document.getElementById("managerForm");
        const bName = document.getElementById("branch_name").value.trim();
        const bCode = document.getElementById("branch_code").value;
        const bEmail = document.getElementById("email").value.trim();
        const bContact = document.getElementById("contact").value.trim();

        if(!validateInput(bName, bEmail, bContact, bCode)) return;

        const btn = document.getElementById("save_btn");
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        const formData = new FormData(form);
        formData.append("user_id", sessionUserId);
        formData.append("save_branch", "1");

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Save Manager';
            
            if (data.Code == 200) {
                managerModalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.msg,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                currentPage = 1; // reset to 1 on new entry
                if(dataTable) dataTable.ajax.reload(null, false);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.msg || "Save Failed!" });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Save Manager';
            Swal.fire({ icon: 'error', title: 'Error', text: "An unexpected error occurred." });
            console.error(err);
        });
    }

    /* ---------- OPEN EDIT MODE ---------- */
    function openEditModal(id, managerName, branchCode, email, contact) {
        document.getElementById("modalTitle").innerHTML = '<i class="ph ph-pencil-simple me-2"></i>Edit Manager Details';
        document.getElementById("edit_id").value = id;
        document.getElementById("branch_name").value = managerName;
        document.getElementById("branch_code").value = branchCode;
        document.getElementById("email").value = email;
        document.getElementById("contact").value = contact;

        document.getElementById("save_btn").style.display = 'none';
        document.getElementById("update_btn").style.display = 'block';

        managerModalInstance.show();
    }

    /* ---------- UPDATE ROW ---------- */
    function updateManager() {
        const form = document.getElementById("managerForm");
        const bName = document.getElementById("branch_name").value.trim();
        const bCode = document.getElementById("branch_code").value;
        const bEmail = document.getElementById("email").value.trim();
        const bContact = document.getElementById("contact").value.trim();

        if(!validateInput(bName, bEmail, bContact, bCode)) return;

        const btn = document.getElementById("update_btn");
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...';

        const formData = new FormData(form);
        formData.append("user_id", sessionUserId);
        formData.append("update_branch", "1");

        fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Update Manager';
            
            if (data.Code == 200) {
                managerModalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: data.msg,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                if(dataTable) dataTable.ajax.reload(null, false);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.msg || 'Update Failed!' });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = 'Update Manager';
            Swal.fire({ icon: 'error', title: 'Error', text: "An unexpected error occurred." });
            console.error(err);
        });
    }

    /* ---------- DELETE ROW ---------- */
    function deleteManager(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This manager will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('id', id);
                formData.append('delete', '1');

                fetch(apiUrl, { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.Code === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Manager has been deleted.',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                        // Automatically fetch without the deleted record
                        if(dataTable) dataTable.ajax.reload(null, false);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: data.message || 'Delete Failed!' });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: 'Error', text: 'An unexpected error occurred.' });
                });
            }
        });
    }

    function clearForm() {
        document.getElementById("modalTitle").innerHTML = '<i class="ph ph-user me-2"></i>Manager Details';
        document.getElementById("managerForm").reset();
        document.getElementById("edit_id").value = '';
        
        document.getElementById("save_btn").style.display = 'block';
        document.getElementById("update_btn").style.display = 'none';
        
        // Reset states just in case
        document.getElementById("save_btn").disabled = false;
        document.getElementById("update_btn").disabled = false;
    }
</script>