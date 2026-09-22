<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container">
    <h4>Panel Details</h4>
    <button type="button" class="btn btn-primary btn-sm mb-3" id="add_panel_btn" data-bs-toggle="modal"
        data-bs-target="#exampleModal">
        Add
    </button>
    <!-- Placeholder for table -->
    <div class="card">
        <div id="panel" class="card-body">
            <div class="table-responsive">
                <table id='dataTable' class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Panel ID</th>
                            <th>Branch Code</th>
                            <th>User Allotment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="branchTableBody">
                        <!-- Data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Panel Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Panel Id</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Panel Id" id="panel_id" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                </div>
                
                <div class="from-group mb-3">
                    <span class="form-label fw-bold">No of User</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter No Of User" id="no_of_user_allotment" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                </div>
                
                <div class="mb-3">
                        <label class="form-label fw-semibold small">Branch Code <span class="text-danger">*</span></label>
                        <select class="form-select" id="branch_code" name="branch_code" >
                            <option value="">Select a Branch...</option>
                        </select>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm text-light" data-bs-dismiss="modal"
                    style="background: red;">Close</button>
                <button type="button" class="btn btn-warning btn-sm" id="save_panel_btn">Save</button>
                <button type="button" class="btn btn-primary btn-sm" id="update_panel_btn">Update</button>
            </div>
        </div>
    </div>
</div>

<?php include('./footer.php'); ?>

<script>
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

document.addEventListener("DOMContentLoaded", function() {
    loadBranchOptions();
    fetchData();
});
// Make sure you select the modal element first
const modalElement = document.getElementById('exampleModal');
const modal = new bootstrap.Modal(modalElement); // initialize bootstrap modal


const save_panel_btn = document.getElementById("save_panel_btn");
const add_panel_btn = document.getElementById("add_panel_btn");

add_panel_btn.addEventListener("click", function() {
    // Reset form fields
    document.getElementById("panel_id").value = "";
    document.getElementById("no_of_user_allotment").value = "";
    document.getElementById("branch_code").value = "";
    // Show save button and hide update button
    save_panel_btn.style.display = "inline-block";
    update_panel_btn.style.display = "none";
});

const update_panel_btn = document.getElementById("update_panel_btn");
update_panel_btn.style.display = "none";

save_panel_btn.addEventListener("click", function() {
    
    var panel_id = document.getElementById("panel_id").value.trim();
    var no_of_user_allotment = document.getElementById("no_of_user_allotment").value.trim();
    var branch_code = document.getElementById("branch_code").value;
    
    // Basic validation
    if (!panel_id) {
        alert("Please fill all the fields.");
        return;
    }

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/panel.php";
    const apiUrl = BASE_API_URL + "/panel.php";

   // const apiUrl = "http://localhost/frutopia/api/panel.php";

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("panel_id", panel_id);
    formData.append("no_of_user_allotment", no_of_user_allotment);
    formData.append("branch_code", branch_code);
    formData.append("save_panel", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                // alert(data.msg);
                document.getElementById("panel_id").value = "";

                fetchData();
                modal.hide();
            } else {
                alert(data.msg || "Failed to save");
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
});


function loadBranchOptions() {
        const apiUrl = BASE_API_URL + "/panel.php";
    
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

function fetchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/panel.php";
    const apiUrl = BASE_API_URL + "/panel.php";

   // const apiUrl = "http://localhost/frutopia/api/panel.php";


    const container = document.getElementById("branchTableBody");

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("get_all_panel", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if (data && data.data && data.data.length > 0) {
                let tableRows = "";
                let srno = 1;
                data.data.forEach(row => {
                    tableRows += `
                        <tr>
                            <td>${srno++}</td>
                            <td>${row.panel_id || ''}</td>
                            <td>${row.branch_code || 'N/A'}</td>
                            <td>${row.no_of_user_allotment || ''}</td>
                            <td><button class="btn btn-success btn-sm" onClick="editData(${row.id});">Edit</button></td>
                        </tr>
                    `;
                });

                container.innerHTML = tableRows;

            } else {
                container.innerHTML =
                    "<tr><td colspan='3' class='text-center text-danger'>No data found or API error.</td></tr>";
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML =
                "<tr><td colspan='3' class='text-center text-danger'>Failed to load data.</td></tr>";
        });

}

function editData(id) {
    
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/panel.php";
    const apiUrl = BASE_API_URL + "/panel.php";

    //const apiUrl = "http://localhost/frutopia/api/panel.php";

    const formData = new FormData();
    formData.append("get_all_panel", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.data) {
                const branch = data.data.find(b => b.id == id);
                if (branch) {
                    document.getElementById("panel_id").value = branch.panel_id;
                    document.getElementById("no_of_user_allotment").value = branch.no_of_user_allotment;
                    document.getElementById("branch_code").value = branch.branch_code;
                    // Show update button and hide save button
                    save_panel_btn.style.display = "none";
                    update_panel_btn.style.display = "inline-block";

                    // Show the modal
                    modal.show();

                    // Set up the update button click event
                    update_panel_btn.onclick = function() {
                        updateBranch(id);
                    };
                }
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
}

function updateBranch(id) {
    var panel_id = document.getElementById("panel_id").value.trim();
    var no_of_user_allotment = document.getElementById("no_of_user_allotment").value.trim();
    var branch_code = document.getElementById("branch_code").value;
    
    // Basic validation
    if (!panel_id) {
        alert("Please fill all the fields.");
        return;
    }

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/panel.php";
    const apiUrl = BASE_API_URL + "/panel.php";

   // const apiUrl = "http://localhost/frutopia/api/panel.php";

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("panel_id", panel_id);
    formData.append("no_of_user_allotment", no_of_user_allotment);
    formData.append("branch_code", branch_code);
    formData.append("id", id);
    formData.append("update_panel", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
          .then(text => {
              console.log("RAW RESPONSE:", text);
              return JSON.parse(text);
          }).then(data => {
            if (data && data.Code === 200) {
                // alert(data.msg);
                document.getElementById("panel_id").value = "";

                // Reset buttons
                save_panel_btn.style.display = "inline-block";
                update_panel_btn.style.display = "none";

                fetchData();
                modal.hide();
            } else {
                alert(data.msg || "Failed to update");
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
}
</script>