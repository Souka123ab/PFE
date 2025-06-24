function togglePassword() {
  const passwordInput = document.getElementById("password")
  const toggleButton = document.querySelector(".password-toggle")

  if (passwordInput.type === "password") {
    passwordInput.type = "text"
    toggleButton.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                <line x1="1" y1="1" x2="23" y2="23"/>
            </svg>
        `
  } else {
    passwordInput.type = "password"
    toggleButton.innerHTML = `
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
        `
  }
}

// Form submission handler
document.querySelector(".login-form").addEventListener("submit", (e) => {
  e.preventDefault()

  const email = document.getElementById("email").value
  const password = document.getElementById("password").value

  if (email && password) {
    // Simulate login process
    const submitBtn = document.querySelector(".submit-btn")
    const originalText = submitBtn.textContent

    submitBtn.textContent = "Connexion..."
    submitBtn.disabled = true

    setTimeout(() => {
      alert("Connexion simulée réussie !")
      submitBtn.textContent = originalText
      submitBtn.disabled = false
    }, 2000)
  }
})

// Google sign-in handler
document.querySelector(".google-btn").addEventListener("click", () => {
  alert("Connexion avec Google simulée !")
})

// Add focus effects
document.querySelectorAll("input").forEach((input) => {
  input.addEventListener("focus", function () {
    this.parentElement.classList.add("focused")
  })

  input.addEventListener("blur", function () {
    this.parentElement.classList.remove("focused")
  })
})
