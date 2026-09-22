<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<!--<div class="container">-->
<!--    <h4>OTP</h4>-->

    <!-- Placeholder for table -->
<!--    <div class="card">-->
<!--        <div id="panel" class="card-body">-->

<!--            <div class="d-flex justify-content-start align-items-center mb-2">-->
<!--                <input type="search" id="searchInput" class="form-control w-25" placeholder="Search...">-->
<!--                <button id="clearSearchBtn" class="btn btn-outline-secondary btn-sm ms-2" style="height:38px;">✕</button>-->
<!--            </div>-->

<!--            <div class="table-responsive">-->
<!--                <table id='dataTable' class='table table-bordered table-striped'>-->
<!--                    <thead>-->
<!--                        <tr>-->
<!--                            <th>Sr No</th>-->
<!--                            <th>Name</th>-->
<!--                            <th>Panel ID</th>-->
<!--                            <th>OTP</th>-->
<!--                            <th>Generation Time</th>-->
<!--                            <th>Expiry Time</th>-->
<!--                        </tr>-->
<!--                    </thead>-->
<!--                    <tbody id="offlineOtpTableBody">-->
                        <!-- Data will be populated here -->
<!--                    </tbody>-->
<!--                </table>-->
<!--            </div>-->

            <!-- Pagination Controls -->
<!--             <div id="pagination" class="mt-3"></div>-->
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<div class="container">

    <h4>OTP Management</h4>

    <!-- 🔹 Tabs -->
    <ul class="nav nav-tabs" id="otpTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#otpByUser">OTP by User</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#otpByAdmin">OTP by Admin</a>
        </li>
    </ul>

    <div class="tab-content mt-3">

        <!-- 🟢 TAB 1 : OTP by USER (YOUR EXISTING PAGE) -->
        <div class="tab-pane fade show active" id="otpByUser">
            
            <!-- 👇 यहां तुम्हारा पूरा existing table code रहेगा -->
            <!-- कुछ भी बदलना नहीं -->
            <div class="card">
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
                                    <th>Panel ID</th>
                                    <th>Branch Name</th>
                                    <th>OTP</th>
                                    <th>Generation Time</th>
                                    <th>Expiry Time</th>
                                </tr>
                            </thead>
                            <tbody id="offlineOtpTableBody">
                            </tbody>
                        </table>
                    </div>

                    <div id="pagination" class="mt-3"></div>
                </div>
            </div>

        </div>

        <!-- 🔵 TAB 2 : OTP BY ADMIN -->
        <div class="tab-pane fade" id="otpByAdmin">

            <div class="card">
                <div class="card-body">

             
              
                    
                     <!-- Placeholder for table -->
                    <div id="adminOtpSection"></div>
  
  
                     <!-- OTP Modal -->
                    <div class="modal fade" id="otpModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                    
                          <div class="modal-header">
                            <h5 class="modal-title">Generated OTP</h5>
                          </div>
                    
                          <div class="modal-body">
                            <h2 id="otpText" style="letter-spacing:5px; text-align:center;"></h2>
                          </div>
                    
                          <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                          </div>
                    
                        </div>
                      </div>
                    </div>


                </div>
            </div>

        </div>

    </div>

</div>



<?php include('./footer.php'); ?>

<script>
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
document.addEventListener("DOMContentLoaded", function() {
    
    
    // sabhi tab links select karo
  const tabLinks = document.querySelectorAll('a[data-bs-toggle="tab"]');

  tabLinks.forEach(function(tab) {
    tab.addEventListener("shown.bs.tab", function (event) {

      const target = event.target.getAttribute("href");

      // 🟢 TAB 1 → OTP by User
      if (target === "#otpByUser") {
        console.log("User OTP tab activated");
        loadUserOtpList();     // <-- tumhara existing function
      }

      // 🔵 TAB 2 → OTP by Admin
      if (target === "#otpByAdmin") {
        console.log("Admin OTP tab activated");
        loadAdminOtpSection(); // <-- yahan admin list load hogi
      }

    });
  });
    
    
    fetchData();
  
});


function loadUserOtpList() {
   // jo current OTP listing wala API call chal raha hai woh yahan daal do
   fetchData();
}


function loadAdminOtpSection() {
   // yahan users list fetch + generate OTP wala system load karna
   
     
    
    const container = document.getElementById("adminOtpSection");
    //  const apiUrl = "https://sarsspl.com/FRUtopia/api/get_users.php";
    const apiUrl = BASE_API_URL + "/get_users.php";

    // tushar change code 
    fetch(apiUrl)
  .then(response => response.json())
  .then(data => {
    if (data && data.data && data.data.length > 0) {

      let table = `
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
           
              <th>Name</th>
              <th>Email</th>
              <th>Panel Id</th>
              <th>Branch Name</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
      `;

      data.data.forEach(row => {
        table += `
          <tr>
            <td>${row.name ?? '-'}</td>
            <td>${row.email_id ?? '-'}</td>
            <td>${row.panel_id ?? '-'}</td>
            <td>${row.branch_name ?? '-'}</td>
            <td>
                <button class="btn btn-primary btn-sm" 
                        onclick="generateotp('${row.userid}','${row.panel_id}', this)">
                  Generate OTP
                </button>
            </td>
          </tr>
        `;
      });

      table += `
          </tbody>
        </table>
      `;

      container.innerHTML = table;
 

    } else {
      container.innerHTML = "<p class='text-danger'>No data found or API error.</p>";
    }
  })
  .catch(error => {
    console.error("Error fetching API:", error);
    container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
  });
}



let currentPage = 1;
const limit = 25;

function fetchData(page = 1) {

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/offlineotp.php";
    const apiUrl = BASE_API_URL + "/offlineotp.php";
    // const apiUrl = "http://localhost/frutopia/api/offlineotp.php";
    const container = document.getElementById("offlineOtpTableBody");

    const formData = new FormData();
    formData.append("get_offline_otp", "1");
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
                        <td>${row.panel_id && row.panel_id !== 'undefined' ? row.panel_id : ''}</td>
                        <td>${row.branch_name || ''}</td>
                        <td>${row.decrypted_otp || ''}</td>
                        <td>${row.decrypted_generation_time || ''}</td>
                        <td>${row.decrypted_expiration_time || ''}</td>
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


document.getElementById('searchInput').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('#offlineOtpTableBody tr');

    rows.forEach(row => {
        const rowText = row.textContent.toLowerCase();
        if (rowText.indexOf(searchTerm) > -1) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Clear button functionality
document.getElementById('clearSearchBtn').addEventListener('click', function() {
    const searchInput = document.getElementById('searchInput');
    searchInput.value = '';
    searchInput.dispatchEvent(new Event('input')); // Trigger search to reset table rows
});

function generateotp(userId, panel_id, btnRef) {

  // disable button immediately
  btnRef.disabled = true;
  btnRef.innerText = "Generating...";

  if (!panel_id || panel_id === '' || panel_id === 'null') {
    alert("This user is not assigned any panel ID");

    // enable back before returning
    btnRef.disabled = false;
    btnRef.innerText = "Generate OTP";
    return;
  }

  let params = new URLSearchParams({
    org_id: '390',
    panel_id: panel_id,
    access_duration: '5',
    user_id: userId
  }).toString();

    const apiUrl = BASE_API_URL + "/fr_offline_otp.php";

//   fetch("https://sarsspl.com/FRUtopia/api/fr_offline_otp.php", {
    fetch(apiUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: params
  })
  .then(res => res.json())
  .then(result => {


    console.log(result);

    // enable button again
    btnRef.disabled = false;
    btnRef.innerText = "Generate OTP";

    if (result.result && result.result.decrypted_otp) {

      document.getElementById("otpText").innerHTML = result.result.decrypted_otp;

      var myModal = new bootstrap.Modal(document.getElementById('otpModal'));
      myModal.show();

    } else {
      alert("Failed to get OTP");
    }

  })
  .catch(err => {

    // enable button even if failed
    btnRef.disabled = false;
    btnRef.innerText = "Generate OTP";

    console.error(err);
    alert("API request failed");
  });

}



</script>