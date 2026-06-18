// PoliLingua - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

  // ── Navbar scroll effect ───────────────────────────────────
  const navbar = document.getElementById('navbar');
  const isHome = document.body.classList.contains('page-home');
  const heroSection = document.getElementById('hero');
  const middleRing = document.querySelector('.hero-ring-2') || document.querySelector('.hero-ring-1');
  let navbarScrollThreshold = 14;

  const computeNavbarThreshold = () => {
    if (!isHome) return 14;
    const ringSize = middleRing ? middleRing.getBoundingClientRect().width : 0;
    const halfOrbit = ringSize > 0 ? ringSize * 0.5 : 340;
    const maxThreshold = window.innerHeight * 0.68;
    const heroTop = heroSection ? heroSection.offsetTop : 0;
    return heroTop + Math.min(halfOrbit, maxThreshold);
  };

  const syncNavbarState = () => {
    if (window.scrollY >= navbarScrollThreshold) {
      navbar?.classList.add('scrolled');
    } else {
      navbar?.classList.remove('scrolled');
    }
  };

  navbarScrollThreshold = computeNavbarThreshold();
  syncNavbarState();
  window.addEventListener('scroll', syncNavbarState, { passive: true });
  window.addEventListener('resize', () => {
    navbarScrollThreshold = computeNavbarThreshold();
    syncNavbarState();
  });

  // ── Hamburger menu ─────────────────────────────────────────
  const hamburger = document.getElementById('hamburger');
  const navMenu = document.getElementById('nav-menu');
  hamburger?.addEventListener('click', () => {
    hamburger.classList.toggle('open');
    navMenu?.classList.toggle('open');
  });

  // Close menu on nav link click
  navMenu?.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
      hamburger?.classList.remove('open');
      navMenu.classList.remove('open');
    });
  });

  // ── Smooth scroll for anchor links ────────────────────────
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
      const id = this.getAttribute('href').slice(1);
      const target = document.getElementById(id);
      if (target) {
        e.preventDefault();
        const offset = 80;
        const top = target.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      }
    });
  });

  // ── Reveal on scroll ──────────────────────────────────────
  const reveals = document.querySelectorAll('.reveal');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        setTimeout(() => {
          entry.target.classList.add('visible');
        }, entry.target.dataset.delay || 0);
        observer.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1 });

  reveals.forEach((el, i) => {
    el.dataset.delay = (i % 4) * 100;
    observer.observe(el);
  });

  // ── Accordion ─────────────────────────────────────────────
  const accordionItems = Array.from(document.querySelectorAll('.accordion-item'));
  const closeAccordionItem = (item) => {
    item.classList.remove('open');
    const body = item.querySelector('.accordion-body');
    if (body) body.style.maxHeight = '0px';
  };

  const openAccordionItem = (item) => {
    const body = item.querySelector('.accordion-body');
    item.classList.add('open');
    if (body) body.style.maxHeight = body.scrollHeight + 'px';
  };

  accordionItems.forEach(item => {
    const header = item.querySelector('.accordion-header');
    header?.addEventListener('click', () => {
      const isOpen = item.classList.contains('open');
      accordionItems.forEach(closeAccordionItem);
      if (!isOpen) openAccordionItem(item);
    });
  });

  window.addEventListener('resize', () => {
    accordionItems.forEach(item => {
      if (!item.classList.contains('open')) return;
      const body = item.querySelector('.accordion-body');
      if (body) body.style.maxHeight = body.scrollHeight + 'px';
    });
  });

  // ── Application form ─────────────────────────────────────
  const getFormToastStack = () => {
    let stack = document.getElementById('form-toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.id = 'form-toast-stack';
      stack.className = 'form-toast-stack';
      document.body.appendChild(stack);
    }
    return stack;
  };

  const showFormToast = (message, type = 'success') => {
    if (!message) return;
    const stack = getFormToastStack();
    const toast = document.createElement('div');
    toast.className = `form-toast form-toast--${type}`;
    toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
    toast.setAttribute('aria-live', 'polite');
    toast.textContent = message;
    stack.appendChild(toast);

    requestAnimationFrame(() => {
      toast.classList.add('is-visible');
    });

    const removeToast = () => {
      toast.classList.remove('is-visible');
      toast.classList.add('is-leaving');
      toast.addEventListener('animationend', () => toast.remove(), { once: true });
    };

    const timer = window.setTimeout(removeToast, 4600);
    toast.addEventListener('click', () => {
      window.clearTimeout(timer);
      removeToast();
    }, { once: true });
  };

  const applyForm = document.getElementById('apply-form');
  applyForm?.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    const successEl = document.getElementById('form-success');
    const errorEl = document.getElementById('form-error');
    const successMessage = successEl?.textContent?.trim() || 'Aplicația ta a fost trimisă cu succes!';
    const genericErrorMessage = errorEl?.textContent?.trim() || 'A apărut o eroare. Te rugăm să încerci din nou.';
    const btn = this.querySelector('[type="submit"]');

    btn.disabled = true;
    btn.textContent = '...';

    fetch(this.action, { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showFormToast(successMessage, 'success');
          applyForm.reset();
        } else {
          showFormToast(data.message || genericErrorMessage, 'error');
        }
      })
      .catch(() => {
        showFormToast(genericErrorMessage, 'error');
      })
      .finally(() => {
        btn.disabled = false;
        btn.textContent = btn.dataset.label || 'Trimite';
      });
  });

  // ── Language dropdown ────────────────────────────────────
  const langDropdown = document.getElementById('lang-dropdown');
  const langToggle = document.getElementById('lang-toggle');
  if (langDropdown && langToggle) {
    langToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = langDropdown.classList.toggle('open');
      langToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', (e) => {
      if (!langDropdown.contains(e.target)) {
        langDropdown.classList.remove('open');
        langToggle.setAttribute('aria-expanded', 'false');
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        langDropdown.classList.remove('open');
        langToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // ── Mobile social toggle ────────────────────────────────
  const socialIcons = document.getElementById('social-icons');
  const socialToggle = document.getElementById('social-toggle');
  if (socialIcons && socialToggle) {
    const closeSocialMenu = () => {
      socialIcons.classList.remove('open');
      socialToggle.setAttribute('aria-expanded', 'false');
    };

    socialToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = socialIcons.classList.toggle('open');
      socialToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    socialIcons.querySelectorAll('.social-icon').forEach((link) => {
      link.addEventListener('click', closeSocialMenu);
    });

    document.addEventListener('click', (e) => {
      if (!socialIcons.contains(e.target)) {
        closeSocialMenu();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeSocialMenu();
      }
    });
  }

});
