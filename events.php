<?php
require_once __DIR__ . '/config/database.php';
$pageTitle='Browse Events';
$q=trim($_GET['q']??''); $category=(int)($_GET['category']??0); $date=$_GET['date']??''; $sort=$_GET['sort']??'date';
$where=["e.status='upcoming'","e.event_date >= CURDATE()"]; $params=[];
if($q!==''){ $where[]="(e.title LIKE ? OR e.description LIKE ? OR e.venue LIKE ?)"; $like="%$q%"; array_push($params,$like,$like,$like); }
if($category){$where[]="e.category_id=?";$params[]=$category;}
if($date){$where[]="e.event_date=?";$params[]=$date;}
$order=$sort==='name'?'e.title ASC':($sort==='seats'?'seats DESC':'e.event_date ASC,e.event_time ASC');
$sql="SELECT e.*,c.name category_name,(e.max_participants-(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id AND r.status='registered')) seats FROM events e JOIN categories c ON c.id=e.category_id WHERE ".implode(' AND ',$where)." ORDER BY $order";
$stmt=$pdo->prepare($sql);$stmt->execute($params);$events=$stmt->fetchAll();
$categories=$pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
require __DIR__ . '/includes/header.php';
?>
<section class="py-5 bg-white border-bottom"><div class="container"><span class="section-eyebrow">Explore</span><h1 class="section-title">Upcoming Events</h1><p class="text-muted mb-0">Search, filter and find your next experience.</p></div></section>
<div class="container py-5">
 <form class="card border-0 shadow-sm rounded-4 p-3 mb-5" method="get"><div class="row g-2 align-items-end">
  <div class="col-lg-4"><label class="form-label small">Search</label><input class="form-control" name="q" placeholder="Event, venue or keyword" value="<?= e($q) ?>"></div>
  <div class="col-lg-3"><label class="form-label small">Category</label><select name="category" class="form-select"><option value="0">All categories</option><?php foreach($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $category==$c['id']?'selected':'' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-lg-2"><label class="form-label small">Date</label><input type="date" name="date" class="form-control" value="<?= e($date) ?>"></div>
  <div class="col-lg-2"><label class="form-label small">Sort</label><select name="sort" class="form-select"><option value="date" <?= $sort==='date'?'selected':'' ?>>Date</option><option value="name" <?= $sort==='name'?'selected':'' ?>>Name</option><option value="seats" <?= $sort==='seats'?'selected':'' ?>>Seats</option></select></div>
  <div class="col-lg-1"><button class="btn btn-primary w-100"><i class="bi bi-search"></i></button></div>
 </div></form>
 <div class="row g-4">
 <?php foreach($events as $event): ?><div class="col-md-6 col-lg-4"><div class="card event-card h-100">
  <img src="<?= e($event['image'] ?: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=900&q=80') ?>" class="event-img" alt="">
  <div class="card-body p-4"><span class="badge badge-soft mb-2"><?= e($event['category_name']) ?></span><h5 class="fw-bold"><?= e($event['title']) ?></h5><p class="text-muted small"><?= e(mb_strimwidth($event['description'],0,110,'...')) ?></p>
  <div class="small text-muted mb-3"><div><i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($event['event_date'])) ?></div><div><i class="bi bi-clock me-1"></i><?= date('h:i A',strtotime($event['event_time'])) ?></div><div><i class="bi bi-geo-alt me-1"></i><?= e($event['venue']) ?></div></div>
  <div class="d-flex gap-2"><a href="/event-management/event-details.php?id=<?= $event['id'] ?>" class="btn btn-outline-primary flex-fill">Details</a><?php if((int)$event['seats']>0): ?><a href="/event-management/event-details.php?id=<?= $event['id'] ?>#register" class="btn btn-primary flex-fill">Register</a><?php endif; ?></div>
  </div></div></div><?php endforeach; ?>
 <?php if(!$events): ?><div class="col-12"><div class="alert alert-info">No events matched your search.</div></div><?php endif; ?>
 </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
