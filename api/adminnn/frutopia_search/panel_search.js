/**
 * ============================================================
 * FILE: frutopia_search/panel_search.js
 * PAGE: panel.php
 * KAAM: Header search bar mein Panel ID type karo →
 *       sirf wo row dikhegi, baaki sab hide ho jaayengi.
 * ============================================================
 *
 * JS KAISE KAAM KARTA HAI — Step by step:
 *
 * STEP 1: Page Check
 *   - Sabse pehle URL dekha jaata hai.
 *   - Agar URL mein "panel.php" nahi hai to script wahan
 *     ruk jaati hai (return). Doosre pages pe kuch nahi hoga.
 *
 * STEP 2: Search Input Listen karna
 *   - header.php ke search input (#globalSearchInput) pe
 *     'input' event lagaya jata hai.
 *   - Har keypress pe 300ms ka debounce hota hai — matlab
 *     ek baar type karna band karo, 300ms baad search chalega.
 *   - Enter dabaoge to turant search hoga.
 *
 * STEP 3: Table Rows Filter karna
 *   - tbody (#branchTableBody) ke andar saari <tr> rows li jaati hain.
 *   - Har row ke 2nd <td> (Panel ID column, index=1) ka text
 *     search query se compare hota hai.
 *   - Match → row dikhti hai + green highlight
 *   - No match → row hide (display:none) ho jaati hai
 *
 * STEP 4: Reset
 *   - Search box khali karo → fetchData() call hota hai
 *     jo original table wapas laata hai.
 * ============================================================
 */

(function () {

  // STEP 1: Sirf panel.php pe chalao
  var isPanelPage = window.location.pathname.toLowerCase().indexOf('panel.php') !== -1;
  if (!isPanelPage) return;

  var searchDebounce = null;

  document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('globalSearchInput');
    if (!searchInput) return;

    // Placeholder change karo is page ke liye
    searchInput.placeholder = 'Search Panel ID...';

    // STEP 2A: Type karne pe 300ms baad search
    searchInput.addEventListener('input', function () {
      clearTimeout(searchDebounce);
      var query = this.value.trim();

      if (query === '') {
        clearPanelSearch(); // Khali karo to reset
        return;
      }

      searchDebounce = setTimeout(function () {
        filterPanelRows(query);
      }, 300);
    });

    // STEP 2B: Enter press pe turant search
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        clearTimeout(searchDebounce);
        var query = this.value.trim();
        if (query !== '') filterPanelRows(query);
      }
    });
  });

  /**
   * STEP 3: Panel ID ke basis pe rows filter karo
   * @param {string} query - search string
   */
  function filterPanelRows(query) {
    var tbody = document.getElementById('branchTableBody');
    if (!tbody) return;

    var rows = Array.from(tbody.querySelectorAll('tr'));
    if (rows.length === 0) return;

    // Purana "no result" row remove karo
    var oldMsg = document.getElementById('panel-no-result-row');
    if (oldMsg) oldMsg.remove();

    // Sab rows pehle reset karo
    rows.forEach(function (row) {
      row.classList.remove('panel-search-highlight');
      row.style.display = '';
    });

    var PANEL_ID_COL = 1; // 0=Sr No, 1=Panel ID
    var matchedRows   = [];
    var unmatchedRows = [];

    // Har row check karo
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

    // Unmatched rows HIDE karo
    unmatchedRows.forEach(function (row) {
      row.style.display = 'none';
    });

    // Koi match nahi mila?
    if (matchedRows.length === 0) {
      var noRow = document.createElement('tr');
      noRow.id  = 'panel-no-result-row';
      noRow.innerHTML =
        "<td colspan='5' class='text-center text-danger py-3'>" +
        "<i class='ph ph-magnifying-glass me-1'></i>" +
        "No panel found for \"" + query + "\"</td>";
      tbody.insertBefore(noRow, tbody.firstChild);
      return;
    }

    // Matched rows TOP pe lao + highlight
    matchedRows.forEach(function (row, index) {
      row.classList.add('panel-search-highlight');
      var firstTd = row.querySelector('td');
      if (firstTd) firstTd.textContent = index + 1;
      tbody.insertBefore(row, tbody.firstChild);
    });

    // Table ke upar scroll karo
    var card = tbody.closest('.table-responsive') || tbody.closest('.card');
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  /**
   * STEP 4: Search clear karo — original table restore karo
   */
  function clearPanelSearch() {
    var oldMsg = document.getElementById('panel-no-result-row');
    if (oldMsg) oldMsg.remove();
    // panel.php ka fetchData() call karo
    if (typeof fetchData === 'function') fetchData();
  }

})();
