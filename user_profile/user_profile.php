<!-- filepath: c:\xampp\htdocs\phonebook\user_profile\user_profile.php -->
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

// Get any messages
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear messages
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Phonebook</title>
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h3 class="mb-0">
                            <i class="bi bi-person-circle me-2"></i>User Profile
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="profile-image mb-3">
                                    <i class="bi bi-person-circle display-1 text-primary"></i>
                                </div>
                                <h4 class="mb-0"><?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></h4>
                                <p class="text-muted">
                                    Member since <?php 
                                    if (isset($user['created_at']) && !empty($user['created_at'])) {
                                        echo date('F Y', strtotime($user['created_at']));
                                    } else {
                                        // If created_at is not set, use the current date
                                        echo date('F Y');
                                    }
                                    ?>
                                </p>
                            </div>
                            <div class="col-md-8">
                                <div class="profile-info">
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="bi bi-person me-2"></i>Personal Information
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <p class="mb-1 text-muted">First Name</p>
                                                <h6><?php echo htmlspecialchars($user['firstName']); ?></h6>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="mb-1 text-muted">Last Name</p>
                                                <h6><?php echo htmlspecialchars($user['lastName']); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-4">
                                        <h5 class="text-primary mb-3">
                                            <i class="bi bi-person-badge me-2"></i>Account Information
                                        </h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <p class="mb-1 text-muted">Username</p>
                                                <h6><?php echo htmlspecialchars($user['username']); ?></h6>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <p class="mb-1 text-muted">Email</p>
                                                <h6><?php echo htmlspecialchars($user['email']); ?></h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="../edit_user_profile/edit_user_profile.php" class="btn btn-primary">
                                        <i class="bi bi-pencil me-2"></i>Edit Profile
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>