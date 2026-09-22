/**
 * ============================================================
 * FILE: frutopia_search/alerts_search.js
 * PAGE: alerts.php
 * KAAM: Header search bar mein Panel ID ya Alert ID type karo →
 *       sirf matching rows dikhegi, baaki sab hide.
 * ============================================================
 *
 * JS KAISE KAAM KARTA HAI — Step by step:
 *
 * STEP 1: Page Check
 *   - URL mein "alerts.php" check hota hai.
 *   - Nahi mila to script yahan ruk jaati hai.
 *
 * STEP 2: Search Input Listen karna
 *   - #globalSearchInput pe 'input' event lagata hai.
 *   - 300ms debounce → type band karo, tab search chalega.
 *   - Enter key → turant search.
 *
 * STEP 3: Table Rows Filter karna
 *   alerts.php mein table dynamically banta hai (innerHTML se),
 *   isliye hum #dataTable ke andar tbody rows lete hain.
 *   Column map:
 *     0 = Sr No
 *     1 = Alert ID   ← search yahan bhi
 *     2 = Panel ID   ← aur yahan bhi
 *   Dono columns mein search hota hai (OR logic).
 *
 * STEP 4: Reset
 *   - Search khali karo → fetchData() call hoga,
 *     table fresh data se rebuild hoga.
 *
 * SPECIAL NOTE (alerts.php):
 *   alerts.php mein table dynamically banta hai (fetchData ke baad).
 *   Isliye search tab kaam karta hai jab table ban chuki ho.
 *   Agar table nahi mili to "Table not ready" message console mein aayega.
 * ============================================================
 */

(function () {

  // STEP 1: Sirf alerts.php pe chalao
  var isAlertsPage = window.location.pathname.toLowerCase().indexOf('alerts.php') !== -1;
  if (!isAlertsPage) return;

  var searchDebounce = null;

  document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('globalSearchInput');
    if (!searchInput) return;

    // Placeholder is page ke hisaab se
    searchInput.placeholder = 'Search Alert ID / Panel ID...';

    // STEP 2A: Type karne pe debounce search
    searchInput.addEventListener('input', function () {
      clearTimeout(searchDebounce);
      var query = this.value.trim();

      if (query === '') {
        clearAlertsSearch();
        return;
      }

      searchDebounce = setTimeout(function () {
        filterAlertRows(query);
      }, 300);
    });

    // STEP 2B: Enter press pe turant search
    searchInput.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        clearTimeout(searchDebounce);
        var query = this.value.trim();
        if (query !== '') filterAlertRows(query);
      }
    });
  });

  /**
   * STEP 3: Alert ID ya Panel ID ke basis pe rows filter karo
   * @param {string} query
   */
  function filterAlertRows(query) {

    // alerts.php mein table dynamically banta hai
    var table = document.getElementById('dataTable');
    if (!table) {
      console.warn('[FRUtopia Search] alerts.php: dataTable not found yet. Wait for data to load.');
      return;
    }

    var tbody = table.querySelector('tbody');
    if (!tbody) return;

    var rows = Array.from(tbody.querySelectorAll('tr'));
    if (rows.length === 0) return;

    // Purana "no result" row remove karo
    var oldMsg = document.getElementById('alerts-no-result-row');
    if (oldMsg) oldMsg.remove();

    // Sab rows reset
    rows.forEach(function (row) {
      row.classList.remove('panel-search-highlight');
      row.style.display = '';
    });

    // Column index:
    // 0=Sr No, 1=Alert ID, 2=Panel ID, 3=Branch, ...
    var ALERT_ID_COL = 1;
    var PANEL_ID_COL = 2;

    var matchedRows   = [];
    var unmatchedRows = [];

    rows.forEach(function (row) {
      var cells     = row.querySelectorAll('td');
      var alertText = cells.length > ALERT_ID_COL ? cells[ALERT_ID_COL].textContent.trim() : '';
      var panelText = cells.length > PANEL_ID_COL ? cells[PANEL_ID_COL].textContent.trim() : '';

      // Alert ID ya Panel ID mein se kisi mein bhi match mile
      var isMatch =
        alertText.toLowerCase().indexOf(query.toLowerCase()) !== -1 ||
        panelText.toLowerCase().indexOf(query.toLowerCase()) !== -1;

      if (isMatch) {
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
      noRow.id  = 'alerts-no-result-row';
      noRow.innerHTML =
        "<td colspan='10' class='text-center text-danger py-3'>" +
        "<i class='ph ph-magnifying-glass me-1'></i>" +
        "No alert found for \"" + query + "\"</td>";
      tbody.insertBefore(noRow, tbody.firstChild);
      return;
    }

    // Matched rows TOP pe
    matchedRows.forEach(function (row, index) {
      row.classList.add('panel-search-highlight');
      var firstTd = row.querySelector('td');
      if (firstTd) firstTd.textContent = index + 1;
      tbody.insertBefore(row, tbody.firstChild);
    });

    // Scroll to table
    var card = tbody.closest('.table-responsive') || table;
    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  /**
   * STEP 4: Search clear → table refresh
   */
  function clearAlertsSearch() {
    var oldMsg = document.getElementById('alerts-no-result-row');
    if (oldMsg) oldMsg.remove();
    if (typeof fetchData === 'function') fetchData();
  }

})();
