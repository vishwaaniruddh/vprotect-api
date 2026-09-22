<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>
<style>
.bg-warning {
    background-color: #ffeb3b !important;
    color: #000 !important;
}
</style>

<div class="container table-scroll">
  <h4>Today Alert List</h4>

  <button id="exportBtn" class="btn btn-primary mb-3 btn-sm" style="display:none;">Export CSV</button>
  <button onclick="testSound()" class="btn btn-secondary mb-3 btn-sm">Test Sound</button>

  <div id="alertTable"></div>
</div>

<?php include('./footer.php'); ?>

<script>
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

var knownAlertIds = [];
var isFirstLoad = true;

var alertSound = new Audio('https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3'); 

document.addEventListener("DOMContentLoaded", function () {

    // Unlock audio
    document.body.addEventListener('click', function() {
        alertSound.play().then(() => {
            alertSound.pause();
            alertSound.currentTime = 0;
        }).catch(() => {});
    }, { once: true });

    fetchData();
});

setInterval(fetchData, 20000);

function testSound() {
    alertSound.play().catch(error => {
        console.error("Error playing sound:", error);
        alert("Click anywhere once to enable sound.");
    });
}

function fetchData() {

    const apiUrl = BASE_API_URL + "/get_alert_list.php";
    const container = document.getElementById("alertTable");
    const exportBtn = document.getElementById("exportBtn");
    var user_id = <?php echo $_user_id; ?>;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {

            if (data && data.data && data.data.length > 0) {

                let currentAlertIds = data.data.map(row => row.alertid);

                if (!isFirstLoad) {
                    let newAlerts = currentAlertIds.filter(id => !knownAlertIds.includes(id));
                    if (newAlerts.length > 0) {
                        alertSound.play().catch(() => {});
                    }
                }

                knownAlertIds = currentAlertIds;
                isFirstLoad = false;

                let table = "<table id='dataTable' class='table table-bordered table-striped'>";
                table += `
                  <thead>
                    <tr>
                      <th>Sr No</th>
                      <th>Alert ID</th>
                      <th>User Name</th>
                      <th>Contact No</th>
                      <th>Access Type</th>
                      <th>Branch</th>
                      <th>Requested At</th>
                      <th>Action</th>
                      <th>Remarks</th>
                    </tr>
                  </thead>
                  <tbody>
                `;

                let srno = 1;

                data.data.forEach(row => {

                    let actionHtml = "";
                    let statusText = "";
                    let remark = row.remark ? row.remark : "";

                    let tr_color = row.requested_status == 0 ? "bg-success text-white" : "";
                    let td_color = row.requested_status == 0 ? "text-white" : "";

                    if (row.requested_status == 1) {
                        statusText = `<span class="text-warning">Approved</span>`;
                    } 
                    else if (row.requested_status == 2) {
                        if (remark == "Expired") {
                            statusText = `<span class="text-danger">Expired</span>`;
                        } else if (remark !== "") {
                            statusText = `<span class="text-danger">Rejected (${remark})</span>`;
                        } else {
                            statusText = `<span class="text-danger">Rejected</span>`;
                        }
                    } 
                    else {
                        statusText = `<span class="text-success">Pending</span>`;
                    }

                    if (row.requested_status == 0) {
                        actionHtml = `
                            <button class="btn btn-sm btn-warning" onclick="approveAlert(${row.alertid}, ${user_id})">
                              Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectAlert(${row.alertid}, ${user_id})">
                              Reject
                            </button>
                        `;
                    } else {
                        actionHtml = statusText;
                    }

                    table += `
                      <tr class="${tr_color}">
                        <td class="${td_color}">${srno++}</td>
                        <td class="${td_color}">${row.alertid ?? ""}</td>
                        <td class="${td_color}">${row.username ?? ""}</td>
                        <td class="${td_color}">${row.usercontact_no ?? ""}</td>
                        <td class="${td_color}">${row.access_type ?? ""}</td>

                        <!-- ✅ NEW BRANCH COLUMN -->
                        <td class="${td_color}">
                          ${row.branch_name ?? ""} 
                          <br>
                          <small>${row.branch_code ?? ""}</small>
                        </td>

                        <td class="${td_color}">${row.requested_at ?? ""}</td>
                        <td class="${td_color}">${actionHtml}</td>
                        <td class="${td_color}">${row.remark ?? ""}</td>
                      </tr>
                    `;
                });

                table += "</tbody></table>";
                container.innerHTML = table;

                exportBtn.style.display = "inline-block";

            } else {
                knownAlertIds = [];
                isFirstLoad = false;
                container.innerHTML = "<p class='text-danger'>No data found</p>";
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
        });

    // Export CSV
    exportBtn.onclick = function () {
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
    };
}

// ✅ Approve
function approveAlert(alertId,userId) {

    if (!confirm("Approve this request?")) return;

    const formdata = new FormData();
    formdata.append("id", alertId);
    formdata.append("userid", userId);
    formdata.append("status", 1);

    fetch(BASE_API_URL + "/update_alertstatus.php", {
        method: "POST",
        body: formdata
    })
    .then(res => res.json())
    .then(() => {
        alert("Approved");
        fetchData();
    })
    .catch(err => console.error(err));
}

// ✅ Reject
function rejectAlert(alertId, userId) {

    if (!confirm("Reject this request?")) return;

    let remark = prompt("Enter rejection remark:");

    if (!remark || remark.trim() === "") {
        alert("Remark required");
        return;
    }

    const formdata = new FormData();
    formdata.append("id", alertId);
    formdata.append("userid", userId);
    formdata.append("status", 2);
    formdata.append("remark", remark);

    fetch(BASE_API_URL + "/update_alertstatus.php", {
        method: "POST",
        body: formdata
    })
    .then(res => res.json())
    .then(() => {
        alert("Rejected");
        fetchData();
    })
    .catch(err => console.error(err));
}
</script>