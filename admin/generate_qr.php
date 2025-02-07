<?php
require 'includes/session.php';
require 'includes/phpqrcode/qrlib.php';

if(isset($_GET['id'])){
    $id = $_GET['id'];
    $sql = "SELECT *, employees.id AS empid FROM employees LEFT JOIN position ON position.id=employees.position_id WHERE employees.id = $id";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();

    // Generar contenido del QR
    $content = "ID: ".$row['employee_id']."\nNombre: ".$row['firstname']." ".$row['lastname']."\nPosición: ".$row['description'];

    // Configuraciones para el QR
    $tempDir = 'temp/';
    $qrFileName = 'qr_'.md5($content).'.png';
    $qrFilePath = $tempDir.$qrFileName;

    // Crear el directorio temporal si no existe
    if (!file_exists($tempDir)) {
        mkdir($tempDir, 0777, true);
    }

    // Generar el código QR
    QRcode::png($content, $qrFilePath, QR_ECLEVEL_L, 4);

    // Crear la imagen final
    $qrImage = imagecreatefrompng($qrFilePath);
    $width = imagesx($qrImage);
    $height = imagesy($qrImage);
    $finalImage = imagecreatetruecolor($width * 2, $height);
    $white = imagecolorallocate($finalImage, 255, 255, 255);
    imagefill($finalImage, 0, 0, $white);

    // Copiar el QR a la imagen final
    imagecopy($finalImage, $qrImage, 0, 0, 0, 0, $width, $height);

    // Añadir los datos del empleado
    $fontFile = __DIR__ . '/arial.ttf'; // Ruta a una fuente TrueType en tu servidor
    $text = "ID: ".$row['employee_id']."\nNombre: ".$row['firstname']." ".$row['lastname']."\nPosición: ".$row['description'];
    $fontSize = 10;
    $x = $width + 10;
    $y = 20;
    imagettftext($finalImage, $fontSize, 0, $x, $y, imagecolorallocate($finalImage, 0, 0, 0), $fontFile, $text);

    // Guardar la imagen final
    $finalFileName = 'final_'.md5($content).'.png';
    $finalFilePath = $tempDir.$finalFileName;
    imagepng($finalImage, $finalFilePath);

    // Descargar imagen
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="'.$finalFileName.'"');
    readfile($finalFilePath);

    // Eliminar archivos temporales
    unlink($qrFilePath);
    unlink($finalFilePath);
    imagedestroy($finalImage);
    exit;
} else {
    $_SESSION['error'] = 'Empleado no encontrado';
    header('location: employees.php');
}
?>

