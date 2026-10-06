<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$id=(int)($_GET['id']??0);
$stmt=$pdo->prepare("SELECT e.*,c.name category_name,(e.max_participants-(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id AND r.status='registered')) seats FROM events e JOIN categories c ON c.id=e.category_id WHERE e.id=?");
$stmt->execute([$id]);$event=$stmt->fetch();
if(!$event){http_response_code(404);$pageTitle='Event Not Found';require __DIR__.'/includes/header.php';echo '<div class="container py-5"><div class="alert alert-danger">Event not found.</div></div>';require __DIR__.'/includes/footer.php';exit;}
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['register_event'])){
 requireLogin();
 if(strtotime($event['registration_deadline'].' 23:59:59')<time()) flash('warning','Registration closed.');
 elseif((int)$event['seats']<=0) flash('warning','Event full.');
 else {
  $check=$pdo->prepare("SELECT id FROM registrations WHERE user_id=? AND event_id=? AND status='registered'");$check->execute([$_SESSION['user_id'],$id]);
  if($check->fetch()) flash('warning','Already registered.');
  else {$ins=$pdo->prepare("INSERT INTO registrations(user_id,event_id,status) VALUES(?,?,'registered')");$ins->execute([$_SESSION['user_id'],$id]);flash('success','Registration successful! You are registered for '. $event['title'].'.');}
 }
 header('Location: /event-management/event-details.php?id='.$id);exit;
}
$registered=false;
if(isLoggedIn()){ $c=$pdo->prepare("SELECT id FROM registrations WHERE user_id=? AND event_id=? AND status='registered'");$c->execute([$_SESSION['user_id'],$id]);$registered=(bool)$c->fetch(); }
$pageTitle=$event['title'];require __DIR__.'/includes/header.php';
?>
<div class="container py-5"><div class="row g-5">
 <div class="col-lg-6"><img src="<?= e($event['image'] ?: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=1200&q=80') ?>" class="w-100 rounded-4 shadow-sm object-cover" style="height:430px" alt=""></div>
 <div class="col-lg-6"><span class="badge badge-soft mb-3"><?= e($event['category_name']) ?></span><h1 class="fw-bold"><?= e($event['title']) ?></h1><p class="lead text-muted"><?= nl2br(e($event['description'])) ?></p>
  <div class="row g-3 my-3"><?php foreach([['bi-calendar3','Date',date('d M Y',strtotime($event['event_date']))],['bi-clock','Time',date('h:i A',strtotime($event['event_time']))],['bi-geo-alt','Venue',$event['venue']],['bi-person','Organizer',$event['organizer']],['bi-people','Seats',(int)$event['seats'].' / '.$event['max_participants']],['bi-hourglass-split','Deadline',date('d M Y',strtotime($event['registration_deadline']))]] as $i): ?><div class="col-sm-6"><div class="p-3 bg-white rounded-3 shadow-sm"><small class="text-muted d-block"><i class="bi <?= $i[0] ?> me-1"></i><?= $i[1] ?></small><strong><?= e((string)$i[2]) ?></strong></div></div><?php endforeach; ?></div>
  <div id="register"><?php if($registered): ?><div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>You are registered for this event.</div><?php elseif(strtotime($event['registration_deadline'].' 23:59:59')<time()): ?><div class="alert alert-secondary">Registration Closed.</div><?php elseif((int)$event['seats']<=0): ?><div class="alert alert-secondary">Event Full.</div><?php else: ?><form method="post"><button name="register_event" class="btn btn-primary btn-lg px-4">Register for Event</button></form><?php endif; ?></div>
 </div>
</div></div>
<?php require __DIR__.'/includes/footer.php'; ?>
