<?php
require 'routeros_api.class.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $API = new RouterosAPI();

    // Koneksi ke Mikrotik
    if ($API->connect('', '', '', 8728)) {

        // Mendapatkan daftar secret PPP
        $API->write('/ppp/secret/print');
        $responses = $API->read();

        // Memeriksa apakah username sudah ada
        $usernameExists = false;
        foreach ($responses as $response) {
            if ($response['name'] === $username) {
                $usernameExists = true;
                break;
            }
        }

        if ($usernameExists) {
            $message = "Username sudah ada. Silakan pilih username lain.";
        } else {
            // Mendapatkan IP address terakhir yang digunakan
            $lastIP = '192.168.25.1'; // Default IP awal jika tidak ada data
            foreach ($responses as $response) {
                if (isset($response['remote-address'])) {
                    $lastIP = $response['remote-address'];
                }
            }

            // Mendapatkan IP address berikutnya
            $parts = explode('.', $lastIP);
            $parts[3] = (int)$parts[3] + 1; // Menambahkan 1 ke oktet terakhir
            $newIP = implode('.', $parts);

            // Membuat secret PPP baru
            $API->write('/ppp/secret/add', false);
            $API->write('=name=' . $username, false);
            $API->write('=password=' . $password, false);
            $API->write('=service=any', false);  // Mengatur layanan ke "any"
            $API->write('=remote-address=' . $newIP, false);
            $API->write('=local-address=192.168.25.1');

            $API->read();

            $message = "<b>Hostname : vpslegi.my.id</b><br>";
			$message .= "<br>";
			$message .= "<br>";
            $message .= "Username: $username<br>";
            $message .= "Password: $password<br>";
            #$message .= "IP Remote: $newIP<br>";
            #$message .= "IP Local: 192.168.25.1<br>";
        }

        $API->disconnect();
    } else {
        $message = 'Error: Could not connect to Mikrotik.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTOMASI VPN</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
        }
        h1 {
            font-size: 1.5em;
            margin-bottom: 20px;
            text-align: center;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            color: white;
            font-size: 1em;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            background-color: #e2e3e5;
            border: 1px solid #d6d8db;
            border-radius: 4px;
            text-align: center;
        }
        .error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>OTOMASI VPN</h1>
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            <br>
            <label for="password">Password:</label>
            <input type="text" id="password" name="password" required>
            <br>
            <button type="submit">SUBMIT</button>
        </form>
    </div>
</body>
</html>
