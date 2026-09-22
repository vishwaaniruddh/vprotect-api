<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container">
    <h4>User Lead Pannel</h4>
    <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                            <th>Name</th>
                            <th>Contact No</th>
                            <th>Email</th>
                            <th>Branch</th>
                        </tr>
                    </thead>
                    <tbody id="leadTableBody">
                        <!-- Data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Lead Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Lead Name</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Lead Name" id="leadName" required />
                </div>

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Lead Mobile</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Lead Mobile" id="leadMobile"
                        maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
                </div>

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Lead Email</span>
                    <input type="email" class="form-control mb-3" placeholder="Enter Lead Email" id="leadEmail"
                        required />
                </div>

                <div class="form-group mb-3">
                    <span class="form-label fw-bold">Branch</span>
                    <select class="form-control mb-3" id="leadBranch" required>
                        <option value="">--Select--</option>
                    </select>
                </div>



            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal"
                    style="background: red;">Close</button>
                <button type="button" class="btn btn-warning" id="lead_save_btn">Save</button>
            </div>
        </div>
    </div>
</div>

<?php include('./footer.php'); ?>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

document.addEventListener("DOMContentLoaded", function() {
    fetchData();
    fetchBranchData();
});


document.getElementById("lead_save_btn").addEventListener("click", function() {
    var leadName = document.getElementById("leadName").value.trim();
    var leadMobile = document.getElementById("leadMobile").value.trim();
    var leadEmail = document.getElementById("leadEmail").value.trim();
    var leadBranch = document.getElementById("leadBranch").value;

    // Basic validation
    if (!leadName || !leadMobile || !leadEmail || !leadBranch) {
        alert("Please fill all the fields.");
        return;
    }

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/lead_details.php";
    const apiUrl = BASE_API_URL + "/lead_details.php";
   // const apiUrl = "http://localhost/frutopia/api/lead_details.php";


    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("name", leadName);
    formData.append("contact_no", leadMobile);
    formData.append("email", leadEmail);
    formData.append("branch_id", leadBranch);
    formData.append("save_lead_details", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                // alert(data.msg);

                // Clear form fields
                document.getElementById("leadName").value = "";
                document.getElementById("leadMobile").value = "";
                document.getElementById("leadEmail").value = "";
                document.getElementById("leadBranch").value = "";

                fetchData();
                // Optional: close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModal'));
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


function fetchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/lead_details.php";
    const apiUrl = BASE_API_URL + "/lead_details.php";

   // const apiUrl = "http://localhost/frutopia/api/lead_details.php";

    const container = document.getElementById("leadTableBody");

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("get_all_user_lead", "gvghvghv");

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
                            <td>${row.name || ''}</td>
                            <td>${row.contact_no || ''}</td>
                            <td>${row.email_id || ''}</td>
                            <td>${row.branch_name || ''}</td>
                        </tr>
                    `;
                });

                container.innerHTML = tableRows;

            } else {
                container.innerHTML =
                    "<tr><td colspan='5' class='text-center text-danger'>No data found</td></tr>";
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML =
                "<tr><td colspan='5' class='text-center text-danger'>Failed to load data.</td></tr>";
        });

}

function fetchBranchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/branch.php";
    const apiUrl = BASE_API_URL + "/branch.php";

  //  const apiUrl = "http://localhost/frutopia/api/branch.php";
    const container = document.getElementById("leadBranch");

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("get_all_branch", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {

            if (data && data.data && data.data.length > 0) {
                let options = `<option value="">--Select--</option>`;
                data.data.forEach(row => {
                    options += `<option value="${row.id}">${row.branch_name || ''}</option>`;
                });

                container.innerHTML = options;

            } else {
                container.innerHTML =
                    "<option value=''>No data found or API error.</option>";
            }

        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML =
                "<tr><td colspan='3' class='text-center text-danger'>Failed to load data.</td></tr>";
        });

}
</script>