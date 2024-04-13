<?php
function getBooks($conn, $searchBy, $search, $orderBy, $orderDir) {
    $sql = "SELECT 
                k.kniha_nazev, 
                k.kniha_isbn, 
                k.kniha_rok, 
                k.kniha_vydavatel, 
                k.kniha_pocet,
                GROUP_CONCAT(DISTINCT CONCAT(a.autor_jmeno, ' ', a.autor_prijmeni) SEPARATOR ', ') AS autori, 
                GROUP_CONCAT(DISTINCT z.zanr_nazev SEPARATOR ', ') AS zanry 
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
                k.kniha_id IN (
                    SELECT kz1.kniha_id FROM kniha_zanr kz1
                    JOIN zanr z1 ON kz1.zanr_id = z1.zanr_id
                    WHERE $searchBy LIKE '%$search%'
                )
            GROUP BY 
                k.kniha_id 
            ORDER BY $orderBy $orderDir";

    return $conn->query($sql);
}

function getAuthors($conn) {
    $sql = "SELECT autor_id, autor_jmeno, autor_prijmeni FROM autor";
    return $conn->query($sql);
}

function getGenres($conn) {
    $sql = "SELECT zanr_id, zanr_nazev FROM zanr";
    return $conn->query($sql);
}

function insertBook($conn, $nazev, $isbn, $vydavatel, $rok, $pocet) {
    $stmtBook = $conn->prepare("INSERT INTO kniha (kniha_nazev, kniha_isbn, kniha_vydavatel, kniha_rok, kniha_pocet) VALUES (?, ?, ?, ?, ?)");
    $stmtBook->bind_param("sssis", $nazev, $isbn, $vydavatel, $rok, $pocet);
    $stmtBook->execute();

    return $conn->insert_id;
}

function insertBookAuthor($conn, $kniha_id, $autori) {
    $stmtAuthor = $conn->prepare("INSERT INTO kniha_autor (kniha_id, autor_id) VALUES (?, ?)");
    foreach ($autori as $autor_id) {
        $stmtAuthor->bind_param("ii", $kniha_id, $autor_id);
        $stmtAuthor->execute();
    }
    $stmtAuthor->close();
}

function insertBookGenre($conn, $kniha_id, $zanry) {
    $stmtGenre = $conn->prepare("INSERT INTO kniha_zanr (kniha_id, zanr_id) VALUES (?, ?)");
    foreach ($zanry as $zanr_id) {
        $stmtGenre->bind_param("ii", $kniha_id, $zanr_id);
        $stmtGenre->execute();
    }
    $stmtGenre->close();
}

?>