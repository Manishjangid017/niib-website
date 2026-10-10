# Location Pages System — Admin Guide

## Overview

The NIIB Institute website now has a dynamic location pages system that automatically links each location to a dedicated landing page. When users visit the main locations page, they can now click on individual locations to view a custom landing page tailored for that center.

### Key Features

✓ **All 16 locations are pre-linked** to their dedicated pages  
✓ **Customizable templates** — each location page can be customized independently  
✓ **Smart CTAs** — "VIEW CAMPUS" button links to location pages when available  
✓ **Location-specific content** — hero section, courses, career paths, gallery, contact form  
✓ **SEO optimized** — each page has custom meta titles, descriptions, and structured data  

---

## How It Works

### Frontend Flow

1. **Locations Page** (`/locations`)
   - Shows all 16 centers grouped by state
   - Featured centers display detailed cards with "VIEW CAMPUS" button
   - Simple centers show compact cards with "EXPLORE CENTER" button
   - If a location is linked to a page, the button links to that page
   - If not linked, buttons default to contact form

2. **Location Landing Pages** (e.g., `/ludhiana-state-hub`)
   - Uses the "location" page type template
   - Displays hero section with custom title and CTAs
   - Shows location-specific courses and career paths
   - Includes gallery, accreditations, and contact form
   - Allows location-specific content editing

### Admin Flow

1. **Locations Module** → View all locations with their linked pages
   - Table now shows "Page" column with edit link
   - When editing a location, see the linked page info in a sidebar card
   - Quick links to edit the page or view it live

2. **Pages Module** → Create/edit location pages
   - "Page Type" dropdown: choose "location" for specialized template
   - Unique URL slug (auto-generated or custom)
   - Template sections:
     - **Hero** — badge, title, two CTAs with URLs
     - **Intro** — heading, paragraphs, stats, image, quote
     - **Courses** — select which courses to display
     - **Career Paths** — 3-4 career option cards with icons
     - **Gallery** — upload multiple images
     - **Accreditations** — comma-separated list
     - **Contact Section** — phone, email, address, city options for form

---

## Database Schema

### locations table
```
id (int) — Primary key
city (varchar) — City name
state (varchar) — State name
page_id (int, NULL) — Links to pages.id
... (other fields like phone, email, address, etc.)
```

### pages table
- `id` — Primary key
- `title` — Page title (e.g., "Master the Art of Beauty in Ludhiana")
- `slug` — URL slug (e.g., "ludhiana-state-hub")
- `page_type` — "location" or "informative"
- `template_data` — JSON with all location-specific content
- `status` — 1 for published, 0 for draft

---

## Managing Location Pages

### Editing Location Content

1. **From Locations Module**
   - Go to Admin → Locations
   - Find the location and click Edit
   - On the right sidebar, see "Location Page" card
   - Click "Edit Page" to customize the landing page

2. **Direct Page Editing**
   - Go to Admin → Pages
   - Find the page by city name (e.g., "Master the Art of Beauty in Ludhiana")
   - Click Edit to customize:
     - Hero section with banner image
     - Introduction text and stats
     - Courses displayed on this page
     - Career path descriptions
     - Gallery images
     - Contact information and phone number

### Creating Pages for New Locations

When you add a new location, it won't have a linked page yet. To create one:

**Option 1: Manual Creation**
1. Go to Admin → Pages
2. Click "Add Page"
3. Set "Type" to "Location"
4. Set "Title" to something like "Master the Art of Beauty in [City]"
5. The slug will auto-generate (e.g., `master-the-art-of-beauty-in-[city]`)
6. Fill in all the template sections
7. Click "Save Page"
8. Note the page ID
9. Go to Locations module, edit the location
10. Update the page_id field (if form shows it)

**Option 2: Bulk Generation (CLI Only)**
- Run: `php admin/modules/.generate-location-pages-backup.php`
- This script creates pages for all locations missing linked pages
- Auto-fills basic content that can then be customized

---

## Frontend User Journey

### User visiting a location

1. User goes to `/locations`
2. Sees all locations grouped by state
3. Clicks on "VIEW CAMPUS" or "EXPLORE CENTER" for a location
4. If page exists → redirected to location-specific page (e.g., `/ludhiana-state-hub`)
5. Sees customized hero, courses, career paths
6. Can submit an enquiry through the location-specific contact form

### URL Structure

- Main locations listing: `/locations`
- Individual location page: `/{page-slug}` (e.g., `/ludhiana-state-hub`)
- These are regular pages — no special routing needed

---

## SEO & Customization

Each location page includes:
- **Meta Title** — "Beauty Courses & Academy in [City] - NIIB Institute"
- **Meta Description** — Location-specific description for search results
- **Meta Keywords** — City-specific keywords
- **Structured Data** — JSON-LD for location schema
- **Breadcrumbs** — Home > [Page Title]
- **OG Image** — For social media sharing

All these are editable in the Pages module under "SEO" section.

---

## Current Status

✅ All 16 locations have pages linked  
✅ URLs generated (e.g., `/ludhiana-state-hub`, `/mohali-state-hub`, etc.)  
✅ Admin interface shows page links  
✅ Frontend locations page links to individual pages  
✅ Each page uses the location template with all sections  
✅ Pages include basic SEO optimizations  

---

## Next Steps (Optional Enhancements)

- Customize hero images for each location
- Add location-specific course selections
- Write unique intro paragraphs for each center
- Add gallery images from events/training
- Set up location-specific email addresses for forms
- Create location-specific testimonials
- Add pricing/schedule information

---

## Support & Reference

- Location page template: `includes/page-location.php`
- Locations admin module: `admin/modules/locations.php`
- Pages admin module: `admin/modules/pages.php`
- Frontend locations page: `locations.php`
- Page rendering: `page.php` (detects "location" type and includes template)

For help with customizing a specific location page, edit the page in the Pages module and refer to the template section explanations.
