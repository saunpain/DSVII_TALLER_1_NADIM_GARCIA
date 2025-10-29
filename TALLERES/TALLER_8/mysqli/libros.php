<?php
require __DIR__ . '/config.php';
$cn = db();

$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $title = str_or_null($_POST['title'] ?? null);
        $author = str_or_null($_POST['author'] ?? null);
        $isbn = str_or_null($_POST['isbn'] ?? null);
        $year = int_or_null($_POST['year'] ?? null);
        $qty = int_or_null($_POST['quantity'] ?? null);
        if (!$title || !$author || !$isbn || !$year || $qty===null) die('Datos inválidos');
        $stmt = $cn->prepare('INSERT INTO books (title,author,isbn,year,quantity) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssii', $title,$author,$isbn,$year,$qty);
        $stmt->execute();
        header('Location: libros.php'); exit;
    }
    if ($action === 'update') {
        $id = int_or_null($_POST['id'] ?? null); if (!$id) die('ID inválido');
        $title = str_or_null($_POST['title'] ?? null);
        $author = str_or_null($_POST['author'] ?? null);
        $isbn = str_or_null($_POST['isbn'] ?? null);
        $year = int_or_null($_POST['year'] ?? null);
        $qty = int_or_null($_POST['quantity'] ?? null);
        if (!$title || !$author || !$isbn || !$year || $qty===null) die('Datos inválidos');
        $stmt = $cn->prepare('UPDATE books SET title=?,author=?,isbn=?,year=?,quantity=? WHERE id=?');
        $stmt->bind_param('sssiii', $title,$author,$isbn,$year,$qty,$id);
        $stmt->execute();
        header('Location: libros.php'); exit;
    }
}

if ($action === 'delete') {
    $id = int_or_null($_GET['id'] ?? null); if (!$id) die('ID inválido');
    $stmt = $cn->prepare('DELETE FROM books WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: libros.php'); exit;
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Libros (MySQLi)</title>
<style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
</head><body>
<p><a href="index.php">← Volver</a></p>
<h2>Libros</h2>

<h3>Agregar</h3>
<form method="post" action="?action=create">
  <input name="title" placeholder="Título" required>
  <input name="author" placeholder="Autor" required>
  <input name="isbn" placeholder="ISBN" required>
  <input name="year" type="number" placeholder="Año" required>
  <input name="quantity" type="number" placeholder="Cantidad" required>
  <button>Crear</button>
</form>

<h3>Buscar</h3>
<form method="get">
  <input type="hidden" name="action" value="list">
  <input name="q" placeholder="título/autor/ISBN" value="<?= e($_GET['q'] ?? '') ?>">
  <button>Buscar</button>
</form>

<?php
[$page,$per,$offset] = paginate();
$q = str_or_null($_GET['q'] ?? null);
$params = [];
$sqlWhere = '';
if ($q) {
    $like = "%{$q}%";
    $sqlWhere = 'WHERE title LIKE ? OR author LIKE ? OR isbn LIKE ?';
}

if ($q) {
    $stmt = $cn->prepare("SELECT COUNT(*) FROM books $sqlWhere");
    $stmt->bind_param('sss', $like,$like,$like);
} else {
    $stmt = $cn->prepare('SELECT COUNT(*) FROM books');
}
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

if ($q) {
    $stmt = $cn->prepare("SELECT id,title,author,isbn,year,quantity FROM books $sqlWhere ORDER BY id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('sssii', $like,$like,$like,$per,$offset);
} else {
    $stmt = $cn->prepare('SELECT id,title,author,isbn,year,quantity FROM books ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ii', $per,$offset);
}
$stmt->execute();
$res = $stmt->get_result();
?>

<table>
  <tr><th>ID</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Año</th><th>Cantidad</th><th>Acciones</th></tr>
  <?php while($r = $res->fetch_assoc()): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['author']) ?></td>
    <td><?= e($r['isbn']) ?></td>
    <td><?= (int)$r['year'] ?></td>
    <td><?= (int)$r['quantity'] ?></td>
    <td>
      <a href="?action=edit&id=<?= (int)$r['id'] ?>">Editar</a>
      |
      <a href="?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php render_pagination((int)$total,$page,$per,'?action=list'.($q?'&q='.urlencode($q):'')); ?>

<?php if (($action==='edit') && ($id=int_or_null($_GET['id']??null))):
    $stmt = $cn->prepare('SELECT id,title,author,isbn,year,quantity FROM books WHERE id=?');
    $stmt->bind_param('i',$id); $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();
    if(!$book){ echo '<p>No encontrado</p>'; } else { ?>
    <h3>Editar</h3>
    <form method="post" action="?action=update">
      <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
      <input name="title" value="<?= e($book['title']) ?>" required>
      <input name="author" value="<?= e($book['author']) ?>" required>
      <input name="isbn" value="<?= e($book['isbn']) ?>" required>
      <input name="year" type="number" value="<?= (int)$book['year'] ?>" required>
      <input name="quantity" type="number" value="<?= (int)$book['quantity'] ?>" required>
      <button>Guardar</button>
    </form>
<?php } endif; ?>
</body></html>
