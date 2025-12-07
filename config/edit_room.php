<?php

include 'conn.php';

if (isset($_POST['room_id'])) {
    $room_id = $_POST['room_id'];
    $room_number = $_POST['room_number'];
    $category_id = $_POST['category'];
    $price_per_night = $_POST['price'];
    $description = $_POST['description'];

    // Handle file upload if a new image is provided
    if (!empty($_FILES["room_image"]["name"])) {
        $target_dir = "../uploads/rooms/";
        $target_file = $target_dir . basename($_FILES["room_image"]["name"]);
        move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file);
        
        $sql = "UPDATE rooms SET room_no='$room_number', category_id='$category_id', price='$price_per_night', description='$description', image='$target_file' WHERE id='$room_id'";
    } else {
        $sql = "UPDATE rooms SET room_no='$room_number', category_id='$category_id', price='$price_per_night', description='$description' WHERE id='$room_id'";
    }

    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin/manage_rooms.php?success=" . urlencode("Room updated successfully."));
        exit();
    } else {
        $error_message = "Error updating room: " . $conn->error;
        echo $error_message;
        exit();
    }
}


?>