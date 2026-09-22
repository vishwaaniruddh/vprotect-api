<?php include('./header.php');

$_user_id = $_SESSION['user_id'];
$edit_id = $_GET['id'] ?? '';

?>

<div class="container">

   <div class="card">
         <div class="card-header fw-bold text-light bg-secondary
                d-flex justify-content-between align-items-center">
        <span>Coordinator Details</span>

        <button type="button"
                class="btn btn-sm btn-light me-2"
                onclick="window.location.href='add_coordinator.php'"
                title="Back to Location">Back
            <i class="bi bi-arrow-left"></i>
        </button>

    </div>
    
        <div class="card-body">
            <form id="userForm">
                <div class="row g-3">

                <input type="hidden" id="user_id">

                     <!-- Customer -->
                    
                    <!-- User Name -->
                    <div class="col-md-6">
                        <label class="fw-bold">Name *</label>
                        <input type="text" class="form-control" placeholder="Full Name" id="name">
                    </div>

                    <!-- Email -->
                    <div class="col-md-6">
                        <label class="fw-bold">Email ID *</label>
                        <input type="email" class="form-control" placeholder="Email ID" id="email_id">
                    </div>

                    <!-- Mobile -->
                    <div class="col-md-6">
                        <label class="fw-bold">Mobile No. *</label>
                        <input type="number" class="form-control" placeholder="+91" id="contact_no"  maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,10);">
                    </div>
                    
                   <!-- Device User ID -->
                    <div class="col-md-6">
                        <label class="fw-bold">Password*</label>
                        <input type="text" class="form-control" placeholder=" User Password" id="password">
                    </div>
                    
                    <!-- Role -->
                    <div class="col-md-6">
                        <label class="fw-bold">Role *</label>
                        <select class="form-control" id="user_role">
                            <option value="">Select Role</option>
                            <option value="7">Coordinator</option>
                        </select>
                    </div>
                    <!-- Branch Mapping -->
                    <div class="col-md-6">
                        <label class="fw-bold">Assign Branch *</label>
                        <select class="form-control" id="branch_id">
                            <option value="">Select Branch</option>
                            <!-- Fetched dynamically -->
                        </select>
                    </div>

                </div>

                <!-- Buttons -->
                <div class="mt-4 text-end">
                    <button type="reset" class="btn btn-outline-danger"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                    <button type="button" class="btn btn-success" id="save_btn"><i class="bi bi-arrow-bar-up"></i> &nbsp; Save User</button>
                    <button type="button" class="btn btn-success" id="update_btn">Update User</button>
                </div>
            </form>
        </div>
    </div>

</div>




<script>
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
document.addEventListener("DOMContentLoaded", function () {

    const editId = "<?php echo $edit_id; ?>";
    
    // First load branches, then check if we need to load user data to pre-fill it
    populateBranches().then(() => {
        if (editId) {
            document.getElementById("save_btn").style.display = "none";
            document.getElementById("update_btn").style.display = "inline-block";
            getUserById(editId);
        } else {
            document.getElementById("save_btn").style.display = "inline-block";
            document.getElementById("update_btn").style.display = "none";
        }
    });

});

async function populateBranches() {
    const apiUrl = BASE_API_URL + "/branch_manager.php";
    const formData = new FormData();
    formData.append("get_active_branch_codes", "1");

    try {
        const res = await fetch(apiUrl, { method: "POST", body: formData });
        const data = await res.json();
        
        if (data.Code === 200 && data.data) {
            let html = '<option value="">Select Branch</option>';
            data.data.forEach(branch => {
                html += `<option value="${branch.id}">${branch.branch_name} (${branch.branch_code})</option>`;
            });
            document.getElementById("branch_id").innerHTML = html;
        }
    } catch (err) {
        console.error("Error fetching branches:", err);
    }
}


document.getElementById("save_btn").addEventListener("click", function () {

   
    const username          = document.getElementById("name").value.trim();
    const email             = document.getElementById("email_id").value.trim();
    const mobile            = document.getElementById("contact_no").value.trim();
    const password          = document.getElementById("password").value.trim();
    const role              = document.getElementById("user_role").value.trim();
    const branchId          = document.getElementById("branch_id").value.trim();


        let errors = [];
        
      
        if (!username) errors.push("Username");
        if (!email) errors.push("Email");
        if (!mobile) errors.push("Mobile Number");
        if (!password) errors.push("Password");
        if (!role) errors.push("Role");
        if (!branchId) errors.push("Assign Branch");
       
        
        if (errors.length > 0) {
            alert("Please fill required fields:\n\n" + errors.join(", "));
            return;
        }

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/coordinator_manage.php";
    const apiUrl = BASE_API_URL + "/coordinator_manage.php";

    const formData = new FormData();

   
    formData.append("name", username);
    formData.append("email_id", email);
    formData.append("contact_no", mobile);
    formData.append("user_role", role);
    formData.append("password", password);
    formData.append("branch_id", branchId);
   
    formData.append("save_user", "1");

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log(data);
        if (data.Code === 200) {
            document.getElementById("userForm").reset();
            alert(data.msg);
            window.location = "./add_coordinator.php";
        } else {
            alert(data.msg);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Something went wrong!");
    });
});


document.getElementById("update_btn").addEventListener("click", function () {

    const id = document.getElementById("user_id").value;
    const username          = document.getElementById("name").value.trim();
    const email             = document.getElementById("email_id").value.trim();
    const mobile            = document.getElementById("contact_no").value.trim();
    const password          = document.getElementById("password").value.trim();
    const role              = document.getElementById("user_role").value.trim();
    const branchId          = document.getElementById("branch_id").value.trim();
    

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/coordinator_manage.php";
     const apiUrl = BASE_API_URL + "/coordinator_manage.php";
    const formData = new FormData();

    formData.append("user_id",id);
    formData.append("name", username);
    formData.append("email_id", email);
    formData.append("contact_no", mobile);
    formData.append("user_role", role);
    formData.append("password", password);
    formData.append("branch_id", branchId);
   
    formData.append("update_user_details", "1");

    fetch(apiUrl, {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.Code === 200) {

            document.getElementById("userForm").reset();
            alert(data.msg);
            window.location="./add_coordinator.php";

        } else {
            alert(data.msg);
        }
    });
});


async function getUserById(id) {

    // const apiUrl = "https://sarsspl.com/FRUtopia/api/coordinator_manage.php";
    const apiUrl = BASE_API_URL + "/coordinator_manage.php";
    const formData = new FormData();

    formData.append("get_user_by_id", "1");
    formData.append("id", id);

    const res = await fetch(apiUrl, { method: "POST", body: formData });
    const data = await res.json();

    if (data.Code === 200 && data.data) {

        const row = data.data;

        document.getElementById("user_id").value = id;
        document.getElementById("name").value = row.name;
        document.getElementById("email_id").value = row.email_id;
        document.getElementById("contact_no").value = row.contact_no;
        document.getElementById("user_role").value = row.user_role;
        document.getElementById("password").value = row.password; // optional
        if(row.branch_id) {
            document.getElementById("branch_id").value = row.branch_id;
        }
    }
}



</script>

<?php include('./footer.php'); ?>

