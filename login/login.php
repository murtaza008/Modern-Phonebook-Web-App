<?php
session_start();
include('../connection.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize login attempts if not set
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['last_attempt'] = time();
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: ../homepage/home.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission!";
    } else {
        // Check for rate limiting
        $time_diff = time() - $_SESSION['last_attempt'];
        if ($_SESSION['login_attempts'] >= 5 && $time_diff < 300) { // 5 minutes lockout
            $error = "Too many login attempts. Please try again in " . ceil((300 - $time_diff) / 60) . " minutes.";
        } else {
            // Reset attempts if 5 minutes have passed
            if ($time_diff >= 300) {
                $_SESSION['login_attempts'] = 0;
            }

            $login = trim(mysqli_real_escape_string($conn, $_POST['login']));
            $password = $_POST['password'];

            // Check if login is email or username
            $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL);
            $query = $isEmail ? 
                "SELECT * FROM users WHERE email = '$login'" : 
                "SELECT * FROM users WHERE username = '$login'";
            
            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) == 1) {
                $user = mysqli_fetch_assoc($result);
                if (password_verify($password, $user['password'])) {
                    // Reset login attempts on successful login
                    $_SESSION['login_attempts'] = 0;
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['firstName'] = $user['firstName'];
                    $_SESSION['lastName'] = $user['lastName'];
                    
                    // Set session timeout to 30 minutes
                    $_SESSION['last_activity'] = time();
                    $_SESSION['expire_time'] = 30 * 60; // 30 minutes in seconds
                    
                    $_SESSION['success'] = "Login successful! Welcome back, " . $user['firstName'] . "!";
                    header("Location: ../homepage/home.php");
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt'] = time();
                    $error = "Invalid password!";
                }
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt'] = time();
                $error = $isEmail ? "Email not found!" : "Username not found!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Phonebook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
</head>
<body class="auth-container">
    <div class="auth-card">
        <div class="card">
            <div class="card-header text-center">
                <div class="auth-header">
                    <h3>🔐 Welcome Back</h3>
                    <p>Sign in to access your phonebook</p>
                </div>
            </div>
            <div class="card-body">
                <?php if(isset($error)) { ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php } ?>
                <?php if(isset($_SESSION['registration_success'])) { ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Registration successful! Please login.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['registration_success']); ?>
                <?php } ?>
                <form method="POST" action="" class="needs-validation auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="form-group mb-4">
                        <label for="login" class="form-label">
                            <i class="bi bi-person-circle me-2"></i>Username or Email
                        </label>
                        <input type="text" class="form-control" id="login" name="login" required maxlength="100"
                               placeholder="Enter your username or email">
                        <div class="invalid-feedback">
                            Please enter your username or email.
                        </div>
                    </div>

                    <div class="form-group mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <div class="password-input-container">
                            <input type="password" class="form-control" id="password" name="password" required
                                   placeholder="Enter your password">
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="invalid-feedback">
                            Please enter your password.
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                        </button>
                    </div>
                </form>
                <div class="auth-footer">
                    <p>Don't have an account? 
                        <a href="../registration/register.php">
                            <i class="bi bi-person-plus me-2"></i>Register here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })()

        // Password toggle functionality
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>