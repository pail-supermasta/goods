<?php
$target_dir = "files/new/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
$uploadOk = 1;
$reportFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

// Check if report file is a actual report or fake report
if (isset($_POST["submit"])) {


// Check if file already exists
    if (file_exists($target_file)) {
        echo "Файл с таким именем уже существует";
        $uploadOk = 0;
    }

// Check file size
    if ($_FILES["fileToUpload"]["size"] > 500000) {
        echo "Файл слишком большой.";
        $uploadOk = 0;
    }

// Allow certain file formats
    if ($reportFileType != "xlsx" && $reportFileType != "xls") {
        echo "Только XLS и XLSX разрешены.";
        $uploadOk = 0;
    }

// Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 0) {
        echo "Файл не был загружен.";
// if everything is ok, try to upload file
    } else {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "Файл " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . " успешно загружен.";
            require_once "parseExcel.php";
        } else {
            echo "Произошла ошибка во время загрузки файла.";
        }
    }
}
?>
