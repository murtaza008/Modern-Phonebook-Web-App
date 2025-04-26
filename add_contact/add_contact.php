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
        session_unset();
        session_destroy();
        header("Location: ../login/login.php?msg=timeout");
        exit();
    }
    $_SESSION['last_activity'] = time();
}

include('../connection.php');

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $typeOfContact = $_POST['typeOfContact'];
    $userId = $_SESSION['user_id'];

    // Validate first name and last name (allow any characters)
    if (empty($firstName)) {
        $error_message = "First name is required!";
    }
    // Validate phone number (must start with 0 and be 11 digits)
    elseif (!preg_match("/^0[0-9]{10}$/", $phoneNumber)) {
        $error_message = "Phone number must be 11 digits starting with 0!";
    }
    // Validate contact type
    elseif (!in_array($typeOfContact, ['Personal', 'Work', 'Other'])) {
        $error_message = "Invalid contact type!";
    }
    else {
        // Check for duplicate phone number
        $check_query = "SELECT * FROM contacts WHERE phoneNumber = ? AND userId = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("si", $phoneNumber, $userId);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "Phone number already exists!";
        } else {
            // Check if full name combination already exists
            $fullName = $firstName . ' ' . $lastName;
            $name_check_query = "SELECT * FROM contacts WHERE CONCAT(firstName, ' ', lastName) = ? AND userId = ?";
            $name_check_stmt = $conn->prepare($name_check_query);
            $name_check_stmt->bind_param("si", $fullName, $userId);
            $name_check_stmt->execute();
            $name_check_result = $name_check_stmt->get_result();

            if ($name_check_result->num_rows > 0) {
                $error_message = "Contact with this name already exists!";
            } else {
                // Insert contact
                $query = "INSERT INTO contacts (firstName, lastName, phoneNumber, typeOfContact, userId) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("ssssi", $firstName, $lastName, $phoneNumber, $typeOfContact, $userId);

                if ($stmt->execute()) {
                    $_SESSION['success'] = "Contact added successfully!";
                    header("Location: ../homepage/home.php");
                    exit();
                } else {
                    $error_message = "Failed to add contact. Please try again.";
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
    <title>Add Contact - Phonebook</title>
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
                            <i class="bi bi-person-plus me-2"></i>Add New Contact
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation add-contact-form" novalidate>
                            <div class="mb-3">
                                <label for="firstName" class="form-label">
                                    <i class="bi bi-person me-2"></i>First Name
                                </label>
                                <input type="text" class="form-control" id="firstName" name="firstName" required
                                       title="First name is required">
                                <div class="invalid-feedback">
                                    First name is required.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="lastName" class="form-label">
                                    <i class="bi bi-person me-2"></i>Last Name
                                </label>
                                <input type="text" class="form-control" id="lastName" name="lastName"
                                       title="Last name is optional">
                                <div class="invalid-feedback">
                                    Last name is optional.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">
                                    <i class="bi bi-telephone me-2"></i>Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" required
                                       pattern="0[0-9]{10}" title="Phone number must be 11 digits starting with 0">
                                <div class="invalid-feedback">
                                    Phone number must be 11 digits starting with 0.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="typeOfContact" class="form-label">
                                    <i class="bi bi-tag me-2"></i>Contact Type
                                </label>
                                <select class="form-select" id="typeOfContact" name="typeOfContact" required>
                                    <option value="">Select type</option>
                                    <option value="Personal">Personal</option>
                                    <option value="Work">Work</option>
                                    <option value="Other">Other</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a contact type.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-2"></i>Add Contact
                                </button>
                                <a href="../homepage/home.php" class="btn btn-outline-secondary">
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

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                new bootstrap.Alert(alert).close();
            });
        }, 5000);
    </script>
</body>
</html> 