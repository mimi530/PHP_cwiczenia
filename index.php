<?php
    session_start();
    if(isset($_SESSION['zalogowany']) && $_SESSION['zalogowany']) header("Location: main.php");
    $bledy = [];
    define('WYMAGANE','To pole jest wymagane!');
    $login = '';
    $email = '';
    $haslo = '';
    $haslo2 = '';
    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $login = dane_post('login');
        $email = dane_post('email');
        $haslo = dane_post('haslo');
        $haslo2 = dane_post('haslo2');
        if(!$login) {
            $bledy['login'] = WYMAGANE;
        } elseif (strlen($login)<3 || strlen($login)>20) {
            $bledy['login'] = 'Login musi posiadać od 3-20 znaków!';
        }
        if(!$email) {
            $bledy['email'] = WYMAGANE;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $bledy['email'] = 'E-mail musi być poprawny!';
        }
        if(!$haslo) {
            $bledy['haslo'] = WYMAGANE;
        } elseif(strlen($haslo)<8) {
            $bledy['haslo'] = "Hasło za krótkie!";
        }
        if(!$haslo2) {
            $bledy['haslo2'] = WYMAGANE;
        }
        if($haslo && $haslo2 && strcmp($haslo, $haslo2)) {
            $bledy['haslo2'] = 'Hasła muszą się zgadzać!';
        }
        if(empty($bledy)) {
            require_once('baza.php');
            mysqli_report(MYSQLI_REPORT_STRICT);
            try {
                $conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
                if($conn->connect_errno!=0) {
                    throw new Exception(mysqli_connect_errno());
                }
                else {
                    $wynik = $conn->query("SELECT id FROM users WHERE email='$email'");
                    if(!$wynik) throw new Exception($conn->error);
                    $ile_maili = $wynik->num_rows;
                    if($ile_maili) {
                        $bledy['email'] = 'Taki email istnieje już w bazie danych!';
                    }
                    $wynik = $conn->query("SELECT id FROM users WHERE login='$login'");
                    if(!$wynik) throw new Exception($conn->error);
                    $ile_loginow = $wynik->num_rows;
                    if($ile_loginow) {
                        $bledy['login'] = 'Taki login istnieje już w bazie danych!';
                    }
                    if(empty($bledy)) {
                        $haslo_hash = password_hash($haslo, PASSWORD_DEFAULT);
                        if($conn->query("INSERT INTO users VALUES (NULL, '$login', '$haslo_hash', '$email')")) {
                            $_SESSION['udana'] = ' udana!';
                            header('Location: index.php');
                            exit();
                        }
                        else throw new Exception($conn->error);
                    }
                    $conn->close();
                }
            }
            catch(Exception $error) {
                echo 'Błąd serwera :/';
            }
        }
    }
    function dane_post($pole)
    {
        $_POST[$pole] ??= '';
        return htmlspecialchars(stripslashes($_POST[$pole]));
    }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta author="Michał Domżalski">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" 
    href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" 
    integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <title>Car Reminder</title>
    <link href="style.css" rel="stylesheet">
    <!--"Talk is cheap, show me the code."-->
</head>
<body>
    <h1>Car-Reminder!</h1>
    <h4>Strona stworzona w celu przećwiczenia umiejętności PHP. Żeby nie była bezużyteczna to symuluje 
        "książeczkę serwisową" aby pamiętać kiedy przeprowadzaliśmy naprawy :)</h4></br>
    <h2>Rejestracja <?= $_SESSION['udana'] ??= ''; unset($_SESSION['udana']);?></h2>
    <form action="" method="post">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Login</label>
                    <input class="form-control <?= isset($bledy['login']) ? 'is-invalid' : ''?>" type="text" name="login" value="<?= $login;?>">
                    <small class="form-text text-muted">3-20 znaków.</small>
                    <div class="invalid-feedback">
                        <?= $bledy['login'] ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>E-mail</label>
                    <input class="form-control <?= isset($bledy['email']) ? 'is-invalid' : ''?>" type="text" name="email" value="<?= $email;?>">
                    <div class="invalid-feedback">
                        <?= $bledy['email'] ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Hasło</label>
                    <input class="form-control <?= isset($bledy['haslo']) ? 'is-invalid' : ''?>" name="haslo" type="password" value="<?= $haslo;?>">
                    <small class="form-text text-muted">Min. 8 znaków</small>
                    <div class="invalid-feedback">
                        <?= $bledy['haslo'] ?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Powtórz hasło</label>
                    <input class="form-control <?= isset($bledy['haslo2']) ? 'is-invalid' : ''?>" name="haslo2" type="password" value="<?= $haslo2;?>">
                    <div class="invalid-feedback">
                        <?= $bledy['haslo2'] ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Zarejestruj!">
        </div>
    </form>
    </br>
    <h2>Logowanie</h2>
    <form action="logowanie.php" method="post">
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <label>Login</label>
                    <input class="form-control <?= isset($_SESSION['blad_log']) ? 'is-invalid' : ''?>" name="llogin" type="text" value="<?= $_SESSION['llogin'] ??= '';?>">
                    <div class="invalid-feedback">
                        <?= $_SESSION['blad_log'] ?? ''?>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="form-group">
                    <label>Hasło</label>
                    <input class="form-control <?= isset($_SESSION['blad_log']) ? 'is-invalid' : ''?>" type="password" name="lhaslo" <?= $_SESSION['llogin'] ? 'autofocus' : ''; ?>>
                </div>
            </div>
        </div>
        <div class="form-group">
            <button class="btn btn-primary">Zaloguj</button>
        </div>
    </form> 
</body>
</html>
<?php
    unset($_SESSION);
?>