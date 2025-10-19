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

// Notifications state
let notifications = []; // latest complaints (non-archived)
const NOTIF_READ_KEY = 'ereklamo_notif_read_ids';

// If your media folders are one level up from /admin, keep '../'.
// If they are in the same folder, set to ''.
const MEDIA_BASE = '../';

const statusColors = {
  'pending': 'status-pending',
  'in-progress': 'status-in-progress',
  'resolved': 'status-resolved',
  'rejected': 'status-rejected',
  'archived': 'status-rejected'
};

const normStatus = s => {
  const v = String(s || '').toLowerCase();
  return v === 'archive' ? 'archived' : v;
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
        barangay: c.barangay || 'Unknown Barangay',
        status: normStatus(c.status)
      }));

      updateStats();
      renderMainAndArchivedTables();
      initializeCharts();
      populateRegionFilter();

      resetSelect(document.getElementById('chartProvinceFilter'), 'Provinces', true);
      resetSelect(document.getElementById('chartCityFilter'), 'Cities', true);
      resetSelect(document.getElementById('chartBarangayFilter'), 'Barangays', true);

      updateChartWithFilters();
      rebuildNotifications();
    })
    .catch(err => {
      console.error('Error:', err);
      showToast('Failed to load complaints from database.', 'error');
    });

  // Chart filters
  document.getElementById('chartRegionFilter').addEventListener('change', handleRegionChange);
  document.getElementById('chartProvinceFilter').addEventListener('change', handleProvinceChange);
  document.getElementById('chartCityFilter').addEventListener('change', handleCityChange);
  document.getElementById('chartBarangayFilter').addEventListener('change', updateChartWithFilters);
  document.getElementById('chartPeriod').addEventListener('change', updateChartWithFilters);
  document.getElementById('clearChartFilters').addEventListener('click', clearAllChartFilters);

  // Table filters
  document.getElementById('searchInput')?.addEventListener('input', filterComplaints);
  document.getElementById('statusFilter')?.addEventListener('change', filterComplaints);

  // Toggles
  document.getElementById('toggleSubcategoryBtn')?.addEventListener('click', toggleSubcategoryChart);
  document.getElementById('toggleArchivedBtn')?.addEventListener('click', toggleArchivedSection);

  // Close notifications when clicking outside
  document.addEventListener('click', (e) => {
    const container = document.querySelector('.notification-container');
    const dropdown = document.getElementById('notificationDropdown');
    if (!container || !dropdown) return;
    if (!container.contains(e.target)) dropdown.classList.remove('show');
  });

  // Expose globals for inline handlers used in HTML
  Object.assign(window, {
    toggleSubcategoryChart,
    toggleArchivedSection,
    handleModalStatusChange,
    handleStatusChange,
    archiveComplaint,
    unarchiveComplaint,
    viewComplaintDetails,
    closeDetailsModal,
    clearAllChartFilters,

    // Notifications
    toggleNotifications,
    closeNotifications,
    markAllAsRead,

    // Lightbox
    closeLightbox
  });
});

// ====================== STATS ======================
function updateStats() {
  const active = complaints.filter(c => c.status !== 'archived');
  document.getElementById('totalCount').textContent = active.length;
  document.getElementById('pendingCount').textContent = active.filter(c => c.status === 'pending').length;
  document.getElementById('progressCount').textContent = active.filter(c => c.status === 'in-progress').length;
  document.getElementById('resolvedCount').textContent = active.filter(c => c.status === 'resolved').length;
}

// ====================== TABLES ======================
function renderComplaints(data, bodyId = 'complaintsTableBody', emptyId = 'emptyState', archivedView = false) {
  const tbody = document.getElementById(bodyId);
  const emptyState = document.getElementById(emptyId);
  if (!tbody) return;

  if (!data || data.length === 0) {
    tbody.innerHTML = '';
    if (emptyState) emptyState.style.display = 'table-row-group';
    return;
  }
  if (emptyState) emptyState.style.display = 'none';

  tbody.innerHTML = data.map(c => `
    <tr>
      <td>${c.trackingNumber}</td>
      <td><div class="category-cell">
        <div class="category-main">${c.category}</div>
        <div class="category-sub">${c.subcategory || '—'}</div>
      </div></td>
      <td title="${escapeHtml(c.description || '')}">${escapeHtml(c.description || '')}</td>
      <td>${escapeHtml(c.location || '')}</td>
      <td>${formatDate(c.dateSubmitted)}</td>
      <td>
        ${archivedView ? `
          <span class="status-select ${statusColors[c.status] || ''}" style="padding:6px 12px;display:inline-block">${c.status}</span>
        ` : `
          <select class="status-select ${statusColors[c.status] || ''}"
                  onchange="handleStatusChange('${c.id}', this.value)">
            <option value="pending" ${c.status==='pending'?'selected':''}>Pending</option>
            <option value="in-progress" ${c.status==='in-progress'?'selected':''}>In Progress</option>
            <option value="resolved" ${c.status==='resolved'?'selected':''}>Resolved</option>
            <option value="rejected" ${c.status==='rejected'?'selected':''}>Rejected</option>
          </select>
        `}
      </td>
      <td>
        <div class="action-buttons">
          <button class="btn-action btn-view" onclick="viewComplaintDetails('${c.id}')">View</button>
          ${archivedView
            ? `<button class="btn-action" onclick="unarchiveComplaint('${c.id}')">Restore</button>`
            : `<button class="btn-action btn-archive" onclick="archiveComplaint('${c.id}')">Archive</button>`
          }
        </div>
      </td>
    </tr>
  `).join('');
}

function renderMainAndArchivedTables() {
  const archived = complaints.filter(c => c.status === 'archived');
  filterComplaints(); // main active only
  renderComplaints(archived, 'archivedTableBody', 'archivedEmptyState', true);
}

function filterComplaints() {
  const term = (document.getElementById('searchInput')?.value || '').toLowerCase();
  const status = document.getElementById('statusFilter')?.value || 'all';
  const active = complaints.filter(c => c.status !== 'archived');

  const filtered = active.filter(c =>
    (
      (c.trackingNumber || '').toLowerCase().includes(term) ||
      (c.description || '').toLowerCase().includes(term) ||
      (c.location || '').toLowerCase().includes(term) ||
      (c.category || '').toLowerCase().includes(term) ||
      (c.subcategory || '').toLowerCase().includes(term)
    ) &&
    (status === 'all' || c.status === status)
  );

  renderComplaints(filtered, 'complaintsTableBody', 'emptyState', false);
}

// ====================== STATUS + ARCHIVE ======================
async function handleStatusChange(id, status) {
  status = normStatus(status);

  complaints = complaints.map(c => c.id == id ? { ...c, status } : c);

  if (selectedComplaint && selectedComplaint.id == id) {
    selectedComplaint.status = status;
    const s = document.getElementById('modalStatusSelect');
    if (s) s.className = `status-select ${statusColors[status] || ''}`;
  }

  updateStats();
  updateChartWithFilters();
  renderMainAndArchivedTables();
  rebuildNotifications();

  try {
    await saveComplaintStatus(id, status);
    showToast('Status saved to database.', 'success');
  } catch (e) {
    console.error(e);
    showToast('Failed to save status to database.', 'error');
  }
}

function handleModalStatusChange(status) {
  if (!selectedComplaint) return;
  handleStatusChange(selectedComplaint.id, status);
}

async function archiveComplaint(id) {
  await handleStatusChange(id, 'archived');
}

async function unarchiveComplaint(id) {
  await handleStatusChange(id, 'pending');
}

async function saveComplaintStatus(id, status) {
  const res = await fetch('update_complaint_status.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ id, status })
  });
  const data = await res.json().catch(() => ({}));
  if (!res.ok || !data.success) throw new Error(data.error || 'Request failed');
}

// ====================== MODAL + MEDIA ======================
function clearModalMedia() {
  const container = document.getElementById('modalMediaContainer');
  const empty = document.getElementById('modalMediaEmpty');
  if (container) container.innerHTML = '';
  if (empty) empty.style.display = 'block';
}

async function loadComplaintMedia(complaintId) {
  const container = document.getElementById('modalMediaContainer');
  const empty = document.getElementById('modalMediaEmpty');
  if (!container) return;

  // Reset
  container.innerHTML = '';
  if (empty) empty.style.display = 'none';

  try {
    const res = await fetch(`get_complaint_media.php?complaint_id=${encodeURIComponent(complaintId)}`, { headers: { 'Accept': 'application/json' } });
    const data = await res.json().catch(() => null);
    if (!data || !data.success) throw new Error(data?.error || 'Failed to load media');

    if (data.type === 'video' && data.url) {
      const v = document.createElement('video');
      v.controls = true;
      v.src = MEDIA_BASE + data.url;
      v.style.width = '100%';
      v.style.maxHeight = '320px';
      v.style.borderRadius = '8px';
      v.addEventListener('click', () => openLightboxVideo(v.src));
      container.appendChild(v);
    } else if (data.type === 'images' && Array.isArray(data.urls) && data.urls.length) {
      const grid = document.createElement('div');
      grid.style.display = 'grid';
      grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(120px, 1fr))';
      grid.style.gap = '8px';

      data.urls.forEach(u => {
        const img = document.createElement('img');
        img.src = MEDIA_BASE + u;
        img.alt = 'Attachment';
        img.style.width = '100%';
        img.style.height = '120px';
        img.style.objectFit = 'cover';
        img.style.borderRadius = '8px';
        img.style.cursor = 'pointer';
        img.addEventListener('click', () => openLightboxImage(img.src));
        grid.appendChild(img);
      });

      container.appendChild(grid);
    } else {
      if (empty) empty.style.display = 'block';
    }
  } catch (e) {
    console.error(e);
    if (empty) {
      empty.textContent = 'Failed to load attachments.';
      empty.style.display = 'block';
    }
  }
}

function openLightboxImage(src) {
  const lb = document.getElementById('mediaLightbox');
  const img = document.getElementById('lightboxImage');
  const vid = document.getElementById('lightboxVideo');
  if (!lb || !img || !vid) return;
  vid.style.display = 'none';
  vid.pause?.();
  img.src = src;
  img.style.display = 'block';
  lb.style.display = 'flex';
}

function openLightboxVideo(src) {
  const lb = document.getElementById('mediaLightbox');
  const img = document.getElementById('lightboxImage');
  const vid = document.getElementById('lightboxVideo');
  if (!lb || !img || !vid) return;
  img.style.display = 'none';
  vid.src = src;
  vid.style.display = 'block';
  lb.style.display = 'flex';
}

function closeLightbox() {
  const lb = document.getElementById('mediaLightbox');
  const img = document.getElementById('lightboxImage');
  const vid = document.getElementById('lightboxVideo');
  if (vid) { vid.pause?.(); vid.src = ''; }
  if (img) { img.src = ''; }
  if (lb) lb.style.display = 'none';
}

function viewComplaintDetails(id) {
  const c = complaints.find(x => x.id == id);
  if (!c) return;
  selectedComplaint = c;

  document.getElementById('modalTrackingNumber').textContent = c.trackingNumber || '';
  document.getElementById('modalCategory').textContent = c.category || '';
  document.getElementById('modalSubcategory').textContent = c.subcategory || '—';
  document.getElementById('modalDescription').textContent = c.description || '';
  document.getElementById('modalLocation').textContent = c.location || '';
  document.getElementById('modalSubmittedBy').textContent = c.submittedBy || '';
  document.getElementById('modalDateSubmitted').textContent = formatDate(c.dateSubmitted);

  const s = document.getElementById('modalStatusSelect');
  if (s) {
    s.value = c.status;
    s.className = `status-select ${statusColors[c.status] || ''}`;
  }

  // Load media
  clearModalMedia();
  loadComplaintMedia(c.id);

  document.getElementById('detailsModal').style.display = 'flex';
}

function closeDetailsModal() {
  document.getElementById('detailsModal').style.display = 'none';
  selectedComplaint = null;
  clearModalMedia();
  closeLightbox();
}

// ====================== CHARTS ======================
function initializeCharts() {
  const ctxMain = document.getElementById('complaintChart').getContext('2d');
  complaintChart = new Chart(ctxMain, {
    type: 'bar',
    data: { labels: [], datasets: [{ label: 'Complaints', data: [], backgroundColor: [], borderColor: [], borderWidth: 2, borderRadius: 8, maxBarThickness: 72 }] },
    options: { responsive: true, maintainAspectRatio: false, layout: { padding: { top: 8, right: 8, bottom: 0, left: 8 } }, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  const ctxSub = document.getElementById('subcategoryChart').getContext('2d');
  subcategoryChart = new Chart(ctxSub, {
    type: 'bar',
    data: { labels: [], datasets: [{ label: 'Subcategories', data: [], backgroundColor: [], borderColor: [], borderWidth: 2, maxBarThickness: 36 }] },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } } }
  });
}

function updateCharts(data) {
  const active = data.filter(c => c.status !== 'archived');

  const catCounts = {};
  const subCounts = {};
  active.forEach(c => {
    catCounts[c.category] = (catCounts[c.category] || 0) + 1;
    const subName = c.subcategory || '—';
    const subKey = `${c.category} - ${subName}`;
    subCounts[subKey] = (subCounts[subKey] || 0) + 1;
  });

  const catLabels = Object.keys(catCounts);
  const subLabels = Object.keys(subCounts);

  complaintChart.data.labels = catLabels;
  complaintChart.data.datasets[0].data = catLabels.map(l => catCounts[l]);
  complaintChart.data.datasets[0].backgroundColor = catLabels.map(getCategoryColor);
  complaintChart.data.datasets[0].borderColor = catLabels.map(getCategoryBorder);
  complaintChart.update();

  subcategoryChart.data.labels = subLabels;
  subcategoryChart.data.datasets[0].data = subLabels.map(l => subCounts[l]);
  subcategoryChart.data.datasets[0].backgroundColor = subLabels.map(l => getCategoryColor(l.split(' - ')[0]));
  subcategoryChart.data.datasets[0].borderColor = subLabels.map(l => getCategoryBorder(l.split(' - ')[0]));
  subcategoryChart.update();
}

// ====================== SUBCATEGORY TOGGLE ======================
function toggleSubcategoryChart() {
  const section = document.getElementById('subcategoryChartSection');
  const btn = document.getElementById('toggleSubcategoryBtn');
  const label = btn ? btn.querySelector('.toggle-label') : null;

  if (!section) {
    showToast('Subcategory panel is missing in the page.', 'error');
    return;
  }

  const willShow = getComputedStyle(section).display === 'none';
  section.style.display = willShow ? 'block' : 'none';
  if (btn) btn.classList.toggle('active', willShow);
  if (label) label.textContent = willShow ? 'Hide Detailed Breakdown' : 'View Detailed Breakdown by Subcategory';

  if (willShow) {
    updateChartWithFilters();
    requestAnimationFrame(() => {
      if (subcategoryChart) { subcategoryChart.resize(); subcategoryChart.update(); }
      section.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  }
}

// ====================== ARCHIVED SECTION TOGGLE ======================
function toggleArchivedSection() {
  const s = document.getElementById('archivedSection');
  if (!s) { showToast('Archived section not found.', 'error'); return; }
  renderMainAndArchivedTables();
  s.style.display = (getComputedStyle(s).display === 'none') ? 'block' : 'none';
}

// ====================== NOTIFICATIONS ======================
function rebuildNotifications() {
  const active = complaints.filter(c => c.status !== 'archived');
  notifications = [...active].sort((a, b) => new Date(b.dateSubmitted) - new Date(a.dateSubmitted)).slice(0, 10);
  renderNotificationList();
}

function getReadSet() {
  try {
    const arr = JSON.parse(localStorage.getItem(NOTIF_READ_KEY) || '[]');
    return new Set(arr.map(String));
  } catch {
    return new Set();
  }
}

function saveReadSet(set) {
  const arr = Array.from(set);
  localStorage.setItem(NOTIF_READ_KEY, JSON.stringify(arr));
}

function renderNotificationList() {
  const list = document.getElementById('notificationList');
  const badge = document.getElementById('notificationBadge');
  if (!list || !badge) return;

  const read = getReadSet();
  const itemsHtml = notifications.map(n => {
    const isUnread = !read.has(String(n.id));
    const title = `${escapeHtml(n.category || 'Complaint')}${n.subcategory ? ' • ' + escapeHtml(n.subcategory) : ''}`;
    const desc = escapeHtml(n.description || '');
    const loc = escapeHtml(n.location || '');
    const time = timeAgo(n.dateSubmitted);
    const status = escapeHtml(n.status || '');

    return `
      <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${n.id}" onclick="openNotification(${n.id})">
        <div class="notification-content">
          <div class="notification-title">
            <span class="notification-dot"></span>${title}
          </div>
          <div class="notification-desc">${desc}</div>
          <div class="notification-meta">
            <div class="notification-location">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="10" r="3"></circle><path d="M12 2a8 8 0 0 0-8 8c0 7 8 12 8 12s8-5 8-12a8 8 0 0 0-8-8z"/></svg>
              ${loc}
            </div>
            <span class="notification-time">${time}</span>
            <span class="notification-status">${status}</span>
          </div>
        </div>
      </div>
    `;
  }).join('');

  list.innerHTML = itemsHtml;

  const unreadCount = notifications.reduce((acc, n) => acc + (getReadSet().has(String(n.id)) ? 0 : 1), 0);
  badge.textContent = unreadCount > 99 ? '99+' : String(unreadCount);
  badge.style.display = unreadCount > 0 ? 'inline-flex' : 'none';
}

function openNotification(id) {
  const read = getReadSet();
  read.add(String(id));
  saveReadSet(read);
  renderNotificationList();
  viewComplaintDetails(id);
  closeNotifications();
}

function toggleNotifications() {
  const dropdown = document.getElementById('notificationDropdown');
  if (!dropdown) return;
  rebuildNotifications();
  dropdown.classList.toggle('show');
}

function closeNotifications() {
  const dropdown = document.getElementById('notificationDropdown');
  dropdown?.classList.remove('show');
}

function markAllAsRead() {
  const read = getReadSet();
  notifications.forEach(n => read.add(String(n.id)));
  saveReadSet(read);
  renderNotificationList();
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

function resetSelect(el, label, disabled = true) {
  el.innerHTML = `<option value="all">All ${label}</option>`;
  el.value = 'all';
  el.disabled = disabled;
}

function populateRegionFilter() {
  const sel = document.getElementById('chartRegionFilter');
  sel.innerHTML = '<option value="all">All Regions</option>';
  [...new Set(complaints.map(c => c.region))].filter(Boolean).sort()
    .forEach(r => { const o = document.createElement('option'); o.value = r; o.textContent = r; sel.appendChild(o); });
}

function handleRegionChange() {
  const region = document.getElementById('chartRegionFilter').value;
  const provSel = document.getElementById('chartProvinceFilter');
  const citySel = document.getElementById('chartCityFilter');
  const brgySel = document.getElementById('chartBarangayFilter');

  resetSelect(provSel, 'Provinces', true);
  resetSelect(citySel, 'Cities', true);
  resetSelect(brgySel, 'Barangays', true);

  if (region !== 'all') {
    const provinces = [...new Set(complaints.filter(c => c.region === region).map(c => c.province))].filter(Boolean).sort();
    provinces.forEach(p => { const o = document.createElement('option'); o.value = p; o.textContent = p; provSel.appendChild(o); });
    provSel.disabled = false;
  }

  updateChartWithFilters();
}

function handleProvinceChange() {
  const region = document.getElementById('chartRegionFilter').value;
  const prov = document.getElementById('chartProvinceFilter').value;
  const citySel = document.getElementById('chartCityFilter');
  const brgySel = document.getElementById('chartBarangayFilter');

  resetSelect(citySel, 'Cities', true);
  resetSelect(brgySel, 'Barangays', true);

  if (prov !== 'all' && region !== 'all') {
    const cities = [...new Set(complaints.filter(c => c.region === region && c.province === prov).map(c => c.city))].filter(Boolean).sort();
    cities.forEach(ci => { const o = document.createElement('option'); o.value = ci; o.textContent = ci; citySel.appendChild(o); });
    citySel.disabled = false;
  }

  updateChartWithFilters();
}

function handleCityChange() {
  const region = document.getElementById('chartRegionFilter').value;
  const prov = document.getElementById('chartProvinceFilter').value;
  const city = document.getElementById('chartCityFilter').value;
  const brgySel = document.getElementById('chartBarangayFilter');

  resetSelect(brgySel, 'Barangays', true);

  if (city !== 'all' && prov !== 'all' && region !== 'all') {
    const brgys = [...new Set(complaints.filter(c => c.region === region && c.province === prov && c.city === city).map(c => c.barangay))].filter(Boolean).sort();
    brgys.forEach(b => { const o = document.createElement('option'); o.value = b; o.textContent = b; brgySel.appendChild(o); });
    brgySel.disabled = false;
  }

  updateChartWithFilters();
}

function updateChartWithFilters() {
  const period = document.getElementById('chartPeriod').value;
  const region = document.getElementById('chartRegionFilter').value;
  const prov = document.getElementById('chartProvinceFilter').value;
  const city = document.getElementById('chartCityFilter').value;
  const brgy = document.getElementById('chartBarangayFilter').value;

  let filtered = complaints.filter(c => c.status !== 'archived');
  filtered = filterByPeriod(filtered, period);
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

function escapeHtml(str) {
  return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}

function timeAgo(dateStr) {
  const d = new Date(dateStr);
  const now = new Date();
  const sec = Math.floor((now - d) / 1000);
  if (sec < 60) return `${sec}s ago`;
  const min = Math.floor(sec / 60);
  if (min < 60) return `${min}m ago`;
  const hr = Math.floor(min / 60);
  if (hr < 24) return `${hr}h ago`;
  const day = Math.floor(hr / 24);
  if (day < 30) return `${day}d ago`;
  const mon = Math.floor(day / 30);
  if (mon < 12) return `${mon}mo ago`;
  const yr = Math.floor(mon / 12);
  return `${yr}y ago`;
}