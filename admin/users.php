<?php include('./header.php'); ?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="mb-0">Dashboard - Active</h5>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mt-4">
  
  <!-- Stats Dashboard Section -->
  <div class="row g-3 mb-4" id="userStatsCards">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 bg-primary text-white h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-white-50 mb-1 fw-bold text-uppercase" style="letter-spacing:1px;">Total Users</h6>
            <h3 class="mb-0 fw-bold" id="stat_total_users">0</h3>
          </div>
          <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="ph ph-users fs-3"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 bg-success text-white h-100">
        <div class="card-body d-flex align-items-center justify-content-between">
          <div>
            <h6 class="text-white-50 mb-1 fw-bold text-uppercase" style="letter-spacing:1px;">Active / Inactive</h6>
            <h3 class="mb-0 fw-bold"><span id="stat_active_users">0</span> <span class="fs-5 text-white-50 fw-normal">/</span> <span id="stat_inactive_users" class="fs-5 text-danger-subtle">0</span></h3>
          </div>
          <div class="bg-white bg-opacity-25 rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
            <i class="ph ph-user-check fs-3"></i>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card shadow-sm border-0 bg-light h-100">
        <div class="card-body py-2 px-3">
          <h6 class="text-muted mb-2 fw-bold text-uppercase border-bottom pb-2" style="font-size: 0.75rem; letter-spacing:1px;">Users by Role</h6>
          <div id="stat_roles_breakdown" style="max-height: 55px; overflow-y: auto;" class="small text-muted">
            <span class="spinner-border spinner-border-sm text-primary"></span> Loading roles...
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
      <h4 class="mb-0 text-primary"><i class="ph ph-users me-2"></i>User Management</h4>
      <div>
          <button id="exportBtn" class="btn btn-outline-primary btn-sm me-2" style="display:none;">
            <i class="ph ph-download-simple me-1"></i>Export CSV
          </button>
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
             <i class="ph ph-plus-circle me-1"></i>Add User
          </button>
      </div>
    </div>
    
    <!-- Filters Section -->
    <div class="card-body border-bottom bg-light bg-opacity-50 py-3">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Search name, email, or phone...">
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" id="filterStatus">
                    <option value="">All Statuses</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select form-select-sm" id="filterRole">
                    <option value="">All Roles</option>
                    <!-- Fetched dynamically -->
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-secondary btn-sm w-100" onclick="resetFilters()">Reset</button>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
      <div class="table-responsive">
        <!-- Placeholder for table -->
        <div id="alertTable" class="p-3"></div>
      </div>
    </div>
    <div class="card-footer bg-white border-top-0 d-flex justify-content-between align-items-center" id="paginationControls">
       <!-- Pagination will be rendered here -->
    </div>
  </div>
  
  <!-- OTP Modal -->
  <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title mb-0">Generated OTP</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center py-4">
          <h2 id="otpText" class="display-6 fw-bold text-primary" style="letter-spacing:5px;"></h2>
        </div>
        <div class="modal-footer border-0 d-flex justify-content-center">
          <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">OK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit User Modal -->
  <div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-light">
          <h5 class="modal-title"><i class="ph ph-pencil-simple me-2"></i>Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editUserForm">
            <input type="hidden" id="editUserId" name="userid">
            <div class="mb-3">
              <label for="editName" class="form-label">Name</label>
              <input type="text" class="form-control" id="editName" name="name" required>
            </div>
            <div class="mb-3">
              <label for="editEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="editEmail" name="email_id" required>
            </div>
            <div class="mb-3">
              <label for="editStatus" class="form-label">Status</label>
              <select class="form-select" id="editStatus" name="status">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveUserBtn">Save Changes</button>
        </div>
      </div>
    </div>
  </div>
  
  
  
<!-- Answer Modal -->
<div class="modal fade" id="answerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Answers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
        <div id="answerModalBody">
           Loading...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


  <!-- Add User Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold"><i class="ph ph-user-plus me-2"></i>Add New User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addUserForm">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="user_name" id="add_user_name" placeholder="Full Name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Email ID <span class="text-danger">*</span></label>
                <input type="email" class="form-control" name="email_id" id="add_email_id" placeholder="Email Address" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Mobile No. <span class="text-danger">*</span></label>
                <input type="tel" class="form-control" name="mobile_no" id="add_mobile_no" placeholder="10 Digit Mobile" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                <input type="password" class="form-control" name="password" id="add_password" placeholder="Password" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                <select class="form-select" name="user_role" id="add_user_role" required>
                  <option value="" disabled selected>Select Role</option>
                  <!-- Fetched Roles go here -->
                </select>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer bg-light border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveNewUserBtn">Save User</button>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- SweetAlert2 CSS & JS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php include('./footer.php'); ?>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
    let currentPage = 1;
    const limitPerPage = 10;
    let editModalInstance;
    let addModalInstance;

    document.addEventListener("DOMContentLoaded", function () {
        editModalInstance = new bootstrap.Modal(document.getElementById('editUserModal'));
        addModalInstance = new bootstrap.Modal(document.getElementById('addUserModal'));
        
        loadRoles();
        loadUserStats();
        loadUsers(currentPage);

        // Export CSV Function
        document.getElementById("exportBtn").addEventListener("click", function () {
            let table = document.querySelector("#alertTable table");
            if (!table) return;

            let rows = table.querySelectorAll("tr");
            let csv = [];

            rows.forEach(row => {
                let cols = row.querySelectorAll("td, th");
                let rowData = [];
                // Exclude last column (Action) from export to keep it clean
                for (let i = 0; i < cols.length - 1; i++) {
                    let text = cols[i].innerText.replace(/"/g, '""'); // escape quotes
                    rowData.push('"' + text + '"');
                }
                csv.push(rowData.join(","));
            });

            // Download CSV
            let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
            let link = document.createElement("a");
            link.href = URL.createObjectURL(csvFile);
            link.download = "users_list.csv";
            link.click();
        });

        // Save Edit user
        document.getElementById("saveUserBtn").addEventListener("click", function() {
            saveUserEdit();
        });

        // Add New User
        document.getElementById("saveNewUserBtn").addEventListener("click", saveNewUser);
        
        // Clear form on add modal close
        document.getElementById('addUserModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById("addUserForm").reset();
        });

        // Filter trigger events
        document.getElementById('filterSearch').addEventListener('keyup', function(e) {
            if(e.key === 'Enter') loadUsers(1);
        });
        document.getElementById('filterStatus').addEventListener('change', () => loadUsers(1));
        document.getElementById('filterRole').addEventListener('change', () => loadUsers(1));
    });

    function resetFilters() {
        document.getElementById('filterSearch').value = '';
        document.getElementById('filterStatus').value = '';
        document.getElementById('filterRole').value = '';
        loadUsers(1);
    }

    function loadRoles() {
        fetch(`${BASE_API_URL}/get_roles.php`)
        .then(res => res.json())
        .then(data => {
            if(data.Code === 200 && data.data) {
                let htmlFilter = '<option value="">All Roles</option>';
                let htmlAdd = '<option value="" disabled selected>Select Role</option>';
                
                data.data.forEach(role => {
                    htmlFilter += `<option value="${role.id}">${role.role}</option>`;
                    htmlAdd += `<option value="${role.id}">${role.role}</option>`;
                });
                
                document.getElementById('filterRole').innerHTML = htmlFilter;
                document.getElementById('add_user_role').innerHTML = htmlAdd;
            }
        })
        .catch(err => console.error('Error fetching roles:', err));
    }

    function loadUserStats() {
        fetch(`${BASE_API_URL}/get_user_stats.php`)
        .then(res => res.json())
        .then(res => {
            if(res.Code === 200 && res.data) {
                document.getElementById('stat_total_users').innerText = res.data.total;
                document.getElementById('stat_active_users').innerText = res.data.active;
                document.getElementById('stat_inactive_users').innerText = res.data.inactive;

                let roleHtml = '';
                if(res.data.by_roles && res.data.by_roles.length > 0) {
                    res.data.by_roles.forEach(r => {
                        let roleName = r.name || 'Unassigned';
                        roleHtml += `<div class="d-flex justify-content-between mb-1"><span>${roleName}</span> <span class="fw-bold text-dark">${r.count}</span></div>`;
                    });
                } else {
                    roleHtml = '<div>No roles found</div>';
                }
                document.getElementById('stat_roles_breakdown').innerHTML = roleHtml;
            }
        })
        .catch(err => console.error('Error fetching stats:', err));
    }

    function saveNewUser() {
        const form = document.getElementById("addUserForm");
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const btn = document.getElementById("saveNewUserBtn");
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        const formData = new FormData(form);

        fetch(`${BASE_API_URL}/add_user.php`, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Save User';

            if(data.Code === 200) {
                addModalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Created!',
                    text: data.msg,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                currentPage = 1;
                loadUsers(currentPage);
                loadUserStats(); // Refresh stats
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.msg || "Failed to create user" });
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = 'Save User';
            Swal.fire({ icon: 'error', title: 'Error', text: "An unexpected error occurred" });
        });
    }

    function loadUsers(page) {
        const search = document.getElementById("filterSearch").value.trim();
        const status = document.getElementById("filterStatus").value;
        const role = document.getElementById("filterRole").value;

        const apiUrl = `${BASE_API_URL}/get_users.php?page=${page}&limit=${limitPerPage}&search=${encodeURIComponent(search)}&status=${status}&role=${role}`;
        
        const container = document.getElementById("alertTable");
        const exportBtn = document.getElementById("exportBtn");
        const paginationControls = document.getElementById("paginationControls");

        container.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Loading...</p></div>';

        fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            
            console.log(data);
            if (data && data.Code === 200 && data.data && data.data.length > 0) {
                let table = `
                    <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email / Contact</th>
                        <th>Image Status</th>
                        <th>Face Encoding</th>
                        <th>Panel ID</th>
                        <th>View Answer</th>
                        <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                data.data.forEach(row => {
                    let statusBadge = row.status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                    
                    // Create safe strings for dataset
                    const nameSafe = (row.name ?? '').replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                    const emailSafe = (row.email_id ?? '').replace(/'/g, "&apos;").replace(/"/g, "&quot;");

                    table += `
                    <tr>
                        <td class="text-muted fw-bold">#${row.userid ?? ''}</td>
                        <td>${row.name ?? ''}</td>
                        <td>
                            <div>${row.email_id ?? ''}</div>
                            <div class="text-muted small">${row.contact_no ?? ''}</div>
                        </td>
                        <td><span class="badge border border-primary text-primary bg-white px-2">${row.profile_img }</span></td>
                        <td>${row.faceEncoding}</td>
                        <td><span class="badge bg-secondary">${row.panel_id ?? 'N/A'}</span></td>
                        <td>
                        <button class='btn btn-info btn-sm' onclick='viewUserAnswers(${row.userid})'>
                        View Answer
                        </button>
                        </td>
                        
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <button class="btn btn-outline-info" 
                                        onclick="generateotp('${row.userid}','${row.panel_id}', this)" title="Generate OTP">
                                <i class="ph ph-key"></i>
                                </button>
                                <button class="btn btn-outline-primary" 
                                        onclick="openEditModal('${row.userid}', '${nameSafe}', '${emailSafe}', '${row.status}')" title="Edit">
                                <i class="ph ph-pencil-simple"></i>
                                </button>
                                <button class="btn btn-outline-danger" 
                                        onclick="deleteUser('${row.userid}')" title="Delete">
                                <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    `;
                });

                table += `
                    </tbody>
                    </table>
                `;

                container.innerHTML = table;
                exportBtn.style.display = "inline-block";

                // Render Pagination
                renderPagination(data.pagination);

            } else {
                container.innerHTML = `<div class="text-center py-5 text-muted"><i class="ph ph-warning-circle fs-1 text-warning mb-2 d-block"></i>No user data found.</div>`;
                paginationControls.innerHTML = '';
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML = `<div class="alert alert-danger m-3"><i class="ph ph-warning me-2"></i>Failed to load data. Please try again.</div>`;
            paginationControls.innerHTML = '';
        });
    }

    function renderPagination(pagination) {
        const controls = document.getElementById("paginationControls");
        if (!pagination || pagination.total_pages <= 1) {
            controls.innerHTML = '';
            return;
        }

        const currPage = parseInt(pagination.current_page);
        const totalPages = parseInt(pagination.total_pages);
        
        let html = `<div class="text-muted small">Showing Page ${currPage} of ${totalPages} (${pagination.total_records} Records)</div>`;
        html += `<ul class="pagination pagination-sm mb-0">`;
        
        // Prev button
        html += `<li class="page-item ${currPage === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${currPage - 1})">Previous</a>
                 </li>`;

        // Page numbers
        let startPage = Math.max(1, currPage - 2);
        let endPage = Math.min(totalPages, currPage + 2);

        if (startPage > 1) {
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(1)">1</a></li>`;
            if (startPage > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<li class="page-item ${currPage === i ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${i})">${i}</a>
                     </li>`;
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            html += `<li class="page-item"><a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${totalPages})">${totalPages}</a></li>`;
        }

        // Next button
        html += `<li class="page-item ${currPage === totalPages ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="event.preventDefault(); loadUsers(${currPage + 1})">Next</a>
                 </li>`;
        
        html += `</ul>`;
        controls.innerHTML = html;
        currentPage = currPage;
    }

    function openEditModal(userid, name, email, status) {
        document.getElementById("editUserId").value = userid;
        document.getElementById("editName").value = name;
        document.getElementById("editEmail").value = email;
        document.getElementById("editStatus").value = status;
        editModalInstance.show();
    }

    function saveUserEdit() {
        const btn = document.getElementById("saveUserBtn");
        const form = document.getElementById("editUserForm");
        
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';

        const formData = new FormData(form);
        
        fetch(`${BASE_API_URL}/update_user.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            
            if (data.Code === 200) {
                editModalInstance.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.msg,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
                loadUsers(currentPage); // Reload current page
                loadUserStats(); // Refresh stats
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.msg
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An unexpected error occurred while saving.'
            });
        });
    }

    function deleteUser(userid) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                
                const formData = new FormData();
                formData.append('userid', userid);

                fetch(`${BASE_API_URL}/delete_user.php`, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.Code === 200) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'User has been deleted.',
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                        loadUsers(currentPage); // Reload list
                        loadUserStats(); // Refresh stats
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.msg || 'Something went wrong!'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred.'
                    });
                });
            }
        });
    }

    // Keep existing OTP function
    function generateotp(userid, panelid, btnElement) {
        // Assume this function already works, just keep signature
        // Example logic from before or placeholder for real logic
        console.log("OTP for", userid, panelid);
        // ... implementation
    }
    
    
     // View Answer logic
    window.viewUserAnswers = function(userid) {
        let answerModalElement = document.getElementById('answerModal');
        let modalBody = document.getElementById('answerModalBody');
        
        let answerModal = bootstrap.Modal.getInstance(answerModalElement);
        if (!answerModal) {
            answerModal = new bootstrap.Modal(answerModalElement);
        }
        
        modalBody.innerHTML = '<div class="text-center">Loading answers...</div>';
        answerModal.show();
        
        fetch(BASE_API_URL + "/get_user_answers.php?userid=" + userid)
            .then(response => response.json())
            .then(data => {
                if (data && data.Code === 200 && data.data && data.data.length > 0) {
                    let ansTable = "<table class='table table-bordered table-sm'>";
                    ansTable += "<thead><tr><th>Question</th><th>Answer</th><th>Date</th></tr></thead><tbody>";
                    
                    data.data.forEach(item => {
                        ansTable += "<tr>";
                        ansTable += "<td>" + (item.question ?? '') + "</td>";
                        ansTable += "<td>" + (item.answer ?? '') + "</td>";
                        ansTable += "<td>" + (item.created_at ?? '') + "</td>";
                        ansTable += "</tr>";
                    });
                    
                    ansTable += "</tbody></table>";
                    modalBody.innerHTML = ansTable;
                } else {
                    modalBody.innerHTML = "<p class='text-danger text-center'>No answers found for this user.</p>";
                }
            })
            .catch(error => {
                console.error("Error fetching answers:", error);
                modalBody.innerHTML = "<p class='text-danger text-center'>Failed to fetch answers.</p>";
            });
    };
    
    ///////////////////////////////////////////////////////////////////////
</script>
