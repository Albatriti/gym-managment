// ── Navbar scroll effect ──
const navbar = document.querySelector('.navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 50);
});

// ── Reveal on scroll ──
const reveals = document.querySelectorAll('.reveal');
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry, i) => {
    if (entry.isIntersecting) {
      setTimeout(() => entry.target.classList.add('visible'), i * 80);
    }
  });
}, { threshold: 0.1 });
reveals.forEach(el => observer.observe(el));

// ── Active nav link ──
const currentPage = window.location.pathname.split('/').pop() || 'index.html';
document.querySelectorAll('.nav-links a').forEach(link => {
  const href = link.getAttribute('href').split('/').pop();
  if (href === currentPage) link.classList.add('active');
});

// ── Hamburger menu ──
const hamburger = document.querySelector('.hamburger');
const navLinks  = document.querySelector('.nav-links');
const navActions = document.querySelector('.nav-actions');
if (hamburger) {
  hamburger.addEventListener('click', () => {
    navLinks.style.display  = navLinks.style.display  === 'flex' ? 'none' : 'flex';
    navActions.style.display = navActions.style.display === 'flex' ? 'none' : 'flex';
  });
}

// ── Counter animation ──
function animateCounter(el) {
  const target = parseInt(el.dataset.target);
  const duration = 2000;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current += step;
    if (current >= target) {
      el.textContent = target + (el.dataset.suffix || '');
      clearInterval(timer);
    } else {
      el.textContent = Math.floor(current) + (el.dataset.suffix || '');
    }
  }, 16);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting && !entry.target.dataset.animated) {
      entry.target.dataset.animated = true;
      animateCounter(entry.target);
    }
  });
}, { threshold: 0.5 });

document.querySelectorAll('[data-target]').forEach(el => counterObserver.observe(el));
