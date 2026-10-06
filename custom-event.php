<?php
require_once __DIR__.'/config/database.php';
require_once __DIR__.'/includes/auth.php';
requireLogin();

$eventTypes = [
    'Wedding' => 'Marriage / wedding celebration',
    'Birthday' => 'Birthday party',
    'Anniversary' => 'Anniversary celebration',
    'Engagement' => 'Engagement ceremony',
    'Baby Shower' => 'Baby shower',
    'Corporate' => 'Corporate / office event',
    'Private Party' => 'Private party',
    'Other' => 'Other personal event'
];
$venues = $pdo->query("SELECT * FROM venues WHERE status='available' ORDER BY name")->fetchAll();

$errors=[];
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $type=trim($_POST['event_type']??''); $title=trim($_POST['title']??''); $date=$_POST['event_date']??'';
    $time=$_POST['event_time']??''; $guests=(int)($_POST['guest_count']??0); $venueId=(int)($_POST['venue_id']??0);
    $theme=trim($_POST['theme']??''); $catering=$_POST['catering']??'none'; $decoration=$_POST['decoration']??'none';
    $music=$_POST['music']??'none'; $photography=$_POST['photography']??'none'; $budget=(float)($_POST['budget']??0); $notes=trim($_POST['notes']??'');
    if (!isset($eventTypes[$type])) $errors[]='Please select a valid event type.';
    if ($title==='') $errors[]='Please enter an event name.';
    if (!$date || $date < date('Y-m-d')) $errors[]='Please choose today or a future date.';
    if (!$time) $errors[]='Please choose an event time.';
    if ($guests<1 || $guests>5000) $errors[]='Guest count must be between 1 and 5000.';
    $vstmt=$pdo->prepare("SELECT * FROM venues WHERE id=? AND status='available'"); $vstmt->execute([$venueId]); $venue=$vstmt->fetch();
    if (!$venue) $errors[]='Please select an available venue.';
    elseif ($guests > (int)$venue['capacity']) $errors[]='Selected venue can accommodate up to '.(int)$venue['capacity'].' guests.';
    if ($budget<0) $errors[]='Budget cannot be negative.';
    if (!$errors) {
        $check=$pdo->prepare("SELECT COUNT(*) FROM custom_events WHERE venue_id=? AND event_date=? AND status IN ('pending','approved') AND ABS(TIME_TO_SEC(TIMEDIFF(event_time,?))) < 21600");
        $check->execute([$venueId,$date,$time]);
        if ((int)$check->fetchColumn()>0) $errors[]='This venue is already requested/booked around that time. Please choose another venue or time.';
    }
    if (!$errors) {
        $stmt=$pdo->prepare("INSERT INTO custom_events (user_id,event_type,title,event_date,event_time,guest_count,venue_id,theme,catering,decoration,music,photography,budget,notes,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
        $stmt->execute([$_SESSION['user_id'],$type,$title,$date,$time,$guests,$venueId,$theme,$catering,$decoration,$music,$photography,$budget,$notes]);
        flash('success','Your custom event request has been submitted. The EventHub team will review it.');
        header('Location: /event-management/user/custom-events.php'); exit;
    }
}
$pageTitle='Create Your Own Event';
require __DIR__.'/includes/header.php';
?>
<div class="container py-5">
  <div class="row justify-content-center"><div class="col-xl-10">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4"><div><span class="badge bg-warning mb-2"><i class="bi bi-stars me-1"></i>Personal Event Planner</span><h1 class="section-title mb-1">Plan Your Dream Celebration</h1><p class="text-muted mb-0">A wedding, birthday, anniversary, engagement or private party — designed around you, from venue to décor.</p></div><a href="/event-management/user/custom-events.php" class="btn btn-outline-primary"><i class="bi bi-calendar2-check me-1"></i>My Custom Events</a></div>
    <?php if($errors): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach($errors as $err): ?><li><?=e($err)?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="form-card p-4 p-lg-5">
      <h5 class="fw-bold mb-3"><i class="bi bi-stars me-2"></i>1. Event Details</h5>
      <div class="row g-3 mb-4">
        <div class="col-md-6"><label class="form-label">Event Type *</label><select name="event_type" class="form-select" required><option value="">Choose...</option><?php foreach($eventTypes as $k=>$v): ?><option value="<?=e($k)?>" <?=($_POST['event_type']??'')===$k?'selected':''?>><?=e($k)?> — <?=e($v)?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Event Name *</label><input name="title" class="form-control" placeholder="e.g. Aniket & Priya Wedding" value="<?=e($_POST['title']??'')?>" required></div>
        <div class="col-md-4"><label class="form-label">Date *</label><input type="date" name="event_date" min="<?=date('Y-m-d')?>" class="form-control" value="<?=e($_POST['event_date']??'')?>" required></div>
        <div class="col-md-4"><label class="form-label">Start Time *</label><input type="time" name="event_time" class="form-control" value="<?=e($_POST['event_time']??'')?>" required></div>
        <div class="col-md-4"><label class="form-label">Number of Guests *</label><input type="number" name="guest_count" min="1" max="5000" class="form-control" value="<?=e($_POST['guest_count']??'50')?>" required></div>
      </div>
      <h5 class="fw-bold mb-3"><i class="bi bi-building me-2"></i>2. Choose a Venue</h5>
      <div class="row g-3 mb-4"><?php foreach($venues as $v): ?><div class="col-md-6 col-lg-4"><label class="w-100"><input class="d-none venue-radio" type="radio" name="venue_id" value="<?=$v['id']?>" data-capacity="<?=$v['capacity']?>" data-price="<?=$v['base_price']?>" <?=((int)($_POST['venue_id']??0)===$v['id'])?'checked':''?> required><div class="venue-choice h-100"><div class="d-flex justify-content-between"><strong><?=e($v['name'])?></strong><span class="badge badge-soft">₹<?=number_format($v['base_price'],0)?>+</span></div><small class="text-muted d-block mt-2"><?=e($v['location'])?></small><small><i class="bi bi-people me-1"></i>Up to <?=number_format($v['capacity'])?> guests</small><p class="small text-muted mt-2 mb-0"><?=e($v['description'])?></p></div></label></div><?php endforeach; ?></div>
      <h5 class="fw-bold mb-3"><i class="bi bi-palette me-2"></i>3. Customize Services</h5>
      <div class="row g-3 mb-4">
        <?php $opts=['catering'=>['Catering','none','veg','premium'],'decoration'=>['Decoration','none','standard','premium'],'music'=>['Music / DJ','none','dj','live_band'],'photography'=>['Photography','none','basic','premium']]; foreach($opts as $name=>$o): ?><div class="col-md-6"><label class="form-label"><?=$o[0]?></label><select name="<?=$name?>" class="form-select"><?php foreach(array_slice($o,1) as $val): ?><option value="<?=$val?>" <?=($_POST[$name]??'none')===$val?'selected':''?>><?=ucwords(str_replace('_',' ',$val))?></option><?php endforeach; ?></select></div><?php endforeach; ?>
        <div class="col-md-6"><label class="form-label">Theme / Color</label><input name="theme" class="form-control" placeholder="e.g. Royal blue & gold" value="<?=e($_POST['theme']??'')?>"></div>
        <div class="col-md-6"><label class="form-label">Maximum Budget (₹)</label><input type="number" name="budget" min="0" step="100" class="form-control" placeholder="e.g. 150000" value="<?=e($_POST['budget']??'')?>"></div>
        <div class="col-12"><label class="form-label">Special Requirements</label><textarea name="notes" rows="4" class="form-control" placeholder="Tell us about seating, food preferences, stage, decoration, accessibility, etc."><?=e($_POST['notes']??'')?></textarea></div>
      </div>
      <div class="alert alert-light border"><i class="bi bi-info-circle me-2"></i>This is a <strong>booking request</strong>. Venue and services are confirmed only after admin approval.</div>
      <button class="btn btn-primary btn-lg px-4"><i class="bi bi-send me-2"></i>Submit Custom Event Request</button>
    </form>
  </div></div>
</div>
<?php require __DIR__.'/includes/footer.php'; ?>
