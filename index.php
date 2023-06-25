<?php

function generate($len = 10) {
    $char = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charLen = strlen($char);
    $randStr = '';
    for ($i = 0; $i < $len; $i++) {
        $randStr .= $char[random_int(0, $charLen - 1)];
    }
    return $randStr;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fileName = $_POST['filename'];
    $exp = $_POST['exp'];
    $limit = $_POST['limit'];
    
    if (empty($fileName)) $fileName = generate();
    if (!empty($fileName) &&
        !empty($exp) &&
        !empty($limit) &&
        is_numeric($exp) &&
        is_numeric($limit)) {
        $fileName = $fileName.'.php';

        $timeExp = strtotime('+'.$exp.' day');
        $fileContent = '<?php
if (('.$timeExp.'-strtotime("now")) > 0) {
    $user_agent = $_SERVER[\'HTTP_USER_AGENT\'];
    $pathurl = explode("/", $_SERVER[\'REQUEST_URI\']);  
    $ip = $_SERVER[\'REMOTE_ADDR\'];
    $blocked_ips = explode("\n", file_get_contents("blocklist.txt"));
    $limit = "limit/".preg_replace(\'/.php/\', \'\', $pathurl[2]).".txt";

    if (in_array($ip, $blocked_ips)) {
        echo \'Alamat IP anda diblokir\';
        header(\'HTTP/1.1 403 Forbidden\');
        exit();
    }

    if (!file_exists("limit/")) mkdir("limit/", 0777, true);
    if (!file_exists($limit)) touch($limit);

    $find_ip = false;
    $get = file_get_contents($limit);
    foreach (explode(";", $get) as $split) {
        if ($split == $ip):
            $find_ip = true;
            break;
        endif;
    }

    if (!$find_ip) {
        if (count(explode(";", file_get_contents($limit))) > '.$limit.') {
            echo \'Terlalu banyak perangkat\';
            header(\'HTTP/1.1 429 Too Many Requests\');
            exit();
        }

        $file = fopen($limit, \'w\');
        $get .= $ip.\';\';
        fwrite($file, $get);
        fclose($file);
    }

    $ver = \'([0-9\.]*)\';
    $dev = \'([0-9A-Za-z;\-\_\.\*\(\)\{\} ]*)\';

    if (preg_match(
        "/(^TiviMate\/$ver \($dev\)$|".
        "TV|"."Player|".
        "ExoPlayer\/$ver$|".
        "^Dalvik\/$ver \($dev\)$|".
        "^okhttp\/$ver$|".
        "^gbscell_aipitv_app$|".
        "^OTT Navigator\/$ver \($dev\)$)/", $user_agent
        )) {
?>
#EXTM3U
<?php
    } elseif (preg_match(
        "/(TV|"."Player|"."^Dalvik|"."^okhttp|".
        "^TiviMate|"."OTT( Navigator)?|^)$/", $user_agent
        )) {
        echo file_get_contents("forbidden.m3u");
    } else {
        echo file_get_contents("forbidden.m3u");
    }
} else {
    echo \'Sesi telah kadaluarsa\';
    header(\'HTTP/1.1 401 Unauthorized\');
    exit();
}
?>
';

        if (!file_exists("blocklist.txt")) touch("blocklist.txt");
        if (file_exists($fileName)) {
            $notification = 'File dengan nama file kustom ini sudah ada.';
        } else {
            $file = fopen("$fileName", "w");
            fwrite($file, $fileContent);
            fclose($file);

            register_shutdown_function(function () use ($fileName) {
                if (file_exists($fileName))
                unlink($fileName);
            });

            $notification = 'Selamat, URL anda berhasil dibuat.';
        }
    } else {
        $notification = 'Nama file custom, expired (Hari), dan limit device harus diisi dengan angka.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi Playlist</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@500&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            animation: slide-up 0.5s ease-in-out;
        }

        @keyframes slide-up {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            margin-top: 20px;
            animation: fade-in 0.5s ease-in-out;
        }

        @keyframes fade-in {
            0% {
                opacity: 0;
            }
            100% {
                opacity: 1;
            }
        }

        .card-title {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
            color: #007bff;
            animation: text-fade 1s ease-in-out infinite alternate;
        }

        @keyframes text-fade {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0.3;
            }
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .alert {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            animation: fade-in 0.5s ease-in-out;
        }

        .input-group-append {
            margin-left: -1px;
        }

        body.dark-mode {
            background-color: #222;
            color: #fff;
        }

        body.dark-mode .container,
        body.dark-mode .card,
        body.dark-mode .btn-primary {
            background-color: #333;
            color: #fff;
        }

        body.night-mode {
            background-color: #000;
            color: #fff;
        }

        body.night-mode .container,
        body.night-mode .card,
        body.night-mode .btn-primary {
            background-color: #111;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mt-5 card-title">REGISTRASI MENDAPATKAN PLAYLIST</h1>
        <div class="card mt-4">
            <div class="card-body">
                <form method="POST">
                    <div class="form-group">
                        <label for="filename">Nama file kustom</label>
                        <input type="text"
                               class="form-control"
                               id="filename"
                               name="filename"
                               placeholder="Masukkan nama file kustom">
                    </div>
                    <div class="form-group">
                        <label for="exp">Expired (Hari)</label>
                        <input type="text"
                               class="form-control"
                               id="exp" name="exp"
                               placeholder="Masukkan waktu kedaluarsa"
                               pattern="[0-9]+"
                               title="Hanya angka yang diperbolehkan"
                               required inputmode="numeric">
                    </div>
                    <div class="form-group">
                        <label for="limit">Limit device</label>
                        <input type="text"
                               class="form-control"
                               id="limit"
                               name="limit"
                               placeholder="Masukkan limit device"
                               pattern="[0-9]+"
                               title="Hanya angka yang diperbolehkan"
                               required inputmode="numeric">
                    </div>
                    <button type="submit"
                            class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    <?php if (isset($notification)) : ?>
        <div class="alert alert-primary mt-4" role="alert">
        <?php echo $notification; ?>
        </div>
    <?php endif; ?>
    <?php
        if (isset($fileName)) {
            if (isset($_SERVER['HTTPS']) &&
                ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1) ||
                isset($_SERVER['HTTP_X_FORWARDED_PROTO']) &&
                $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
                $protocol = 'https';
            } else {
                $protocol = 'http';
            }
            $domain = $_SERVER['HTTP_HOST'];
            $url_port = $_SERVER['SERVER_PORT'];
            $url_path = $_SERVER['REQUEST_URI'];

            $url = $protocol.$domain.$url_path;
            $url = substr($url, 0, strpos($url, "?"));

            if ($protocol == 'https') {
                $url .= $protocol.'://'.$domain.$url_path.urlencode($fileName);
            } else {
                $url .= $protocol.'://'.$domain.':'.$url_port.$url_path.urlencode($fileName);
            }
    ?>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">URL Custom</h5>
                <div class="input-group">
                    <input type="text"
                           class="form-control"
                           id="url"
                           value="<?php echo $url; ?>"
                           readonly>
                    <div class="input-group-append">
                        <button class="btn btn-primary"
                                type="button"
                                onclick="copyURL()">Salin</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Informasi</h5>
                <p class="card-text">File custom akan kedaluarsa setelah <?php echo $exp; ?> hari.</p>
            </div>
        </div>
    <?php } ?>
    </div>
    <script>
        function copyURL() {
            var urlInput = document.getElementById("url");
            urlInput.select();
            urlInput.setSelectionRange(0, 99999);
            document.execCommand("copy");
        }

        function toggleDarkMode() {
            var body = document.body;
            body.classList.toggle("dark-mode");
        }

        function toggleNightMode() {
            var body = document.body;
            body.classList.toggle("night-mode");
        }
    </script>
</body>
</html>
