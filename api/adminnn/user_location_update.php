<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container mt-3">
  <h4>User Location Update</h4>
    
     <!-- Placeholder for table -->
    <div class="card mt-3">
        <div id="panel" class="card-body">

            <div class="d-flex justify-content-start align-items-center mb-2">
                <input type="search" id="searchInput" class="form-control w-25" placeholder="Search...">
                <button id="clearSearchBtn" class="btn btn-outline-secondary btn-sm ms-2" style="height:38px;">✕</button>
            </div>

            <div class="table-responsive">
                <table id='dataTable' class='table table-bordered table-striped'>
                    <thead>
                        <tr>
                            <th>Sr No</th>
                            <th>Name</th>
                            <th>location</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="offlineOtpTableBody">
                        <!-- Data will be populated here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
             <div id="pagination" class="mt-3"></div>
        </div>
    </div>
    
</div>

<?php include('./footer.php'); ?>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

document.addEventListener("DOMContentLoaded", function () {
 
     fetchData();

});



let currentPage = 1;
const limit = 10;

function fetchData(page = 1) {

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_location_update.php";
    const apiUrl = BASE_API_URL + "/user_location_update.php";

    const container = document.getElementById("offlineOtpTableBody");

    const formData = new FormData();
    formData.append("get_user_set_panel", "1");
    formData.append("page", page);
    formData.append("limit", limit);

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        
        console.log(data);
        if (data && data.data && data.data.length > 0) {
            let tableRows = "";
            let srno = 1 + (page - 1) * limit;
            data.data.forEach(row => {
                
                let actionHtml = "";

                
                    let statusText = "";
                    
                    let remark = row.remark ? row.remark : ""; // NULL ko hata diya
                    
                    if (row.is_aprove == 1) {
                        statusText = `<span class="text-warning">Approved</span>`;
                    } 
                    else if (row.is_aprove == 2) {
                         if (remark !== "") {
                            statusText = `<span class="text-danger">Rejected (${remark})</span>`;
                        } 
                       
                    } 
                    else {
                        statusText = `<span class="text-success">Pending</span>`;
                    }
                    
                    if (row.is_aprove == 0) {
                        actionHtml = `
                            <button class="btn btn-sm btn-warning" onclick="approveAlert(${row.id}, ${row.user_id})">
                              Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectAlert(${row.id}, ${row.user_id})">
                              Reject
                            </button>
                        `;
                    } else {
                        actionHtml = statusText;
                    }
                
        
                tableRows += `
                    <tr>
                        <td>${srno++}</td>
                        <td>${row.name || ''}</td>
                        <td>${row.location || ''}</td>
                        <td>${actionHtml}</td>
                    </tr>
                `;
            });
            container.innerHTML = tableRows;

            // ✅ Pagination Buttons
            renderPagination(data.pagination.total_pages, page);
        } else {
            container.innerHTML = "<tr><td colspan='6' class='text-center text-danger'>No data found.</td></tr>";
        }
    })
    .catch(error => {
        console.error("Error fetching API:", error);
        container.innerHTML = "<tr><td colspan='6' class='text-center text-danger'>Failed to load data.</td></tr>";
    });
}

function renderPagination(totalPages, currentPage) {
    const paginationContainer = document.getElementById("pagination");
    let paginationHTML = `<ul class="pagination justify-content-center">`;

    // Previous Button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="fetchData(${currentPage - 1})">Previous</a>
        </li>
    `;

    // Current Page
    paginationHTML += `
        <li class="page-item active">
            <a class="page-link" href="javascript:void(0)">${currentPage}</a>
        </li>
    `;

    // Next Page
    if (currentPage < totalPages) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="fetchData(${currentPage + 1})">${currentPage + 1}</a>
            </li>
        `;
    }

    // Next Button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="javascript:void(0)" onclick="fetchData(${currentPage + 1})">Next</a>
        </li>
    `;

    paginationHTML += `</ul>`;
    paginationContainer.innerHTML = paginationHTML;
}


// ✅ Event delegation — works for dynamically created checkboxes
document.addEventListener("change", function(e) {
    if (e.target && e.target.classList.contains("statusCheckbox")) {
        const id = e.target.getAttribute("data-id");
        const isChecked = e.target.checked;
        changeStatus(id, isChecked);
    }
});

function changeStatus(id, isChecked) {
    const status = isChecked ? 1 : 0;
    console.log(`ID: ${id}, New Status: ${status}`);

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_location_update.php";
    const apiUrl = BASE_API_URL + "/user_location_update.php";


    const formData = new FormData();
    formData.append("id", id);
    formData.append("status", status);
    formData.append("changeStatus", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                alert(data.msg || "Failed to Change Status");
                fetchData();
            } else {
                fetchData();
                alert(data.msg || "Failed to Change Status");
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
    
}



// ✅ Approve Alert (dummy for now)
function approveAlert(alertId,userId) {
    const formdata = new FormData();
    formdata.append("id", alertId);
    formdata.append("user_id", userId);
    formdata.append("status", 1);
    formdata.append("Approve_request", "sdf");
    
    const requestOptions = {
      method: "POST",
      body: formdata,
      redirect: "follow"
    };
    // 👉 Later: add API call to update status
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_location_update.php";
    const apiUrl = BASE_API_URL + "/user_location_update.php";

    
    if (confirm("Do you want to approve the request?")) {
    
        fetch(apiUrl,requestOptions)
            .then(response => response.json())
            .then(data => {
                console.log("Success :", data);
                alert("Successfully Approved");
                fetchData();
            }).catch(error => {
                console.error("Error fetching API:", error);
                //container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
            });
    }else {
      console.log("Fetch canceled by user.");
    }
}


function rejectAlert(alertId, userId) {
    

    if (!confirm("Do you want to reject the request?")) {
        console.log("User cancelled.");
        return;
    }

    // 🔥 Step 1: Ask remark from user
    let remark = prompt("Enter rejection remark:");

    // If user pressed Cancel or gave blank input
    if (remark === null || remark.trim() === "") {
        alert("Remark is required to reject the request.");
        return;
    }

    // 🔥 Step 2: Prepare form data
    const formdata = new FormData();
    formdata.append("id", alertId);
    formdata.append("user_id", userId);
    formdata.append("remark", remark);
    formdata.append("Rejected_request", "sdf");

    const requestOptions = {
        method: "POST",
        body: formdata,
        redirect: "follow"
    };

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_location_update.php";
    const apiUrl = BASE_API_URL + "/user_location_update.php";


    // 🔥 Step 3: API call
    fetch(apiUrl, requestOptions)
        .then(response => response.json())
        .then(data => {
            console.log("Success :", data);
            alert("Successfully Rejected");
            fetchData();
        })
        .catch(error => {
            console.error("Error fetching API:", error);
        });
}



</script>
