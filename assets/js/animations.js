// PoliLingua - Orbit & Animations

(function () {
  'use strict';

  // ── Hero orbit animation ───────────────────────────────────
  function initHeroOrbit() {
    const container = document.getElementById('hero-orbit');
    if (!container) return;

    const rings = Array.from(document.querySelectorAll('.hero-rings .hero-ring'));
    const avatars = Array.from(container.querySelectorAll('.hero-avatar[data-ring][data-angle]'));
    if (!rings.length || !avatars.length) return;

    const ringSpeeds = [-12, 8, -5]; // ring1 stanga, ring2 dreapta, ring3 stanga
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const orbitState = avatars.map((avatar) => {
      const ringIndex = Number.parseInt(avatar.dataset.ring || '0', 10);
      const startAngle = Number.parseFloat(avatar.dataset.angle || '0');
      return {
        avatar,
        ringIndex: Number.isNaN(ringIndex) ? 0 : ringIndex,
        angle: Number.isNaN(startAngle) ? 0 : startAngle,
      };
    });

    let centerX = 0;
    let centerY = 0;
    let radii = [];
    let lastTime = 0;
    let resizeTimer = null;

    function measure() {
      centerX = container.offsetWidth / 2;
      centerY = container.offsetHeight / 2;
      radii = rings.map((ring) => ring.getBoundingClientRect().width / 2);
    }

    function position() {
      orbitState.forEach((item) => {
        const { avatar, ringIndex, angle } = item;
        if (avatar.offsetParent === null) return; // hidden in mobile layout
        if (!radii[ringIndex]) return;

        const rad = (angle * Math.PI) / 180;
        const x = centerX + radii[ringIndex] * Math.cos(rad) - avatar.offsetWidth / 2;
        const y = centerY + radii[ringIndex] * Math.sin(rad) - avatar.offsetHeight / 2;
        avatar.style.transform = `translate3d(${x}px, ${y}px, 0)`;
      });
    }

    function animate(timestamp) {
      if (!lastTime) lastTime = timestamp;
      const delta = (timestamp - lastTime) / 1000;
      lastTime = timestamp;

      orbitState.forEach((item) => {
        const speed = ringSpeeds[item.ringIndex] || ringSpeeds[ringSpeeds.length - 1];
        item.angle = (item.angle + (speed * delta)) % 360;
      });

      position();
      requestAnimationFrame(animate);
    }

    measure();
    position();

    if (!reduceMotion) {
      requestAnimationFrame(animate);
    }

    window.addEventListener('resize', () => {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(() => {
        measure();
        position();
      }, 100);
    });
  }

  // ── Sticky note micro interactions ────────────────────────
  function initStickyNotes() {
    document.querySelectorAll('.sticky-note').forEach((note) => {
      note.addEventListener('mouseenter', () => {
        note.style.zIndex = 10;
      });
      note.addEventListener('mouseleave', () => {
        note.style.zIndex = '';
      });
    });
  }

  // Run after DOM ready
  document.addEventListener('DOMContentLoaded', function () {
    initHeroOrbit();
    initStickyNotes();
  });
})();
