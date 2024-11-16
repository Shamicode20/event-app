<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet" />
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #f4f9fc;
            color: #1b4965;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }

        .container {
            max-width: 1000px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: flex;
            flex-direction: row;
            background-color: #ffffff;
        }

        .left {
            background-color: #5fa8d3;
            display: flex;
            justify-content: center;
            align-items: center;
            flex: 1;
        }

        .left img {
            max-width: 90%;
            height: auto;
            border-radius: 10px;
        }

        .right {
            flex: 1;
            padding: 3rem;
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
            font-weight: 500;
        }

        .form-control {
            padding: 10px;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #1b4965;
            border-color: #1b4965;
            padding: 10px;
            font-size: 1rem;
            cursor: pointer;
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
            }

            .left {
                padding: 1rem;
            }

            .right {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Left Column (Image/Logo) -->
        <div class="left">
            <img src="assets/img/logo.png" alt="Logo" />
        </div>

        <!-- Right Column (Login Form) -->
        <div class="right">
            <div class="card-header">Login</div>
            <form id="loginForm">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required />
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required />
                </div>
                <div id="feedback"></div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            <div class="text-center mt-3">
                <small>Forgot your password? <a href="forgot_password.php" class="text-decoration-none">Reset it here</a>.</small><br />
                <small>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a>.</small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loginForm = document.getElementById("loginForm");
            const feedback = document.getElementById("feedback");

            loginForm.addEventListener("submit", async function(e) {
                e.preventDefault();

                const formData = new FormData(loginForm);
                formData.append("action", "login");

                try {
                    const response = await fetch("api/auth.php", {
                        method: "POST",
                        body: formData,
                    });

                    const data = await response.json();

                    if (response.ok) {
                        feedback.className = "success";
                        feedback.textContent = "Login successful! Redirecting...";
                        feedback.style.display = "block";

                        setTimeout(() => {
                            window.location.href = "index.php";
                        }, 1500);
                    } else {
                        feedback.className = "error";
                        feedback.textContent = data.message || "Login failed. Please try again.";
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