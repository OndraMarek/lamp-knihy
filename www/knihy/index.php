<?php
session_start();

require 'database.php';
include 'queries.php';

$conn = Connection();

$orderBy = $_GET['orderBy'] ?? 'kniha_nazev';
$orderDir = $_GET['orderDir'] ?? 'ASC';
$search = $_GET['search'] ?? '1';
$searchBy = $_GET['searchBy'] ?? '1';

$validOrderByColumns = ['kniha_nazev', 'kniha_isbn', 'autori', 'kniha_vydavatel', 'zanry', 'kniha_rok', 'kniha_pocet'];
if (!in_array($orderBy, $validOrderByColumns)) {
  $orderBy = 'kniha_nazev';
}

$validOrderDirs = ['ASC', 'DESC'];
if (!in_array($orderDir, $validOrderDirs)) {
  $orderDir = 'ASC';
}

$result = getBooks($conn, $searchBy, $search, $orderBy, $orderDir);

$authorsResult = getAuthors($conn);

if (!$authorsResult) {
    die('Error: ' . $conn->error);
}

$genresResult = getGenres($conn);

if (!$genresResult) {
    die('Error: ' . $conn->error);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST['nazev']) || empty($_POST['isbn']) || empty($_POST['vydavatel']) || empty($_POST['rok']) || empty($_POST['pocet'])) {
        $message =  "Vyplňte všechny pole";
    }
    else{
        $nazev = $_POST['nazev'];
        $isbn = $_POST['isbn'];
        $autori = $_POST['autor'];
        $vydavatel = $_POST['vydavatel'];
        $zanry = $_POST['zanr'];
        $rok = $_POST['rok'];
        $pocet = $_POST['pocet'];

        $kniha_id = insertBook($conn, $nazev, $isbn, $vydavatel, $rok, $pocet);
        insertBookAuthor($conn, $kniha_id, $autori);
        insertBookGenre($conn, $kniha_id, $zanry);

        header("Location: " . $_SERVER['REQUEST_URI']);
        exit;
    }
}
?>

<!doctype html>
<html lang="cs">

<head>
    <title>Katalog knih</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
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
        <form class="row mb-4" method="get" action="index.php">
            <div class="col-3">
                <label for="search" class="mt-3 form-label">Vyhledávání:</label>
                <input type="text" class="form-control" id="search" name="search">
            </div>
            <div class="col-3">
                <select class="mt-5 form-select" name="searchBy">
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
                <form method="post"> 
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
                            <th class="col-1"><a href="?orderBy=kniha_pocet&orderDir=<?=$orderDir?>">Počet stran</a></th>
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
                            <td><input type="text" class="form-control" id="isbn" name="isbn"></td>
                            <td><select class="form-select multiple-select" name="autor[]" multiple>
                                <?php while ($row = $authorsResult->fetch_assoc()): ?>
                                    <option value="<?= $row['autor_id'] ?>"><?= $row['autor_jmeno'] . ' ' . $row['autor_prijmeni'] ?></option>
                                <?php endwhile; ?>
                            </select></td>
                            <td><input type="text" class="form-control" id="vydavatel" name="vydavatel"></td>
                            <td><select class="form-select multiple-select" name="zanr[]" multiple>
                                <?php while ($row = $genresResult->fetch_assoc()): ?>
                                    <option value="<?= $row['zanr_id'] ?>"><?= $row['zanr_nazev'] ?></option>
                                <?php endwhile; ?>
                            </select></td>
                            <td><input type="number" class="form-control" id="rok" name="rok" min="1"></td>
                            <td><input type="number" class="form-control" id="pocet" name="pocet" min="1"></td>
                        </tr>
                    </tbody>
                </table>
                <div class="text-end">
                    <button type="submit" class="btn btn-success">Přidat</button>
                </div>
                </form>
            <?php
            if (!empty($message)) {
                echo "<p>$message</p>";
            }
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
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.0/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $( '.multiple-select' ).select2( {
    theme: "bootstrap-5",
    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
    placeholder: $( this ).data( 'placeholder' ),
    closeOnSelect: false,
} );
</script>

</html>