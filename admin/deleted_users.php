<?php include('./header.php'); ?>

<div class="page-header">
  <div class="page-block">
    <div class="row align-items-center">
      <div class="col-md-12">
        <div class="page-header-title">
          <h5 class="mb-0">Dashboard - Deleted Users</h5>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container mt-3 table-scroll">
  <h4>Deleted User List</h4>

  <!-- Export Button -->
  <button id="exportBtn" class="btn btn-primary mb-3" style="display:none;">Export CSV</button>

  <!-- Placeholder for table -->
  <div id="alertTable"></div>
  

<!-- Answer Modal -->
<div class="modal fade" id="answerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">User Answers</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
        <div id="answerModalBody">
           Loading...
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

</div>

<?php include('./footer.php'); ?>

<script>
    const BASE_API_URL = "<?php echo BASE_API_URL; ?>";

document.addEventListener("DOMContentLoaded", function () {
    const apiUrl = BASE_API_URL + "/get_deleted_user.php";

    const container = document.getElementById("alertTable");
    const exportBtn = document.getElementById("exportBtn");

    let currentPage = 1;
    const limit = 50;

    function loadUsers(page) {
        currentPage = page;
        const fetchUrl = apiUrl + "?page=" + page + "&limit=" + limit;
        
        container.innerHTML = "Loading data...";
        
        fetch(fetchUrl)
            .then(response => response.json())
            .then(data => {
                if (data && data.data && data.data.length > 0) {
                    let table = "<table id='dataTable' class='table table-bordered table-striped'>";
                    table += "<thead><tr>";

                    // Headers
                    Object.keys(data.data[0]).forEach(header => {
                        table += "<th>" + header + "</th>";
                    });
                    table += "<th>Action</th>";
                    table += "</tr></thead><tbody>";

                    // Rows
                    data.data.forEach(row => {
                        table += "<tr>";
                        Object.values(row).forEach(cell => {
                            table += "<td>" + (cell !== null ? cell : "") + "</td>";
                        });
                        table += "<td><button class='btn btn-info btn-sm' onclick='viewUserAnswers(" + row.userid + ")'>View Answer</button></td>";
                        table += "</tr>";
                    });

                    table += "</tbody></table>";
                    
                    // Pagination controls
                    let paginationHtml = "<div class='d-flex justify-content-between align-items-center mt-3'>";
                    if(data.pagination) {
                        paginationHtml += "<div>Showing page " + data.pagination.current_page + " of " + data.pagination.total_pages + " (" + data.pagination.total_records + " total records)</div>";
                        paginationHtml += "<div>";
                        if (data.pagination.current_page > 1) {
                            paginationHtml += "<button class='btn btn-secondary btn-sm me-2' onclick='window.loadUsers(" + (data.pagination.current_page - 1) + ")'>Previous</button>";
                        }
                        if (data.pagination.current_page < data.pagination.total_pages) {
                            paginationHtml += "<button class='btn btn-secondary btn-sm' onclick='window.loadUsers(" + (data.pagination.current_page + 1) + ")'>Next</button>";
                        }
                        paginationHtml += "</div>";
                    }
                    paginationHtml += "</div>";
                    
                    container.innerHTML = table + paginationHtml;

                    // Show export button
                    exportBtn.style.display = "inline-block";
                } else {
                    container.innerHTML = "<p class='text-danger'>No data found or API error.</p>";
                }
            })
            .catch(error => {
                console.error("Error fetching API:", error);
                container.innerHTML = "<p class='text-danger'>Failed to load data.</p>";
            });
    }

    // Initial load
    loadUsers(1);
    
    // Expose to window so onclick handlers can reach it
    window.loadUsers = loadUsers;

    // Export CSV Function
    exportBtn.addEventListener("click", function () {
        exportBtn.innerHTML = "Exporting...";
        exportBtn.disabled = true;
        
        fetch(apiUrl + "?export=true")
            .then(response => response.json())
            .then(data => {
                if (data && data.data && data.data.length > 0) {
                    let csv = [];
                    // Headers
                    let headers = Object.keys(data.data[0]);
                    csv.push(headers.map(h => '"' + h + '"').join(","));
                    
                    // Rows
                    data.data.forEach(row => {
                        let rowData = Object.values(row).map(val => {
                            let text = (val !== null ? String(val) : "").replace(/"/g, '""');
                            return '"' + text + '"';
                        });
                        csv.push(rowData.join(","));
                    });
                    
                    let csvFile = new Blob([csv.join("\n")], { type: "text/csv" });
                    let link = document.createElement("a");
                    link.href = URL.createObjectURL(csvFile);
                    link.download = "deleted_users_list_full.csv";
                    link.click();
                } else {
                    alert("No data to export");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Failed to export data");
            })
            .finally(() => {
                exportBtn.innerHTML = "Export CSV";
                exportBtn.disabled = false;
            });
    });
    
    // View Answer logic
    window.viewUserAnswers = function(userid) {
        let answerModalElement = document.getElementById('answerModal');
        let modalBody = document.getElementById('answerModalBody');
        
        let answerModal = bootstrap.Modal.getInstance(answerModalElement);
        if (!answerModal) {
            answerModal = new bootstrap.Modal(answerModalElement);
        }
        
        modalBody.innerHTML = '<div class="text-center">Loading answers...</div>';
        answerModal.show();
        
        fetch(BASE_API_URL + "/get_user_answers.php?userid=" + userid)
            .then(response => response.json())
            .then(data => {
                if (data && data.Code === 200 && data.data && data.data.length > 0) {
                    let ansTable = "<table class='table table-bordered table-sm'>";
                    ansTable += "<thead><tr><th>Question</th><th>Answer</th><th>Date</th></tr></thead><tbody>";
                    
                    data.data.forEach(item => {
                        ansTable += "<tr>";
                        ansTable += "<td>" + (item.question ?? '') + "</td>";
                        ansTable += "<td>" + (item.answer ?? '') + "</td>";
                        ansTable += "<td>" + (item.created_at ?? '') + "</td>";
                        ansTable += "</tr>";
                    });
                    
                    ansTable += "</tbody></table>";
                    modalBody.innerHTML = ansTable;
                } else {
                    modalBody.innerHTML = "<p class='text-danger text-center'>No answers found for this user.</p>";
                }
            })
            .catch(error => {
                console.error("Error fetching answers:", error);
                modalBody.innerHTML = "<p class='text-danger text-center'>Failed to fetch answers.</p>";
            });
    };
});
</script>
