ALTER TABLE #__brands ADD COLUMN logo_svg_path text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER logo_svg;
ALTER TABLE #__brands ADD COLUMN logo_png_path text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER logo_svg_path;
ALTER TABLE #__brands ADD COLUMN favicon_zip_path text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL AFTER logo_png_path;
