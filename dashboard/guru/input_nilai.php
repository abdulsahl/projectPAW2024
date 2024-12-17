<?php
include "../../koneksi.php";
session_start();

// Fungsi untuk mengambil daftar kelas
function getDaftarKelas()
{
  global $conn;
  $query = "SELECT * FROM kelas";
  return mysqli_query($conn, $query);
}

// Fungsi untuk mengambil daftar tugas berdasarkan kelas
function getDaftarTugas($kelas)
{
  global $conn;
  $query = "SELECT * FROM tugas WHERE kelas_id = '$kelas'";
  return mysqli_query($conn, $query);
}

// Fungsi untuk mengambil daftar murid berdasarkan kelas
function getDaftarMurid($kelas)
{
  global $conn;
  $query = "SELECT * FROM siswa WHERE kelas_id = '$kelas' ORDER BY nama_lengkap";
  return mysqli_query($conn, $query);
}

// Fungsi untuk mengambil file tugas
function getFile($siswa, $tugas)
{
  global $conn;
  $query = "SELECT file_tugas FROM pengumpulan_tugas WHERE id_siswa = '" . $conn->real_escape_string($siswa) . "' AND id_tugas = '" . $conn->real_escape_string($tugas) . "'";
  return mysqli_query($conn, $query);
}

// Variabel untuk menyimpan pilihan
$selected_kelas = isset($_POST['kelas']) ? $_POST['kelas'] : '';
$selected_tugas = isset($_POST['tugas']) ? $_POST['tugas'] : '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Input Nilai</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 text-gray-800">
  <!-- Sidebar -->
  <?php include '../../layout/sidebar.php'; ?>

  <!-- Navbar -->
  <header class="bg-blue-600 text-white py-4">
    <?php include '../../layout/header.php'; ?>
  </header>

  <!-- Main Content -->
  <div class="container mx-auto mt-8 px-4">
    <h2 class="text-2xl font-bold text-center mb-6">Input Nilai Siswa</h2>

    <div class="flex space-x-8">
      <!-- Form Pilihan Kelas dan Tugas -->
      <div class="w-1/3 bg-white shadow-md rounded-lg p-6">
        <h3 class="text-xl font-semibold mb-4">Pilih Kelas dan Tugas</h3>
        <form action="" method="POST">
          <div class="mb-4">
            <label for="kelas" class="block text-sm font-semibold mb-2">Kelas</label>
            <select id="kelas" name="kelas" class="w-full p-2 border border-gray-300 rounded" required onchange="this.form.submit()">
              <option value="">Pilih Kelas</option>
              <?php
              $kelas_list = getDaftarKelas();
              while ($row = mysqli_fetch_assoc($kelas_list)) {
                $selected = ($selected_kelas == $row['id_kelas']) ? 'selected' : '';
                echo "<option value='" . $row['id_kelas'] . "' $selected>" . $row['tingkat'] . " " . $row['nama_kelas'] . "</option>";
              }
              ?>
            </select>
          </div>

          <?php if ($selected_kelas): ?>
            <div class="mb-4">
              <label for="tugas" class="block text-sm font-semibold mb-2">Pilih Tugas</label>
              <select id="tugas" name="tugas" class="w-full p-2 border border-gray-300 rounded" required onchange="this.form.submit()">
                <option value="">Pilih Tugas</option>
                <?php
                $daftar_tugas = getDaftarTugas($selected_kelas);
                while ($row = mysqli_fetch_assoc($daftar_tugas)) {
                  $selected = ($selected_tugas == $row['id_tugas']) ? 'selected' : '';
                  echo "<option value='" . $row['id_tugas'] . "' $selected>" . $row['judul'] . "</option>";
                }
                ?>
              </select>
            </div>
          <?php endif; ?>
        </form>
      </div>

      <!-- Tabel Daftar Murid -->
      <div class="w-2/3 bg-white shadow-md rounded-lg p-6">
        <?php if ($selected_kelas && $selected_tugas): ?>
          <h3 class="text-xl font-semibold mb-4">Daftar Murid</h3>
          <form action="simpan_nilai.php" method="POST">
            <input type="hidden" name="kelas" value="<?php echo $selected_kelas; ?>">
            <input type="hidden" name="tugas" value="<?php echo $selected_tugas; ?>">

            <table class="table-auto w-full text-left border-collapse border border-gray-300">
              <thead>
                <tr>
                  <th class="px-4 py-2 border border-gray-300 bg-blue-100">NO</th>
                  <th class="px-4 py-2 border border-gray-300 bg-blue-100">Nama</th>
                  <th class="px-4 py-2 border border-gray-300 bg-blue-100">NISN</th>
                  <th class="px-4 py-2 border border-gray-300 bg-blue-100">File Tugas</th>
                  <th class="px-4 py-2 border border-gray-300 bg-blue-100">Nilai</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $daftar_murid = getDaftarMurid($selected_kelas);
                $no = 1;
                while ($row = mysqli_fetch_assoc($daftar_murid)) {
                  echo "<tr>";
                  echo "<td class='px-4 py-2 border border-gray-300'>" . $no++ . "</td>";
                  echo "<td class='px-4 py-2 border border-gray-300'>" . $row['nama_lengkap'] . "</td>";
                  echo "<td class='px-4 py-2 border border-gray-300'>" . $row['nis'] . "</td>";

                  echo "<td class='px-4 py-2 border border-gray-300'>";
                  $file_tugas = getFile($row['id_siswa'], $selected_tugas);
                  if ($file_tugas && $task = mysqli_fetch_assoc($file_tugas)) {
                    echo "<a href='uploads/" . htmlspecialchars($task['file_tugas']) . "' class='text-blue-600 hover:underline' download>Unduh</a>";
                  } else {
                    echo "<span class='text-gray-500'>Tidak Ada File</span>";
                  }
                  echo "</td>";

                  echo "<td class='px-4 py-2 border border-gray-300'>";
                  echo "<input type='number' name='nilai[" . $row['id_siswa'] . "]' min='0' max='100' class='w-20 p-1 border rounded' required>";
                  echo "</td>";
                  echo "</tr>";
                }
                ?>
              </tbody>
            </table>

            <div class="mt-4 text-right">
              <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Simpan Nilai</button>
            </div>
          </form>
        <?php else: ?>
          <p class="text-center text-gray-500">Pilih kelas dan tugas untuk menampilkan daftar murid.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
