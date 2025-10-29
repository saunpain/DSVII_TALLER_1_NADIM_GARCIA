<?php
require __DIR__ . '/config.php';
$cn = db();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = str_or_null($_POST['name'] ?? null);
        $email = valid_email($_POST['email'] ?? null);
        $pass = str_or_null($_POST['password'] ?? null);
        if (!$name || !$email || !$pass) die('Datos inválidos');
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $cn->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)');
        $stmt->bind_param('sss',$name,$email,$hash);
        $stmt->execute();
        header('Location: usuarios.php'); exit;
    }
    if ($action === 'update') {
        $id = int_or_null($_POST['id'] ?? null); if (!$id) die('ID inválido');
        $name = str_or_null($_POST['name'] ?? null);
        $email = valid_email($_POST['email'] ?? null);
        $pass = str_or_null($_POST['password'] ?? null);
        if (!$name || !$email) die('Datos inválidos');
        if ($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $stmt = $cn->prepare('UPDATE users SET name=?, email=?, password_hash=? WHERE id=?');
            $stmt->bind_param('sssi',$name,$email,$hash,$id);
        } else {
            $stmt = $cn->prepare('UPDATE users SET name=?, email=? WHERE id=?');
            $stmt->bind_param('ssi',$name,$email,$id);
        }
        $stmt->execute();
        header('Location: usuarios.php'); exit;
    }
}

if ($action === 'delete') {
    $id = int_or_null($_GET['id'] ?? null); if (!$id) die('ID inválido');
    $stmt = $cn->prepare('DELETE FROM users WHERE id=?');
    $stmt->bind_param('i',$id); $stmt->execute();
    header('Location: usuarios.php'); exit;
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Usuarios (MySQLi)</title>
<style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
</head><body>
<p><a href="index.php">← Volver</a></p>
<h2>Usuarios</h2>

<h3>Registrar</h3>
<form method="post" action="?action=create">
  <input name="name" placeholder="Nombre" required>
  <input name="email" placeholder="Email" type="email" required>
  <input name="password" placeholder="Contraseña" type="password" required>
  <button>Crear</button>
</form>

<h3>Buscar</h3>
<form method="get">
  <input type="hidden" name="action" value="list">
  <input name="q" placeholder="nombre/email" value="<?= e($_GET['q'] ?? '') ?>">
  <button>Buscar</button>
</form>

<?php
[$page,$per,$offset] = paginate();
$q = str_or_null($_GET['q'] ?? null);
if ($q) { $like = "%{$q}%"; }

// total
if ($q) {
    $stmt = $cn->prepare('SELECT COUNT(*) FROM users WHERE name LIKE ? OR email LIKE ?');
    $stmt->bind_param('ss',$like,$like);
} else {
    $stmt = $cn->prepare('SELECT COUNT(*) FROM users');
}
$stmt->execute(); $stmt->bind_result($total); $stmt->fetch(); $stmt->close();

// list
if ($q) {
    $stmt = $cn->prepare('SELECT id,name,email,created_at FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ssii',$like,$like,$per,$offset);
} else {
    $stmt = $cn->prepare('SELECT id,name,email,created_at FROM users ORDER BY id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('ii',$per,$offset);
}
$stmt->execute(); $res = $stmt->get_result();
?>

<table>
  <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Creado</th><th>Acciones</th></tr>
  <?php while($r=$res->fetch_assoc()): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['name']) ?></td>
    <td><?= e($r['email']) ?></td>
    <td><?= e($r['created_at']) ?></td>
    <td>
      <a href="?action=edit&id=<?= (int)$r['id'] ?>">Editar</a> |
      <a href="?action=delete&id=<?= (int)$r['id'] ?>" onclick="return confirm('¿Eliminar?')">Eliminar</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php render_pagination((int)$total,$page,$per,'?action=list'.($q?'&q='.urlencode($q):'')); ?>

<?php if (($action==='edit') && ($id=int_or_null($_GET['id']??null))):
    $stmt = $cn->prepare('SELECT id,name,email FROM users WHERE id=?');
    $stmt->bind_param('i',$id); $stmt->execute();
    $u=$stmt->get_result()->fetch_assoc();
    if(!$u){ echo '<p>No encontrado</p>'; } else { ?>
    <h3>Editar</h3>
    <form method="post" action="?action=update">
      <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
      <input name="name" value="<?= e($u['name']) ?>" required>
      <input name="email" type="email" value="<?= e($u['email']) ?>" required>
      <input name="password" type="password" placeholder="(opcional) Nueva contraseña">
      <button>Guardar</button>
    </form>
<?php } endif; ?>
</body></html>
