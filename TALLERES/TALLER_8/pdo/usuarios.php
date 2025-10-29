<?php
require __DIR__ . '/config.php';
$pdo = pdo();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create') {
        $name = str_or_null($_POST['name'] ?? null);
        $email = valid_email($_POST['email'] ?? null);
        $pass = str_or_null($_POST['password'] ?? null);
        if (!$name || !$email || !$pass) die('Datos inválidos');
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare('INSERT INTO users (name,email,password_hash) VALUES (?,?,?)')->execute([$name,$email,$hash]);
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
            $pdo->prepare('UPDATE users SET name=?, email=?, password_hash=? WHERE id=?')->execute([$name,$email,$hash,$id]);
        } else {
            $pdo->prepare('UPDATE users SET name=?, email=? WHERE id=?')->execute([$name,$email,$id]);
        }
        header('Location: usuarios.php'); exit;
    }
}

if ($action === 'delete') {
    $id = int_or_null($_GET['id'] ?? null); if (!$id) die('ID inválido');
    $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    header('Location: usuarios.php'); exit;
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Usuarios (PDO)</title>
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
if ($q) {
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM users WHERE name LIKE :q OR email LIKE :q');
    $stmt->execute([':q'=>"%$q%"]); $total=(int)$stmt->fetchColumn();
    $stmt=$pdo->prepare('SELECT id,name,email,created_at FROM users WHERE name LIKE :q OR email LIKE :q ORDER BY id DESC LIMIT :lim OFFSET :off');
    $stmt->bindValue(':q',"%$q%",PDO::PARAM_STR);
    $stmt->bindValue(':lim',$per,PDO::PARAM_INT);
    $stmt->bindValue(':off',$offset,PDO::PARAM_INT);
    $stmt->execute();
} else {
    $total=(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stmt=$pdo->prepare('SELECT id,name,email,created_at FROM users ORDER BY id DESC LIMIT :lim OFFSET :off');
    $stmt->bindValue(':lim',$per,PDO::PARAM_INT);
    $stmt->bindValue(':off',$offset,PDO::PARAM_INT);
    $stmt->execute();
}
$rows=$stmt->fetchAll();
?>
<table>
  <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Creado</th><th>Acciones</th></tr>
  <?php foreach($rows as $r): ?>
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
  <?php endforeach; ?>
</table>
<?php render_pagination($total,$page,$per,'?action=list'.($q?'&q='.urlencode($q):'')); ?>

<?php if (($action==='edit') && ($id=int_or_null($_GET['id']??null))):
    $stmt=$pdo->prepare('SELECT id,name,email FROM users WHERE id=?'); $stmt->execute([$id]); $u=$stmt->fetch();
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
