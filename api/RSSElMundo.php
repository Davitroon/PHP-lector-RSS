<?php

require_once "conexionRSS.php";
require_once "conexionBBDD.php";

$sXML = download("https://e00-elmundo.uecdn.es/elmundo/rss/espana.xml");

if (!$sXML) {
    die("Error: no se pudo descargar el XML.");
}

try {
    $oXML = new SimpleXMLElement($sXML);
} catch (Exception $e) {
    die("Error: el XML no es válido. " . $e->getMessage());
}

// Traer todos los links de una sola vez antes del bucle
$existingLinks = [];
$response = $db->query("SELECT link FROM elmundo");

// Verificamos si hay resultados y los guardamos en un array simple
if (isset($response['results'][0]['response']['result']['rows'])) {
    foreach ($response['results'][0]['response']['result']['rows'] as $row) {
        // En la API de Turso, el valor suele venir en $row[0]['value'] o $row[0]
        $val = isset($row[0]['value']) ? $row[0]['value'] : $row[0];
        $existingLinks[] = $val;
    }
}

$contador = 0;
$categoria = ["Política","Deportes","Ciencia","España","Economía","Música","Cine","Europa","Justicia"];
$categoriaFiltro = "";

foreach ($oXML->channel->item as $item) {

    $media = $item->children("media", true);
    $description = (string)$media->description;
    $link = (string)$item->link;

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

    // Usamos el array en memoria (mucho más rápido)
    $Repit = in_array($link, $existingLinks);

    // Insertar si no existe y hay categoría
    if (!$Repit && $categoriaFiltro !== "") {
        
        $sql = "INSERT INTO elmundo (titulo, link, descripcion, categoria, fPubli, contenido) VALUES (?, ?, ?, ?, ?, ?)";
        
        // Preparamos los parámetros en orden
        $params = [
            (string)$item->title,
            $link,
            $description,
            $categoriaFiltro,
            $new_fPubli,
            (string)$item->guid
        ];

        // Ejecutamos
        $db->query($sql, $params);
        
        // Añadimos al array local para no intentar insertarlo de nuevo si sale duplicado en el mismo XML
        $existingLinks[] = $link;
        $contador++;
    }

    $categoriaFiltro = "";
}
?>