<?php include('./header.php'); ?>
<style>
    .bg-warning {
    background-color: #ffeb3b !important;
    color: #000 !important;
}
</style>
<div class="container table-scroll">
  <h4>Today Alert List</h4>

  <!-- Export Button -->
  <button id="exportBtn" class="btn btn-primary mb-3 btn-sm" style="display:none;">Export CSV</button>
  <button onclick="testSound()" class="btn btn-secondary mb-3 btn-sm">Test Sound</button>

  <!-- Placeholder for table -->
  <div id="alertTable"></div>
</div>

<?php include('./footer.php'); ?>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

// Sound Notification Setup
var knownAlertIds = [];
var isFirstLoad = true;
// You can replace this URL with a local path like './notification.mp3'
var alertSound = new Audio('https://media.geeksforgeeks.org/wp-content/uploads/20190531135120/beep.mp3'); 

document.addEventListener("DOMContentLoaded", function () {
    // Unlock audio on first interaction to allow auto-play policies
    document.body.addEventListener('click', function() {
        alertSound.play().then(() => {
            alertSound.pause();
            alertSound.currentTime = 0;
        }).catch((e) => {});
    }, { once: true });

    fetchData();
});

setInterval(fetchData, 20000);
// setInterval(autoreject, 60000); // 60000ms = 1 minute

function testSound() {
    alertSound.play().then(() => {
        // Sound played successfully
        console.log("Sound played successfully");
    }).catch(error => {
        console.error("Error playing sound:", error);
        alert("Sound playback failed. Please interact with the document first or check console.");
    });
}


function fetchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/get_today_alert_list_backup.php";
    // const apiUrl = BASE_API_URL + "/today_alerts_admin.php";
    
    const apiUrl = BASE_API_URL + "/today_alerts_admin_test.php";
    

    const container = document.getElementById("alertTable");
    const exportBtn = document.getElementById("exportBtn");
    
    // var user_id = <?php echo $_user_id; ?>;
    
    // const formdata = new FormData();
    // formdata.append("user_id", user_id);
    
    const requestOptions = {
      method: "POST",
      redirect: "follow"
    };

    fetch(apiUrl,requestOptions)
        .then(response => response.json())
        .then(data => {
          
          console.log(data);  
          
            if (data && data.data && data.data.length > 0) {
                
                // Check for new alerts
                var currentAlertIds = data.data.map(row => row.alertid);
                if (!isFirstLoad) {
                    var newAlerts = currentAlertIds.filter(id => !knownAlertIds.includes(id));
                    if (newAlerts.length > 0) {
                        alertSound.play().catch(e => console.warn("Sound blocked:", e));
                    }
                }
                knownAlertIds = currentAlertIds;
                isFirstLoad = false;

                let table = "<table id='dataTable' class='table table-bordered table-striped'>";
                table += `
                  <thead>
                    <tr>
                      <th>Sr No</th>
                      <th>Branch Address</th>
                      <th>Branch Code</th>
                      <th>User Name</th>
                      <th>Contact No</th>
                      <th>Access Type</th>
                      <th>Requested At</th>
                      <th>Action</th>
                      <th>Remarks</th>
                    </tr>
                  </thead>
                  <tbody>
                `;

                let srno = 1;
                data.data.forEach(row => {
                    

                    // let statusText = "";
                    // if (row.requested_status == 1) {
                    //     statusText = `<span class="text-warning">Approved</span>`;
                    // } else if (row.requested_status == 2) {
                    //     statusText = row.remark != "" ? `<span class="text-danger">Rejected</span>` :`<span class="text-danger">Rejected</span>`;
                    // } else {
                    //     statusText = `<span class="text-success">Pending</span>`;
                    // }
                    
                    let actionHtml = "";

                
                    let statusText = "";
                    
                    let remark = row.remark ? row.remark : ""; // NULL ko hata diya
                    
                    let tr_color = row.requested_status == 0 ? "bg-success text-white" : "";
                    let td_color = row.requested_status == 0 ? "text-white" : "";
                    if (row.requested_status == 1) {
                        statusText = `<span class="text-warning">Approved</span>`;
                    } 
                    else if (row.requested_status == 2) {
                        if (remark == "Expired") {
                            statusText = `<span class="text-danger">Expired</span>`;
                        } 
                        else if (remark !== "") {
                            statusText = `<span class="text-danger">Rejected (${remark})</span>`;
                        } 
                        else {
                            statusText = `<span class="text-danger">Rejected</span>`; // NULL case
                        }
                    } 
                    else {
                        statusText = `<span class="text-success">Pending</span>`;
                    }
                    
                    if (row.requested_status == 0) {
                        actionHtml = `
                            <button class="btn btn-sm btn-warning" onclick="approveAlert(${row.alertid})">
                              Approve
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="rejectAlert(${row.alertid})">
                              Reject
                            </button>
                        `;
                    } else {
                        actionHtml = statusText;
                    }

                    

                    table += `
                      <tr class="${tr_color}">
                        <td class="${td_color}">${srno++}</td>
                        <td class="${td_color}">${row.branch_addr ?? ""}</td>
                        <td class="${td_color}">${row.branch_code ?? ""}</td>
                        <td class="${td_color}">${row.username ?? ""}</td>
                        <td class="${td_color}">${row.usercontact_no ?? ""}</td>
                        <td class="${td_color}">${row.access_type ?? ""}</td>
                        <td class="${td_color}">${row.requested_at ?? ""}</td>
                        <td class="${td_color}">${actionHtml}</td>
                        <td class="${td_color}">${row.remark ?? ""}</td>
                      </tr>
                    `;
                });

                table += "</tbody></table>";
                container.innerHTML = table;
                autoreject();
                exportBtn.style.display = "inline-block";
            } else {
                knownAlertIds = [];
                isFirstLoad = false;
                container.innerHTML = "<p class='text-danger'>No data found </p>";
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
function approveAlert(alertId) {
    const formdata = new FormData();
    formdata.append("id", alertId);
    formdata.append("userid", 1);
    formdata.append("status", 1);
    
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

// ✅ Approve Alert (dummy for now)
// function rejectAlert(alertId,userId) {
//     const formdata = new FormData();
//     formdata.append("id", alertId);
//     formdata.append("userid", userId);
//     formdata.append("status", 2);
    
//     const requestOptions = {
//       method: "POST",
//       body: formdata,
//       redirect: "follow"
//     };
//     // 👉 Later: add API call to update status
//     const apiUrl = "https://sarsspl.com/FRUtopia/api/update_alertstatus.php";
    
//     if (confirm("Do you want to reject the request?")) {
    
//         fetch(apiUrl,requestOptions)
//             .then(response => response.json())
//             .then(data => {
//                 console.log("Success :", data);
//                 alert("Successfully Rejected");
//                 fetchData();
//             }).catch(error => {
//                 console.error("Error fetching API:", error);
//                 //container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
//             });
//     }else {
//       console.log("Fetch canceled by user.");
//     }
// }

function rejectAlert(alertId) {

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
    formdata.append("userid", 1);
    formdata.append("status", 2);  // Reject
    formdata.append("remark", remark); // 👉 sending remark

    const requestOptions = {
        method: "POST",
        body: formdata,
        redirect: "follow"
    };

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/update_alertstatus.php";
    const apiUrl = BASE_API_URL + "/update_alertstatus.php";


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


function autoreject(){
     const formdata = new FormData();
    formdata.append("remark", "Expired");
    
     const requestOptions = {
      method: "POST",
       body: formdata,
      redirect: "follow"
    };
    
    // 👉 Later: add API call to update status
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/alert_auto_reject.php";
    const apiUrl = BASE_API_URL + "/alert_auto_reject.php";

    fetch(apiUrl,requestOptions)
            .then(response => response.text())
            .then(data => {
                // console.log("Auto Reject Respone:-", data);
            }).catch(error => {
                console.error("Error fetching API:", JSON.parse(error));
            });
}
</script>
