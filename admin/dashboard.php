<?php 

include(__DIR__ . '/baseurl.php');

$_user_role = $_SESSION['user_role'] ?? '';

// if($_user_role == 1) {
//     header("Location: " . BASE_URL . "/today_alerts_admin.php");
//     exit();
// }

// if($_user_role == 7) {
//     header("Location: " . BASE_URL . "/today_alerts_new.php");
//     exit();
// }

include('./header.php'); 

?>
<div class="row mb-3">
    <div class="col-md-3">
        <select class="form-control" id="dashboard_filter">
            <option value="day">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
        </select>
    </div>
</div>

        <!-- [ Main Content ] start -->
        <div class="row">
          <!-- [ Enhanced KPI Cards ] start -->
          <div class="col-md-6 col-xl-3">
            <div class="card bg-primary">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h6 class="mb-2 text-white">Total OTP Requests</h6>
                    <h3 class="text-white mb-0 f-w-300" id="total_requests">0</h3>
                  </div>
                  <div class="flex-shrink-0">
                    <div id="total-revenue-chart" style="height: 50px; width: 80px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xl-3">
            <div class="card bg-success">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h6 class="mb-2 text-white">Realtime Active Requests</h6>
                    <h3 class="text-white mb-0 f-w-300" id="active_requests">0</h3>
                  </div>
                  <div class="flex-shrink-0">
                    <div id="active-users-chart" style="height: 50px; width: 80px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xl-3">
            <div class="card bg-warning">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h6 class="mb-2 text-white">Accessed Requests</h6>
                    <h3 class="text-white mb-0 f-w-300" id="accessed_requests">0</h3>
                  </div>
                  <div class="flex-shrink-0">
                    <div id="orders-chart" style="height: 50px; width: 80px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-6 col-xl-3">
            <div class="card bg-info">
              <div class="card-body">
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">
                    <h6 class="mb-2 text-white">Pending Requests</h6>
                    <h3 class="text-white mb-0 f-w-300" id="pending_requests">0</h3>
                  </div>
                  <div class="flex-shrink-0">
                    <div id="conversion-chart" style="height: 50px; width: 80px;"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- [ Enhanced KPI Cards ] end -->

          
        </div>
        <!-- [ Main Content ] end -->
      </div>
    </div>
    <!-- [ Main Content ] end -->
   
<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

function loadDashboard() {
    
     const apiUrl = BASE_API_URL + "/dashboard.php";

    const filter =
        document.getElementById("dashboard_filter").value;

    fetch(apiUrl, {

        method: "POST",

        headers: {
            "Content-Type":
                "application/x-www-form-urlencoded"
        },

        body: new URLSearchParams({
            filter: filter
        })

    })

    .then(response => response.json())

    .then(response => {

        console.log(response);

        if (response.Code == 200) {

            document.getElementById("total_requests")
                .innerText =
                response.data.total_otp_requests_received;

            document.getElementById("active_requests")
                .innerText =
                response.data.total_realtime_active_requests;

            document.getElementById("accessed_requests")
                .innerText =
                response.data.total_accessed_requests;

            document.getElementById("pending_requests")
                .innerText =
                response.data.total_pending_requests;
        }

    })

    .catch(error => {

        console.log(error);

    });

}

document.addEventListener("DOMContentLoaded", function() {

    loadDashboard();

    document
        .getElementById("dashboard_filter")
        .addEventListener("change", loadDashboard);

    setInterval(loadDashboard, 5000);

});

</script>
<?php include('./footer.php'); ?>