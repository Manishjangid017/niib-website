# Media Picker — Quick Reference

## Key Features at a Glance

| Feature | How It Works |
|---------|-------------|
| **Select Images** | Click "Select from Library" → choose from modal → image path saved |
| **Upload Directly** | Click "Upload" button → choose file from computer → preview appears |
| **Multi-Select Gallery** | Click "Add from Library" → can select 2, 3, or more images at once |
| **Search** | Type in modal search box to find images by filename |
| **Remove** | Click × button on thumbnail to remove from gallery |
| **Preview** | Image preview shows immediately after selection |

## Where It's Available

### Informative Pages
- **Banner Image** (right sidebar) → Settings card

### Location Pages
- **Hero Banner Image** (section 1)
- **Intro Image** (section 2)  
- **Gallery Images** (section 5)

## Step-by-Step: Add Hero Image to Location Page

```
1. Admin → Pages → Edit location page
2. Scroll to "1. Hero Section"
3. See "Hero Banner Image" field
4. Click "Select from Library" button
5. Modal opens showing media grid
6. (Optional) Type in search box to filter
7. Click on an image to select it (blue border)
8. Click "Select" button at bottom
9. Modal closes, image preview appears
10. Scroll down and click "Save Page"
11. Done! Image is now in your location page
```

## Step-by-Step: Add Multiple Gallery Images

```
1. Admin → Pages → Edit location page
2. Scroll to "5. Recognition / Gallery"
3. Click "Add from Library" button
4. Modal opens
5. Click first image (selected = blue border)
6. Hold Ctrl (Cmd on Mac) and click more images
7. Select 3-4 images total
8. Click "Select" button
9. Modal closes
10. All thumbnails appear with × buttons
11. Click × on any to remove before saving
12. Click "Save Page"
13. Done! Gallery is populated
```

## What Happens Behind the Scenes

```
Click "Select from Library"
        ↓
Modal fetches images from database via AJAX
        ↓
User selects images in modal
        ↓
Image path stored in form field
        ↓
Preview image shown on page
        ↓
Click "Save Page"
        ↓
Image path saved to pages table
        ↓
Frontend renders using saved path
```

## Image Types Supported

**Images (can display):**
- JPG, JPEG
- PNG
- GIF
- WebP
- SVG

**Other files (can select but may not display):**
- PDF
- DOC, DOCX
- MP4

## Pro Tips

✓ **Better Than Uploading Each Time** — Reuse images across multiple pages  
✓ **Search First** — Type city name to find location-specific images  
✓ **Use Descriptive Names** — "ludhiana-campus.jpg" vs "IMG_12345.jpg"  
✓ **Add to Library First** — Go to Admin → Media before editing pages  
✓ **Preview Before Saving** — Always check image looks right before saving page  
✓ **Remove Duplicates** — Click × on gallery items to clean up before save  

## Common Tasks

### Find & Use an Existing Image
```
Click "Select from Library" 
→ Type image name in search
→ Click to select
→ Click "Select" button
```

### Upload New Image Then Use It
```
Click "Upload" button
→ Choose file from computer
→ Preview appears
→ Save page
(Image goes to pages upload folder)
```

### Add Same Image to Multiple Pages
```
First page:
  Click "Select from Library"
  → Select image
  → Save page

Second page:
  Click "Select from Library"
  → Select same image again
  → Save page
```

### Remove Image from Gallery
```
Edit page
→ Scroll to gallery section
→ See image thumbnails
→ Click × on the one to remove
→ Thumbnail disappears
→ Save page
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| Modal won't open | Refresh browser, check console for errors |
| Images not showing | Go to Admin → Media to verify images exist |
| Can't find image | Try searching by partial name in modal |
| Image disappeared | Was it deleted from Media Library? Check Admin → Media |
| Same image twice in gallery | Click × to remove duplicate |
| Image looks wrong | Delete and select correct image, then save |

## Browser Keys

**In Media Picker Modal:**
- **Click** — Select single image
- **Ctrl+Click** — Select multiple (Windows/Linux)
- **Cmd+Click** — Select multiple (Mac)
- **Type** — Search for images

## API Reference

### Opening Media Picker
```javascript
// For single selection
openMediaPicker(function(item) {
    console.log(item.path);  // media path
    console.log(item.url);   // full URL
    console.log(item.name);  // original filename
});

// For images only
openMediaPicker(callback, 'image');

// For all file types
openMediaPicker(callback, 'all');
```

### Item Object Returned
```javascript
{
    id: 123,
    path: "assets/media/ludhiana-campus.jpg",
    url: "http://localhost/niibinstitute/assets/media/...",
    name: "ludhiana-campus.jpg",
    type: "jpg"
}
```

## Files You Don't Need to Edit

- `admin/modules/media-picker.php` — API endpoint
- `admin/includes/media-picker-modal.php` — Modal component
- `admin/modules/pages.php` — Already updated ✓

Just use the UI buttons!

## Files in Media Library

```
/uploads/media/
├── image1.jpg
├── image2.png
├── document.pdf
└── ... (26 items)
```

Manage at: Admin → Media Library

## Remember

🎯 **Goal:** Select images from centralized library instead of uploading to each page  
✨ **Benefit:** Reuse images, consistent image management  
⚡ **Speed:** Faster than uploading same image multiple times  
🔍 **Organization:** All images in one searchable place  

**Happy image managing!** 📸
