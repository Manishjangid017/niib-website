# Media Library Integration — Admin Guide

## Overview

The pages module now integrates fully with the Media Library, allowing you to select images and files from a centralized library instead of uploading files repeatedly. This makes it easier to manage assets and reuse images across multiple pages and location pages.

## Features

✅ **Media Library Button** — "Select from Library" for all image fields  
✅ **Quick Upload** — Direct upload alternative for convenience  
✅ **Search & Browse** — Find images quickly with search  
✅ **Multi-select** — Add multiple gallery images at once  
✅ **Duplicates Prevention** — Can't add same image twice to gallery  
✅ **Easy Removal** — Click × to remove from gallery  
✅ **Preview** — See thumbnails before selecting  

## How to Use

### For Informative Pages

#### Adding a Banner Image

1. **Edit/Add Page** → go to Settings panel on the right
2. See "Banner Image" card
3. Choose one of:
   - **"From Library"** button → browse and select from existing media
   - **"Upload"** button → upload a new image directly
4. Image preview shows below
5. Save page

### For Location Pages

#### Hero Section Banner

1. **Edit/Add Location Page** → scroll to "1. Hero Section"
2. See "Hero Banner Image" field
3. Choose:
   - **"Select from Library"** → pick from existing media
   - **"Upload"** → upload new image directly
4. Image preview appears above the buttons
5. Save page

#### Introduction Section Image

1. Scroll to "2. Introduction Section"
2. See "Intro Image" field
3. Choose:
   - **"Select from Library"** → pick from media library
   - **"Upload"** → upload new image directly
4. Image preview shows after selection
5. Save page

#### Gallery Images

1. Scroll to "5. Recognition / Gallery"
2. See "Gallery Images" section
3. **Existing images** show as thumbnails with × buttons to remove
4. Add more images:
   - **"Add from Library"** → select multiple images at once
   - **"Upload"** → upload new images (can select multiple files)
5. All selected images appear as thumbnails
6. Remove any by clicking the × button
7. Save page

### Media Library Modal

When you click "Select from Library" or "Add from Library":

1. **Modal Opens** with grid of available images
2. **Search** — type to filter images (searches by filename)
3. **Browse** — scroll through all available media
4. **Select** — click an image to highlight it (blue border)
5. **Pagination** — navigate through pages if many images
6. **Confirm** — click "Select" button to add to your page

## File Types Supported

**Images:**
- JPG, JPEG
- PNG
- GIF
- WebP
- SVG

**Other Media:**
- PDF
- DOC, DOCX
- MP4

## Managing the Media Library

### Upload Media

1. **Go to Admin → Media Library**
2. Click "Upload" or drag files to the upload box
3. Select one or more image files
4. Click "Upload" button
5. Files appear in the library grid

### View Media

- Thumbnail shows for images
- File type icon shows for documents/videos
- File size displayed
- Click link icon to open full image in new tab
- Click clipboard icon to copy image URL

### Delete Media

1. Find the image in Media Library
2. Click trash icon in the bottom right of the card
3. Confirm deletion
4. Image is removed from library
5. **Note:** If used in pages, those references will be broken

### Search Media

1. In Media Library, use the search box
2. Type filename to filter
3. Results show matching files
4. Clear search to see all media

## Database Behind the Scenes

### media_library table
```
id             → unique ID
file_name      → stored filename
original_name  → original uploaded filename
file_path      → path used in HTML (img_url() compatible)
file_type      → extension (jpg, png, pdf, etc.)
file_size      → size in bytes
alt_text       → optional alt text (for future use)
uploaded_by    → admin user who uploaded
created_at     → upload timestamp
```

### Pages Integration
- **Informative pages:** `banner_image` field stores media path
- **Location pages:** `template_data` JSON includes paths for:
  - Hero banner image
  - Intro section image
  - Gallery images array

## Best Practices

### Naming Files
- Use descriptive names: `ludhiana-campus-entrance.jpg` (not `IMG_12345.jpg`)
- Use lowercase with hyphens
- Include location/context in filename

### Image Sizes
- **Hero banners:** 1200×600px or similar wide format
- **Intro images:** 600×400px or similar
- **Gallery:** 800×600px or larger
- **Logo/icons:** 400×400px or square

### Organization
- Upload images by category (locations, courses, events, etc.)
- Use consistent naming convention
- Delete unused images to keep library clean
- Consider adding prefixes: `ludhiana-`, `mohali-`, etc.

### Performance
- Optimize images before uploading (compress for web)
- Use appropriate format (JPEG for photos, PNG for graphics)
- Avoid excessively large files (aim for <500KB per image)

## Workflow Example: Creating a Location Page

1. **Go to Media Library**
   - Upload hero banner image for city
   - Upload intro/about image
   - Upload 3-4 gallery images

2. **Go to Pages → Add Page**
   - Set type to "Location"
   - Fill in basic info (title, slug)

3. **Hero Section**
   - Click "Select from Library"
   - Find and select hero banner
   - Fill in title, badge, CTA text

4. **Intro Section**
   - Click "Select from Library"
   - Find and select about image
   - Write introduction text
   - Add statistics

5. **Gallery Section**
   - Click "Add from Library"
   - Select multiple images at once (multi-select)
   - Images appear as thumbnails

6. **Save Page**
   - All media paths saved to template_data JSON
   - Preview live at location page URL

## Troubleshooting

### "Image not found" on published page
- Check if image was deleted from Media Library
- Re-upload image with same filename/path
- Or use "Select from Library" to update the path

### Gallery shows wrong number of images
- Some images may have been deleted from library
- Remove references by editing the page
- Re-add images from current library

### Search not finding images
- Search looks for exact filename match
- Try partial filename (e.g., "ludhiana" not "ludhiana-campus")
- Check that image is actually in library (might be deleted)

### Modal won't load
- Ensure you have Admin access
- Check browser console for errors
- Clear browser cache and reload

## File Size Limits

- Individual file: typically 50MB (depends on server config)
- Upload multiple: depends on server PHP settings
- Recommended: keep images under 5MB each

## Backup Recommendations

- Regularly backup the `/uploads/media/` directory
- Database contains media_library records (backup your DB)
- Consider external backup service for important media
