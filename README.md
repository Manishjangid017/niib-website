# NIIB Institute — Core PHP Website + Admin Panel

A complete, dynamic, SEO-optimised institute website built in **clean Core PHP** (no frameworks, no Composer), with a feature-rich admin panel.

---

## 1. Quick Start

### Database details
| | |
|---|---|
| Host | `localhost` |
| Database | `niibinstitute` |
| User | `root` |
| Password | `mysql` |

### Installation (one-time)

1. Copy the project folder to your web server's document root, e.g. `C:\Program Files\Ampps\www\niibinstitute`.
2. Start Apache + MySQL.
3. Open in browser: **http://localhost/niibinstitute/install.php**
4. Click **Run Installation** — this will:
   * create the `niibinstitute` database
   * create all 30 tables
   * load demo content
   * set the default admin password hash
5. **Delete `install.php`** after success.

### Default Admin Login
```
URL:      http://localhost/niibinstitute/admin/
Email:    admin@admin.com
Password: admin123
```
Change the password immediately from **Admin → Admin Users**.

---

## 2. Project Structure

```
/niibinstitute
│
├─ index.php               Homepage (dynamic)
├─ about.php               About page (dynamic)
├─ contact.php             Contact page (dynamic, with form)
├─ courses.php             Course listing + search + categories
├─ course-details.php      Single course (?slug=)
├─ blog.php                Blog listing + search + categories
├─ blog-details.php        Single blog post (?slug=)
├─ page.php                Custom dynamic page (?slug=)
├─ 404.php                 Not-found page + redirect engine
├─ sitemap.php             XML sitemap auto-generator
├─ robots.php              Dynamic robots.txt (admin-editable)
├─ install.php             One-time installer
│
├─ /config                 Database & site config
├─ /includes               header.php, footer.php, init.php, functions.php
├─ /assets                 css, js, images, logo, favicon (SVG)
├─ /uploads                Sub-folders auto-created: courses, blogs, sliders, gallery…
├─ /api                    AJAX endpoints: contact.php, enquiry.php, newsletter.php
├─ /database               niibinstitute.sql  (full schema + seed)
│
└─ /admin
   ├─ login.php
   ├─ logout.php
   ├─ index.php            Dashboard
   ├─ /includes            auth.php, header.php, footer.php, crud.php
   ├─ /assets/css/admin.css
   └─ /modules             31 admin modules (one file per feature)
```

---

## 3. Admin Panel Features

| Module | What admin can do |
|---|---|
| **Dashboard** | Total enquiries, contacts, courses, blogs, pages, subscribers; recent messages; quick links |
| **Site Settings** | Name, tagline, email, phone, WhatsApp, address, logo, favicon, footer text, copyright, maintenance mode |
| **Homepage Sections** | Toggle on/off each section (About, Courses, Why-choose, Counters, Testimonials, CTA, Blog, FAQ, Contact) + edit content |
| **Hero Sliders** | Multiple slides, titles, two buttons each, image, text-align, ordering |
| **Pages** | Create/edit/delete dynamic pages with full SEO controls + custom banner |
| **Menus** | Header / footer / both menus, parent–child dropdowns, sort order, custom URL or page link |
| **Course Categories** | Name, slug, icon, image, description |
| **Courses** | Title, slug, short + full description, duration, fees + original price, eligibility, image, brochure (PDF), level, language, featured, plus full SEO fields & schema JSON-LD |
| **Blog Categories** | CRUD |
| **Blog Posts** | Title, content, featured image, author, tags, publish date, full SEO + schema |
| **Testimonials** | Name, designation, company, rating, review, photo |
| **FAQ** | Question, answer, category, ordering |
| **Gallery** | Multi-category image gallery |
| **Team Members** | Photo, bio, social links |
| **Counters** | Animated counter blocks |
| **Why Choose Us** | Icon-based feature cards |
| **Banners** | Reusable banner images for any page |
| **Social Links** | Platform, URL, icon |
| **Popup** | Title, description, image, button, delay, "show only once" |
| **Contact Messages** | View, read/unread, CSV export, delete |
| **Course Enquiries** | View, mark read, CSV export, delete |
| **Newsletter** | Subscribers list + CSV export |
| **SEO Settings** | Global meta title/desc/keywords, OG tags, Twitter card, robots meta, Organization JSON-LD, robots.txt editor, links to /sitemap.xml |
| **Header Codes** | Multiple snippets injected into `<head>` or right after `<body>` (Google Analytics, GTM, Facebook Pixel, verification meta…) |
| **Footer Codes** | Chat widgets, tracking pixels, custom JS before `</body>` |
| **Custom CSS** | Global CSS textarea, loaded on every page |
| **Custom JS** | Global JS textarea, loaded in footer |
| **Redirects** | 301/302 redirects from old → new URLs, hit-counter |
| **Media Library** | Multi-upload, copy URL, delete, gallery view |
| **Admin Users** | Multi-admin, roles (Super Admin / Editor), password change, last-login tracking |
| **Activity Logs** | All admin actions logged with IP & user-agent |

---

## 4. SEO Coverage (every page)

* Dynamic `<title>`, meta description, keywords, canonical
* Open Graph + Twitter card meta tags
* Organization schema (JSON-LD) site-wide
* Course schema on course details
* BlogPosting schema on blog posts
* Breadcrumbs on inner pages
* Auto `sitemap.xml` (static + pages + courses + blogs)
* Dynamic `robots.txt` (admin-editable)
* SEO-friendly URLs via `.htaccess`:
  * `/about`, `/contact`, `/courses`, `/blog`
  * `/course/{slug}`, `/blog/{slug}`, `/page/{slug}`
* Image alt text, lazy loading (`loading="lazy"`)
* Mobile-first responsive layout

---

## 5. Security

* PDO with prepared statements everywhere (zero string concatenation in queries)
* `password_hash()` / `password_verify()` (bcrypt)
* CSRF token on every form
* Session fixation prevention (`session_regenerate_id`)
* 1-hour session timeout
* HttpOnly + SameSite cookies
* Upload allow-listing (only configured file types)
* `.htaccess` denies access to `/config`, `/includes`, `/database`
* `.htaccess` in `/uploads` blocks PHP execution
* HTML output escaped via `e()` helper
* Rate-limiting on public form endpoints (max 5 / hour / IP)
* Security headers: `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, `Referrer-Policy`
* Force HTTPS rule available in `.htaccess` (commented for local dev)

---

## 6. Performance

* No heavy libraries — Bootstrap 5 + Bootstrap Icons via CDN only
* Browser caching (1 year images, 1 month CSS/JS) via `.htaccess`
* Gzip output compression enabled
* In-page query caching for settings, SEO, menus (static cache)
* Lazy-loaded images
* SQL indices on slugs, status, parent_id, is_read, etc.
* SVG logo / favicon / placeholder (zero raster overhead)

---

## 7. How to do common tasks

### Edit Site Settings (logo, phone, etc.)
Admin → **Site Settings**

### Add a new Course
Admin → **Courses → Add Course** (fill title, fees, duration, image, SEO, then Save)

### Add a Blog Post
Admin → **Blog Posts → Add Post**

### Toggle Homepage Sections
Admin → **Homepage Sections** → click the power button next to any section.
Edit section title/subtitle/button via the pencil icon.

### Insert Google Analytics / Tag Manager
Admin → **Header Codes → Add Code**, paste the GA / GTM snippet, choose position (`head` or `body_start`), Save. Loads automatically site-wide.

### Add a Chat Widget (Tawk.to, etc.)
Admin → **Footer Codes → Add Code**, paste the script, Save.

### Add Custom CSS
Admin → **Custom CSS**, paste rules, enable, Save.

### Update SEO Tags
Per-page: edit Page / Course / Blog and fill the **SEO** card.
Site-wide defaults: Admin → **SEO Settings**.

### Change Menu Items
Admin → **Menus → Add Menu Item** (or edit existing). Choose location (header / footer / both), parent for dropdowns, sort order.

### Manage Enquiries
Admin → **Course Enquiries** or **Contact Messages**. View, mark read, export CSV, delete.

### Add a 301 Redirect
Admin → **Redirects → Add Redirect**, enter old + new URL, choose 301, Save. The 404 handler processes these.

### Add a Promo Popup
Admin → **Popup**, fill content, set delay, enable. Use the "show once" option to avoid annoying repeat visitors.

---

## 8. URL Structure (clean)

| Path | Goes to |
|---|---|
| `/` | `index.php` |
| `/about` | `about.php` |
| `/contact` | `contact.php` |
| `/courses` | `courses.php` |
| `/course/{slug}` | `course-details.php?slug={slug}` |
| `/blog` | `blog.php` |
| `/blog/{slug}` | `blog-details.php?slug={slug}` |
| `/page/{slug}` | `page.php?slug={slug}` |
| `/sitemap.xml` | `sitemap.php` |
| `/robots.txt` | `robots.php` |

---

## 9. Switching to Production

In `config/database.php`:
```php
define('ENVIRONMENT', 'production');
```
This hides PHP errors. Also un-comment the HTTPS redirect block in `.htaccess`.

---

## 10. Tech Stack

* **PHP 7.4+ / 8.x** (no Composer, no frameworks)
* **MySQL 5.7+ / 8.0+** (utf8mb4)
* **Bootstrap 5.3** + **Bootstrap Icons 1.11** (CDN)
* Vanilla JavaScript (no jQuery)
* Apache mod_rewrite + mod_expires + mod_deflate + mod_headers

---

## 11. Credits

NIIB Institute — Powered by clean Core PHP. Built to be fast, secure, SEO-friendly, and production-ready.
