<?php
session_start();

require 'database.php';

$conn = Connection();

$orderBy = $_GET['orderBy'] ?? 'kniha_nazev';
$orderDir = $_GET['orderDir'] ?? 'ASC';
$search = $_POST['search'] ?? '1';
$searchBy = $_POST['searchBy'] ?? '1';

$validOrderByColumns = ['kniha_nazev', 'kniha_isbn', 'autori', 'kniha_vydavatel', 'zanry', 'kniha_rok', 'kniha_pocet'];
if (!in_array($orderBy, $validOrderByColumns)) {
  $orderBy = 'kniha_nazev';
}

$validOrderDirs = ['ASC', 'DESC'];
if (!in_array($orderDir, $validOrderDirs)) {
  $orderDir = 'ASC';
}

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
        WHERE 
            $searchBy LIKE '%$search%'
        GROUP BY 
            k.kniha_id 
        ORDER BY $orderBy $orderDir";

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
        
        <div class="text-center mt-4 p-5 bg-primary text-white">
            <h1>Katalog knih</h1>
        </div>
    </header>
    <main>
        <div class="container">
        <form class="row mb-4" method="POST" action="index.php">
            <div class="col-3">
                <label for="search" class="mt-3 form-label">Slovo</label>
                <input type="text" class="form-control" id="search" name="search">
            </div>
            <div class="col-3">
                <label for="search" class="mt-3 form-label">Hleda podle</label>
                <select class="form-select" name="searchBy">
                    <option value="kniha_nazev">Název knihy</option>
                    <option value="kniha_isbn">ISBN</option>
                    <option value="autor_jmeno||autor_prijmeni">Autoři</option>
                    <option value="kniha_vydavatel">Vydavatel</option>
                    <option value="zanr_nazev">Žánry</option>
                </select>
            </div>
            <div class="col-3">
                <button type="submit" class="btn btn-primary mt-5">Hledat</button>
            </div>
        </form>

            <?php
            if ($result->num_rows > 0) {
                ?>
                <form> 
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                        <?php
                            $orderDir = $orderDir == 'ASC' ? 'DESC' : 'ASC';
                        ?>
                            <th><a href="?orderBy=kniha_nazev&orderDir=<?=$orderDir?>">Název</a></th>
                            <th><a href="?orderBy=kniha_isbn&orderDir=<?=$orderDir?>">ISBN</a></th>
                            <th><a href="?orderBy=autori&orderDir=<?=$orderDir?>">Autoři</a></th>
                            <th><a href="?orderBy=kniha_vydavatel&orderDir=<?=$orderDir?>">Vydavatel</a></th>
                            <th><a href="?orderBy=zanry&orderDir=<?=$orderDir?>">Žánry</a></th>
                            <th class="col-1"><a href="?orderBy=kniha_rok&orderDir=<?=$orderDir?>">Rok vydání</a></th>
                            <th class="col-1"><a href="?orderBy=kniha_pocet&orderDir=<?=$orderDir?>">Počet</a></th>
                        </tr>
                    </thead>
                    <tbody>
                <?php
                while($row = $result->fetch_assoc()) {
                    $nazev = $row["kniha_nazev"];
                    $isbn = $row["kniha_isbn"];
                    $rok = $row["kniha_rok"];
                    $vydavatel = $row["kniha_vydavatel"];
                    $pocet = $row["kniha_pocet"];
                    $popis = $row["kniha_popis"];
                    $autori = $row["autori"];
                    $zanry = $row["zanry"];
                ?>
                        <tr>
                            <td><?= $nazev ?></td>
                            <td><?= $isbn ?></td>
                            <td><?= $autori ?></td>
                            <td><?= $vydavatel ?></td>
                            <td><?= $zanry ?></td>
                            <td><?= $rok ?></td>
                            <td><?= $pocet ?></td>
                        </tr>
                    <?php
                }
                    ?>
                        <tr>      
                            <td><input type="text" class="form-control" id="nazev" name="nazev"></td>
                            <td><input type="text" class="form-control" id="nazev" name="nazev"></td>
                            <td><input type="text" class="form-control" id="nazev" name="nazev"></td>
                            <td><input type="text" class="form-control" id="nazev" name="nazev"></td>
                            <td><input type="text" class="form-control" id="nazev" name="nazev"></td>
                            <td><input type="number" class="form-control" id="nazev" name="nazev"></td>
                            <td><input type="number" class="form-control" id="nazev" name="nazev"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Přidat</button>
                </div>
                </form>
            <?php
            } else {
                echo "<p>Žádné knihy nebyly nalezeny.</p>";
            }
            
            $conn->close();
            ?>
        </div>
    </main>

    <footer class="text-center mt-4 p-5 bg-primary text-white">
        <p>Školní projekt v rámci předmětu Databázové systémy II | © Ondřej Marek</p>
    </footer>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</html>