<?php
require __DIR__ . '/config.php';
$pdo = pdo();
$action = $_GET['action'] ?? 'list';

function get_users(PDO $pdo){ return $pdo->query('SELECT id,name FROM users ORDER BY name')->fetchAll(); }
function get_books_avail(PDO $pdo){ return $pdo->query('SELECT id,title,quantity FROM books WHERE quantity>0 ORDER BY title')->fetchAll(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'loan') {
        $user_id = int_or_null($_POST['user_id'] ?? null);
        $book_id = int_or_null($_POST['book_id'] ?? null);
        if (!$user_id || !$book_id) die('Datos inválidos');
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT quantity FROM books WHERE id=? FOR UPDATE');
            $stmt->execute([$book_id]); $qty = $stmt->fetchColumn();
            if ($qty === false || (int)$qty < 1) throw new Exception('Sin stock');
            $pdo->prepare('INSERT INTO loans (user_id, book_id) VALUES (?,?)')->execute([$user_id,$book_id]);
            $pdo->prepare('UPDATE books SET quantity=quantity-1 WHERE id=?')->execute([$book_id]);
            $pdo->commit();
            header('Location: prestamos.php'); exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            die('Error: '.$e->getMessage());
        }
    }
    if ($action === 'return') {
        $loan_id = int_or_null($_POST['loan_id'] ?? null);
        if (!$loan_id) die('ID inválido');
        $pdo->beginTransaction();
        try {
            $stmt=$pdo->prepare('SELECT book_id, returned FROM loans WHERE id=? FOR UPDATE');
            $stmt->execute([$loan_id]); $row=$stmt->fetch();
            if(!$row) throw new Exception('Préstamo no existe');
            if ((int)$row['returned']===1) throw new Exception('Ya devuelto');
            $pdo->prepare('UPDATE loans SET returned=1, return_date=NOW() WHERE id=?')->execute([$loan_id]);
            $pdo->prepare('UPDATE books SET quantity=quantity+1 WHERE id=?')->execute([$row['book_id']]);
            $pdo->commit();
            header('Location: prestamos.php'); exit;
        } catch (Throwable $e) {
            $pdo->rollBack();
            die('Error: '.$e->getMessage());
        }
    }
}
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>Préstamos (PDO)</title>
<style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #ddd;padding:8px}</style>
</head><body>
<p><a href="index.php">← Volver</a></p>
<h2>Préstamos</h2>

<h3>Registrar Préstamo</h3>
<form method="post" action="?action=loan">
  <select name="user_id" required>
    <option value="">Seleccione usuario</option>
    <?php foreach(get_users($pdo) as $u): ?>
      <option value="<?= (int)$u['id'] ?>"><?= e($u['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="book_id" required>
    <option value="">Seleccione libro (disponible)</option>
    <?php foreach(get_books_avail($pdo) as $b): ?>
      <option value="<?= (int)$b['id'] ?>"><?= e($b['title']) ?> (<?= (int)$b['quantity'] ?>)</option>
    <?php endforeach; ?>
  </select>
  <button>Prestar</button>
</form>

<h3>Préstamos activos</h3>
<?php
[$page,$per,$offset] = paginate();
$total = (int)$pdo->query('SELECT COUNT(*) FROM loans WHERE returned=0')->fetchColumn();
$stmt=$pdo->prepare('SELECT l.id, u.name AS user_name, b.title AS book_title, l.loan_date
    FROM loans l
    JOIN users u ON u.id=l.user_id
    JOIN books b ON b.id=l.book_id
    WHERE l.returned=0
    ORDER BY l.id DESC LIMIT :lim OFFSET :off');
$stmt->bindValue(':lim',$per,PDO::PARAM_INT); $stmt->bindValue(':off',$offset,PDO::PARAM_INT); $stmt->execute();
$rows=$stmt->fetchAll();
?>
<table>
  <tr><th>ID</th><th>Usuario</th><th>Libro</th><th>Fecha préstamo</th><th>Acción</th></tr>
  <?php foreach($rows as $r): ?>
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
  <?php endforeach; ?>
</table>
<?php render_pagination($total,$page,$per,'?action=list'); ?>

<h3>Historial por usuario</h3>
<form method="get">
  <input type="hidden" name="action" value="history">
  <input name="user_id" type="number" placeholder="ID de usuario" value="<?= e($_GET['user_id'] ?? '') ?>" required>
  <button>Ver historial</button>
</form>

<?php if (($action==='history') && ($uid=int_or_null($_GET['user_id']??null))):
    [$page,$per,$offset] = paginate();
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM loans WHERE user_id=?'); $stmt->execute([$uid]); $t=(int)$stmt->fetchColumn();
    $stmt=$pdo->prepare('SELECT l.id,b.title,l.loan_date,l.return_date,l.returned
        FROM loans l JOIN books b ON b.id=l.book_id WHERE l.user_id=?
        ORDER BY l.id DESC LIMIT :lim OFFSET :off');
    $stmt->bindValue(1,$uid,PDO::PARAM_INT);
    $stmt->bindValue(':lim',$per,PDO::PARAM_INT); $stmt->bindValue(':off',$offset,PDO::PARAM_INT); $stmt->execute();
    $rows=$stmt->fetchAll();
    echo '<h4>Usuario ID '.(int)$uid.'</h4>';
    echo '<table><tr><th>ID</th><th>Libro</th><th>Fecha préstamo</th><th>Fecha devolución</th><th>Estado</th></tr>';
    foreach($rows as $r){
        echo '<tr><td>'.(int)$r['id'].'</td><td>'.e($r['title']).'</td><td>'.e($r['loan_date']).'</td><td>'.e($r['return_date']??'-').'</td><td>'.((int)$r['returned']? 'Devuelto':'Activo').'</td></tr>';
    }
    echo '</table>';
    render_pagination($t,$page,$per,'?action=history&user_id='.(int)$uid);
endif; ?>
</body></html>
