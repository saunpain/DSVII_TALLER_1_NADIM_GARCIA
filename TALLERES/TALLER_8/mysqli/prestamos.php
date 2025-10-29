<?php
require __DIR__ . '/config.php';
$cn = db();
$action = $_GET['action'] ?? 'list';

function get_users(mysqli $cn){ return $cn->query('SELECT id,name FROM users ORDER BY name')->fetch_all(MYSQLI_ASSOC); }
function get_books_avail(mysqli $cn){ return $cn->query('SELECT id,title,quantity FROM books WHERE quantity > 0 ORDER BY title')->fetch_all(MYSQLI_ASSOC); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'loan') {
        $user_id = int_or_null($_POST['user_id'] ?? null);
        $book_id = int_or_null($_POST['book_id'] ?? null);
        if (!$user_id || !$book_id) die('Datos inválidos');
        $cn->begin_transaction();
        try {
            $stmt = $cn->prepare('SELECT quantity FROM books WHERE id=? FOR UPDATE');
            $stmt->bind_param('i',$book_id); $stmt->execute();
            $stmt->bind_result($qty); $stmt->fetch(); $stmt->close();
            if ($qty === null || $qty < 1) throw new Exception('Sin stock');
            
            $stmt = $cn->prepare('INSERT INTO loans (user_id, book_id) VALUES (?,?)');
            $stmt->bind_param('ii',$user_id,$book_id); $stmt->execute();
            
            $stmt = $cn->prepare('UPDATE books SET quantity = quantity - 1 WHERE id=?');
            $stmt->bind_param('i',$book_id); $stmt->execute();
            $cn->commit();
            header('Location: prestamos.php'); exit;
        } catch (Throwable $e) {
            $cn->rollback();
            die('Error: '.$e->getMessage());
        }
    }
    if ($action === 'return') {
        $loan_id = int_or_null($_POST['loan_id'] ?? null);
        if (!$loan_id) die('ID inválido');
        $cn->begin_transaction();
        try {
            $stmt = $cn->prepare('SELECT book_id, returned FROM loans WHERE id=? FOR UPDATE');
            $stmt->bind_param('i',$loan_id); $stmt->execute();
            $stmt->bind_result($book_id,$returned); $stmt->fetch(); $stmt->close();
            if ($book_id===null) throw new Exception('Préstamo no existe');
            if ((int)$returned === 1) throw new Exception('Ya devuelto');
            
            $stmt = $cn->prepare('UPDATE loans SET returned=1, return_date=NOW() WHERE id=?');
            $stmt->bind_param('i',$loan_id); $stmt->execute();
            
            $stmt = $cn->prepare('UPDATE books SET quantity = quantity + 1 WHERE id=?');
            $stmt->bind_param('i',$book_id); $stmt->execute();
            $cn->commit();
            header('Location: prestamos.php'); exit;
        } catch (Throwable $e) {
            $cn->rollback();
            die('Error: '.$e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Préstamos (MySQLi)</title>
<style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
</head><body>
<p><a href="index.php">← Volver</a></p>
<h2>Préstamos</h2>

<h3>Registrar Préstamo</h3>
<form method="post" action="?action=loan">
  <select name="user_id" required>
    <option value="">Seleccione usuario</option>
    <?php foreach(get_users($cn) as $u): ?>
      <option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="book_id" required>
    <option value="">Seleccione libro (disponible)</option>
    <?php foreach(get_books_avail($cn) as $b): ?>
      <option value="<?= (int)$b['id'] ?>"><?= e($b['title']) ?> (<?= (int)$b['quantity'] ?>)</option>
    <?php endforeach; ?>
  </select>
  <button>Prestar</button>
</form>

<h3>Préstamos activos</h3>
<?php
[$page,$per,$offset] = paginate();
$stmt = $cn->prepare('SELECT COUNT(*) FROM loans WHERE returned=0');
$stmt->execute(); $stmt->bind_result($totalAct); $stmt->fetch(); $stmt->close();
$stmt = $cn->prepare('SELECT l.id, u.name as user_name, b.title as book_title, l.loan_date
  FROM loans l
  JOIN users u ON u.id = l.user_id
  JOIN books b ON b.id = l.book_id
  WHERE l.returned=0
  ORDER BY l.id DESC LIMIT ? OFFSET ?');
$stmt->bind_param('ii',$per,$offset); $stmt->execute(); $res = $stmt->get_result();
?>
<table>
  <tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Fecha préstamo</th><th>Acción</th></tr>
  <?php while($r=$res->fetch_assoc()): ?>
  <tr>
    <td><?= (int)$r['id'] ?></td>
    <td><?= e($r['user_name']) ?></td>
    <td><?= e($r['book_title']) ?></td>
    <td><?= e($r['loan_date']) ?></td>
    <td>
      <form method="post" action="?action=return" style="display:inline">
        <input type="hidden" name="loan_id" value="<?= (int)$r['id'] ?>">
        <button onclick="return confirm('Marcar devolución?')">Devolver</button>
      </form>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php render_pagination((int)$totalAct,$page,$per,'?action=list'); ?>

<h3>Historial por usuario</h3>
<form method="get">
  <input type="hidden" name="action" value="history">
  <input name="user_id" type="number" placeholder="ID de usuario" value="<?= e($_GET['user_id'] ?? '') ?>" required>
  <button>Ver historial</button>
</form>

<?php if (($action==='history') && ($uid=int_or_null($_GET['user_id']??null))):
    [$page,$per,$offset] = paginate();
    $stmt=$cn->prepare('SELECT COUNT(*) FROM loans WHERE user_id=?');
    $stmt->bind_param('i',$uid); $stmt->execute(); $stmt->bind_result($t); $stmt->fetch(); $stmt->close();
    $stmt=$cn->prepare('SELECT l.id,b.title,l.loan_date,l.return_date,l.returned
        FROM loans l JOIN books b ON b.id=l.book_id WHERE l.user_id=?
        ORDER BY l.id DESC LIMIT ? OFFSET ?');
    $stmt->bind_param('iii',$uid,$per,$offset); $stmt->execute(); $res=$stmt->get_result();
    echo '<h4>Usuario ID '.(int)$uid.'</h4>';
    echo '<table><tr><th>ID</th><th>Libro</th><th>Fecha préstamo</th><th>Fecha devolución</th><th>Estado</th></tr>';
    while($r=$res->fetch_assoc()){
        echo '<tr><td>'.(int)$r['id'].'</td><td>'.e($r['title']).'</td><td>'.e($r['loan_date']).'</td><td>'.e($r['return_date']??'-').'</td><td>'.((int)$r['returned']? 'Devuelto':'Activo').'</td></tr>';
    }
    echo '</table>';
    render_pagination((int)$t,$page,$per,'?action=history&user_id='.(int)$uid);
endif; ?>
</body></html>
