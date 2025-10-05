Auto-install Composer variant:
- app/ (non-public): config.php, ensure_vendor.php, api/, pdf.php, admin/, composer.json
- public/ (docroot): index.php, api/save.php wrapper, pdf.php wrapper, admin/index.php wrapper, .htaccess

How it works:
- On first PDF request, app/pdf.php includes app/ensure_vendor.php which tries to run 'composer install' automatically.
- Requires PHP exec functions and outbound HTTPS. If disabled, run 'composer install' manually in app/.

Deploy:
1) Set your vhost DocumentRoot to /path/to/anketa/public
2) Edit /path/to/anketa/app/config.php (DB_USER/DB_PASS). DB: motorpoi_anketa
3) Open https://anketa.motorpoint.org/ and submit.
4) Download PDF -> will trigger auto-install if vendor missing.
5) Admin: https://anketa.motorpoint.org/admin/
