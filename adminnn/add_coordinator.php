<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container mt-4">

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        
        <span class="fw-bold">Coordinator List</span>

        <div class="d-flex gap-2">
            <a href="./coordinator_details.php" class="btn btn-success btn-sm">
                Add Coordinator
            </a>

            <button type="button" class="btn btn-outline-danger btn-sm" onclick="fetchData()">
                Reset
            </button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="dataTable" class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>SR NO</th>
                        <th>USER NAME</th>
                        <th>EMAIL ID</th>
                        <th>MOBILE</th>
                        <th>USER TYPE</th>
                        <th>ACTIVE?</th>
                        <th>ACTION</th>
                    </tr>
                </thead>
                <tbody id="usertable">
                    <tr>
                        <td colspan="7" class="text-center text-muted">
                            Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<?php include('./footer.php'); ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
// const apiUrl = "https://sarsspl.com/FRUtopia/api/coordinator_manage.php";
const apiUrl = BASE_API_URL + "/coordinator_manage.php";
$(document).ready(function() {
    fetchData();
});

/* ---------- FETCH DATA ---------- */

function fetchData() {

    // Destroy old DataTable before reload
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().clear().destroy();
    }

    const container = document.getElementById("usertable");

    const formData = new FormData();
    formData.append("fetch_user_data", "1");

    container.innerHTML = `
        <tr>
            <td colspan="7" class="text-center text-muted">Loading...</td>
        </tr>
    `;

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(res => {

        let tableRows = "";
        let srno = 1;

        if (res.data && res.data.length > 0) {

            res.data.forEach(row => {

                let statusText = row.status == 1 ? 'Yes' : 'No';
                let userType = row.user_role == 7 ? 'Coordinator' : 'User';

                tableRows += `
                    <tr>
                        <td>${srno++}</td>
                        <td>${row.name}</td>
                        <td>${row.email_id}</td>
                        <td>${row.contact_no}</td>
                        <td>${userType}</td>
                        <td>${statusText}</td>
                        <td>
                            <button class="btn btn-sm btn-primary"
                                onclick="editUser(${row.id})">
                                <i class="bi bi-pencil-square"></i>
                            </button>
                            
                            <button class="btn btn-sm btn-danger"
                                onclick="deleteUser(${row.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

        } else {
            tableRows = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        No data found
                    </td>
                </tr>
            `;
        }

        container.innerHTML = tableRows;

        // Reinitialize DataTable
        $('#dataTable').DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            ordering: true,
            destroy: true
        });

    })
    .catch(err => {
        console.error(err);
        container.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger">
                    Error loading data
                </td>
            </tr>
        `;
    });
}

/* ---------- EDIT ---------- */

function editUser(id) {
    window.location = "coordinator_details.php?id=" + id;
}

/* ---------- DELETE ---------- */

function deleteUser(id) {

    if (!confirm("Are you sure you want to delete this user?")) return;

    const formData = new FormData();
    formData.append("delete_user", "1");
    formData.append("id", id);

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data.Code === 200) {
            fetchData(); // refresh table
        } else {
            alert(data.msg);
        }

    })
    .catch(err => {
        console.error(err);
        alert("Something went wrong!");
    });
}
</script>