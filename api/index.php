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
    require_once "conexionBBDD.php"; // $db es SQLite3
    require_once "RSSElPais.php";
    require_once "RSSElMundo.php";

    // Función para mostrar resultados
    function filtros($sql, $db)
    {
        $result = $db->query($sql);
        echo "<table style='border: 5px #E4CCE8 solid;'>";
        echo "<tr>
                <th style='color:#66E9D9;'>TITULO</th>
                <th style='color:#66E9D9;'>CONTENIDO</th>
                <th style='color:#66E9D9;'>DESCRIPCIÓN</th>
                <th style='color:#66E9D9;'>CATEGORÍA</th>
                <th style='color:#66E9D9;'>ENLACE</th>
                <th style='color:#66E9D9;'>FECHA DE PUBLICACIÓN</th>
              </tr>";

        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['titulo'] . "</td>";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['contenido'] . "</td>";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['descripcion'] . "</td>";
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $row['categoria'] . "</td>";
            echo "<td style='border: 1px #E4CCE8 solid;'><a href='" . $row['link'] . "' target='_blank'>Enlace</a></td>";
            $fecha = date_create($row['fPubli']);
            $fechaConversion = date_format($fecha, 'd-M-Y');
            echo "<td style='border: 1px #E4CCE8 solid;'>" . $fechaConversion . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

    // Variables de filtros
    $periodicos = isset($_GET['periodicos']) ? $_GET['periodicos'] : 'elpais';
    $categoria   = isset($_GET['categoria']) ? $_GET['categoria'] : '';
    $fecha       = isset($_GET['fecha']) ? $_GET['fecha'] : '';
    $palabra     = isset($_GET['buscar']) ? $_GET['buscar'] : '';

    // Construcción dinámica de la consulta
    $sql = "SELECT * FROM $periodicos WHERE 1=1"; // 1=1 para concatenar condiciones

    if ($categoria !== '') {
        $sql .= " AND categoria LIKE '%$categoria%'";
    }
    if ($fecha !== '') {
        $sql .= " AND fPubli = '$fecha'";
    }
    if ($palabra !== '') {
        $sql .= " AND descripcion LIKE '%$palabra%'";
    }

    $sql .= " ORDER BY fPubli DESC";

    // Mostrar resultados
    filtros($sql, $db);
    ?>
</body>

</html>