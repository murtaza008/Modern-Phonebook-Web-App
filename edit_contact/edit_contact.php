<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

include('../connection.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    // Fetch the contact details
    $query = "SELECT * FROM contacts WHERE id = $id AND userId = $userId";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $contact = mysqli_fetch_assoc($result);
    } else {
        header("Location: ../homepage/home.php?error=Contact not found");
        exit();
    }
} else {
    header("Location: ../homepage/home.php?error=Invalid request");
    exit();
}

// Handle form submission for updating the contact
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $lastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $phoneNumber = mysqli_real_escape_string($conn, $_POST['phoneNumber']);
    $typeOfContact = mysqli_real_escape_string($conn, $_POST['typeOfContact']);
    $userId = $_SESSION['user_id'];

    // Validate first name (allow any characters)
    if (empty($firstName)) {
        header("Location: ../homepage/home.php?error=First name is required!");
        exit();
    }

    // Validate phone number format (11 digits starting with 0)
    if (!preg_match("/^0[0-9]{10}$/", $phoneNumber)) {
        header("Location: ../homepage/home.php?error=Phone number must be 11 digits starting with 0!");
        exit();
    }

    // Validate contact type
    $validTypes = ['Personal', 'Work', 'Other'];
    if (!in_array($typeOfContact, $validTypes)) {
        header("Location: ../homepage/home.php?error=Invalid contact type!");
        exit();
    }

    // Check for duplicate phone number (excluding current contact)
    $check_phone_query = "SELECT * FROM contacts WHERE phoneNumber = ? AND userId = ? AND id != ?";
    $check_phone_stmt = $conn->prepare($check_phone_query);
    $check_phone_stmt->bind_param("sii", $phoneNumber, $userId, $id);
    $check_phone_stmt->execute();
    $check_phone_result = $check_phone_stmt->get_result();

    if ($check_phone_result->num_rows > 0) {
        header("Location: ../homepage/home.php?error=Phone number already exists!");
        exit();
    }

    // Check if full name combination already exists (excluding current contact)
    $fullName = $firstName . ' ' . $lastName;
    $check_name_query = "SELECT * FROM contacts WHERE CONCAT(firstName, ' ', lastName) = ? AND userId = ? AND id != ?";
    $check_name_stmt = $conn->prepare($check_name_query);
    $check_name_stmt->bind_param("sii", $fullName, $userId, $id);
    $check_name_stmt->execute();
    $check_name_result = $check_name_stmt->get_result();

    if ($check_name_result->num_rows > 0) {
        header("Location: ../homepage/home.php?error=Contact with this name already exists!");
        exit();
    }

    $updateQuery = "UPDATE contacts SET 
                    firstName = '$firstName', 
                    lastName = '$lastName', 
                    phoneNumber = '$phoneNumber', 
                    typeOfContact = '$typeOfContact' 
                    WHERE id = $id AND userId = $userId";

    if (mysqli_query($conn, $updateQuery)) {
        $_SESSION['success'] = "Contact updated successfully";
        header("Location: ../homepage/home.php");
    } else {
        $_SESSION['error'] = "Failed to update contact";
        header("Location: ../homepage/home.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Contact - Phonebook</title>
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
                            <i class="bi bi-pencil me-2"></i>Edit Contact
                        </h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="firstName" class="form-label">
                                    <i class="bi bi-person me-2"></i>First Name
                                </label>
                                <input type="text" class="form-control" id="firstName" name="firstName" 
                                       value="<?php echo htmlspecialchars($contact['firstName']); ?>" required maxlength="50"
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
                                       value="<?php echo htmlspecialchars($contact['lastName']); ?>" maxlength="50"
                                       title="Last name is optional">
                                <div class="invalid-feedback">
                                    Last name is optional.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="phoneNumber" class="form-label">
                                    <i class="bi bi-telephone me-2"></i>Phone Number
                                </label>
                                <input type="tel" class="form-control" id="phoneNumber" name="phoneNumber" 
                                       value="<?php echo htmlspecialchars($contact['phoneNumber']); ?>" required maxlength="11" 
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
                                    <option value="Personal" <?php echo $contact['typeOfContact'] === 'Personal' ? 'selected' : ''; ?>>Personal</option>
                                    <option value="Work" <?php echo $contact['typeOfContact'] === 'Work' ? 'selected' : ''; ?>>Work</option>
                                    <option value="Other" <?php echo $contact['typeOfContact'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a contact type.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="updateButton">
                                    <i class="bi bi-save me-2"></i>Save Changes
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
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const submitButton = document.getElementById('updateButton');
            const initialValues = {
                firstName: document.getElementById('firstName').value,
                lastName: document.getElementById('lastName').value,
                phoneNumber: document.getElementById('phoneNumber').value,
                typeOfContact: document.getElementById('typeOfContact').value
            };

            // Disable submit button initially
            submitButton.disabled = true;

            // Function to check if any field has changed
            function checkForChanges() {
                const currentValues = {
                    firstName: document.getElementById('firstName').value,
                    lastName: document.getElementById('lastName').value,
                    phoneNumber: document.getElementById('phoneNumber').value,
                    typeOfContact: document.getElementById('typeOfContact').value
                };

                const hasChanges = Object.keys(initialValues).some(key => 
                    initialValues[key] !== currentValues[key]
                );

                submitButton.disabled = !hasChanges;
            }

            // Add input event listeners to all form fields
            form.querySelectorAll('input, select').forEach(field => {
                field.addEventListener('input', checkForChanges);
                field.addEventListener('change', checkForChanges);
            });

            // Form validation
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });
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