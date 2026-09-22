<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<!-- DataTables CSS -->

<div class="container mt-4">
    

    <button type="button" class="btn btn-primary btn-sm mb-3" id="branch_add_btn" data-bs-toggle="modal"
        data-bs-target="#exampleModal">
        Add Branch Manager 
    </button>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="branchTableBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Branch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="edit_id">

                <div class="mb-3">
                    <label class="fw-bold">Branch Name</label>
                    <input type="text" class="form-control" id="branch_name" placeholder="Enter Name">
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Enter Email">
                </div>

                <div class="mb-3">
                    <label class="fw-bold">Contact</label>
                    <input type="tel" maxlength="10" class="form-control" id="contact" placeholder="Enter Contact">
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-primary" id="save_btn">Save</button>
                <button class="btn btn-success" id="update_btn" style="display:none;">Update</button>
            </div>
        </div>
    </div>
</div>

<?php include('./footer.php'); ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
// const apiUrl = "https://sarsspl.com/FRUtopia/api/branch.php";
const apiUrl = BASE_API_URL + "/branch.php";
const sessionUserId = "<?php echo $_user_id; ?>";

let dataTable;
const modal = new bootstrap.Modal(document.getElementById('exampleModal'));

$(document).ready(function () {
    fetchData();
});



function fetchData() {

    // 🔥 Destroy DataTable BEFORE changing HTML
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().clear().destroy();
    }

    const formData = new FormData();
    formData.append("get_all_branch", "1");

    fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {

            let rows = "";
            let sr = 1;

            if (data.data && data.data.length > 0) {
                data.data.forEach(row => {
                    rows += `
                        <tr>
                            <td>${sr++}</td>
                            <td>${row.branch_name}</td>
                            <td>${row.email}</td>
                            <td>${row.contact}</td>
                            <td>
                               <button class="btn btn-sm btn-success" onclick="editData(${row.id})">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <button class="btn btn-sm btn-danger" onclick="deleteData(${row.id})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                });
            } else {
                rows = `<tr><td colspan="5" class="text-center text-danger">No Data Found</td></tr>`;
            }

            $("#branchTableBody").html(rows);

            // 🔥 Reinitialize AFTER html update
            $('#dataTable').DataTable({
                pageLength: 5,
                lengthMenu: [5, 10, 25, 50],
                destroy: true
            });
        });
}

/* ---------- SAVE ---------- */

$("#save_btn").click(function () {

    const branch_name = $("#branch_name").val().trim();
    const email = $("#email").val().trim();
    const contact = $("#contact").val().trim();

    if (!branch_name || !email || !contact) {
        alert("All fields are required!");
        return;
    }

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(email)) {
        alert("Invalid Email!");
        return;
    }

    const contactPattern = /^[0-9]{10}$/;
    if (!contactPattern.test(contact)) {
        alert("Contact must be 10 digits!");
        return;
    }

    const formData = new FormData();
    formData.append("user_id", sessionUserId);
    formData.append("branch_name", branch_name);
    formData.append("email", email);
    formData.append("contact", contact);
    formData.append("save_branch", "1");

    fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.Code == 200) {
                modal.hide();
                fetchData();
                clearForm();
            } else {
                alert("Save Failed!");
            }
        });
});

/* ---------- EDIT ---------- */

function editData(id) {

    const formData = new FormData();
    formData.append("get_all_branch", "1");

    fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {

            const branch = data.data.find(b => b.id == id);

            $("#edit_id").val(branch.id);
            $("#branch_name").val(branch.branch_name);
            $("#email").val(branch.email);
            $("#contact").val(branch.contact);

            $("#save_btn").hide();
            $("#update_btn").show();

            modal.show();
        });
}

/* ---------- UPDATE ---------- */

$("#update_btn").click(function () {

    const id = $("#edit_id").val();
    const branch_name = $("#branch_name").val().trim();
    const email = $("#email").val().trim();
    const contact = $("#contact").val().trim();

    const formData = new FormData();
    formData.append("id", id);
    formData.append("branch_name", branch_name);
    formData.append("email", email);
    formData.append("contact", contact);
    formData.append("update_branch", "1");

    fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.Code == 200) {
                modal.hide();
                fetchData();
                clearForm();
                $("#save_btn").show();
                $("#update_btn").hide();
            } else {
                alert("Update Failed!");
            }
        });
});

/* ---------- DELETE ---------- */

function deleteData(id) {

    if (!confirm("Are you sure you want to delete this record?")) return;

    const formData = new FormData();
    formData.append("id", id);
    formData.append("delete", "1");

    fetch(apiUrl, { method: "POST", body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.Code == 200) {
                fetchData();
            } else {
                alert("Delete Failed!");
            }
        });
}

function clearForm() {
    $("#branch_name").val('');
    $("#email").val('');
    $("#contact").val('');
}
</script>