<?php
$target_dir = "post_photos/";
$target_file = $target_dir . basename($_FILES["PhotoToUpload"]["name"]);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is a actual image or fake image
if(isset($_POST["btnSubmit"])) {
  $check = getimagesize($_FILES["PhotoToUpload"]["tmp_name"]);
  if($check !== false) {
    echo "File is an image - " . $check["mime"] . ".";
    $uploadOk = 1;
  } else {
    echo "File is not an image.";
    $uploadOk = 0;
  }
}

// Check if file already exists
if (file_exists($target_file)) {
  echo "Sorry, file already exists.";
  $uploadOk = 0;
}

// Check file size
if ($_FILES["PhotoToUpload"]["size"] > 500000) {
  echo "Sorry, your file is too large.";
  $uploadOk = 0;
}

// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
  echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
  $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
  echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
  if (move_uploaded_file($_FILES["PhotoToUpload"]["tmp_name"], $target_file)) {

    $Complaint_ID = mysqli_insert_id($connections);
    $upload_date = date("Y-m-d H:i:s");

    $stmt = mysqli_prepare($connections, "INSERT INTO complaint_media (Complaint_ID, File_Type, Upload_Date) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "iss", $Complaint_ID, $imageFileType, $upload_date);
    mysqli_stmt_execute($stmt);

    mysqli_query($connections, "UPDATE complaint_media SET File_Path='$target_file' WHERE Complaint_ID='$Complaint_ID'");
    echo "The file ". htmlspecialchars( basename( $_FILES["PhotoToUpload"]["name"])). " has been uploaded.";

  } else {
    echo "Sorry, there was an error uploading your file.";
  }
}
?>