<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registration</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: "Poppins", sans-serif;
      background-color: #f4f9fc;
      color: #1b4965;
      margin: 0;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 1rem;
    }

    .container {
      display: flex;
      flex-direction: row;
      width: 100%;
      max-width: 1000px;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .left {
      flex: 1;
      background-color: #5fa8d3;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
    }

    .left img {
      max-width: 100%;
      height: auto;
      border-radius: 10px;
    }

    .right {
      flex: 1;
      background-color: white;
      padding: 2rem;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .card-header {
      color: #5fa8d3;
      text-align: center;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .form-label {
      display: block;
      margin-bottom: 0.5rem;
    }

    .form-control {
      font-size: 1rem;
      padding: 10px;
      margin-bottom: 1rem;
    }

    .btn-primary {
      background-color: #1b4965;
      border-color: #1b4965;
      padding: 10px 20px;
      font-size: 1rem;
    }

    .btn-primary:hover {
      background-color: #143a50;
      border-color: #143a50;
    }

    #feedback {
      display: none;
      margin-top: 1rem;
    }

    #feedback.success {
      color: green;
      text-align: center;
    }

    #feedback.error {
      color: red;
      text-align: center;
    }

    @media (max-width: 768px) {
      .container {
        flex-direction: column;
        height: auto;
      }

      .left, .right {
        width: 100%;
        padding: 1rem;
      }

      .card-header {
        font-size: 1.25rem;
      }

      .form-control {
        font-size: 0.9rem;
      }

      .btn-primary {
        width: 100%;
      }
    }

    @media (max-width: 480px) {
      .container {
        padding: 1rem;
      }

      .card-header {
        font-size: 1.1rem;
      }

      .form-control {
        font-size: 0.85rem;
      }

      .btn-primary {
        font-size: 0.9rem;
      }
    }
  </style>
</head>

<body>
  <div class="container">
    <!-- Left Column (Image/Logo) -->
    <div class="left">
      <img src="assets/img/logo.png" alt="LGU Logo" />
    </div>

    <!-- Right Column (Registration Form) -->
    <div class="right">
      <div class="card-header">Registration</div>
      <form id="registerForm">
        <div class="mb-3">
          <label for="username" class="form-label">Full Name</label>
          <input type="text" class="form-control" id="username" name="name" placeholder="Enter your full name" required />
        </div>
        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
        </div>
        <div class="mb-3">
          <label for="password" class="form-label">Password</label>
          <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
        </div>
        <div class="mb-3">
          <label for="confirmPassword" class="form-label">Confirm Password</label>
          <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm your password" required />
        </div>
        <div id="feedback"></div>
        <div class="d-grid">
          <button type="submit" class="btn btn-primary">Register</button>
        </div>
      </form>
      <div class="text-center mt-3">
        <small>Already have an account? <a href="login.php" class="text-decoration-none">Login here</a>.</small>
      </div>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const registerForm = document.getElementById("registerForm");
      const feedback = document.getElementById("feedback");

      registerForm.addEventListener("submit", async function(e) {
        e.preventDefault();

        const password = document.getElementById("password").value.trim();
        const confirmPassword = document.getElementById("confirmPassword").value.trim();

        if (password !== confirmPassword) {
          feedback.className = "error";
          feedback.textContent = "Passwords do not match.";
          feedback.style.display = "block";
          return;
        }

        const formData = new FormData(registerForm);
        formData.append("action", "register");

        try {
          const response = await fetch("api/auth.php", {
            method: "POST",
            body: formData,
          });

          const data = await response.json();

          if (response.ok) {
            feedback.className = "success";
            feedback.textContent = "Registration successful! Redirecting...";
            feedback.style.display = "block";

            setTimeout(() => {
              window.location.href = "login.php";
            }, 1500);
          } else {
            feedback.className = "error";
            feedback.textContent = data.message || "Registration failed. Please try again.";
            feedback.style.display = "block";
          }
        } catch (error) {
          feedback.className = "error";
          feedback.textContent = "An error occurred. Please try again.";
          feedback.style.display = "block";
        }
      });
    });
  </script>
</body>

</html>
