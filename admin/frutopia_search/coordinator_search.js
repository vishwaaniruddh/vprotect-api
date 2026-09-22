/**
 * ============================================================
 * FILE: frutopia_search/coordinator_search.js
 * PAGE: assign_coordinator.php
 * KAAM: Header search bar mein Panel ID type karo →
 *       sirf matching rows dikhegi, baaki sab hide.
 * ============================================================
 *
 * JS KAISE KAAM KARTA HAI — Step by step:
 *
 * STEP 1: Page Check
 *   - URL mein "assign_coordinator.php" check hota hai.
 *   - Nahi mila to script ruk jaati hai.
 *
 * STEP 2: Search Input Listen karna
 *   - #globalSearchInput pe 'input' event lagata hai.
 *   - 300ms debounce search.
 *   - Enter → turant search.
 *
 * STEP 3: Table Rows Filter karna
 *   assign_coordinator.php ka tbody ID: "offlineOtpTableBody"
 *   Column map:
 *     0 = Sr No
 *     1 = Panel ID   ← search yahan hoga
 *     2 = Coordinator Name
 *     3 = Status
 *
 * STEP 4: Reset
 *   - Search khali karo → fetchData() call hoga.
 * ============================================================
 */

(function () {

  // STEP 1: Sirf assign_coordinator.php pe chalao
  var isCoordPage = window.location.pathname.toLowerCase().indexOf('assign_coordinator.php') !== -1;
  if (!isCoordPage) return;

  var searchDebounce = null;

  document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('globalSearchInput');
    if (!searchInput) return;

    // Placeholder is page ke hisaab se
    searchInput.placeholder = 'Search Panel ID...';

    // STEP 2A: Type karne pe debounce search
    searchInput.addEventListener('input', function () {
      clearTimeout(searchDebounce);
      var query = this.value.trim();

      if (query === '') {
        clearCoordSearch();
        return;
      }

      searchDebounce = setTimeout(function () {
        filterCoordRows(query);
      }, 300);
    });

    // STEP 2B: Enter press pe turant search
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        clearTimeout(searchDebounce);
        var query = this.value.trim();
        if (query !== '') filterCoordRows(query);
      }
    });
  });

  /**
   * STEP 3: Panel ID ke basis pe rows filter karo
   * @param {string} query
   */
  function filterCoordRows(query) {
    var tbody = document.getElementById('offlineOtpTableBody');
    if (!tbody) return;

    var rows = Array.from(tbody.querySelectorAll('tr'));
    if (rows.length === 0) return;

    // Purana "no result" row remove karo
    var oldMsg = document.getElementById('coord-no-result-row');
    if (oldMsg) oldMsg.remove();

    // Sab rows reset
    rows.forEach(function (row) {
      row.classList.remove('panel-search-highlight');
      row.style.display = '';
    });

    var PANEL_ID_COL = 1; // 0=Sr No, 1=Panel ID

    var matchedRows   = [];
    var unmatchedRows = [];

    rows.forEach(function (row) {
      var cells    = row.querySelectorAll('td');
      var cellText = cells.length > PANEL_ID_COL
                       ? cells[PANEL_ID_COL].textContent.trim()
                       : '';

      if (cellText.toLowerCase().indexOf(query.toLowerCase()) !== -1) {
        matchedRows.push(row);
      } else {
        unmatchedRows.push(row);
      }
    });

    // Unmatched rows HIDE
    unmatchedRows.forEach(function (row) {
      row.style.display = 'none';
    });

    // Koi match nahi?
    if (matchedRows.length === 0) {
      var noRow = document.createElement('tr');
      noRow.id  = 'coord-no-result-row';
      noRow.innerHTML =
        "<td colspan='4' class='text-center text-danger py-3'>" +
        "<i class='ph ph-magnifying-glass me-1'></i>" +
        "No panel found for \"" + query + "\"</td>";
      tbody.insertBefore(noRow, tbody.firstChild);
      return;
    }

    // Matched rows TOP pe + highlight
    matchedRows.forEach(function (row, index) {
      row.classList.add('panel-search-highlight');
      var firstTd = row.querySelector('td');
      if (firstTd) firstTd.textContent = index + 1;
      tbody.insertBefore(row, tbody.firstChild);
    });

    // Scroll to table
    var card = tbody.closest('.table-responsive') || tbody.closest('.card');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  /**
   * STEP 4: Search clear → table refresh
   */
  function clearCoordSearch() {
    var oldMsg = document.getElementById('coord-no-result-row');
    if (oldMsg) oldMsg.remove();
    if (typeof fetchData === 'function') fetchData();
  }

})();
