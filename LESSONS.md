# Lessons Learned

## HEIC Photo Upload

**Problem:** Uploading HEIC photos from iPhone gave "The photo field must be an image."

**What we learned:**

1. **Use `mimes:` not `image`** — Laravel's `image` validation rule only allows jpeg, png, bmp, gif, svg, webp. It does not include HEIC/HEIF. Always use `mimes:jpeg,jpg,png,gif,webp,heic,heif` for photo uploads that need to accept iPhone photos.

2. **Be explicit on `accept`** — `accept="image/*"` is too vague. Some browsers and mobile OSes handle HEIC inconsistently under it. Explicitly listing MIME types and extensions works more reliably:
   ```html
   accept="image/jpeg,image/png,image/gif,image/webp,image/heic,image/heif,.heic,.heif,.jpg,.jpeg,.png"
   ```

3. **Convert HEIC server-side before sending to third-party APIs** — External APIs (like PlantNet) don't accept HEIC. Imagick is the right tool. Convert to JPEG at 85% quality and strip metadata before POSTing:
   ```php
   $imagick = new \Imagick();
   $imagick->readImageBlob(file_get_contents($file->getRealPath()));
   $imagick->setImageFormat('jpeg');
   $imagick->setImageCompressionQuality(85);
   $imagick->stripImage();
   ```

4. **Distinguish error messages** — If you ever see "The photo field must be an image" it's the `image` rule. "The photo field must be a file of type: ..." is the `mimes` rule. Knowing which one fired tells you exactly where the validation is happening.

5. **Imagick availability** — Verify Imagick is installed in your Docker container early: `php -r "echo extension_loaded('imagick') ? 'YES' : 'NO';"`. The Sail 8.4 image includes it by default.

6. **HEIC auxiliary frames crash Imagick** — iPhone HEIC photos (especially portrait mode and Live Photos) contain multiple auxiliary image references (depth map, thumbnail, etc.). Both `readImageBlob()` and `readImage($path . '[0]')` fail with `ImagickException: Too many auxiliary image references` when the version of libheif in the container is too old to handle them. The `convert` CLI binary is not included in the Sail 8.4 image either.
   - **Short-term fix:** wrap Imagick conversion in a try/catch and fall back gracefully — send the original HEIC to third-party APIs (PlantNet accepts HEIC natively) and store HEIC as-is if conversion fails.
   - **Long-term fix:** upgrade libheif in the Dockerfile (`apt-get install libheif-dev`) and rebuild the image. Newer libheif versions handle the auxiliary frame format correctly.
