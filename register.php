<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: /event-management/index.php'); exit; }
$pageTitle='Create Account';
$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $name=trim($_POST['name']??''); $email=trim($_POST['email']??''); $phone=trim($_POST['phone']??'');
    $password=$_POST['password']??''; $confirm=$_POST['confirm_password']??'';
    if ($name==='' || $email==='' || $password==='') $errors[]='Please fill all required fields.';
    if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Please enter a valid email address.';
    if (strlen($password)<6) $errors[]='Password must contain at least 6 characters.';
    if ($password!==$confirm) $errors[]='Passwords do not match.';
    $check=$pdo->prepare("SELECT id FROM users WHERE email=?"); $check->execute([$email]);
    if ($check->fetch()) $errors[]='Email is already registered.';
    if (!$errors) {
        $stmt=$pdo->prepare("INSERT INTO users(name,email,phone,password,role,status) VALUES(?,?,?,?, 'user','active')");
        $stmt->execute([$name,$email,$phone,password_hash($password,PASSWORD_DEFAULT)]);
        flash('success','Account created successfully. Please login.');
        header('Location: /event-management/login.php'); exit;
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
 <div class="text-center mb-4"><div class="icon-box mx-auto"><i class="bi bi-person-plus"></i></div><h2 class="fw-bold mt-3">Create your account</h2><p class="text-muted">Join EventHub and start exploring.</p></div>
 <?php foreach($errors as $err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endforeach; ?>
 <form method="post" novalidate>
  <div class="mb-3"><label class="form-label">Full name *</label><input name="name" class="form-control" required value="<?= e($_POST['name']??'') ?>"></div>
  <div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required value="<?= e($_POST['email']??'') ?>"></div>
  <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($_POST['phone']??'') ?>"></div>
  <div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required></div>
  <div class="mb-4"><label class="form-label">Confirm password *</label><input type="password" name="confirm_password" class="form-control" required></div>
  <button class="btn btn-primary w-100 py-2">Create Account</button>
 </form>
 <p class="text-center mt-4 mb-0 text-muted">Already registered? <a href="/event-management/login.php">Login</a></p>
</div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
