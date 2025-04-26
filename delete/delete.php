<?php
session_start();
include('../connection.php');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $userId = $_SESSION['user_id'];
    
    // Verify that the contact belongs to the current user
    $check_query = "SELECT * FROM contacts WHERE id = $id AND userId = $userId";
    $check_result = mysqli_query($conn, $check_query);
    
    if (mysqli_num_rows($check_result) > 0) {
        $query = "DELETE FROM contacts WHERE id = $id AND userId = $userId";
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Contact deleted successfully!";
        } else {
            $_SESSION['error'] = "Failed to delete contact. Please try again.";
        }
    } else {
        $_SESSION['error'] = "Contact not found or you don't have permission to delete it.";
    }
} else {
    $_SESSION['error'] = "Invalid request.";
}

header("Location: ../homepage/home.php");
exit();