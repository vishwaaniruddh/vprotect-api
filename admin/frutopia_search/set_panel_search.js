/**
 * ============================================================
 * FILE: frutopia_search/set_panel_search.js
 * PAGE: set_panel.php
 * KAAM: Header search bar mein Panel ID ya Name type karo →
 *       sirf matching rows dikhegi, baaki sab hide.
 * ============================================================
 *
 * TABLE STRUCTURE (set_panel.php):
 *   tbody ID : offlineOtpTableBody
 *   Columns  : 0=Sr No | 1=Name | 2=Panel ID | 3=Status
 *
 * SEARCH COLUMNS:
 *   - Name     (col 1) — user ka naam search
 *   - Panel ID (col 2) — panel id search
 *   Dono mein OR logic — kisi mein bhi match mile chalega
 *
 * NOTE:
 *   set_panel.php mein ek apna local #searchInput bhi hai.
 *   Ye script header wale #globalSearchInput ko handle karti hai.
 *   Dono ek saath kaam kar sakte hain.
 * ============================================================
 */

(function () {

  // STEP 1: Sirf set_panel.php pe chalao
  var isSetPanelPage = window.location.pathname.toLowerCase().indexOf('set_panel.php') !== -1;
  if (!isSetPanelPage) return;

  var searchDebounce = null;

  document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('globalSearchInput');
    if (!searchInput) return;

    // Placeholder is page ke hisaab se
    searchInput.placeholder = 'Search Name / Panel ID...';

    // STEP 2A: Type karne pe 300ms debounce search
    searchInput.addEventListener('input', function () {
      clearTimeout(searchDebounce);
      var query = this.value.trim();

      if (query === '') {
        clearSetPanelSearch();
        return;
      }

      searchDebounce = setTimeout(function () {
        filterSetPanelRows(query);
      }, 300);
    });

    // STEP 2B: Enter press pe turant search
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        clearTimeout(searchDebounce);
        var query = this.value.trim();
        if (query !== '') filterSetPanelRows(query);
      }
    });
  });

  /**
   * STEP 3: Name ya Panel ID ke basis pe rows filter karo
   * @param {string} query
   */
  function filterSetPanelRows(query) {
    var tbody = document.getElementById('offlineOtpTableBody');
    if (!tbody) {
      console.warn('[FRUtopia Search] set_panel.php: offlineOtpTableBody not found. Data load hone ka wait karo.');
      return;
    }

    var rows = Array.from(tbody.querySelectorAll('tr'));
    if (rows.length === 0) return;

    // Purana "no result" row remove karo
    var oldMsg = document.getElementById('setpanel-no-result-row');
    if (oldMsg) oldMsg.remove();

    // Sab rows reset
    rows.forEach(function (row) {
      row.classList.remove('panel-search-highlight');
      row.style.display = '';
    });

    // Column index
    var NAME_COL     = 1; // Name
    var PANEL_ID_COL = 2; // Panel ID

    var matchedRows   = [];
    var unmatchedRows = [];

    rows.forEach(function (row) {
      var cells     = row.querySelectorAll('td');
      var nameText  = cells.length > NAME_COL     ? cells[NAME_COL].textContent.trim()     : '';
      var panelText = cells.length > PANEL_ID_COL ? cells[PANEL_ID_COL].textContent.trim() : '';

      // Name ya Panel ID — kisi mein bhi match mile
      var isMatch =
        nameText.toLowerCase().indexOf(query.toLowerCase())  !== -1 ||
        panelText.toLowerCase().indexOf(query.toLowerCase()) !== -1;

      if (isMatch) {
        matchedRows.push(row);
      } else {
        unmatchedRows.push(row);
      }
    });

    // Unmatched rows HIDE karo
    unmatchedRows.forEach(function (row) {
      row.style.display = 'none';
    });

    // Koi match nahi?
    if (matchedRows.length === 0) {
      var noRow = document.createElement('tr');
      noRow.id  = 'setpanel-no-result-row';
      noRow.innerHTML =
        "<td colspan='4' class='text-center text-danger py-3'>" +
        "<i class='ph ph-magnifying-glass me-1'></i>" +
        "No record found for \"" + query + "\"</td>";
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

    // Smooth scroll to table
    var card = tbody.closest('.table-responsive') || tbody.closest('.card');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  /**
   * STEP 4: Search clear → page 1 se fresh data laao
   */
  function clearSetPanelSearch() {
    var oldMsg = document.getElementById('setpanel-no-result-row');
    if (oldMsg) oldMsg.remove();
    // set_panel.php ka fetchData(1) call karo
    if (typeof fetchData === 'function') fetchData(1);
  }

})();
