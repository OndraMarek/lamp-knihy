<?php
session_start();

require 'database.php';

$conn = Connection();

$sql = "SELECT 
            k.kniha_nazev, 
            k.kniha_isbn, 
            k.kniha_rok, 
            k.kniha_vydavatel, 
            k.kniha_pocet, 
            k.kniha_popis, 
            GROUP_CONCAT(CONCAT(a.autor_jmeno, ' ', a.autor_prijmeni) SEPARATOR ', ') AS autori, 
            GROUP_CONCAT(z.zanr_nazev SEPARATOR ', ') AS zanry 
        FROM 
            kniha k 
        LEFT JOIN 
            kniha_autor ka ON k.kniha_id = ka.kniha_id 
        LEFT JOIN 
            autor a ON ka.autor_id = a.autor_id 
        LEFT JOIN 
            kniha_zanr kz ON k.kniha_id = kz.kniha_id 
        LEFT JOIN 
            zanr z ON kz.zanr_id = z.zanr_id 
        GROUP BY 
            k.kniha_id";

$result = $conn->query($sql);
?>

<!doctype html>
<html lang="cs">

<head>
    <title>Katalog knih</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <header>
        <nav>
        </nav>
        
        <div class="text-center mt-4 p-5 bg-primary text-white rounded">
            <h1>Katalog knih</h1>
        </div>
    </header>
    <main>
        <div class="container">
            <?php
            if ($result->num_rows > 0) {
                echo "<ul>";
                while($row = $result->fetch_assoc()) {
                    echo "<li class='row'>";
                    $nazev = $row["kniha_nazev"];
                    $isbn = $row["kniha_isbn"];
                    $rok = $row["kniha_rok"];
                    $vydavatel = $row["kniha_vydavatel"];
                    $pocet = $row["kniha_pocet"];
                    $popis = $row["kniha_popis"];
                    $autori = $row["autori"];
                    $zanry = $row["zanry"];
                    
                    echo "<div class='col-md-12'>";
                    echo "<h2>Název: $nazev</h2>";
                    echo "ISBN: $isbn";
                    echo "Rok vydání: $rok";
                    echo "</div>";
                    echo "<div class='col-md-12'>";         
                    echo "<p>Autoři: $autori</p>";
                    echo "<p>Vydavatel: $vydavatel</p>";
                    echo "</div>";
                    echo "<div class='col-md-12'>";
                    echo "<p>Počet stránek: $pocet</p>";
                    echo "<p>Žánry: $zanry</p>";
                    echo "<p>Popis: $popis</p>";
                    echo "</div>";
                    echo "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>Žádné knihy nebyly nalezeny.</p>";
            }
            
            $conn->close();
            ?>
        </div>
    </main>

    <footer>
        <p>Školní projekt v rámci předmětu Databázové systémy II | © Ondřej Marek</p>
    </footer>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</html>