<?php
require __DIR__ . '/config.php';
$pdo = pdo();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $title = str_or_null($_POST['title'] ?? null);
        $author = str_or_null($_POST['author'] ?? null);
        $isbn = str_or_null($_POST['isbn'] ?? null);
        $year = int_or_null($_POST['year'] ?? null);
        $qty = int_or_null($_POST['quantity'] ?? null);
        if (!$title || !$author || !$isbn || !$year || $qty===null) die('Datos inválidos');
        $stmt = $pdo->prepare('INSERT INTO books (title,author,isbn,year,quantity) VALUES (?,?,?,?,?)');
        $stmt->execute([$title,$author,$isbn,$year,$qty]);
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
        $stmt = $pdo->prepare('UPDATE books SET title=?,author=?,isbn=?,year=?,quantity=? WHERE id=?');
        $stmt->execute([$title,$author,$isbn,$year,$qty,$id]);
        header('Location: libros.php'); exit;
    }
}

if ($action === 'delete') {
    $id = int_or_null($_GET['id'] ?? null); if (!$id) die('ID inválido');
    $pdo->prepare('DELETE FROM books WHERE id=?')->execute([$id]);
    header('Location: libros.php'); exit;
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Libros (PDO)</title>
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
$where='';
if ($q) { $where='WHERE title LIKE :q OR author LIKE :q OR isbn LIKE :q'; }

if ($q) {
    $stmt=$pdo->prepare("SELECT COUNT(*) FROM books $where");
    $stmt->execute([':q'=>"%$q%"]); $total=(int)$stmt->fetchColumn();
} else { $total=(int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(); }

if ($q) {
    $stmt=$pdo->prepare("SELECT id,title,author,isbn,year,quantity FROM books $where ORDER BY id DESC LIMIT :lim OFFSET :off");
    $stmt->bindValue(':q',"%$q%",PDO::PARAM_STR);
    $stmt->bindValue(':lim',$per,PDO::PARAM_INT);
    $stmt->bindValue(':off',$offset,PDO::PARAM_INT);
    $stmt->execute();
} else {
    $stmt=$pdo->prepare('SELECT id,title,author,isbn,year,quantity FROM books ORDER BY id DESC LIMIT :lim OFFSET :off');
    $stmt->bindValue(':lim',$per,PDO::PARAM_INT);
    $stmt->bindValue(':off',$offset,PDO::PARAM_INT);
    $stmt->execute();
}
$rows=$stmt->fetchAll();
?>
<table>
  <tr><th>ID</th><th>Título</th><th>Autor</th><th>ISBN</th><th>Año</th><th>Cantidad</th><th>Acciones</th></tr>
  <?php foreach($rows as $r): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['title']) ?></td>
    <td><?= e($r['author']) ?></td>
    <td><?= e($r['isbn']) ?></td>
    <td><?= (int)$r['year'] ?></td>
    <td><?= (int)$r['quantity'] ?></td>
    <td>
      <a href="?action=edit&id=<?= (int)$r['id'] ?>">Editar</a> |
      <a href="?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php render_pagination($total,$page,$per,'?action=list'.($q?'&q='.urlencode($q):'')); ?>

<?php if (($action==='edit') && ($id=int_or_null($_GET['id']??null))):
    $stmt=$pdo->prepare('SELECT * FROM books WHERE id=?'); $stmt->execute([$id]); $book=$stmt->fetch();
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
