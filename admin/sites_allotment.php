<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Make dropdown almost full screen */
.select2-dropdown.custom-scroll {
    max-height: 80vh;         /* 80% of viewport height */
    overflow-y: auto;
}

/* Make search box and selected item white */
.select2-container--default .select2-selection--single {
    background-color: #fff;
    color: #000;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #000;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #000 transparent transparent transparent;
}

.select2-container--default .select2-dropdown {
    background-color: #fff;
    color: #000;
}

.select2-container--default .select2-search--dropdown .select2-search__field {
    background-color: #fff;
    color: #000;
}
</style>

<div class="container mt-3">
  <h4>Set Panel To User</h4>

   <!-- Placeholder for table -->
   <div class="card">
    <div id="panel" class="card-body">
      <div class="row">
        <div class="col-md-6">
            <select class="mb-3 form-select " id="userData">
              
            </select>
        </div>
        <div class="col-md-6">
            <select class="mb-0 form-select" id="panelData">
              <option value="">Select Panel</option>
            </select>
            
            
        </div>
        <div class="col-md-6">
            <button class="btn btn-primary btn-sm" onclick="setPanel(<?php echo $_user_id;?>)"> Submit</button>
           <!--<button class="btn btn-primary" type="submit">Submit</button>-->
        </div>
        </div>
    </div>
    </div>
    
    
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
                            <th>Site Name</th>
                            <!--<th>Status</th>-->
                          
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

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
    
document.addEventListener("DOMContentLoaded", function () {
    fetchUserData();
     fetchData();
    fetchPanelData();
    
    // Initialize Select2 on the selects
// Initialize Select2 on the selects
$('#userData').select2({
    placeholder: "Select User",
    allowClear: true,
    width: '100%',
    dropdownAutoWidth: true,
    minimumResultsForSearch: 0,
    dropdownCssClass: "custom-scroll",
    language: {
        noResults: function() { return "No users found"; }
    }
});

// When dropdown opens, change the search input placeholder
$('#userData').on('select2:open', function() {
    $('.select2-search__field').attr('placeholder', 'Search Users');
});

$('#panelData').select2({
    placeholder: "Select Panel",
    allowClear: true,
    width: '100%',
    dropdownAutoWidth: true,
    minimumResultsForSearch: 0,
    dropdownCssClass: "custom-scroll",
    language: {
        noResults: function() { return "No panels found"; }
    }
});

$('#panelData').on('select2:open', function() {
    $('.select2-search__field').attr('placeholder', 'Search Panels');
});
});



let currentPage = 1;
const limit = 25;

function fetchData(page = 1) {

    const apiUrl = BASE_API_URL + "/user_set_site.php";
    // const apiUrl = "http://localhost/frutopia/api/user_set_panel.php";
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
        if (data && data.data && data.data.length > 0) {
            let tableRows = "";
            let srno = 1 + (page - 1) * limit;
            data.data.forEach(row => {
                tableRows += `
                    <tr>
                        <td>${srno++}</td>
                        <td>${row.name || ''}</td>
                        <td>${row.site_name || ''}</td>
                       <td> <input type="checkbox" class="statusCheckbox" data-id="${row.id}" ${row.status == 1 ? 'checked' : ''} style="width:15px; height:15px; transform: scale(1.5); cursor:pointer;position: relative; left: 10px;"></td>
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

     const apiUrl = BASE_API_URL + "/user_set_site.php";
    // const apiUrl = "http://localhost/frutopia/api/otp_request_category.php";

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


function fetchUserData() {
    const apiUrl = BASE_API_URL + "/get_users.php";
    const container = document.getElementById("userData");
    
    var user_id = <?php echo $_user_id; ?>;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => { 
            if (data && data.data && data.data.length > 0) {
                let table = "<option value=''>Select User</option>";
               
                let srno = 1;
                data.data.forEach(row => {
                    
                    if(row.status == 1){
                    // Always show direction button
                    table += `
                        <option value="${row.userid}">${row.name}</option>
                        `;
                    }


                   
                });

                container.innerHTML = table;

            } else {
                container.innerHTML = '<option value="">Select User</option>';
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML = '<option value="">Select User</option>';
        });

    
}

function fetchPanelData() {
    const apiUrl = BASE_API_URL + "/sites.php";
    const container = document.getElementById("panelData");
    
    // var user_id = <?php echo $_user_id; ?>;
    
    const formData = new FormData();
    formData.append("get_all_sites", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => { 
            
            console.log("sites",data);
            if (data && data.data && data.data.length > 0) {
                let table = "<option value=''>Select Site</option>";
               
                let srno = 1;
                data.data.forEach(row => {
                    
                    // Always show direction button
                    table += `
                        <option value="${row.id}">${row.site_name}</option>
                        `;
                    
                });

                container.innerHTML = table;

            } else {
                container.innerHTML = '<option value="">Select Sites</option>';
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML = '<option value="">Select Panel</option>';
        });

    
}

function setPanel(created_by) {
   
    var panelid = document.getElementById("panelData").value;
    var userId = document.getElementById("userData").value;
    
    if(panelid != '' && userId != ''){
        const formdata = new FormData();
        formdata.append("site_id", panelid);
        formdata.append("user_id", userId);
        formdata.append("created_by", created_by);
        formdata.append("site_allotment", "1");
        
        const apiUrl = BASE_API_URL + "/sites.php";
        
        if (confirm("Do you want to set the Site to this user?")) {
        
           fetch(apiUrl, {
              method: "POST",
              body: formdata
           })
           .then(response => response.json())
           .then(data => {
              alert(data.msg);
              fetchData();
           })
           .catch(error => {
              console.error("Error:", error);
           });

        }
    } else {
        alert("Please select Panel and User");   
    }
}
// ✅ Approve Alert (dummy for now)
// function setPanel(created_by) {
   
//     var panelid = document.getElementById("panelData").value;
//     var userId = document.getElementById("userData").value;
    
//     if(panelid!='' && userId!=''){
//         const formdata = new FormData();
//         formdata.append("site_id", panelid);
//         formdata.append("user_id", userId);
//         formdata.append("created_by", created_by);
//         formdata.append("site_allotment", "1");
        
//         const requestOptions = {
//           method: "POST",
//           body: formdata,
//           redirect: "follow"
//         };
//         // 👉 Later: add API call to update status
//         const apiUrl = BASE_API_URL + "/sites.php";
        
//         if (confirm("Do you want to set the Site to this user?")) {
        
//           fetch(apiUrl, requestOptions)
//               .then(response => response.text())
//               .then(text => {
//                   console.log("RAW RESPONSE:", text);
//                   return JSON.parse(text);
//               })
//               .then(data => {
//                   console.log("Success:", data);
//                   alert(data.msg);
//                   fetchData();
//               })
//               .catch(error => {
//                   console.error("Error:", error);
//               });
//         }else {
//           console.log("Fetch canceled by user.");
//         }
//     }else{
//         alert("Please select Panel and User");   
//     }
// }
</script>
