<?php include('./header.php');
?>

<div class="container">
    <h4>Sites Details</h4>
    <button type="button" class="btn btn-primary btn-sm mb-3" id="branch_add_btn" data-bs-toggle="modal"
        data-bs-target="#exampleModal">
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
                            <th>Site Name</th>
                            <th>View</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody id="branchTableBody">
                        <!-- Data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Site Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="from-group mb-3">

                    <span class="form-label fw-bold">Site Name</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Site Name" id="site_name"
                        required />
                </div>

                <div class="form-group mb-3">
                    <span class="form-label fw-bold">No. of Doors</span>
                    <input type="number" class="form-control" id="no_of_doors" placeholder="Enter No. of Doors" />
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="door_table" style="display:none;">
                        <thead>
                            <tr>
                                <th>Door Name</th>
                                <th>Mac ID</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody id="door_table_body"></tbody>
                    </table>
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-bs-dismiss="modal"
                    style="background: red;">Close</button>
                <button type="button" class="btn btn-warning" id="save_branch_btn">Save</button>
                <button type="button" class="btn btn-primary" id="update_branch_btn">Update</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewDoorModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Door Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Door Name</th>
                                <th>Mac Address</th>
                                <th>Password</th>
                            </tr>
                        </thead>
                        <tbody id="viewDoorTableBody"></tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include('./footer.php'); ?>

<script>

    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

    document.addEventListener("DOMContentLoaded", function () {
        fetchData();
    });
    // Make sure you select the modal element first
    const modalElement = document.getElementById('exampleModal');
    const modal = new bootstrap.Modal(modalElement); // initialize bootstrap modal


    const saveBranchBtn = document.getElementById("save_branch_btn");
    const branch_add_btn = document.getElementById("branch_add_btn");

    branch_add_btn.addEventListener("click", function () {
        // Reset form fields
        document.getElementById("site_name").value = "";
         document.getElementById("no_of_doors").value = "";
         
             // ✅ Re-enable input when adding
    document.getElementById("no_of_doors").disabled = false;


    // Clear door table
    document.getElementById("door_table_body").innerHTML = "";

    // Hide door table
    document.getElementById("door_table").style.display = "none";

        // Show save button and hide update button
        saveBranchBtn.style.display = "inline-block";
        updateBranchBtn.style.display = "none";
      updateBranchBtn.onclick = null;
    });

    const updateBranchBtn = document.getElementById("update_branch_btn");
    updateBranchBtn.style.display = "none";

    saveBranchBtn.addEventListener("click", function() {
        var site_name = document.getElementById("site_name").value.trim();
        var no_of_doors = document.getElementById("no_of_doors").value;

        if (!site_name || !no_of_doors) {
            alert("Please fill all the fields.");
            return;
        }

        let mac_ids = document.querySelectorAll(".mac_id");
        let passwords = document.querySelectorAll(".password");

        let doors = [];

        for (let i = 0; i < mac_ids.length; i++) {
            let doorName = (i === mac_ids.length - 1) ? "Vault Door" : "Door " + (i + 1);

            doors.push({
                door_name: doorName,
                mac_id: mac_ids[i].value,
                password: passwords[i].value
            });
        }

        const apiUrl = BASE_API_URL + "/sites.php";

        const formData = new FormData();
        formData.append("site_name", site_name);
        formData.append("no_of_doors", no_of_doors);
        formData.append("doors", JSON.stringify(doors));
        formData.append("save_site", "1");

        fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                document.getElementById("site_name").value = "";
document.getElementById("no_of_doors").value = "";
                fetchData();
                modal.hide();
            } else {
                alert(data.msg || "Failed to save");
            }
        });
    });


    document.getElementById("no_of_doors").addEventListener("input", function () {
        let count = parseInt(this.value);
        let tableBody = document.getElementById("door_table_body");
        let table = document.getElementById("door_table");

        tableBody.innerHTML = "";

        if (count > 0) {
            table.style.display = "table";

            for (let i = 1; i <= count; i++) {
                let doorName = (i === count) ? "Vault Door" : "Door " + i;

                let row = `
                <tr>
                    <td>${doorName}</td>
                    <td><input type="text" class="form-control mac_id"></td>
                    <td><input type="text" class="form-control password"></td>
                </tr>
            `;
                tableBody.innerHTML += row;
            }
        } else {
            table.style.display = "none";
        }
    });
function fetchData() {
    const apiUrl = BASE_API_URL + "/sites.php";
    const container = document.getElementById("branchTableBody");

    const formData = new FormData();
    formData.append("get_all_sites", "1");

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        container.innerHTML = "";

        if (data && data.data && data.data.length > 0) {
            data.data.forEach((row, index) => {
                let tr = document.createElement("tr");

                tr.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${row.site_name || ''}</td>
                    <td><button class="btn btn-success btn-sm view-btn">View</button></td>
                    <td><button class="btn btn-primary btn-sm edit-btn">Edit</button></td>
                    <td><button class="btn btn-danger btn-sm delete-btn">Delete</button></td>
                `;

                container.appendChild(tr);

                // Add event listeners
                tr.querySelector(".view-btn").addEventListener("click", () => ViewData(row.id));
                tr.querySelector(".edit-btn").addEventListener("click", () => EditData(row.id));
                tr.querySelector(".delete-btn").addEventListener("click", () => DeleteSite(row.id));
            });
        } else {
            container.innerHTML =
                "<tr><td colspan='5' class='text-center text-danger'>No data found or API error.</td></tr>";
        }
    })
    .catch(error => {
        console.error("Error fetching API:", error);
        container.innerHTML =
            "<tr><td colspan='5' class='text-center text-danger'>Failed to load data.</td></tr>";
    });
}
    // function fetchData() {
    //     const apiUrl = BASE_API_URL + "/sites.php";
    //     // const apiUrl = "http://localhost/frutopia/api/branch.php";


    //     const container = document.getElementById("branchTableBody");

    //     // ✅ Create FormData object
    //     const formData = new FormData();
    //     formData.append("get_all_sites", "1");

    //     fetch(apiUrl, {
    //         method: "POST",
    //         body: formData
    //     })
    //         .then(response => response.json())
    //         .then(data => {

    //             if (data && data.data && data.data.length > 0) {
    //                 let tableRows = "";
    //                 let srno = 1;
    //                 data.data.forEach(row => {
    //                     tableRows += `
    //                     <tr>
    //                         <td>${srno++}</td>
    //                         <td>${row.site_name || ''}</td>
    //                         <td><button class="btn btn-success btn-sm" onClick="ViewData(${row.id});">View</button></td>
    //                         <td><button class="btn btn-primary btn-sm" onClick="EditData(${row.id});">Edit</button></td>
    //                         <td><button class="btn btn-danger btn-sm" onClick="DeleteSite(${row.id});">Delete</button></td>
    //                     </tr>
    //                 `;
    //                 });

    //                 container.innerHTML = tableRows;

    //             } else {
    //                 container.innerHTML =
    //                     "<tr><td colspan='3' class='text-center text-danger'>No data found or API error.</td></tr>";
    //             }
    //         })
    //         .catch(error => {
    //             console.error("Error fetching API:", error);
    //             container.innerHTML =
    //                 "<tr><td colspan='3' class='text-center text-danger'>Failed to load data.</td></tr>";
    //         });

    // }

    // function ViewData(id) {
        

    //     const apiUrl = BASE_API_URL + "/sites.php";

    //     const formData = new FormData();
    //     formData.append("site_id", id);
    //     formData.append("get_mac_by_site", "1");

    //     fetch(apiUrl, {
    //         method: "POST",
    //         body: formData
    //     })
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data && data.data) {
    //                 const branch = data.data.find(b => b.id == id);
    //                 if (branch) {
    //                     document.getElementById("branch_name").value = branch.branch_name;

    //                     // Show update button and hide save button
    //                     saveBranchBtn.style.display = "none";
    //                     updateBranchBtn.style.display = "inline-block";

    //                     // Show the modal
    //                     modal.show();

    //                     // Set up the update button click event
    //                     updateBranchBtn.onclick = function () {
    //                         updateBranch(id);
    //                     };
    //                 }
    //             }
    //         })
    //         .catch(error => {
    //             console.error("Error fetching API:", error);
    //             alert("Something went wrong!");
    //         });
    // }

    function ViewData(id) {

    const apiUrl = BASE_API_URL + "/sites.php";

    const formData = new FormData();
    formData.append("site_id", id);
    formData.append("get_mac_by_site", "1");

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {

        if (data && data.data) {

            let tableRows = "";

            data.data.forEach(row => {
                tableRows += `
                    <tr>
                        <td>${row.door_name}</td>
                        <td>${row.mac_address}</td>
                        <td>${row.password}</td>
                    </tr>
                `;
            });

            document.getElementById("viewDoorTableBody").innerHTML = tableRows;

            // Show modal
            let viewModal = new bootstrap.Modal(document.getElementById('viewDoorModal'));
            viewModal.show();
        }

    })
    .catch(error => {
        console.error("Error fetching API:", error);
        alert("Something went wrong!");
    });
}

function EditData(id) {

    const apiUrl = BASE_API_URL + "/sites.php";

    const formData = new FormData();
    formData.append("site_id", id);
    formData.append("get_mac_by_site", "1");

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data && data.data) {

            let tableBody = document.getElementById("door_table_body");
            let table = document.getElementById("door_table");

            tableBody.innerHTML = "";
            table.style.display = "table";

            data.data.forEach((row, index) => {

                let tr = `
                <tr>
                    <td>${row.door_name}</td>
                    <td><input type="text" class="form-control mac_id" value="${row.mac_address}"></td>
                    <td><input type="text" class="form-control password" value="${row.password}"></td>
                </tr>
                `;

                tableBody.innerHTML += tr;
            });

            // Hide Save, show Update
            saveBranchBtn.style.display = "none";
            updateBranchBtn.style.display = "inline-block";
            
            document.getElementById("no_of_doors").disabled = true;

            // Attach update event
            updateBranchBtn.onclick = function () {
                updateSite(id);
            };

            modal.show();
        }

    });
}

function updateSite(id) {

    let mac_ids = document.querySelectorAll(".mac_id");
    let passwords = document.querySelectorAll(".password");

    let doors = [];

    for (let i = 0; i < mac_ids.length; i++) {

        doors.push({
            door_name: (i === mac_ids.length - 1) ? "Vault Door" : "Door " + (i + 1),
            mac_id: mac_ids[i].value,
            password: passwords[i].value
        });
    }

    const apiUrl = BASE_API_URL + "/sites.php";

    const formData = new FormData();
    formData.append("site_id", id);
    formData.append("doors", JSON.stringify(doors));
    formData.append("update_site", "1"); // 👈 handle this in API

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        if (data && data.Code === 200) {

            alert("Updated successfully");

            modal.hide();
            fetchData();

        } else {
            alert(data.msg || "Update failed");
        }

    });
}

    function updateBranch(id) {
        var branch_name = document.getElementById("branch_name").value.trim();

        // Basic validation
        if (!branch_name) {
            alert("Please fill all the fields.");
            return;
        }

        const apiUrl = BASE_API_URL + "/branch.php";
        // const apiUrl = "http://localhost/frutopia/api/branch.php";

        // ✅ Create FormData object
        const formData = new FormData();
        formData.append("branch_name", branch_name);
        formData.append("id", id);
        formData.append("update_branch", "1");

        fetch(apiUrl, {
            method: "POST",
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data && data.Code === 200) {
                    // alert(data.msg);
                    document.getElementById("branch_name").value = "";

                    // Reset buttons
                    saveBranchBtn.style.display = "inline-block";
                    updateBranchBtn.style.display = "none";

                    fetchData();
                    modal.hide();
                } else {
                    alert(data.msg || "Failed to update");
                }
            })
            .catch(error => {
                console.error("Error fetching API:", error);
                alert("Something went wrong!");
            });
    }
    
        function DeleteSite(id){
        if(!confirm("Are you sure you want to delete this site?")){
            return;
        }
        
        const apiUrl = BASE_API_URL + "/sites.php";
        
        const formData = new FormData();
        formData.append("site_id", id);
        formData.append("delete_site","1");
        
        fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(res =>res.json())
        .then(data => {
            if(data && data.Code ===200){
                alert("Deleted Successfully");
                fetchData();
            }else{
                alert(data.msg || "Delete failed");
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Something went wrong!");
        });
    }
</script>