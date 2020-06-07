<?php
    if(isset($_POST['login'])) {
        session_start();
        require_once("connect.php");
        mysqli_report(MYSQLI_REPORT_STRICT);
        try {
            $conn = new mysqli($host, $db_user, $db_pass, $db_name);
            if($conn->connect_errno!=0) {
                throw new Exception(mysqli_connect_errno());
            }
            else {
                $login = filter_input(INPUT_POST, 'login');
                $haslo = filter_input(INPUT_POST, 'haslo');
                if($wynik = $conn->query("SELECT * FROM users WHERE login='$login'"))
                {
                    $ilosc = $wynik->num_rows;
                    if($ilosc>0) {
                        $wiersz = $wynik->fetch_assoc();
                        if(password_verify($haslo, $wiersz['haslo'])) {
                            $_SESSION['zalogowany'] = true;
                            $_SESSION['id'] = $wiersz['id'];
                            $_SESSION['login'] = $wiersz['login'];
                            $_SESSION['email'] = $wiersz['email'];
                            header("Location: main.php");
                        }
                        else {
                            $_SESSION['blad_log'] = "Nieprawidłowy login lub hasło!";
                            header("Location: index.php");
                        }
                    }
                    else {
                        $_SESSION['blad_log'] = "Nieprawidłowy login lub hasło!";
                        header("Location: index.php");
                        
                    }
                }
                else throw new Exception($conn->error);
            }
        }
        catch(Exception $error) {
            echo 'Błąd serwera :/';
        }
        $conn->close();
    }
    else {header("Location: index.php"); exit();}
    
    function dane_post($pole)
    {
        $_POST[$pole] ??= '';
        return htmlspecialchars(stripslashes($_POST[$pole]));
    }