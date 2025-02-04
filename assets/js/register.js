// assets/js/register.js

document.addEventListener("DOMContentLoaded", function () {
  console.log("Register Siswa JS loaded");

  // Utility functions
  const createElement = (tag, className = "", attributes = {}) => {
    const element = document.createElement(tag);
    if (className) element.className = className;
    Object.entries(attributes).forEach(([key, value]) =>
      element.setAttribute(key, value)
    );
    return element;
  };

  // Password Toggle Visibility
  function setupPasswordToggle(inputId, toggleId) {
    const input = document.getElementById(inputId);
    const toggle = document.getElementById(toggleId);

    if (toggle && input) {
      toggle.addEventListener("click", function () {
        const type =
          input.getAttribute("type") === "password" ? "text" : "password";
        input.setAttribute("type", type);
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
      });
    }
  }

  setupPasswordToggle("password", "togglePassword");
  setupPasswordToggle("confirm_password", "toggleConfirmPassword");

  // Password Strength Meter
  function checkPasswordStrength(password) {
    const criteria = {
      length: password.length >= 8,
      lowercase: /[a-z]/.test(password),
      uppercase: /[A-Z]/.test(password),
      numbers: /[0-9]/.test(password),
      special: /[$@#&!]/.test(password),
    };

    const strength = Object.values(criteria).filter(Boolean).length;
    const strengthText =
      ["Sangat Lemah", "Lemah", "Sedang", "Kuat", "Sangat Kuat"][
        strength - 1
      ] || "";

    const container = createElement("div");
    const progressBar = createElement("div", "progress", {
      style: "height: 5px;",
    });
    const progress = createElement(
      "div",
      `progress-bar ${
        ["bg-danger", "bg-danger", "bg-warning", "bg-info", "bg-success"][
          strength - 1
        ]
      }`,
      {
        style: `width: ${strength * 20}%`,
        "aria-valuenow": strength * 20,
        "aria-valuemin": "0",
        "aria-valuemax": "100",
      }
    );

    progressBar.appendChild(progress);
    container.appendChild(progressBar);

    const textIndicator = createElement("small", "text-muted mt-1 d-block");
    textIndicator.textContent = strengthText;
    container.appendChild(textIndicator);

    return container;
  }

  // Setup Password Strength Meter
  const passwordInput = document.getElementById("password");
  if (passwordInput) {
    const strengthContainer = document.getElementById(
      "password-strength-container"
    );
    passwordInput.addEventListener("input", function () {
      strengthContainer.innerHTML = "";
      if (this.value) {
        strengthContainer.appendChild(checkPasswordStrength(this.value));
      }
    });
  }

  // File Upload Preview
  const fileInput = document.querySelector('input[name="foto_profile"]');
  if (fileInput) {
    const previewContainer = createElement("div", "preview-container mt-3");
    fileInput.parentNode.appendChild(previewContainer);

    fileInput.addEventListener("change", function (e) {
      const file = this.files[0];
      if (!file) return;

      // Validate file
      const validTypes = ["image/jpeg", "image/png", "image/jpg"];
      if (!validTypes.includes(file.type)) {
        showError("Hanya file JPG, JPEG, dan PNG yang diperbolehkan! 🖼️");
        this.value = "";
        return;
      }

      if (file.size > 2 * 1024 * 1024) {
        showError("Ukuran file terlalu besar! Maksimal 2MB ya! 📁");
        this.value = "";
        return;
      }

      // Show preview
      const reader = new FileReader();
      reader.onload = function (e) {
        previewContainer.innerHTML = `
                    <div class="preview-image-wrapper position-relative">
                        <img src="${e.target.result}" class="preview-image" alt="Preview">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-1" 
                                aria-label="Close" id="removeImage"></button>
                    </div>
                `;

        document
          .getElementById("removeImage")
          .addEventListener("click", function () {
            fileInput.value = "";
            previewContainer.innerHTML = "";
          });
      };
      reader.readAsDataURL(file);
    });
  }

  // Form Validation
  const registerForm = document.querySelector(".register-form");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      e.preventDefault();

      const password = document.getElementById("password").value;
      const confirmPassword = document.getElementById("confirm_password").value;
      const kelas = document.querySelector('select[name="id_kelas"]').value;
      const fileInput = document.querySelector('input[name="foto_profile"]');
      const submitBtn = this.querySelector('button[type="submit"]');

      // Validation checks
      const validations = [
        {
          condition: !kelas,
          message: "Mohon pilih kelas kamu ya! 🏫",
        },
        {
          condition: password !== confirmPassword,
          message: "Ups! Kata sandi tidak cocok. Coba periksa lagi ya! 😊",
        },
        {
          condition: password.length < 8,
          message: "Kata sandi minimal 8 karakter ya! 🔐",
        },
        {
          condition: !/[A-Z]/.test(password),
          message: "Kata sandi harus memiliki minimal 1 huruf besar! 🔠",
        },
        {
          condition: !/[0-9]/.test(password),
          message: "Kata sandi harus memiliki minimal 1 angka! 🔢",
        },
        {
          condition:
            fileInput.files.length > 0 &&
            fileInput.files[0].size > 2 * 1024 * 1024,
          message: "Ukuran file terlalu besar! Maksimal 2MB ya! 📁",
        },
      ];

      const failedValidation = validations.find((v) => v.condition);
      if (failedValidation) {
        showError(failedValidation.message);
        return;
      }

      // Submit form with loading state
      submitBtn.disabled = true;
      submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>
                                 Mendaftarkan Siswa Kelas ${kelas}...`;

      try {
        this.submit();
      } catch (error) {
        showError("Terjadi kesalahan. Silakan coba lagi! 😔");
        submitBtn.disabled = false;
        submitBtn.innerHTML =
          '<i class="fas fa-user-plus me-2"></i>Daftar Sekarang';
      }
    });
  }

  // Error Display
  function showError(message) {
    const existingAlert = document.querySelector(".alert");
    if (existingAlert) {
      existingAlert.remove();
    }

    const alertDiv = createElement(
      "div",
      "alert alert-danger alert-dismissible fade show"
    );
    alertDiv.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

    const form = document.querySelector(".register-form");
    form.insertBefore(alertDiv, form.firstChild);

    // Auto-dismiss alert
    setTimeout(() => {
      alertDiv.classList.remove("show");
      setTimeout(() => alertDiv.remove(), 150);
    }, 5000);
  }
});
