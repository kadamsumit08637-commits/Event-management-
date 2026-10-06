<?php
require_once __DIR__ . '/config/database.php';
$pageTitle = 'Home';

$stmt = $pdo->query("SELECT e.*, c.name AS category_name,
    (e.max_participants - (SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id AND r.status='registered')) AS seats
    FROM events e JOIN categories c ON c.id=e.category_id
    WHERE e.status='upcoming' AND e.event_date >= CURDATE()
    ORDER BY e.event_date, e.event_time LIMIT 6");
$events = $stmt->fetchAll();
$categories = $pdo->query("SELECT c.*, COUNT(e.id) event_count FROM categories c LEFT JOIN events e ON e.category_id=c.id GROUP BY c.id ORDER BY c.name")->fetchAll();
$venues = $pdo->query("SELECT * FROM venues WHERE status='available' ORDER BY id LIMIT 4")->fetchAll();
$venueImages = [
    'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1478146059778-26028b07395a?auto=format&fit=crop&w=700&q=80',
    'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=700&q=80',
];

require __DIR__ . '/includes/header.php';
?>
<section class="hero-photo">
 <div class="container">
  <div class="row">
   <div class="col-lg-7">
    <span class="hero-eyebrow"><i class="bi bi-award"></i>Designed For Memories</span>
    <h1 class="display-3 mt-3">Make Your Moments<br><span class="gold">Royal &amp; Memorable</span></h1>
    <p class="lead">From dreamy weddings to unforgettable birthdays, we help you plan every detail — venue, décor, catering and more — with perfection and elegance.</p>
    <div class="d-flex flex-wrap gap-3 mt-4">
      <a href="/event-management/events.php" class="btn btn-warning btn-lg px-4"><i class="bi bi-search me-2"></i>Explore Events</a>
      <a href="/event-management/<?= isLoggedIn() ? 'custom-event.php' : 'register.php' ?>" class="btn btn-outline-gold btn-lg px-4"><i class="bi bi-calendar2-heart me-2"></i>Plan Custom Event</a>
    </div>
   </div>
  </div>
 </div>
</section>

<section class="section-pad bg-royal-dark">
 <div class="container">
  <div class="text-center mb-5">
    <div class="divider-flourish on-dark"><i class="bi bi-gem"></i></div>
    <span class="section-eyebrow on-dark">Our Services</span>
    <h2 class="section-title on-dark mt-2">Everything you need to make your event perfect</h2>
  </div>
  <div class="row g-4">
   <?php foreach([['bi-cake2','Birthday Parties','Celebrate birthdays with style and joy.'],['bi-gem','Weddings','Beautiful weddings planned to perfection.'],['bi-balloon-heart','Private Parties','Private parties for your special moments.'],['bi-cup-hot','Anniversaries','Cherish love with elegant anniversary events.'],['bi-emoji-smile','Baby Showers','Celebrate new beginnings with love.'],['bi-briefcase','Corporate Events','Professional events that inspire and impress.']] as $s): ?>
   <div class="col-6 col-md-4 col-lg-2">
     <div class="service-card">
       <div class="icon-circle"><i class="bi <?= $s[0] ?>"></i></div>
       <h5 class="fw-bold fs-6 mb-2"><?= $s[1] ?></h5>
       <p class="small"><?= $s[2] ?></p>
     </div>
   </div>
   <?php endforeach; ?>
  </div>
 </div>
</section>

<section class="section-pad bg-blush">
 <div class="container">
  <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
   <div>
     <div class="divider-flourish" style="justify-content:flex-start"><i class="bi bi-gem"></i></div>
     <span class="section-eyebrow">Popular Venues</span>
     <h2 class="section-title mt-2 mb-0">Discover the perfect place for your special event</h2>
   </div>
   <a href="/event-management/<?= isLoggedIn() ? 'custom-event.php' : 'register.php' ?>" class="btn btn-outline-primary mt-3 mt-md-0">View All Venues <i class="bi bi-arrow-right ms-1"></i></a>
  </div>
  <div class="row g-4">
   <?php foreach($venues as $i => $v): ?>
   <div class="col-sm-6 col-lg-3">
     <div class="venue-card">
       <img class="venue-img w-100" src="<?= e($venueImages[$i % count($venueImages)]) ?>" alt="<?= e($v['name']) ?>">
       <div class="venue-body">
         <h6 class="fw-bold mb-1"><?= e($v['name']) ?></h6>
         <p class="small text-muted mb-2"><i class="bi bi-geo-alt me-1"></i><?= e($v['location']) ?></p>
         <div class="d-flex justify-content-between align-items-center">
           <span class="badge badge-gold-outline">Up to <?= number_format($v['capacity']) ?></span>
           <span class="small fw-semibold">₹<?= number_format($v['base_price'],0) ?>+</span>
         </div>
       </div>
     </div>
   </div>
   <?php endforeach; ?>
   <?php if (!$venues): ?><div class="col-12"><div class="alert alert-info">No venues available right now.</div></div><?php endif; ?>
  </div>
 </div>
</section>

<section class="section-pad">
 <div class="container">
  <div class="d-flex justify-content-between align-items-end mb-4">
   <div><span class="section-eyebrow">What's Happening</span><h2 class="section-title mb-0">Upcoming Events</h2></div>
   <a href="/event-management/events.php" class="btn btn-outline-primary">View All</a>
  </div>
  <div class="row g-4">
   <?php foreach($events as $event): ?>
   <div class="col-md-6 col-lg-4">
    <div class="card event-card h-100">
      <img class="event-img" src="<?= e($event['image'] ?: 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?auto=format&fit=crop&w=900&q=80') ?>" alt="<?= e($event['title']) ?>">
      <div class="card-body p-4">
       <span class="badge badge-soft mb-2"><?= e($event['category_name']) ?></span>
       <h5 class="fw-bold"><?= e($event['title']) ?></h5>
       <p class="text-muted small"><?= e(mb_strimwidth($event['description'],0,100,'...')) ?></p>
       <div class="small text-muted mb-3"><i class="bi bi-calendar3 me-1"></i><?= date('d M Y',strtotime($event['event_date'])) ?> · <?= date('h:i A',strtotime($event['event_time'])) ?><br><i class="bi bi-geo-alt me-1"></i><?= e($event['venue']) ?></div>
       <div class="d-flex justify-content-between align-items-center"><span class="small fw-semibold"><?= (int)$event['seats'] ?> seats left</span><a href="/event-management/event-details.php?id=<?= (int)$event['id'] ?>" class="btn btn-primary btn-sm">View Details</a></div>
      </div>
    </div>
   </div>
   <?php endforeach; ?>
   <?php if (!$events): ?><div class="col-12"><div class="alert alert-info">No upcoming events available.</div></div><?php endif; ?>
  </div>
 </div>
</section>

<section class="section-pad bg-white" id="about">
 <div class="container">
  <div class="row g-4">
   <div class="col-lg-6"><span class="section-eyebrow">About EventHub</span><h2 class="section-title mt-2">One place for campus events and personal celebrations.</h2><p class="text-muted">EventHub makes it simple to discover campus activities, reserve your seat and keep track of your registrations — and, when it's your moment to celebrate, plan a wedding, birthday or private party with the same ease.</p></div>
   <div class="col-lg-6"><div class="row g-3">
    <?php foreach($categories as $cat): ?><div class="col-6"><div class="stat-card"><i class="bi bi-grid-3x3-gap text-primary fs-3"></i><h6 class="mt-2 mb-1"><?= e($cat['name']) ?></h6><small class="text-muted"><?= (int)$cat['event_count'] ?> events</small></div></div><?php endforeach; ?>
   </div></div>
  </div>
 </div>
</section>

<section class="section-pad">
 <div class="container">
  <div class="text-center mb-5"><span class="section-eyebrow">Why EventHub</span><h2 class="section-title">Built for students, hosts and organizers</h2></div>
  <div class="row g-4">
   <?php foreach([['bi-lightning-charge','Quick Discovery','Find relevant events with search and category filters.'],['bi-palette','Personalized Planning','Pick a venue, theme, catering and décor for your own celebration.'],['bi-shield-check','Secure & Reliable','Passwords and authenticated actions are protected using modern PHP security practices.']] as $x): ?>
   <div class="col-md-4"><div class="stat-card h-100"><div class="icon-box"><?= '<i class="bi '.$x[0].'"></i>' ?></div><h5 class="fw-bold mt-3"><?= $x[1] ?></h5><p class="text-muted mb-0"><?= $x[2] ?></p></div></div>
   <?php endforeach; ?>
  </div>
 </div>
</section>

<section class="section-pad bg-white" id="contact">
 <div class="container">
  <div class="row align-items-center">
   <div class="col-lg-7"><span class="section-eyebrow">Get in Touch</span><h2 class="section-title mt-2">Have a question?</h2><p class="text-muted">Contact our event coordination team for help with registrations, event information, or planning your own wedding, birthday or party.</p></div>
   <div class="col-lg-5"><div class="stat-card"><p><i class="bi bi-envelope text-primary me-2"></i>events@example.com</p><p><i class="bi bi-telephone text-primary me-2"></i>+91 12345 67890</p><p class="mb-0"><i class="bi bi-geo-alt text-primary me-2"></i>College Campus, India</p></div></div>
  </div>
 </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
