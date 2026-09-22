<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container mt-3 table-scroll">
  <h4>Alert List</h4>

  <!-- Export Button -->
  <button id="exportBtn" class="btn btn-primary mb-3" style="display:none;">Export CSV</button>

  <!-- Placeholder for table -->
  <div id="alertTable"></div>
</div>

<?php include('./footer.php'); ?>

<script>
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
document.addEventListener("DOMContentLoaded", function () {
    fetchData();
});

setInterval(fetchData, 20000);

function fetchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/get_alert_list.php";
    const apiUrl = BASE_API_URL + "/get_alert_list.php";
    const container = document.getElementById("alertTable");
    const exportBtn = document.getElementById("exportBtn");
    
    var user_id = <?php echo $_user_id; ?>;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data && data.data && data.data.length > 0) {
                let table = "<table id='dataTable' class='table table-bordered table-striped'>";
                table += `
                  <thead>
                    <tr>
                      <th>Sr No</th>
                      <th>Alert ID</th>
                      <th>Panel Id</th>
                      <th>Branch</th>
                      <th>User Name</th>
                      <th>Contact No</th>
                      <th>Access Type</th>
                      <th>Requested At</th>
                      <th>Status</th>
                      <th>Remarks</th>
                    </tr>
                  </thead>
                  <tbody>
                `;

                let srno = 1;
                data.data.forEach(row => {
                    // let statusText = row.requested_status == 1 ? "Approved" : "Pending";
                    
                    let statusText = "";
                    if (row.requested_status == 1) {
                        statusText = `<span class="text-warning">Approved</span>`;
                    } else if (row.requested_status == 2) {
                        statusText = `<span class="text-danger">Rejected</span>`;
                    } else {
                        statusText = `<span class="text-success">Pending</span>`;
                    }

                    // Always show direction button
                    let actionHtml = `
                        <button class="btn btn-sm btn-info" onclick="openMap(${row.latitude}, ${row.longitude})">
                          Direction
                        </button>
                    `;

                    // Show approve button only if pending
                    if (row.requested_status == 0) {
                        actionHtml += `
                          <button class="btn btn-sm btn-success" onclick="approveAlert(${row.alertid},${user_id})">
                            Approve
                          </button>
                        `;
                    }

                    table += `
                      <tr>
                        <td>${srno++}</td>
                        <td>${row.alertid ?? ""}</td>

                        <td>${row.panel_id ?? "-"}</td>
                        
                        <td>${row.branch_name ?? "-"} ${row.branch_code ?? "-"}</td>
                        
                        
                        <td>${row.username ?? ""}</td>
                        <td>${row.usercontact_no ?? ""}</td>
                        <td>${row.access_type ?? ""}</td>
                        <td>${row.requested_at ?? ""}</td>
                        <td>${statusText}</td>
                        <td>${row.remark ?? ""}</td>
                      </tr>
                    `;
                });

                table += "</tbody></table>";
                container.innerHTML = table;

                exportBtn.style.display = "inline-block";
            } else {
                container.innerHTML = "<p class='text-danger'>No data found or API error.</p>";
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
        });

    // Export CSV Function
    exportBtn.addEventListener("click", function () {
        let table = document.getElementById("dataTable");
        if (!table) return;

        let rows = table.querySelectorAll("tr");
        let csv = [];

        rows.forEach(row => {
            let cols = row.querySelectorAll("td, th");
            let rowData = [];
            cols.forEach(col => {
                let text = col.innerText.replace(/"/g, '""'); 
                rowData.push('"' + text + '"');
            });
            csv.push(rowData.join(","));
        });

        let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
        let link = document.createElement("a");
        link.href = URL.createObjectURL(csvFile);
        link.download = "alert_list.csv";
        link.click();
    });
}

// ✅ Open Google Maps with Lat/Lng
function openMap(lat, lng) {
    if (lat && lng) {
        window.open(`https://www.google.com/maps?q=${lat},${lng}`, "_blank");
    } else {
        alert("Location not available");
    }
}

// ✅ Approve Alert (dummy for now)
function approveAlert(alertId,userId) {
    const formdata = new FormData();
    formdata.append("id", alertId);
    formdata.append("userid", userId);
    formdata.append("status", "1");
    
    const requestOptions = {
      method: "POST",
      body: formdata,
      redirect: "follow"
    };
    // 👉 Later: add API call to update status
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/update_alertstatus.php";
    const apiUrl = BASE_API_URL + "/update_alertstatus.php";
    
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
</script>
