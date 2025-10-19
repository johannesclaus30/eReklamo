// ====================== CATEGORY COLORS ======================
const categoryColors = {
  'Infrastructure': 'rgba(255, 107, 53, 0.8)',
  'Public Safety': 'rgba(59, 130, 246, 0.8)',
  'Utilities': 'rgba(16, 185, 129, 0.8)',
  'Environment': 'rgba(245, 158, 11, 0.8)',
  'Health and Sanitation': 'rgba(239, 68, 68, 0.8)',
  'Traffic and Transportation': 'rgba(139, 92, 246, 0.8)',
  'Administrative': 'rgba(236, 72, 153, 0.8)',
  'Community Services': 'rgba(20, 184, 166, 0.8)',
  'Peace and Order': 'rgba(34, 197, 94, 0.8)',
  'Others': 'rgba(156, 163, 175, 0.8)'
};

const categoryBorderColors = Object.fromEntries(
  Object.entries(categoryColors).map(([k, v]) => [k, v.replace('0.8', '1')])
);

const getCategoryColor = c => categoryColors[c] || 'rgba(158,158,158,0.8)';
const getCategoryBorder = c => categoryBorderColors[c] || 'rgba(158,158,158,1)';

// ====================== GLOBAL VARIABLES ======================
let complaints = [];
let selectedComplaint = null;
let complaintChart = null;
let subcategoryChart = null;

const statusColors = {
  'pending': 'status-pending',
  'in-progress': 'status-in-progress',
  'resolved': 'status-resolved',
  'rejected': 'status-rejected'
};

// ====================== FETCH DATA ======================
window.addEventListener('DOMContentLoaded', () => {
  fetch('get_complaints.php')
    .then(res => res.json())
    .then(data => {
      if (!Array.isArray(data)) throw new Error('Invalid JSON response');

      complaints = data.map(c => ({
        ...c,
        region: c.region || 'Unknown Region',
        province: c.province || 'Unknown Province',
        city: c.city || 'Unknown City',
        barangay: c.barangay || 'Unknown Barangay'
      }));

      updateStats();
      renderComplaints(complaints);
      initializeCharts();
      populateRegionFilter();

      // Ensure dependent selects are reset and disabled initially
      resetSelect(document.getElementById('chartProvinceFilter'), 'Provinces', true);
      resetSelect(document.getElementById('chartCityFilter'), 'Cities', true);
      resetSelect(document.getElementById('chartBarangayFilter'), 'Barangays', true);

      updateChartWithFilters();
    })
    .catch(err => {
      console.error('Error:', err);
      showToast('Failed to load complaints from database.', 'error');
    });

  // Filter event listeners
  document.getElementById('chartRegionFilter').addEventListener('change', handleRegionChange);
  document.getElementById('chartProvinceFilter').addEventListener('change', handleProvinceChange);
  document.getElementById('chartCityFilter').addEventListener('change', handleCityChange);
  document.getElementById('chartBarangayFilter').addEventListener('change', updateChartWithFilters);
  document.getElementById('chartPeriod').addEventListener('change', updateChartWithFilters);
  document.getElementById('clearChartFilters').addEventListener('click', clearAllChartFilters);
});

// ====================== STATS ======================
function updateStats() {
  const total = complaints.length;
  const pending = complaints.filter(c => c.status === 'pending').length;
  const progress = complaints.filter(c => c.status === 'in-progress').length;
  const resolved = complaints.filter(c => c.status === 'resolved').length;

  document.getElementById('totalCount').textContent = total;
  document.getElementById('pendingCount').textContent = pending;
  document.getElementById('progressCount').textContent = progress;
  document.getElementById('resolvedCount').textContent = resolved;
}

// ====================== TABLE ======================
function renderComplaints(data) {
  const tbody = document.getElementById('complaintsTableBody');
  const emptyState = document.getElementById('emptyState');
  if (data.length === 0) {
    tbody.innerHTML = '';
    emptyState.style.display = 'table-row';
    return;
  }
  emptyState.style.display = 'none';
  tbody.innerHTML = data.map(c => `
    <tr>
      <td>${c.trackingNumber}</td>
      <td><div class="category-cell">
        <div class="category-main">${c.category}</div>
        <div class="category-sub">${c.subcategory || '—'}</div>
      </div></td>
      <td title="${c.description}">${c.description}</td>
      <td>${c.location}</td>
      <td>${formatDate(c.dateSubmitted)}</td>
      <td>
        <select class="status-select ${statusColors[c.status]}"
                onchange="handleStatusChange('${c.id}', this.value)">
          <option value="pending" ${c.status==='pending'?'selected':''}>Pending</option>
          <option value="in-progress" ${c.status==='in-progress'?'selected':''}>In Progress</option>
          <option value="resolved" ${c.status==='resolved'?'selected':''}>Resolved</option>
          <option value="rejected" ${c.status==='rejected'?'selected':''}>Rejected</option>
        </select>
      </td>
      <td>
        <div class="action-buttons">
          <button class="btn-action btn-view" onclick="viewComplaintDetails('${c.id}')">View</button>
          ${c.status==='resolved'?`<button class="btn-action btn-archive" onclick="archiveComplaint('${c.id}')">Archive</button>`:''}
        </div>
      </td>
    </tr>
  `).join('');
}

function filterComplaints() {
  const term = document.getElementById('searchInput').value.toLowerCase();
  const status = document.getElementById('statusFilter').value;

  const filtered = complaints.filter(c =>
    (c.trackingNumber.toLowerCase().includes(term) ||
     c.description.toLowerCase().includes(term) ||
     c.location.toLowerCase().includes(term) ||
     c.category.toLowerCase().includes(term)) &&
    (status === 'all' || c.status === status)
  );

  renderComplaints(filtered);
}

// ====================== STATUS + ARCHIVE ======================
function handleStatusChange(id, status) {
  complaints = complaints.map(c => c.id == id ? {...c, status} : c);
  updateStats();
  filterComplaints();
  updateChartWithFilters();
  showToast('Status updated successfully!', 'success');
}

function archiveComplaint(id) {
  complaints = complaints.filter(c => c.id != id);
  updateStats();
  filterComplaints();
  updateChartWithFilters();
  showToast('Complaint archived successfully!', 'success');
}

// ====================== MODAL ======================
function viewComplaintDetails(id) {
  const c = complaints.find(x => x.id == id);
  if (!c) return;
  selectedComplaint = c;
  document.getElementById('modalTrackingNumber').textContent = c.trackingNumber;
  document.getElementById('modalCategory').textContent = c.category;
  document.getElementById('modalSubcategory').textContent = c.subcategory || '—';
  document.getElementById('modalDescription').textContent = c.description;
  document.getElementById('modalLocation').textContent = c.location;
  document.getElementById('modalSubmittedBy').textContent = c.submittedBy;
  document.getElementById('modalDateSubmitted').textContent = formatDate(c.dateSubmitted);
  const s = document.getElementById('modalStatusSelect');
  s.value = c.status;
  s.className = `status-select ${statusColors[c.status]}`;
  document.getElementById('detailsModal').style.display = 'flex';
}

function closeDetailsModal() {
  document.getElementById('detailsModal').style.display = 'none';
  selectedComplaint = null;
}

// ====================== CHARTS ======================
function initializeCharts() {
  const ctxMain = document.getElementById('complaintChart').getContext('2d');
  complaintChart = new Chart(ctxMain, {
    type: 'bar',
    data: { 
      labels: [], 
      datasets: [{
        label: 'Complaints',
        data: [],
        backgroundColor: [],
        borderColor: [],
        borderWidth: 2,
        borderRadius: 8,
        maxBarThickness: 72
      }]
    },
    options: { 
      responsive: true,
      maintainAspectRatio: false, // allow full-width/height fill of parent
      layout: { padding: { top: 8, right: 8, bottom: 0, left: 8 } },
      plugins: { legend: { display: false } },
      scales: { 
        x: { grid: { display: false } },
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });

  const ctxSub = document.getElementById('subcategoryChart').getContext('2d');
  subcategoryChart = new Chart(ctxSub, {
    type: 'bar',
    data: { 
      labels: [], 
      datasets: [{
        label: 'Subcategories',
        data: [],
        backgroundColor: [],
        borderColor: [],
        borderWidth: 2,
        maxBarThickness: 36
      }]
    },
    options: { 
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { beginAtZero: true, ticks: { precision: 0 } },
        y: { grid: { display: false } }
      }
    }
  });
}

function updateCharts(data) {
  const catCounts = {};
  const subCounts = {};
  data.forEach(c => {
    catCounts[c.category] = (catCounts[c.category] || 0) + 1;
    const subName = c.subcategory || '—';
    const subKey = `${c.category} - ${subName}`;
    subCounts[subKey] = (subCounts[subKey] || 0) + 1;
  });

  const catLabels = Object.keys(catCounts);
  const catVals = Object.values(catCounts);
  const subLabels = Object.keys(subCounts);
  const subVals = Object.values(subCounts);

  complaintChart.data.labels = catLabels;
  complaintChart.data.datasets[0].data = catVals;
  complaintChart.data.datasets[0].backgroundColor = catLabels.map(getCategoryColor);
  complaintChart.data.datasets[0].borderColor = catLabels.map(getCategoryBorder);
  complaintChart.update();

  subcategoryChart.data.labels = subLabels;
  subcategoryChart.data.datasets[0].data = subVals;
  subcategoryChart.data.datasets[0].backgroundColor = subLabels.map(l => getCategoryColor(l.split(' - ')[0]));
  subcategoryChart.data.datasets[0].borderColor = subLabels.map(l => getCategoryBorder(l.split(' - ')[0]));
  subcategoryChart.update();
}

// ====================== FILTER HELPERS ======================
function filterByPeriod(list, period) {
  if (period === 'all') return list;
  const now = new Date();
  return list.filter(c => {
    const d = new Date(c.dateSubmitted);
    const diff = (now - d) / (1000 * 60 * 60 * 24);
    if (period === 'week') return diff <= 7;
    if (period === 'month') return diff <= 30;
    if (period === 'year') return diff <= 365;
    return true;
  });
}

// Utility to reset a select element with a default "All ..." option and set disabled state
function resetSelect(el, label, disabled = true) {
  el.innerHTML = `<option value="all">All ${label}</option>`;
  el.value = 'all';
  el.disabled = disabled;
}

function populateRegionFilter() {
  const sel = document.getElementById('chartRegionFilter');
  sel.innerHTML = '<option value="all">All Regions</option>';
  [...new Set(complaints.map(c => c.region))]
    .filter(Boolean)
    .sort()
    .forEach(r => {
      const o = document.createElement('option'); o.value = r; o.textContent = r; sel.appendChild(o);
    });
}

function handleRegionChange() {
  const region = document.getElementById('chartRegionFilter').value;
  const provSel = document.getElementById('chartProvinceFilter');
  const citySel = document.getElementById('chartCityFilter');
  const brgySel = document.getElementById('chartBarangayFilter');

  // Reset and disable all downstream selects first
  resetSelect(provSel, 'Provinces', true);
  resetSelect(citySel, 'Cities', true);
  resetSelect(brgySel, 'Barangays', true);

  if (region !== 'all') {
    const provinces = [...new Set(complaints.filter(c => c.region === region).map(c => c.province))]
      .filter(Boolean)
      .sort();
    provinces.forEach(p => {
      const o = document.createElement('option'); o.value = p; o.textContent = p; provSel.appendChild(o);
    });
    provSel.disabled = false; // enable province when region is selected
  }

  updateChartWithFilters();
}

function handleProvinceChange() {
  const region = document.getElementById('chartRegionFilter').value;
  const prov = document.getElementById('chartProvinceFilter').value;
  const citySel = document.getElementById('chartCityFilter');
  const brgySel = document.getElementById('chartBarangayFilter');

  // Reset downstream selects
  resetSelect(citySel, 'Cities', true);
  resetSelect(brgySel, 'Barangays', true);

  if (prov !== 'all' && region !== 'all') {
    const cities = [...new Set(complaints
      .filter(c => c.region === region && c.province === prov)
      .map(c => c.city))]
      .filter(Boolean)
      .sort();

    cities.forEach(ci => {
      const o = document.createElement('option'); o.value = ci; o.textContent = ci; citySel.appendChild(o);
    });
    citySel.disabled = false; // enable city when province is selected
  }

  updateChartWithFilters();
}

function handleCityChange() {
  const region = document.getElementById('chartRegionFilter').value;
  const prov = document.getElementById('chartProvinceFilter').value;
  const city = document.getElementById('chartCityFilter').value;
  const brgySel = document.getElementById('chartBarangayFilter');

  // Reset barangay
  resetSelect(brgySel, 'Barangays', true);

  if (city !== 'all' && prov !== 'all' && region !== 'all') {
    const brgys = [...new Set(complaints
      .filter(c => c.region === region && c.province === prov && c.city === city)
      .map(c => c.barangay))]
      .filter(Boolean)
      .sort();

    brgys.forEach(b => {
      const o = document.createElement('option'); o.value = b; o.textContent = b; brgySel.appendChild(o);
    });
    brgySel.disabled = false; // enable barangay when city is selected
  }

  updateChartWithFilters();
}

function updateChartWithFilters() {
  const period = document.getElementById('chartPeriod').value;
  const region = document.getElementById('chartRegionFilter').value;
  const prov = document.getElementById('chartProvinceFilter').value;
  const city = document.getElementById('chartCityFilter').value;
  const brgy = document.getElementById('chartBarangayFilter').value;

  let filtered = filterByPeriod(complaints, period);
  filtered = filtered.filter(c =>
    (region === 'all' || c.region === region) &&
    (prov === 'all' || c.province === prov) &&
    (city === 'all' || c.city === city) &&
    (brgy === 'all' || c.barangay === brgy)
  );
  updateCharts(filtered);
}

// ====================== UTILS ======================
function formatDate(d) {
  if (!d) return '—';
  const date = new Date(d);
  return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function clearAllChartFilters() {
  document.getElementById('chartPeriod').value = 'all';
  document.getElementById('chartRegionFilter').value = 'all';

  resetSelect(document.getElementById('chartProvinceFilter'), 'Provinces', true);
  resetSelect(document.getElementById('chartCityFilter'), 'Cities', true);
  resetSelect(document.getElementById('chartBarangayFilter'), 'Barangays', true);

  updateChartWithFilters();
  showToast('All filters cleared.', 'info');
}

function showToast(msg, type='info') {
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.classList.add('show'), 10);
  setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 300); }, 3000);
}