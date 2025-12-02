<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSS Reader</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans text-gray-800 p-6">

    <div class="max-w-7xl mx-auto bg-white p-8 rounded-lg shadow-md">

        <h1 class="text-2xl font-bold mb-6 text-gray-700 border-b pb-2">Lector de Noticias RSS</h1>

        <form action="index.php" method="get" class="mb-8">
            <fieldset class="border border-gray-300 rounded-lg p-6">
                <legend class="text-sm font-semibold text-blue-600 px-2 uppercase tracking-wider">Filtros de Búsqueda</legend>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periódico:</label>
                        <select name="periodicos" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2 border">
                            <option value="elpais" <?php if (isset($_GET['periodicos']) && $_GET['periodicos'] == 'elpais') echo 'selected'; ?>>El País</option>
                            <option value="elmundo" <?php if (isset($_GET['periodicos']) && $_GET['periodicos'] == 'elmundo') echo 'selected'; ?>>El Mundo</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Categoría:</label>
                        <select name="categoria" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2 border">
                            <option value="">Todas</option>
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
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha:</label>
                        <input type="date" name="fecha" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2 border">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Buscar palabra:</label>
                        <input type="text" name="buscar" placeholder="En la descripción..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 focus:ring-opacity-50 p-2 border">
                    </div>
                </div>

                <div class="mt-6 text-right">
                    <input type="submit" name="filtrar" value="Filtrar Resultados" class="cursor-pointer bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded transition duration-200">
                </div>
            </fieldset>
        </form>

        <div class="overflow-x-auto">
            <?php
            require_once "conexionBBDD.php";
            require_once "RSSElPais.php";
            require_once "RSSElMundo.php";

            function filtros($sql, $db)
            {
                // Si $db falla
                if (!$db) {
                    echo "<p class='text-red-500 p-4 bg-red-100 rounded'>Error: No hay conexión a la base de datos.</p>";
                    return;
                }

                $response = $db->query($sql);

                // Verificación de la estructura de respuesta 
                if (!isset($response['results'][0]['response']['result'])) {
                    echo "<p class='text-gray-500 italic p-4 text-center'>Realiza una búsqueda para ver resultados.</p>";
                    return;
                }

                $data = $response['results'][0]['response']['result'];

                // Si no hay filas
                if (empty($data['rows'])) {
                    echo "<div class='bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4' role='alert'>
                            <p>No hay noticias con ese filtro.</p>
                          </div>";
                    return;
                }

                $cols = $data['cols'];
                $rows = $data['rows'];

                // INICIO DE LA TABLA
                echo "<table class='min-w-full divide-y divide-gray-200 text-sm border border-gray-200 rounded-lg overflow-hidden'>";

                // Cabecera
                echo "<thead class='bg-gray-50'>";
                echo "<tr>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Título</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Contenido</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Descripción</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Categoría</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider'>Enlace</th>
                        <th class='px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24'>Fecha</th>
                      </tr>";
                echo "</thead>";

                // Cuerpo
                echo "<tbody class='bg-white divide-y divide-gray-200'>";

                foreach ($rows as $rawRow) {
                    $row = [];
                    foreach ($rawRow as $index => $cell) {
                        $colName = $cols[$index]['name'];
                        $val = (is_array($cell) && isset($cell['value'])) ? $cell['value'] : $cell;
                        $row[$colName] = $val;
                    }

                    echo "<tr class='hover:bg-gray-50 transition duration-150'>";

                    // Título
                    echo "<td class='px-6 py-4 font-medium text-gray-900'>" . $row['titulo'] . "</td>";

                    // Contenido 
                    $contenido = isset($row['contenido']) ? substr($row['contenido'], 0, 100) . "..." : "";
                    echo "<td class='px-6 py-4 text-gray-500'>" . $contenido . "</td>";

                    // Descripción
                    echo "<td class='px-6 py-4 text-gray-500'>" . $row['descripcion'] . "</td>";

                    // Categoría 
                    echo "<td class='px-6 py-4 whitespace-nowrap'><span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800'>" . $row['categoria'] . "</span></td>";

                    // Enlace
                    echo "<td class='px-6 py-4 whitespace-nowrap text-blue-600 hover:text-blue-900'><a href='" . $row['link'] . "' target='_blank' class='hover:underline'>Leer más</a></td>";

                    // Fecha
                    $fecha = date_create($row['fPubli']);
                    $fechaConversion = $fecha ? date_format($fecha, 'd M Y') : $row['fPubli'];
                    echo "<td class='px-6 py-4 whitespace-nowrap text-gray-500'>" . $fechaConversion . "</td>";

                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            }

            // --- Lógica de Filtros (Sin cambios funcionales, solo recuperación de variables) ---
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
            if (isset($db)) {
                filtros($sql, $db);
            }
            ?>
        </div>
    </div>
</body>

</html>