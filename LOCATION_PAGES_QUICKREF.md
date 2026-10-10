# Location Pages — Quick Reference

## URLs

| Page | URL |
|------|-----|
| Locations Index | `http://localhost/niibinstitute/locations` |
| Ludhiana Page | `http://localhost/niibinstitute/ludhiana-state-hub` |
| Mohali Page | `http://localhost/niibinstitute/mohali-state-hub` |
| Amritsar Page | `http://localhost/niibinstitute/amritsar-state-hub` |
| Sector 34-A Page | `http://localhost/niibinstitute/sector-34-a-hub-union-territory-hub` |

## Admin URLs

| Module | URL |
|--------|-----|
| Locations Admin | `http://localhost/niibinstitute/admin/modules/locations.php` |
| Pages Admin | `http://localhost/niibinstitute/admin/modules/pages.php` |

## Quick Tasks

### Edit a Location's Landing Page
1. Go to **Admin → Locations**
2. Find the location and click **Edit**
3. In the right sidebar, see **"Location Page"** card
4. Click **"Edit Page"** button
5. Customize the hero, courses, career paths, gallery, etc.
6. Save

### View a Location Page Live
1. Go to **Admin → Locations**
2. Find the location and click **Edit**
3. In the right sidebar, click **"View"** (external link icon)
4. Or navigate directly to `/[city-slug]`

### Customize Location Page Content
1. Go to **Admin → Pages**
2. Search for the location page by city name
3. Edit any of these sections:
   - **Hero**: Banner image, title, CTAs
   - **Intro**: Heading, paragraphs, stats, image, quote
   - **Courses**: Select which courses to show
   - **Career Paths**: Add 3-4 career options
   - **Gallery**: Upload images
   - **Accreditations**: List certifications
   - **Contact**: Phone, email, address, form cities
   - **SEO**: Meta tags, canonical URL, schema

### All 16 Linked Locations
- Bihar: Patna
- Chandigarh: Sector 34-A Hub
- Jammu & Kashmir: Jammu City
- Uttarakhand: Dehradun
- Uttar Pradesh: Lucknow
- Punjab: Abohar, Amritsar, Bathinda, Hoshiarpur, Jalandhar, Ludhiana, Moga, Mohali, Pathankot, Patiala, Phagwara

## What Changed

1. **Database**: Added `page_id` column to `locations` table
2. **Admin Locations**: Now shows linked pages with edit button
3. **Admin Pages**: Supports "location" page type with specialized template
4. **Frontend**: Locations page links to individual location pages
5. **Features**: Each location has a customizable landing page with template sections

## Testing

Visit `http://localhost/niibinstitute/locations` and click any location button:
- If linked to a page → redirects to location landing page
- If not linked → redirects to contact form (fallback)

Then visit a location page (e.g., `/ludhiana-state-hub`) to see:
- Hero section with custom title
- Introduction with stats
- Course listings
- Career path cards
- Image gallery
- Contact form with location-specific info
