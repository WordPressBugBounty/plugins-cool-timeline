Inter font - self-hosted (WordPress guideline compliant)
======================================================

Place the following Inter woff2 files in this folder (names must match exactly; Linux/InstaWP is case-sensitive):

  - Inter-Regular.woff2   (weight 400)
  - Inter-Medium.woff2    (weight 500)
  - Inter-SemiBold.woff2  (weight 600)
  - Inter-Bold.woff2      (weight 700)

Download from: https://github.com/rsms/inter/releases
Extract from the release zip and copy the woff2 files from the font folder into this directory.
Alternatively use: https://rsms.me/inter/ (download and take woff2 from the package).

How to check if fonts are loading on live/InstaWP:
- DevTools > Network tab > filter "Font" > refresh page. Look for Inter-*.woff2: 404 = wrong path or filename; 200 = OK.
- Console tab may show CORS or block messages. Filenames must match exactly (case-sensitive on Linux).
- The plugin injects @font-face with absolute URLs from the main plugin file so paths work on all hosts.
