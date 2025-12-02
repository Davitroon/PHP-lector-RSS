<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>RSS Reader</title>
</head>

<body>
    <form action="index.php" method="get">
        <fieldset>
            <legend>FILTRO</legend>
            <label>PERIODICO: </label>
            <select name="periodicos">
                <option value="elpais">El País</option>
                <option value="elmundo">El Mundo</option>
            </select>

            <label>CATEGORIA: </label>
            <select name="categoria">
                <option value=""></option>
                <option value="Política">Política</option>
                <option value="Deportes">Deportes</option>
                <option value="Ciencia">Ciencia</option>
                <option value="España">España</option>
                <option value="Economía">Economía</option>
                <option value="Música">Música</option>
                <option value="Cine">Cine</option>
                <option value="Europa">Europa</option>
                <option value="Justicia">Justicia</option>
            </select>

            <label>FECHA: </label>
            <input type="date" name="fecha">

            <label style="margin-left: 5vw;">AMPLIAR FILTRO (la descripción contenga la palabra): </label>
            <input type="text" name="buscar">

            <input type="submit" name="filtrar" value="Filtrar">
        </fieldset>
    </form>

    <?php
    require_once "conexionBBDD.php";
    require_once "RSSElPais.php";
    require_once "RSSElMundo.php";

    function filtros($sql, $db)
    {
        $response = $db->query($sql);

        if (!isset($response['results'][0]['response']['result'])) {
            echo "<p>No se encontraron resultados o hubo un error en la conexión.</p>";
            var_dump($response);
            return;
        }

        $data = $response['results'][0]['response']['result'];

        // Si no hay filas, salimos
        if (empty($data['rows'])) {
            echo "<p>No hay noticias con ese filtro.</p>";
            return;
        }

        $cols = $data['cols'];
        $rows = $data['rows'];

        echo "<table style='border: 5px #E4CCE8 solid;'>";
        echo "<tr>
                <th style='color:#66E9D9;'>TITULO</th>
                <th style='color:#66E9D9;'>CONTENIDO</th>
                <th style='color:#66E9D9;'>DESCRIPCIÓN</th>
                <th style='color:#66E9D9;'>CATEGORÍA</th>
                <th style='color:#66E9D9;'>ENLACE</th>
                <th style='color:#66E9D9;'>FECHA DE PUBLICACIÓN</th>
              </tr>";

        foreach ($rows as $rawRow) {

            $row = [];
            foreach ($rawRow as $index => $cell) {
                $colName = $cols[$index]['name'];
                $val = (is_array($cell) && isset($cell['value'])) ? $cell['value'] : $cell;
                $row[$colName] = $val;
            }

            echo "<tr>";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['titulo'] . "</td>";

            $contenido = isset($row['contenido']) ? substr($row['contenido'], 0, 100) . "..." : "";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $contenido . "</td>";

            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['descripcion'] . "</td>";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['categoria'] . "</td>";
            echo "<td style='border: 1px #E4CCE8 solid;'><a href='" . $row['link'] . "' target='_blank'>Enlace</a></td>";

            $fecha = date_create($row['fPubli']);
            $fechaConversion = $fecha ? date_format($fecha, 'd-M-Y') : $row['fPubli'];
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $fechaConversion . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Variables de filtros
    $periodicos = isset($_GET['periodicos']) ? $_GET['periodicos'] : 'elpais';
    $categoria  = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $fecha      = isset($_GET['fecha']) ? $_GET['fecha'] : '';
    $palabra    = isset($_GET['buscar']) ? $_GET['buscar'] : '';

    if ($periodicos !== 'elpais' && $periodicos !== 'elmundo') {
        $periodicos = 'elpais';
    }

    $sql = "SELECT * FROM $periodicos WHERE 1=1";

    if ($categoria !== '') {
        $sql .= " AND categoria LIKE '%$categoria%'";
    }
    if ($fecha !== '') {
        $sql .= " AND fPubli = '$fecha'";
    }
    if ($palabra !== '') {
        $sql .= " AND descripcion LIKE '%$palabra%'";
    }

    $sql .= " ORDER BY fPubli DESC LIMIT 50";

    // Mostrar resultados
    filtros($sql, $db);
    ?>
</body>

</html>