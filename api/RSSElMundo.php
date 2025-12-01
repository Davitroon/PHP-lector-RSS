<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php"; // $db es SQLite3

$sXML = download("https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml");

if (!$sXML) {
    die("Error: no se pudo descargar el XML.");
}

try {
    $oXML = new SimpleXMLElement($sXML);
} catch (Exception $e) {
    die("Error: el XML no es válido. " . $e->getMessage());
}

$contador = 0;
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];
$categoriaFiltro = "";

foreach ($oXML->channel->item as $item) {

    $media = $item->children("media", true);
    $description = $media->description;

    // Filtrar categorías
    for ($i = 0; $i < count($item->category); $i++) {
        for ($j = 0; $j < count($categoria); $j++) {
            if ($item->category[$i] == $categoria[$j]) {
                $categoriaFiltro = "[" . $categoria[$j] . "]" . $categoriaFiltro;
            }
        }
    }

    $fPubli = strtotime($item->pubDate);
    $new_fPubli = date('Y-m-d', $fPubli);

    // Comprobar si el link ya existe
    $result = $db->query("SELECT link FROM elmundo");
    $Repit = false;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        if ($row['link'] == $item->link) {
            $Repit = true;
            $contador++;
            break;
        }
    }

    // Insertar si no existe y hay categoría
    if (!$Repit && $categoriaFiltro !== "") {
        $stmt = $db->prepare('INSERT INTO elmundo (titulo, link, descripcion, categoria, fPubli, contenido) VALUES (:titulo, :link, :descripcion, :categoria, :fPubli, :contenido)');
        $stmt->bindValue(':titulo', $item->title);
        $stmt->bindValue(':link', $item->link);
        $stmt->bindValue(':descripcion', $description);
        $stmt->bindValue(':categoria', $categoriaFiltro);
        $stmt->bindValue(':fPubli', $new_fPubli);
        $stmt->bindValue(':contenido', $item->guid);
        $stmt->execute();
    }

    $categoriaFiltro = "";
}
