<?php include('./header.php');

$_user_id = $_SESSION['user_id'];
?>

<div class="container">
    <h4>User Security Question</h4>
    <button type="button" class="btn btn-primary btn-sm mb-3" id="question_add_btn" data-bs-toggle="modal"
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
                            <th>Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="questionTableBody">
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
                <h1 class="modal-title fs-5 fw-bold" id="exampleModalLabel">Security Question Details</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="from-group mb-3">
                    <span class="form-label fw-bold">Security Question</span>
                    <input type="text" class="form-control mb-3" placeholder="Enter Question" id="question"
                        required />
                </div>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm" data-bs-dismiss="modal"
                    style="background: red; color:white;">Close</button>
                <button type="button" class="btn btn-warning btn-sm" id="save_question_btn">Save</button>
                <button type="button" class="btn btn-primary btn-sm" id="update_question_btn">Update</button>
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
// Make sure you select the modal element first
const modalElement = document.getElementById('exampleModal');
const modal = new bootstrap.Modal(modalElement); // initialize bootstrap modal


const save_question_btn = document.getElementById("save_question_btn");
const question_add_btn = document.getElementById("question_add_btn");

question_add_btn.addEventListener("click", function() {
    // Reset form fields
    document.getElementById("question").value = "";

    // Show save button and hide update button
    save_question_btn.style.display = "inline-block";
    update_question_btn.style.display = "none";
});

const update_question_btn = document.getElementById("update_question_btn");
update_question_btn.style.display = "none";

save_question_btn.addEventListener("click", function() {
    var question = document.getElementById("question").value.trim();

    // Basic validation
    if (!question) {
        alert("Please fill all the fields.");
        return;
    }

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_security_question.php";
    const apiUrl = BASE_API_URL + "/user_security_question.php";

   // const apiUrl = "http://localhost/frutopia/api/user_security_question.php";


    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("question", question);
    formData.append("save_question", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                // alert(data.msg);
                document.getElementById("question").value = "";

                fetchData();
                modal.hide();
            } else {
                alert(data.msg || "Failed to save");
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
});


function fetchData() {
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_security_question.php";
    const apiUrl = BASE_API_URL + "/user_security_question.php";

    //const apiUrl = "http://localhost/frutopia/api/user_security_question.php";


    const container = document.getElementById("questionTableBody");

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("get_all_question", "1");

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
                            <td>${row.question || ''}</td>
                            <td> <input type="checkbox" class="statusCheckbox" data-id="${row.id}" ${row.status == 1 ? 'checked' : ''} style="width:15px; height:15px; transform: scale(1.5); cursor:pointer;position: relative; left: 10px;"></td>
                            <td><button class="btn btn-success btn-sm" onClick="editData(${row.id});">Edit</button></td>
                        </tr>
                    `;
                });

                container.innerHTML = tableRows;

            } else {
                container.innerHTML =
                    "<tr><td colspan='3' class='text-center text-danger'>No data found or API error.</td></tr>";
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            container.innerHTML =
                "<tr><td colspan='3' class='text-center text-danger'>Failed to load data.</td></tr>";
        });

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

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_security_question.php";
    const apiUrl = BASE_API_URL + "/user_security_question.php";

    //const apiUrl = "http://localhost/frutopia/api/user_security_question.php";

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
                alert(data.msg || "Failed to Change Status");
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
    
}


function editData(id) {
    
    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_security_question.php";
    const apiUrl = BASE_API_URL + "/user_security_question.php";

   // const apiUrl = "http://localhost/frutopia/api/user_security_question.php";

    const formData = new FormData();
    formData.append("get_all_question", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.data) {
                const branch = data.data.find(b => b.id == id);
                if (branch) {
                    document.getElementById("question").value = branch.question;

                    // Show update button and hide save button
                    save_question_btn.style.display = "none";
                    update_question_btn.style.display = "inline-block";

                    // Show the modal
                    modal.show();

                    // Set up the update button click event
                    update_question_btn.onclick = function() {
                        updateBranch(id);
                    };
                }
            }
        })
        .catch(error => {
            console.error("Error fetching API:", error);
            alert("Something went wrong!");
        });
}

function updateBranch(id) {
    var question = document.getElementById("question").value.trim();

    // Basic validation
    if (!question) {
        alert("Please fill all the fields.");
        return;
    }

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/user_security_question.php";
    const apiUrl = BASE_API_URL + "/user_security_question.php";

   // const apiUrl = "http://localhost/frutopia/api/user_security_question.php";

    // ✅ Create FormData object
    const formData = new FormData();
    formData.append("question", question);
    formData.append("id", id);
    formData.append("update_question", "1");

    fetch(apiUrl, {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data && data.Code === 200) {
                // alert(data.msg);
                document.getElementById("question").value = "";

                // Reset buttons
                save_question_btn.style.display = "inline-block";
                update_question_btn.style.display = "none";

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
</script>