/* =============================================================================
   theme-toggle.js — CHISA ERP Dark/Light theme controller
   -----------------------------------------------------------------------------
   - Single source of truth for the active theme ("light" | "dark").
   - Persists to BOTH localStorage and a cookie so the choice survives reloads,
     new tabs and new sessions (and is readable server-side if ever needed).
   - Applies the theme to <html data-bs-theme> immediately (a matching pre-paint
     snippet in the page <head> prevents any flash of the wrong theme).
   - Binds via event delegation, so every `.js-theme-toggle` works on every
     view without re-binding and without breaking on navigation.
   - Keeps the navbar icon (fa-moon / fa-sun) and its aria-label in sync.
   ============================================================================= */
(function () {
  'use strict';

  var STORAGE_KEY = 'appstack-config-theme'; // kept for backward compatibility
  var COOKIE_KEY = 'erp_theme';
  var SIDEBAR_THEME = 'dark'; // sidebar is brand-colored via CSS; value is cosmetic

  // Normalize any stored/legacy value to a real Bootstrap theme.
  function normalize(value) {
    return value === 'dark' ? 'dark' : 'light';
  }

  function readCookie(name) {
    var match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  }

  function writeCookie(name, value) {
    try {
      var oneYear = 60 * 60 * 24 * 365;
      document.cookie =
        name + '=' + encodeURIComponent(value) + ';path=/;max-age=' + oneYear + ';SameSite=Lax';
    } catch (e) {}
  }

  // Resolve the theme that should be active right now.
  function resolveTheme() {
    var stored = null;
    try {
      stored = localStorage.getItem(STORAGE_KEY);
    } catch (e) {}
    if (stored === null) {
      stored = readCookie(COOKIE_KEY);
    }
    if (stored === 'dark' || stored === 'light') {
      return stored;
    }
    // Legacy values ("default"/"colored") -> light. Otherwise honor the
    // attribute already on <html>, else fall back to light.
    if (stored) {
      return normalize(stored);
    }
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
  }

  function persist(theme) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (e) {}
    writeCookie(COOKIE_KEY, theme);
  }

  function updateToggleUI(theme) {
    var isDark = theme === 'dark';
    // New single-icon markup: <i data-theme-icon>.
    document.querySelectorAll('[data-theme-icon]').forEach(function (icon) {
      icon.classList.remove('fa-sun', 'fa-moon', 'fas');
      icon.classList.add('fas', isDark ? 'fa-sun' : 'fa-moon');
    });
    // Accessibility + tooltip on the control itself.
    document.querySelectorAll('.js-theme-toggle').forEach(function (btn) {
      var label = isDark ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro';
      btn.setAttribute('aria-label', label);
      btn.setAttribute('title', label);
      btn.setAttribute('role', 'button');
    });
  }

  function applyTheme(theme, opts) {
    theme = normalize(theme);
    var root = document.documentElement;
    root.setAttribute('data-bs-theme', theme);
    root.setAttribute('data-sidebar-theme', theme === 'dark' ? 'dark' : SIDEBAR_THEME);
    if (!opts || opts.persist !== false) {
      persist(theme);
    }
    updateToggleUI(theme);
    try {
      document.dispatchEvent(new CustomEvent('erp:themechange', { detail: { theme: theme } }));
    } catch (e) {}
    return theme;
  }

  function currentTheme() {
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'light';
  }

  function toggleTheme() {
    applyTheme(currentTheme() === 'dark' ? 'light' : 'dark');
  }

  // Apply immediately (re-affirms the pre-paint value and syncs the icon).
  applyTheme(resolveTheme(), { persist: false });

  // Event delegation: one listener handles every current/future toggle.
  document.addEventListener('click', function (ev) {
    var trigger = ev.target.closest ? ev.target.closest('.js-theme-toggle') : null;
    if (trigger) {
      ev.preventDefault();
      toggleTheme();
    }
  });

  // Keep multiple tabs in sync.
  window.addEventListener('storage', function (ev) {
    if (ev.key === STORAGE_KEY && ev.newValue) {
      applyTheme(ev.newValue, { persist: false });
    }
  });

  // Re-sync the icon once the DOM (and lucide/FontAwesome) are ready.
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
      updateToggleUI(currentTheme());
    });
  } else {
    updateToggleUI(currentTheme());
  }

  // Expose a tiny API for other scripts if needed.
  window.ErpTheme = {
    get: currentTheme,
    set: function (t) { applyTheme(t); },
    toggle: toggleTheme
  };
})();
