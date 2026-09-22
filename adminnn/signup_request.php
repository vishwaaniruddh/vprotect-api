<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container">
    <h4>Signup Request Panel</h4>

    <!-- <button type="button" class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#exampleModal">
        Request Test
    </button> -->

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
                            <th>Remark</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="signup_request_TableBody">
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
                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Signp Request</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Name</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Your Name" id="leadName" required />
                </div>

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Mobile</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Your Mobile" id="leadMobile"
                        maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required />
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
                <button type="button" class="btn btn-warning" id="signup_request_btn">Request</button>
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


// Make sure you select the modal element first
const modalElement = document.getElementById('exampleModal');
const modal = new bootstrap.Modal(modalElement); // initialize bootstrap modal


document.getElementById("signup_request_btn").addEventListener("click", function() {

    var leadName = document.getElementById("leadName").value.trim();
    var leadMobile = document.getElementById("leadMobile").value.trim();
    var leadBranch = document.getElementById("leadBranch").value;

    // Basic validation
    if (!leadName || !leadMobile || !leadBranch) {
        alert("Please fill all the fields.");
        return;
    }

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("name", leadName);
    formData.append("contact_no", leadMobile);
    formData.append("branch_id", leadBranch);
    formData.append("signup_request_user", "1");


    // const apiUrl = "https://sarsspl.com/FRUtopia/api/lead_details.php";
    const apiUrl = BASE_API_URL + "/lead_details.php";

    // const apiUrl = "http://localhost/frutopia/api/lead_details.php";

    const requestOptions = {
        method: "POST",
        body: formData
    };

    fetch(apiUrl, requestOptions)
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                document.getElementById("leadName").value = "";
                document.getElementById("leadMobile").value = "";
                document.getElementById("leadBranch").value = "";
                fetchData();

                if (modal) modal.hide();
            } else {
                alert(data.msg || "Failed to save");
                if (modal) modal.hide();
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });

});


function fetchData() {

    const container = document.getElementById("signup_request_TableBody");

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("get_signup_request_list", "1");

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/lead_details.php";
    const apiUrl = BASE_API_URL + "/lead_details.php";

    // const apiUrl = "http://localhost/frutopia/api/lead_details.php";

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
                            <td>${row.remark || '---'}</td>
                            <td>${ row.status == 0 ? `<button class="btn btn-success btn-sm" onClick="approveRequest(${row.id});">Accept</button> <button class="btn btn-danger btn-sm" onClick="rejectRequest(${row.id})">Reject</button>` : row.status == 1 ?  `<span class="text-success">Accepted</span>` : `<span class="text-danger">Rejected</span>`}</td>
                        </tr>
                    `;
                });

                container.innerHTML = tableRows;

            } else {
                container.innerHTML =
                    "<tr><td colspan='7' class='text-center text-danger'>No data found </td></tr>";
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML =
                "<tr><td colspan='7' class='text-center text-danger'>Failed to load data.</td></tr>";
        });

}



function approveRequest(requestId) {


    const confirmReject = confirm("Are you sure you want to accept this request?");

    if (confirmReject) {
        // Step 3: Proceed with rejection
        // alert("Rejecting request ID: " + requestId + "\nRemark: " + remark);

        const formdata = new FormData();
        formdata.append("id", requestId);
        formdata.append("status", 1); // 1 for acceptance
        formdata.append("update_signup_request_status", "1");

        // const apiUrl = "https://sarsspl.com/FRUtopia/api/lead_details.php";
        const apiUrl = BASE_API_URL + "/lead_details.php";

        // const apiUrl = "http://localhost/frutopia/api/lead_details.php";


        const requestOptions = {
            method: "POST",
            body: formdata
        };
        fetch(apiUrl, requestOptions)
            .then(response => response.json())
            .then(data => {
                console.log("Success :", data);
                fetchData();
            }).catch(error => {
                console.error("Error fetching :", error);
                //container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
            });

    }

}

function rejectRequest(requestId) {

    const remark = prompt("Please enter a remark for rejection:");

    if (remark === null || remark.trim() === "") {
        alert("Rejection cancelled (no remark entered).");
        return;
    }

    const confirmReject = confirm("Are you sure you want to reject this request?");

    if (confirmReject) {
        // Step 3: Proceed with rejection
        // alert("Rejecting request ID: " + requestId + "\nRemark: " + remark);

        const formdata = new FormData();
        formdata.append("id", requestId);
        formdata.append("status", 2); // 2 for rejection
        formdata.append("remark", remark);
        formdata.append("update_signup_request_status", "1");

        // const apiUrl = "https://sarsspl.com/FRUtopia/api/lead_details.php";
        const apiUrl = BASE_API_URL + "/lead_details.php";

        // const apiUrl = "http://localhost/frutopia/api/lead_details.php";


        const requestOptions = {
            method: "POST",
            body: formdata
        };
        fetch(apiUrl, requestOptions)
            .then(response => response.json())
            .then(data => {
                console.log("Success :", data);
                fetchData();
            }).catch(error => {
                console.error("Error fetching :", error);
                //container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
            });

    }
}

function fetchBranchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/branch.php";
    const apiUrl = BASE_API_URL + "/branch.php";

    // const apiUrl = "http://localhost/frutopia/api/branch.php";
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