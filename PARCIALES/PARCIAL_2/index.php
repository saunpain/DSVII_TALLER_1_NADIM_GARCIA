<?php
require_once 'clases.php';

$gestor = new GestorInventario('productos.json');
$listaProductos = $gestor->obtenerTodos();
$itemParaEditar = null;

$estadosLegibles = [
    'disponible' => 'Disponible',
    'agotado' => 'Agotado',
    'por_recibir' => 'Por Recibir'
];

$categoriasLegibles = [
    'electronico' => 'Electrónico',
    'alimento' => 'Alimento',
    'ropa' => 'Ropa'
];

$accion = $_GET['accion'] ?? null;

switch ($accion) {
    case 'crear':
        $categoria = $_POST['categoria'] ?? '';
        $data = [
            'id' => null,
            'nombre' => $_POST['nombre'],
            'precio' => $_POST['precio'],
            'estado' => $_POST['estado'],
            'categoria' => $categoria,
            'fechaIngreso' => date('Y-m-d')
        ];

        if ($categoria == 'electronico') {
            $data['garantiaMeses'] = $_POST['garantiaMeses'];
            $nuevo = new ProductoElectronico($data);
        } elseif ($categoria == 'alimento') {
            $data['fechaVencimiento'] = $_POST['fechaVencimiento'];
            $nuevo = new ProductoAlimento($data);
        } elseif ($categoria == 'ropa') {
            $data['talla'] = $_POST['talla'];
            $nuevo = new ProductoRopa($data);
        }

        $gestor->agregar($nuevo);
        header("Location: index.php");
        break;

    case 'modificar':
        $id = $_POST['id'];
        $categoria = $_POST['categoria'];
        $data = [
            'id' => $id,
            'nombre' => $_POST['nombre'],
            'precio' => $_POST['precio'],
            'estado' => $_POST['estado'],
            'categoria' => $categoria,
            'fechaIngreso' => $_POST['fechaIngreso']
        ];

        if ($categoria == 'electronico') {
            $data['garantiaMeses'] = $_POST['garantiaMeses'];
            $nuevo = new ProductoElectronico($data);
        } elseif ($categoria == 'alimento') {
            $data['fechaVencimiento'] = $_POST['fechaVencimiento'];
            $nuevo = new ProductoAlimento($data);
        } elseif ($categoria == 'ropa') {
            $data['talla'] = $_POST['talla'];
            $nuevo = new ProductoRopa($data);
        }

        $gestor->actualizar($nuevo);
        header("Location: index.php");
        break;

    case 'eliminar':
        $gestor->eliminar($_GET['id']);
        header("Location: index.php");
        break;

    case 'cambiar_estado':
        $gestor->cambiarEstado($_GET['id'], $_GET['estado']);
        header("Location: index.php");
        break;

    case 'editar':
        $itemParaEditar = $gestor->obtenerPorId($_GET['id']);
        break;

    case 'filtrar':
        $listaProductos = $gestor->filtrarPorEstado($_GET['estado']);
        break;

    case 'ordenar':
        $campoOrden = $_GET['campo'];
        $tipoOrden = $_GET['tipo'] ?? 'asc';
        usort($listaProductos, function($a, $b) use ($campoOrden, $tipoOrden) {
            if ($a->$campoOrden == $b->$campoOrden) return 0;
            return ($tipoOrden == 'asc')
                ? ($a->$campoOrden < $b->$campoOrden ? -1 : 1)
                : ($a->$campoOrden > $b->$campoOrden ? -1 : 1);
        });
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Inventario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h1>Inventario</h1>

    <!-- Tabla -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th><th>Nombre</th><th>Precio</th><th>Categoría</th><th>Estado</th><th>Fecha Ingreso</th><th>Información de Inventario</th><th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($listaProductos as $p): ?>
            <tr>
                <td><?= $p->id ?></td>
                <td><?= $p->nombre ?></td>
                <td><?= $p->precio ?></td>
                <td><?= $categoriasLegibles[$p->categoria] ?? $p->categoria ?></td>
                <td><?= $estadosLegibles[$p->estado] ?? $p->estado ?></td>
                <td><?= $p->fechaIngreso ?></td>
                <td><?= $p->obtenerInformacionInventario() ?></td>
                <td>
                    <a href="?accion=editar&id=<?= $p->id ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="?accion=eliminar&id=<?= $p->id ?>" class="btn btn-danger btn-sm">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Formulario Crear / Editar -->
    <div class="card mt-4">
      <div class="card-header">
        <?= $itemParaEditar ? "Editar Producto" : "Crear Nuevo Producto" ?>
      </div>
      <div class="card-body">
        <form method="post" action="?accion=<?= $itemParaEditar ? "modificar" : "crear" ?>">
          <?php if($itemParaEditar): ?>
            <input type="hidden" name="id" value="<?= $itemParaEditar->id ?>">
            <input type="hidden" name="fechaIngreso" value="<?= $itemParaEditar->fechaIngreso ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" class="form-control" required 
                   value="<?= $itemParaEditar->nombre ?? '' ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Precio</label>
            <input type="number" step="0.01" name="precio" class="form-control" required 
                   value="<?= $itemParaEditar->precio ?? '' ?>">
          </div>

          <div class="mb-3">
            <label class="form-label">Categoría</label>
            <select name="categoria" id="categoria" class="form-select" required onchange="mostrarCamposCategoria()">
              <?php foreach($categoriasLegibles as $key => $val): ?>
                <option value="<?= $key ?>" <?= ($itemParaEditar && $itemParaEditar->categoria == $key) ? 'selected' : '' ?>>
                  <?= $val ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select name="estado" class="form-select" required>
              <?php foreach($estadosLegibles as $key => $val): ?>
                <option value="<?= $key ?>" <?= ($itemParaEditar && $itemParaEditar->estado == $key) ? 'selected' : '' ?>>
                  <?= $val ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Campos dinámicos -->
          <div class="mb-3" id="campoElectronico" style="display:none;">
            <label class="form-label">Garantía (meses)</label>
            <input type="number" name="garantiaMeses" class="form-control"
                   value="<?= $itemParaEditar->garantiaMeses ?? '' ?>">
          </div>

          <div class="mb-3" id="campoAlimento" style="display:none;">
            <label class="form-label">Fecha de Vencimiento</label>
            <input type="date" name="fechaVencimiento" class="form-control"
                   value="<?= $itemParaEditar->fechaVencimiento ?? '' ?>">
          </div>

          <div class="mb-3" id="campoRopa" style="display:none;">
            <label class="form-label">Talla</label>
            <select name="talla" class="form-select">
              <?php $tallas = ['XS','S','M','L','XL','XXL']; ?>
              <?php foreach($tallas as $t): ?>
                <option value="<?= $t ?>" <?= ($itemParaEditar && $itemParaEditar->talla == $t) ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <button type="submit" class="btn btn-success">💾 Guardar</button>
        </form>
      </div>
    </div>

    <script>
    function mostrarCamposCategoria() {
      const cat = document.getElementById("categoria").value;
      document.getElementById("campoElectronico").style.display = (cat === "electronico") ? "block" : "none";
      document.getElementById("campoAlimento").style.display = (cat === "alimento") ? "block" : "none";
      document.getElementById("campoRopa").style.display = (cat === "ropa") ? "block" : "none";
    }
    // ejecutar al cargar la página (útil para modo edición)
    if(document.getElementById("categoria")) mostrarCamposCategoria();
    </script>

</body>
</html>
