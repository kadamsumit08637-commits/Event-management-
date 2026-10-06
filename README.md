# EventHub Celebrations – Event Management System

An elegant, ready-to-use platform for discovering college events **and** planning personal
celebrations — weddings, birthdays, anniversaries, engagements, baby showers and private
parties — with a warm wine-and-gold "celebrations" theme (Playfair Display + Poppins,
custom cards, flourishes and gradients).

## Technology
- HTML5 / CSS3
- Bootstrap 5
- JavaScript
- PHP 8+
- MySQL/MariaDB
- XAMPP
- phpMyAdmin

## Installation

1. Install XAMPP.
2. Start **Apache** and **MySQL**.
3. Copy the `event-management` folder into:
   `C:\xampp\htdocs\event-management\`
4. Open phpMyAdmin:
   `http://localhost/phpmyadmin/`
5. Import `database/event_management.sql`.
6. Open:
   `http://localhost/event-management/`

## Database
Database name: `event_management`

Default XAMPP connection:
- Host: localhost
- User: root
- Password: empty

If your MySQL root account has a password, edit `config/database.php`.

## Demo Admin
Email: `admin@eventhub.local`
Password: `Admin@123`

Change the demo password for any real deployment.

## Main Features
- Public event discovery
- Search/filter/sort
- User registration and login
- PHP sessions and role-based access
- User dashboard
- Event registration/cancellation
- Admin dashboard/statistics
- Event CRUD
- Category management
- User management
- Registration management
- Prepared PDO statements
- Password hashing
- Output escaping

## Important
This is designed as a college project and uses external image URLs for sample event images. For an offline-only deployment, download the images into `assets/images/` and update the event records.

## Design
- Elegant wine/burgundy + gold + blush palette, tuned for weddings, birthdays and parties.
- `Playfair Display` (headings) + `Poppins` (body) via Google Fonts.
- Reusable theme classes: `.hero`, `.celebration-card`, `.event-card`, `.stat-card`,
  `.divider-flourish`, `.section-eyebrow` / `.section-title`, `.auth-card`, `.sidebar`.
- All colors live in CSS variables at the top of `assets/css/style.css` — change
  `--eh-primary` / `--eh-accent` there to re-theme the entire site in one place.

## Personal Event Planner
Users can now create private event booking requests for weddings, birthdays, anniversaries, engagements, baby showers, corporate events and parties. They can choose a venue, date/time, guest count, theme, catering, decoration, music, photography and budget. Admins can review and approve/reject requests from **Admin → Custom Events**.

After updating an existing installation, import the additional SQL at the bottom of `database/event_management.sql` in phpMyAdmin.
