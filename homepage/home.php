<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Check session timeout
if (isset($_SESSION['last_activity']) && isset($_SESSION['expire_time'])) {
    $inactive = time() - $_SESSION['last_activity'];
    if ($inactive >= $_SESSION['expire_time']) {
        // Destroy session and redirect to login
        session_unset();
        session_destroy();
        header("Location: ../login/login.php?msg=timeout");
        exit();
    }
    // Update last activity time
    $_SESSION['last_activity'] = time();
}

// Include database connection
include('../connection.php');

// Get user's contacts
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM contacts WHERE userId = $user_id ORDER BY firstName, lastName";
$result = mysqli_query($conn, $query);

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
    <title>Home - Phonebook</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../styles.css" rel="stylesheet">
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
                        <span class="nav-link text-light">
                            <i class="bi bi-person-circle"></i> Welcome, <?php echo htmlspecialchars($_SESSION['firstName'] . ' ' . $_SESSION['lastName']); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../user_profile/user_profile.php">
                            <i class="bi bi-person"></i> View Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout/logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
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
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-person-lines-fill me-2"></i>My Contacts
                        </h5>
                        <a href="../add_contact/add_contact.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-2"></i>Add Contact
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><i class="bi bi-person me-2"></i>First Name</th>
                                            <th><i class="bi bi-person me-2"></i>Last Name</th>
                                            <th><i class="bi bi-telephone me-2"></i>Phone Number</th>
                                            <th><i class="bi bi-tag me-2"></i>Contact Type</th>
                                            <th><i class="bi bi-gear me-2"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($contact = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($contact['firstName']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['lastName']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['phoneNumber']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['typeOfContact']); ?></td>
                                                <td>
                                                    <a href="../edit_contact/edit_contact.php?id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-info">
                                                        <i class="bi bi-pencil"></i> Edit
                                                    </a>
                                                    <a href="../delete/delete.php?id=<?php echo $contact['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this contact?');">
                                                        <i class="bi bi-trash"></i> Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="bi bi-emoji-frown display-1 text-muted mb-3"></i>
                                <p class="lead text-muted">No contacts found. Add your first contact!</p>
                                <a href="../add_contact/add_contact.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Add Contact
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html>