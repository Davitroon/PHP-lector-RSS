<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

$sXML = download("https://ep00.epimg.net/rss/elpais/portada.xml");

if (!$sXML) {
    die("Error: no se pudo descargar el XML.");
}

try {
    $oXML = new SimpleXMLElement($sXML);
} catch (Exception $e) {
    die("Error: el XML no es válido. " . $e->getMessage());
}

// Traer todos los links existentes antes del bucle
$existingLinks = [];
$response = $db->query("SELECT link FROM elpais");

if (isset($response['results'][0]['response']['result']['rows'])) {
    foreach ($response['results'][0]['response']['result']['rows'] as $row) {
        $val = isset($row[0]['value']) ? $row[0]['value'] : $row[0];
        $existingLinks[] = $val;
    }
}

$contador = 0;
$categoria = ["Política", "Deportes", "Ciencia", "España", "Economía", "Música", "Cine", "Europa", "Justicia"];
$categoriaFiltro = "";

foreach ($oXML->channel->item as $item) {

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

    $content = $item->children("content", true);
    $encoded = (string)$content->encoded;
    $link = (string)$item->link;

    // Verificamos contra el array en memoria
    $Repit = in_array($link, $existingLinks);

    // Insertar si no existe y hay categoría
    if (!$Repit && $categoriaFiltro !== "") {
        
        $sql = "INSERT INTO elpais (titulo, link, descripcion, categoria, fPubli, contenido) VALUES (?, ?, ?, ?, ?, ?)";
        
        $params = [
            (string)$item->title,
            $link,
            (string)$item->description,
            $categoriaFiltro,
            $new_fPubli,
            $encoded
        ];

        $db->query($sql, $params);
        
        // Actualizamos lista local
        $existingLinks[] = $link;
        $contador++;
    }

    $categoriaFiltro = "";
}
?>