<?php
session_start();
include "../../koneksi.php";

// Cek apakah session username ada
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit;
}

$username = $_SESSION['username']; // Mengambil username dari session

// Ambil data siswa berdasarkan username
$query = "SELECT * FROM siswa WHERE username = '$username'";
$result = mysqli_query($conn, $query);

if ($result) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "<script>alert('Gagal mengambil data pengguna.');</script>";
    exit;
}

// Direktori untuk menyimpan foto
$target_dir = "../../uploads/foto_siswa/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

// Path foto lama dari database
$photo_path = $user['photo'];

if (!empty($_FILES['photo']['name'])) {
    // Membuat nama file baru dengan timestamp untuk mencegah konflik nama
    $new_file_name = time() . "_" . basename($_FILES['photo']['name']);
    $target_file = $target_dir . $new_file_name;
    $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validasi format file
    $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($image_file_type, $valid_extensions)) {
        echo "<script>alert('Format file tidak valid! Hanya JPG, JPEG, PNG, dan GIF diperbolehkan.');</script>";
        exit;
    }

    // Validasi ukuran file (maksimal 2MB)
    if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
        echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB.');</script>";
        exit;
    }

    // Upload file ke server
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        // Update path foto jika berhasil diunggah
        $photo_path = "uploads/foto_siswa/" . $new_file_name;

        // Perbarui path foto di database
        $update_photo_query = "UPDATE siswa SET photo = '$photo_path' WHERE username = '$username'";
        if (!mysqli_query($conn, $update_photo_query)) {
            echo "<script>alert('Gagal memperbarui foto di database.');</script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal mengunggah foto ke server!');</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Siswa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

    <!-- Sidebar -->
    <?php include '../../layout/sidebar.php'; ?>
    <!-- Navbar -->
    <header id="header" class="bg-blue-600 text-white py-4 transition-all duration-300">
        <?php include '../../layout/header.php' ?>
    </header>
    
    <!-- Profile Section -->
<div class="container mx-auto mt-8 px-4">
    <h2 class="text-3xl font-bold mb-6">Profile Siswa</h2>

    <!-- Form untuk update profile -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <!-- Menampilkan Foto Profil -->
        <div class="mb-6">
            <label class="block text-gray-700 font-semibold mb-2">Foto Profil</label>
            <img src="../../<?php echo $user['photo'] ? $user['photo'] : 'uploads/default.png'; ?>" alt="Foto Profil" class="w-32 h-32 object-cover rounded-full border">
        </div>

        <form method="POST" enctype="multipart/form-data"> <!-- Tambahkan enctype untuk upload file -->
            <h3 class="text-lg font-semibold mb-4">Edit Profile</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="nama_lengkap" class="block text-gray-700">Nama Lengkap</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $user['nama_lengkap']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="nis" class="block text-gray-700">NIS</label>
                    <input type="text" id="nis" name="nis" value="<?php echo $user['nis']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md" readonly>
                </div>
                <div class="mb-4">
                    <label for="kelas_id" class="block text-gray-700">Kelas</label>
                    <input type="text" id="kelas_id" name="kelas_id" value="<?php echo $user['kelas_id']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md" readonly>
                </div>
                <div class="mb-4">
                    <label for="alamat" class="block text-gray-700">Alamat</label>
                    <input type="text" id="alamat" name="alamat" value="<?php echo $user['alamat']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="tanggal_lahir" class="block text-gray-700">Tanggal Lahir</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $user['tanggal_lahir']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="no_telp" class="block text-gray-700">No. Telepon</label>
                    <input type="text" id="no_telp" name="no_telp" value="<?php echo $user['no_telp']; ?>" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="jenis_kelamin" class="block text-gray-700">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                        <option value="Laki-laki" <?php echo $user['jenis_kelamin'] === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo $user['jenis_kelamin'] === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="photo" class="block text-gray-700">Foto Profil</label>
                    <input type="file" id="photo" name="photo" accept="image/*" class="w-full px-4 py-2 border border-gray-300 rounded-md">
                </div>
            </div>
            <button type="submit" name="update_profile" class="bg-blue-500 text-white px-6 py-2 rounded-md mt-4">Update Profile</button>
        </form>
    </div>
</div>
</body>
</html>

<?php
// Update profile jika form disubmit
if (isset($_POST['update_profile'])) {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $kelas_id = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);
    $tanggal_lahir = mysqli_real_escape_string($conn, $_POST['tanggal_lahir']);
    $no_telp = mysqli_real_escape_string($conn, $_POST['no_telp']);
    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    // Validasi password
    if (!empty($password) && $password !== $confirm_password) {
        echo "<script>alert('Password dan konfirmasi password tidak cocok!');</script>";
        exit;
    }

    // Hash password baru jika ada perubahan
    if (!empty($password)) {
        $password = password_hash($password, PASSWORD_DEFAULT);
    } else {
        $password = $user['password'];
    }

    // Query untuk update data siswa
    $update_query = "UPDATE siswa SET 
        nama_lengkap = '$nama_lengkap', 
        kelas_id = '$kelas_id', 
        alamat = '$alamat', 
        tanggal_lahir = '$tanggal_lahir', 
        no_telp = '$no_telp', 
        jenis_kelamin = '$jenis_kelamin', 
        password = '$password', 
        photo = '$photo_path' 
        WHERE username = '$username'";

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('Profile updated successfully'); window.location.href = 'profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile');</script>";
    }
}
?>
