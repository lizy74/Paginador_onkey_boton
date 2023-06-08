<?php
// Realizar la conexión a la base de datos y ejecutar la consulta

$host = 'localhost:33065';
$db = 'empresa2';
$user = 'root';
$password = '';

try {
  $dsn = "mysql:host=$host;dbname=$db";
  $pdo = new PDO($dsn, $user, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

  // Obtener la consulta y la página enviadas por AJAX
  $consulta = isset($_POST['consulta']) ? $_POST['consulta'] : '';
  $pagina = isset($_POST['pagina']) ? $_POST['pagina'] : 1;

  if ($consulta === '') {
    echo '';
    return;
  }

  // Calcular el rango de registros a mostrar
  $registrosPorPagina = 10;
  $inicio = ($pagina - 1) * $registrosPorPagina;

  // Realizar la consulta a la base de datos
  $stmt = $pdo->prepare("SELECT * FROM empleado WHERE nombre LIKE :consulta LIMIT :inicio, :registrosPorPagina");
  $stmt->bindValue(':consulta', "%$consulta%", PDO::PARAM_STR);
  $stmt->bindValue(':inicio', $inicio, PDO::PARAM_INT);
  $stmt->bindValue(':registrosPorPagina', $registrosPorPagina, PDO::PARAM_INT);
  $stmt->execute();
  $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Obtener el total de registros para paginación
  $stmtTotal = $pdo->prepare("SELECT COUNT(*) AS total FROM empleado WHERE nombre LIKE :consulta");
  $stmtTotal->bindValue(':consulta', "%$consulta%", PDO::PARAM_STR);
  $stmtTotal->execute();
  $totalRegistros = $stmtTotal->fetchColumn();

  // Calcular el total de páginas
  $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

  // Preparar los datos para enviar como JSON
  $datos = array(
    'registros' => $resultados,
    'paginaActual' => $pagina,
    'totalPaginas' => $totalPaginas
  );

  echo json_encode($datos);
} catch (PDOException $e) {
  echo 'Error: ' . $e->getMessage();
}
?>
