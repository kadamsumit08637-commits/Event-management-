<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) { header('Location: /event-management/index.php'); exit; }
$pageTitle='Login'; $error='';
$flash=getFlash();
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $email=trim($_POST['email']??''); $password=$_POST['password']??'';
    $stmt=$pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1"); $stmt->execute([$email]); $user=$stmt->fetch();
    if (!$user || !password_verify($password,$user['password'])) $error='Invalid email or password.';
    elseif ($user['status']!=='active') $error='Your account is inactive. Contact the administrator.';
    else {
        session_regenerate_id(true);
        $_SESSION['user_id']=$user['id']; $_SESSION['name']=$user['name']; $_SESSION['role']=$user['role']; $_SESSION['email']=$user['email'];
        $target=$user['role']==='admin' ? '/event-management/admin/dashboard.php' : '/event-management/user/dashboard.php';
        header('Location: '.$target); exit;
    }
}
require __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap"><div class="auth-card">
 <div class="text-center mb-4"><div class="icon-box mx-auto"><i class="bi bi-box-arrow-in-right"></i></div><h2 class="fw-bold mt-3">Welcome back</h2><p class="text-muted">Login to manage your events.</p></div>
 <?php if($flash): ?><div class="alert alert-<?= e($flash['type']) ?> auto-dismiss"><?= e($flash['message']) ?></div><?php endif; ?>
 <?php if($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
 <form method="post">
  <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
  <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" id="remember"><label class="form-check-label" for="remember">Remember me</label></div>
  <button class="btn btn-primary w-100 py-2">Login</button>
 </form>
 <p class="text-center mt-4 mb-0 text-muted">New to EventHub? <a href="/event-management/register.php">Create an account</a></p>
</div></div>
<?php require __DIR__ . '/includes/footer.php'; ?>
