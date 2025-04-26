<?php
session_start();
include('../connection.php');

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid form submission!";
    } else {
        $firstName = trim(mysqli_real_escape_string($conn, $_POST['firstName']));
        $lastName = trim(mysqli_real_escape_string($conn, $_POST['lastName']));
        $username = trim(mysqli_real_escape_string($conn, $_POST['username']));
        $email = trim(mysqli_real_escape_string($conn, $_POST['email']));
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];
        
        // Validate empty fields
        if (empty($firstName) || empty($lastName) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
            $error = "All fields are required!";
        }
        // Validate first name and last name (first letter capital, others small, no spaces or numbers)
        elseif (!preg_match("/^[A-Z][a-z]*$/", $firstName)) {
            $error = "First name must start with a capital letter and contain only letters!";
        }
        elseif (!preg_match("/^[A-Z][a-z]*$/", $lastName)) {
            $error = "Last name must start with a capital letter and contain only letters!";
        }
        // Validate username (no spaces, must contain numbers)
        elseif (!preg_match("/^[a-zA-Z0-9]*$/", $username) || !preg_match("/[0-9]/", $username)) {
            $error = "Username must contain at least one number and no spaces!";
        }
        // Validate email format
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format!";
        }
        // Validate password strength
        elseif (strlen($password) < 8) {
            $error = "Password must be at least 8 characters long!";
        }
        elseif (!preg_match("/[A-Z]/", $password)) {
            $error = "Password must contain at least one uppercase letter!";
        }
        elseif (!preg_match("/[a-z]/", $password)) {
            $error = "Password must contain at least one lowercase letter!";
        }
        elseif (!preg_match("/[0-9]/", $password)) {
            $error = "Password must contain at least one number!";
        }
        // Check if passwords match
        elseif ($password !== $confirmPassword) {
            $error = "Passwords do not match!";
        }
        else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Check if email or username already exists
            $check_query = "SELECT * FROM users WHERE email = '$email' OR username = '$username'";
            $result = mysqli_query($conn, $check_query);

            if (mysqli_num_rows($result) > 0) {
                $row = mysqli_fetch_assoc($result);
                if ($row['email'] === $email) {
                    $error = "Email already exists!";
                } else {
                    $error = "Username already exists!";
                }
            } else {
                $query = "INSERT INTO users (firstName, lastName, username, email, password) VALUES ('$firstName', '$lastName', '$username', '$email', '$hashedPassword')";
                if (mysqli_query($conn, $query)) {
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    $_SESSION['registration_success'] = true;
                    header("Location: ../login/login.php");
                    exit();
                } else {
                    $error = "Registration failed! Please try again.";
                }
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
    <title>Register - Phonebook</title>
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
                    <h3>📝 Create Your Account</h3>
                    <p>Join our phonebook community today!</p>
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
                <form method="POST" action="" class="needs-validation auth-form" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="firstName" class="form-label">
                                    <i class="bi bi-person me-2"></i>First Name
                                </label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required 
                                       maxlength="50" pattern="[A-Z][a-z]*" 
                                       title="First name must start with a capital letter and contain only letters"
                                       placeholder="Enter your first name">
                                <div class="invalid-feedback">
                                    First name must start with a capital letter and contain only letters.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lastName" class="form-label">
                                    <i class="bi bi-person me-2"></i>Last Name
                                </label>
                                <input type="text" class="form-control" id="lastName" name="lastName" required 
                                       maxlength="50" pattern="[A-Z][a-z]*" 
                                       title="Last name must start with a capital letter and contain only letters"
                                       placeholder="Enter your last name">
                                <div class="invalid-feedback">
                                    Last name must start with a capital letter and contain only letters.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person-circle me-2"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" required 
                                       maxlength="50" pattern="[a-zA-Z0-9]*" 
                                       title="Username must contain at least one number and no spaces"
                                       placeholder="Choose a username">
                                <div class="invalid-feedback">
                                    Username must contain at least one number and no spaces.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" required maxlength="100"
                                       placeholder="Enter your email">
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8"
                                           placeholder="Create a strong password">
                                    <button type="button" class="password-toggle" id="togglePassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.
                                </div>
                                <div class="invalid-feedback">
                                    Password must meet the requirements.
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="confirmPassword" class="form-label">
                                    <i class="bi bi-lock-fill me-2"></i>Confirm Password
                                </label>
                                <div class="password-input-container">
                                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required minlength="8"
                                           placeholder="Confirm your password">
                                    <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">
                                    Passwords must match.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>
                <div class="auth-footer">
                    <p>Already have an account? 
                        <a href="../login/login.php">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login here
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="register.js"></script>
</body>
</html> 