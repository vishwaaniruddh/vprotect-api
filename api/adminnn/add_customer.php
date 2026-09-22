<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>



<div class="container mt-4">

    <div class="d-flex justify-content-end mb-3">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#customerModal" id="add_btn">
            + Add Customer
        </button>
    </div>

    <!-- LIST CARD -->
    <div class="card">
        <div class="card-header fw-bold">
            Customer List
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>SR NO</th>
                            <th>CUSTOMER NAME</th>
                            <th>ACTION</th>
                        </tr>
                    </thead>
                    <tbody id="usertable"></tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<!-- CUSTOMER MODAL -->
<div class="modal fade" id="customerModal">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalTitle">Add Customer</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="customer_id">

        <div class="mb-3">
            <label class="fw-semibold">Customer Name *</label>
            <input type="text" class="form-control" id="customer" placeholder="Enter Customer Name">
        </div>

      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-success" id="save_btn">Save</button>
        <button class="btn btn-primary d-none" id="update_btn">Update</button>
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
const sessionUserId = "<?php echo $_user_id; ?>";
// const apiUrl = "https://sarsspl.com/FRUtopia/api/customer_manage.php";
const apiUrl = BASE_API_URL + "/customer_manage.php";


let dataTable;
const modal = new bootstrap.Modal(document.getElementById('customerModal'));

$(document).ready(function () {
    fetchData();
});

/* ---------- FETCH DATA ---------- */

function fetchData() {

    // Destroy existing datatable before reload
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().clear().destroy();
    }

    const formData = new FormData();
    formData.append("fetch_user_data", "1");

    fetch(apiUrl, { method: "POST", body: formData })
    .then(res => res.json())
    .then(res => {

        let rows = "";
        let sr = 1;

        if (res.data && res.data.length > 0) {

            res.data.forEach(row => {

                rows += `
                    <tr>
                        <td>${sr++}</td>
                        <td>${row.customer}</td>
                        <td>
                        
                            <button class="btn btn-sm btn-primary me-1"
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
            rows = `
                <tr>
                    <td colspan="3" class="text-center text-danger">
                        No Data Found
                    </td>
                </tr>
            `;
        }

        $("#usertable").html(rows);

        // Reinitialize DataTable
        $('#dataTable').DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 25, 50],
            destroy: true
        });

    });
}

/* ---------- SAVE ---------- */

$("#save_btn").click(function () {

    const customer = $("#customer").val().trim();

    if (customer === "") {
        alert("Customer Name Required");
        return;
    }

    const formData = new FormData();
    formData.append("customer", customer);
    formData.append("user_id", sessionUserId);
    formData.append("save_user", "1");

    fetch(apiUrl, { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {

        if (data.Code === 200) {
            modal.hide();
            clearForm();
            fetchData();
        } else {
            alert(data.msg);
        }

    });
});

/* ---------- EDIT ---------- */

window.editUser = function(id){

    const formData = new FormData();
    formData.append("get_user_by_id", "1");
    formData.append("id", id);

    fetch(apiUrl, { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {

        if(data.Code === 200){

            $("#customer").val(data.data.customer);
            $("#customer_id").val(data.data.id);

            $("#save_btn").addClass("d-none");
            $("#update_btn").removeClass("d-none");
            $("#modalTitle").text("Update Customer");

            modal.show();
        }
    });
};

/* ---------- UPDATE ---------- */

$("#update_btn").click(function () {

    const customer = $("#customer").val().trim();
    const customer_id = $("#customer_id").val();

    if (customer === "") {
        alert("Customer Name Required");
        return;
    }

    const formData = new FormData();
    formData.append("customer", customer);
    formData.append("customer_id", customer_id);
    formData.append("update_user", "1");

    fetch(apiUrl, { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {

        if (data.Code === 200) {
            modal.hide();
            clearForm();
            fetchData();
            $("#save_btn").removeClass("d-none");
            $("#update_btn").addClass("d-none");
            $("#modalTitle").text("Add Customer");
        } else {
            alert(data.msg);
        }

    });
});

/* ---------- DELETE ---------- */

window.deleteUser = function(id){

    if(!confirm("Are you sure you want to delete this customer?")) return;

    const formData = new FormData();
    formData.append("delete_user", "1");
    formData.append("id", id);

    fetch(apiUrl, { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {

        if(data.Code === 200){
            fetchData();
        } else {
            alert(data.msg);
        }

    });
};

function clearForm(){
    $("#customer").val('');
    $("#customer_id").val('');
}
</script>