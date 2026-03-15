// ── Sidebar HTML (injected in every page) ──
function getSidebarHTML(activePage) {
  const links = [
    { href: 'index.html',    icon: '📊', label: 'Dashboard',  id: 'dashboard' },
    { href: 'members.html',  icon: '👥', label: 'Anëtarët',   id: 'members'   },
    { href: 'trainers.html', icon: '🏋️', label: 'Trajnerët',  id: 'trainers'  },
    { href: 'classes.html',  icon: '📅', label: 'Klasat',     id: 'classes'   },
    { href: 'payments.html', icon: '💳', label: 'Pagesat',    id: 'payments'  },
    { href: 'checkin.html',  icon: '✅', label: 'Check-In',   id: 'checkin'   },
  ];

  const navItems = links.map(l => `
    <a href="${l.href}" class="nav-link ${activePage === l.id ? 'active' : ''}">
      <span class="nav-icon">${l.icon}</span> ${l.label}
    </a>
  `).join('');

  return `
    <div class="sidebar-logo">
      <div class="logo-icon">🏟️</div>
      <h1>GYMFLOW</h1>
      <p>Management System</p>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-section-label">Kryesore</div>
      ${navItems}
    </nav>
    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar">AD</div>
        <div class="user-info">
          <div class="user-name">Admin</div>
          <div class="user-role">Administrator</div>
        </div>
      </div>
    </div>
  `;
}

function initSidebar(activePage) {
  const sidebar = document.getElementById('sidebar');
  if (sidebar) sidebar.innerHTML = getSidebarHTML(activePage);
}

// ── Toast notification ──
function showToast(message, type = 'success') {
  const colors = { success: '#2ed573', danger: '#ff4757', warning: '#ffa502' };
  const toast = document.createElement('div');
  toast.style.cssText = `
    position:fixed; bottom:24px; right:24px; z-index:9999;
    background:#1a1a1a; border:1px solid #2a2a2a; border-left:3px solid ${colors[type]};
    color:#f0f0f0; padding:14px 20px; border-radius:10px;
    font-family:'DM Sans',sans-serif; font-size:0.875rem;
    box-shadow:0 8px 32px rgba(0,0,0,0.4);
    animation: slideIn 0.3s ease;
    max-width: 320px;
  `;
  toast.textContent = message;

  const style = document.createElement('style');
  style.textContent = `@keyframes slideIn { from { opacity:0; transform:translateX(20px); } to { opacity:1; transform:translateX(0); } }`;
  document.head.appendChild(style);

  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// ── Modal helpers ──
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

// Close modal on overlay click
document.addEventListener('click', e => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});
