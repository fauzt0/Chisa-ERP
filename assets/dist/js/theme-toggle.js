(function () {
  var STORAGE_KEY = 'appstack-config-theme';
  var MAP = {
    default: { bsTheme: 'light', sidebarTheme: 'dark' },
    light: { bsTheme: 'light', sidebarTheme: 'light' },
    colored: { bsTheme: 'light', sidebarTheme: 'colored' },
    dark: { bsTheme: 'dark', sidebarTheme: 'dark' }
  };

  function applyThemeName(name) {
    var spec = MAP[name] || MAP.default;
    var root = document.documentElement;
    root.setAttribute('data-bs-theme', spec.bsTheme);
    root.setAttribute('data-sidebar-theme', spec.sidebarTheme);
    try {
      localStorage.setItem(STORAGE_KEY, name);
    } catch (e) {}
  }

  function currentName() {
    try {
      var stored = localStorage.getItem(STORAGE_KEY);
      if (stored && MAP[stored]) {
        return stored;
      }
    } catch (e) {}
    return document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'dark' : 'default';
  }

  applyThemeName(currentName());

  function bind() {
    document.querySelectorAll('.js-theme-toggle').forEach(function (el) {
      if (el.dataset.themeBound) {
        return;
      }
      el.dataset.themeBound = '1';
      el.addEventListener('click', function (ev) {
        ev.preventDefault();
        applyThemeName(currentName() === 'dark' ? 'default' : 'dark');
      });
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bind);
  } else {
    bind();
  }
})();
