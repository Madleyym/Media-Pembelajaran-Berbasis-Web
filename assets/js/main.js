// assets/js/main.js

document.addEventListener("DOMContentLoaded", function () {
  // Auto hide alerts after 5 seconds
  setTimeout(function () {
    const alerts = document.querySelectorAll(".alert");
    alerts.forEach(function (alert) {
      if (alert && bootstrap.Alert) {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
      }
    });
  }, 5000);

  // Prevent multiple form submissions
  document.querySelectorAll("form").forEach((form) => {
    form.addEventListener("submit", function () {
      const submitButton = form.querySelector('button[type="submit"]');
      if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML =
          '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
      }
    });
  });

  // Active link highlighting
  const currentLocation = location.href;
  const menuItems = document.querySelectorAll("a.nav-link");
  menuItems.forEach((link) => {
    if (link.href === currentLocation) {
      link.classList.add("active");
    }
  });
});
