<?php
include 'conn.php';

if(isset($_POST['add_room'])) {

    $room_number = $_POST['room_number'];
    $category_id = $_POST['category'];
    $price_per_night = $_POST['price'];
    $description = $_POST['description'];

    // Handle file upload
    $target_dir = "../uploads/rooms/";
    $target_file = $target_dir . basename($_FILES["room_image"]["name"]);
    move_uploaded_file($_FILES["room_image"]["tmp_name"], $target_file);
    
    $sql = "INSERT INTO rooms (room_no, category_id, price, description, image) VALUES ('$room_number', '$category_id', '$price_per_night', '$description', '$target_file')";
    if ($conn->query($sql) === TRUE) {
        header("Location: ../admin/manage_rooms.php?success=" . urlencode("Room added successfully."));
        exit();
    } else {
        $error_message = "Error adding room: " . $conn->error;
        //header("Location: ../admin/add_room.php?error=" . urlencode($error_message));
        echo $error_message;
        exit();
    }

}


?>