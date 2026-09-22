<?php
include(__DIR__ . '/baseurl.php');
include('./header.php');
?>

<style>
/* Premium Table Layout Styling */
.table-responsive {
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    background: #fff;
    overflow-x: auto;
}

.table {
    margin-bottom: 0;
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
}

.table th {
    background-color: #f8fafc;
    color: #475569;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding: 14px 16px;
    border-top: none;
    border-bottom: 2px solid #e2e8f0 !important;
    white-space: nowrap;
    text-align: left;
}

.table td {
    padding: 14px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
    color: #334155;
    white-space: nowrap;
}

.table tbody tr {
    transition: background-color 0.2s ease;
}

.table tbody tr:hover {
    background-color: #f8fafc;
}

.card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.card-body {
    padding: 1.5rem;
}

/* Custom Scrollbar */
.table-responsive::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}
.table-responsive::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Badge Custom Styling */
.badge {
    border-radius: 6px;
    font-size: 0.75rem;
    padding: 5px 10px;
}

/* Custom Calendar Input Group Styling */
.input-group input[type="date"] {
    border-right: 0;
    cursor: pointer;
}
.input-group input[type="date"]:focus {
    border-color: #86b7fe;
    box-shadow: none;
}
.input-group input[type="date"]:focus + .input-group-text {
    border-color: #86b7fe;
}
.input-group-text {
    border-color: #dee2e6;
    background-color: #fff;
    cursor: pointer;
}
input:disabled + .input-group-text {
    background-color: #e9ecef !important;
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<div class="row mb-4">

    <div class="col-md-2">

        <select class="form-control" id="filter">

            <option value="day">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="custom">Custom Date</option>

        </select>

    </div>

    <div class="col-md-2">

        <div class="input-group">
            <input
                type="date"
                class="form-control"
                id="from_date"
                disabled>
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
        </div>

    </div>

    <div class="col-md-2">

        <div class="input-group">
            <input
                type="date"
                class="form-control"
                id="to_date"
                disabled>
            <span class="input-group-text"><i class="bi bi-calendar3"></i></span>
        </div>

    </div>

    <div class="col-md-2">

        <button
            class="btn btn-primary w-100"
            id="search_btn">

            Search

        </button>

    </div>

    <div class="col-md-2">

        <button
            class="btn btn-success w-100"
            id="excel_btn">

            Download Excel

        </button>

    </div>

</div>



<div class="card mt-4">
    <div class="card-body pb-0">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Site Name</th>
                        <th>Device Name</th>
                        <th>Device Number</th>
                        <th>OTP</th>
                        <th>Send To</th>
                        <th>Send By</th>
                        <th>Send At</th>
                        <th>Requested Time</th>
                        <th>Wait Time</th>
                        <th>Mobile No</th>
                        <th>Status</th>
                        <th>OTP Type</th>
                        <th>Reason</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex align-items-center justify-content-between flex-wrap gap-3 bg-transparent border-top-0 pt-3 pb-3">
        <div class="text-muted small" id="showingInfo">
            Showing 0 to 0 of 0 records
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small text-nowrap">Show:</span>
                <select class="form-select form-select-sm w-auto" id="perPageSelect" style="padding: 2px 8px; font-size: 0.8rem; border-radius: 4px;">
                    <option value="10">10</option>
                    <option value="25" selected>25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm mb-0" id="paginationNav">
                    <!-- Page items will be rendered dynamically here -->
                </ul>
            </nav>
        </div>
    </div>
</div>

<script>
// Use relative path so it is environment independent (works locally and in production)
const BASE_API_URL = "<?php echo BASE_API_URL; ?>";
const apiUrl = BASE_API_URL + "/alerts_reports_api.php";
// const API = "../api/alerts_reports_api.php";

function getFilters() {
    return {
        filter: document.getElementById("filter").value,
        from_date: document.getElementById("from_date").value,
        to_date: document.getElementById("to_date").value
    };
}

let currentPage = 1;
let perPage = 25;

function loadData(page = 1) {
    currentPage = page;
    const f = getFilters();
    const tableBody = document.getElementById("tableBody");
    tableBody.innerHTML = `
        <tr>
            <td colspan="14" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                Loading reports...
            </td>
        </tr>
    `;

    fetch(apiUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        body: new URLSearchParams({
            filter: f.filter,
            from_date: f.from_date,
            to_date: f.to_date,
            Page: currentPage,
            perpg: perPage
        })
    })
    .then(r => r.json())
    .then(res => {
        console.log(res);

        let html = "";

        if (res.data && res.data.length > 0) {
            res.data.forEach(row => {

                let statusBadge = "";
                if (row.requested_status == 1) {
                    statusBadge = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fw-semibold">Used</span>`;
                } else if (row.requested_status == 2) {
                    statusBadge = `<span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fw-semibold">Rejected</span>`;
                } else {
                    statusBadge = `<span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 fw-semibold">Un Used</span>`;
                }

                html += `
                    <tr>
                        <td class="fw-semibold text-dark">${row.client_name || '-'}</td>
                        <td>${row.site_name || '-'}</td>
                        <td>${row.device_name || '-'}</td>
                        <td class="font-monospace text-muted">${row.device_number || '-'}</td>
                        <td class="font-monospace text-center">${row.otp || '-'}</td>
                        <td>${row.send_to || '-'}</td>
                        <td>${row.send_by || '-'}</td>
                        <td>${row.send_at || '-'}</td>
                        <td>${row.requested_time || '-'}</td>
                        <td class="font-monospace">${row.wait_time || '-'}</td>
                        <td class="font-monospace">${row.mobile_no || '-'}</td>
                        <td>${statusBadge}</td>
                        <td><span class="badge bg-info-subtle text-info border border-info-subtle px-2.5 py-1 fw-semibold">${row.otp_type || '-'}</span></td>
                        <td class="text-wrap text-start" style="max-width: 300px; white-space: normal;">${row.reason || '-'}</td>
                    </tr>
                `;
            });
        } else {
            html = `
                <tr>
                    <td colspan="14" class="text-center text-muted py-4">No OTP requests found for the selected period.</td>
                </tr>
            `;
        }

        tableBody.innerHTML = html;

        renderPagination(res);
    })
    .catch(err => {
        console.error(err);
        tableBody.innerHTML = `
            <tr>
                <td colspan="14" class="text-center text-danger py-4">Failed to load reports. Please try again later.</td>
            </tr>
        `;
    });
}

function renderPagination(res) {
    const totalPages = res.total_pages || 1;
    const totalRecords = res.total_records || 0;
    const showingFrom = res.showing_from || 0;
    const showingTo = res.showing_to || 0;

    document.getElementById("showingInfo").innerText = `Showing ${showingFrom} to ${showingTo} of ${totalRecords} records`;

    const paginationNav = document.getElementById("paginationNav");
    paginationNav.innerHTML = "";

    if (totalPages <= 1) {
        return;
    }

    // Previous Button
    const prevLi = document.createElement("li");
    prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); if(${currentPage} > 1) loadData(${currentPage - 1});">&laquo;</a>`;
    paginationNav.appendChild(prevLi);

    // Page Numbers
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (startPage > 1) {
        const firstLi = document.createElement("li");
        firstLi.className = `page-item`;
        firstLi.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); loadData(1);">1</a>`;
        paginationNav.appendChild(firstLi);

        if (startPage > 2) {
            const ellipsisLi = document.createElement("li");
            ellipsisLi.className = "page-item disabled";
            ellipsisLi.innerHTML = `<span class="page-link">...</span>`;
            paginationNav.appendChild(ellipsisLi);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement("li");
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); loadData(${i});">${i}</a>`;
        paginationNav.appendChild(li);
    }

    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            const ellipsisLi = document.createElement("li");
            ellipsisLi.className = "page-item disabled";
            ellipsisLi.innerHTML = `<span class="page-link">...</span>`;
            paginationNav.appendChild(ellipsisLi);
        }

        const lastLi = document.createElement("li");
        lastLi.className = `page-item`;
        lastLi.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); loadData(${totalPages});">${totalPages}</a>`;
        paginationNav.appendChild(lastLi);
    }

    // Next Button
    const nextLi = document.createElement("li");
    nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="event.preventDefault(); if(${currentPage} < ${totalPages}) loadData(${currentPage + 1});">&raquo;</a>`;
    paginationNav.appendChild(nextLi);
}

document.getElementById("filter").addEventListener("change", function () {
    const custom = this.value === "custom";
    document.getElementById("from_date").disabled = !custom;
    document.getElementById("to_date").disabled = !custom;
    if (!custom) {
        loadData(1);
    }
});

document.getElementById("from_date").addEventListener("change", function() {
    if (document.getElementById("to_date").value) {
        loadData(1);
    }
});

document.getElementById("to_date").addEventListener("change", function() {
    if (document.getElementById("from_date").value) {
        loadData(1);
    }
});

document.getElementById("perPageSelect").addEventListener("change", function() {
    perPage = parseInt(this.value);
    loadData(1);
});

document.getElementById("search_btn").addEventListener("click", function() {
    loadData(1);
});

document.getElementById("excel_btn").addEventListener("click", function () {
    const f = getFilters();
    const form = document.createElement("form");
    form.method = "POST";
    form.action = apiUrl;
    form.target = "_blank";
    form.innerHTML = `
        <input name="action" value="excel">
        <input name="filter" value="${f.filter}">
        <input name="from_date" value="${f.from_date}">
        <input name="to_date" value="${f.to_date}">
    `;
    document.body.appendChild(form);
    form.submit();
    form.remove();
});

// Forward click on calendar icon span to open the native date picker
document.querySelectorAll(".input-group-text").forEach(span => {
    span.addEventListener("click", function() {
        const input = this.previousElementSibling;
        if (input && input.type === "date" && !input.disabled) {
            input.showPicker();
        }
    });
});

loadData(1);
</script>

<?php include('./footer.php'); ?>