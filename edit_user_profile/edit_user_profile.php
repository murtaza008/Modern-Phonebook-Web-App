<!-- filepath: c:\xampp\htdocs\phonebook\edit_user_profile\edit_user_profile.php -->
<?php
// Include database connection
include '../connection.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Check if user data exists
if (!$user) {
    die("Error: User data not found. Please contact support.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Validate first name and last name (first letter capital, others small, no spaces or numbers)
    if (!preg_match("/^[A-Z][a-z]*$/", $firstName)) {
        $error_message = "First name must start with a capital letter and contain only letters!";
    }
    elseif (!preg_match("/^[A-Z][a-z]*$/", $lastName)) {
        $error_message = "Last name must start with a capital letter and contain only letters!";
    }
    // Validate username (no spaces, must contain numbers)
    elseif (!preg_match("/^[a-zA-Z0-9]*$/", $username) || !preg_match("/[0-9]/", $username)) {
        $error_message = "Username must contain at least one number and no spaces!";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    }
    else {
        // Check if email or username already exists (excluding current user)
        $check_query = "SELECT * FROM users WHERE (email = ? OR username = ?) AND id != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ssi", $email, $username, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $row = $check_result->fetch_assoc();
            if ($row['email'] === $email) {
                $error_message = "Email already exists!";
            } else {
                $error_message = "Username already exists!";
            }
        } else {
            // Update user details
            $update_query = "UPDATE users SET firstName = ?, lastName = ?, username = ?, email = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ssssi", $firstName, $lastName, $username, $email, $user_id);

            if ($update_stmt->execute()) {
                $_SESSION['success'] = "Profile updated successfully!";
                // Update session variables
                $_SESSION['firstName'] = $firstName;
                $_SESSION['lastName'] = $lastName;
                header("Location: ../user_profile/user_profile.php");
                exit();
            } else {
                $error_message = "Failed to update profile. Please try again.";
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
    <title>Edit Profile - Phonebook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="bi bi-book"></i> Phonebook
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../homepage/home.php">
                            <i class="bi bi-house me-2"></i>Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../user_profile/user_profile.php">
                            <i class="bi bi-person me-2"></i>View Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-pencil me-2"></i>Edit Profile
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if(isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="firstName" class="form-label">
                                    <i class="bi bi-person me-2"></i>First Name
                                </label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                       value="<?php echo htmlspecialchars($user['firstName'] ?? ''); ?>" required
                                       pattern="[A-Z][a-z]*" title="First name must start with a capital letter and contain only letters">
                                <div class="invalid-feedback">
                                    First name must start with a capital letter and contain only letters.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="lastName" class="form-label">
                                    <i class="bi bi-person me-2"></i>Last Name
                                </label>
                                <input type="text" class="form-control" id="lastName" name="lastName" 
                                       value="<?php echo htmlspecialchars($user['lastName'] ?? ''); ?>" required
                                       pattern="[A-Z][a-z]*" title="Last name must start with a capital letter and contain only letters">
                                <div class="invalid-feedback">
                                    Last name must start with a capital letter and contain only letters.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person-circle me-2"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required
                                       pattern="[a-zA-Z0-9]*" title="Username must contain at least one number and no spaces">
                                <div class="invalid-feedback">
                                    Username must contain at least one number and no spaces.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                <div class="invalid-feedback">
                                    Please enter a valid email address.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-2"></i>Update Profile
                                </button>
                                <a href="../user_profile/user_profile.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation and change detection
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            const initialValues = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                username: document.getElementById('username').value,
                email: document.getElementById('email').value
            };

            // Disable submit button initially
            submitButton.disabled = true;

            // Function to check if any field has changed
            function checkForChanges() {
                const currentValues = {
                    firstName: document.getElementById('firstName').value,
                    lastName: document.getElementById('lastName').value,
                    username: document.getElementById('username').value,
                    email: document.getElementById('email').value
                };

                const hasChanges = Object.keys(initialValues).some(key => 
                    initialValues[key] !== currentValues[key]
                );

                submitButton.disabled = !hasChanges;
            }

            // Add input event listeners to all form fields
            form.querySelectorAll('input').forEach(input => {
                input.addEventListener('input', checkForChanges);
            });

            // Form validation
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                document.querySelectorAll('.alert').forEach(function(alert) {
                    new bootstrap.Alert(alert).close();
                });
            }, 5000);
        });
    </script>
</body>
</html>