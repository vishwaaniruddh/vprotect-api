<?php include('./header.php'); 
$_user_id = $_SESSION['user_id'];
?>

<div class="container mt-4">
    
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white fw-bold">
            Assign Coordinator - Panel List
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Sr No</th>
                            <th>Panel ID</th>
                            <th>Coordinator Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="offlineOtpTableBody">
                        <tr>
                            <td colspan="3" class="text-muted">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<?php include('./footer.php'); ?>
<script>
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
document.addEventListener("DOMContentLoaded", function() {
    fetchData();
});

// function fetchData() {

//     const apiUrl = "https://sarsspl.com/FRUtopia/api/assign_coordinator_api.php";
//     const container = document.getElementById("offlineOtpTableBody");

//     const formData = new FormData();
//     formData.append("fetch_panels", "1");

//     fetch(apiUrl, {
//         method: "POST",
//         body: formData
//     })
//     .then(res => res.json())
//     .then(res => {

//         console.log(res);

//         if (res.Code === 200 && res.data.length > 0) {

//             let rows = "";
//             let srno = 1;

//             res.data.forEach(row => {

//                 let statusText = row.status == 1 
//                     ? '<span class="badge bg-success">Active</span>' 
//                     : '<span class="badge bg-danger">Inactive</span>';

//                 rows += `
//                     <tr>
//                         <td>${srno++}</td>
//                         <td>${row.panel_id}</td>
//                         <td>${row.coordinator_id}</td>
//                         <td>${statusText}</td>
//                     </tr>
//                 `;
//             });

//             container.innerHTML = rows;

//         } else {

//             container.innerHTML = `
//                 <tr>
//                     <td colspan="3" class="text-danger">
//                         No data found
//                     </td>
//                 </tr>
//             `;
//         }
//     })
//     .catch(error => {
//         console.error(error);
//         container.innerHTML = `
//             <tr>
//                 <td colspan="3" class="text-danger">
//                     Error loading data
//                 </td>
//             </tr>
//         `;
//     });
// }

function fetchData() {


    // const apiUrl = "https://sarsspl.com/FRUtopia/api/assign_coordinator_api.php";
    const apiUrl = BASE_API_URL + "/assign_coordinator_api.php";
    const container = document.getElementById("offlineOtpTableBody");

    const formData = new FormData();
    formData.append("fetch_panels", "1");

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(res => {

        if (res.Code === 200 && res.panels.length > 0) {

            let rows = "";
            let srno = 1;

            res.panels.forEach(panel => {

                let statusText = panel.status == 1 
                    ? '<span class="badge bg-success">Active</span>' 
                    : '<span class="badge bg-danger">Inactive</span>';

                // Build dropdown
                let dropdown = `<select class="form-select form-select-sm" 
                                    onchange="assignCoordinator(${panel.id}, this.value)">`;

                dropdown += `<option value="">Select Coordinator</option>`;

                res.coordinators.forEach(user => {

                    let selected = panel.coordinator_id == user.id ? "selected" : "";

                    dropdown += `
                        <option value="${user.id}" ${selected}>
                            ${user.name}
                        </option>
                    `;
                });

                dropdown += `</select>`;

                rows += `
                    <tr>
                        <td>${srno++}</td>
                        <td>${panel.panel_id}</td>
                        <td>${dropdown}</td>
                        <td>${statusText}</td>
                    </tr>
                `;
            });

            container.innerHTML = rows;

        } else {

            container.innerHTML = `
                <tr>
                    <td colspan="4" class="text-danger">
                        No data found
                    </td>
                </tr>
            `;
        }
    });
}




function assignCoordinator(panel_id, coordinator_id) {

    // if (!coordinator_id) return;

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/assign_coordinator_api.php";
    const apiUrl = BASE_API_URL + "/assign_coordinator_api.php";
    const formData = new FormData();

    formData.append("assign_coordinator", "1");
    formData.append("panel_id", panel_id);
    formData.append("coordinator_id", coordinator_id);

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data.Code === 200) {
            alert(data.msg);
            fetchData();
        } else {
            alert("Error assigning coordinator");
        }
    });
}




</script>

