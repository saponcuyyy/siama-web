#!/bin/bash
set -euo pipefail

# ==========================================
# KONFIGURASI HOSTINGER
# Buat file deploy-config.sh (tidak ikut git)
# dan isi nilai HOST, USER, PORT, REMOTE_DIR
# Contoh: cp deploy-config.example.sh deploy-config.sh
# ==========================================
CONFIG_FILE="$(dirname "$0")/deploy-config.sh"

if [[ -f "$CONFIG_FILE" ]]; then
    source "$CONFIG_FILE"
else
    echo "❌ ERROR: deploy-config.sh tidak ditemukan!"
    echo "   Jalankan: cp deploy-config.example.sh deploy-config.sh"
    echo "   Lalu isi nilai HOST, USER, PORT, REMOTE_DIR di dalamnya."
    exit 1
fi

# Validasi variabel wajib
: "${HOST:?'HOST tidak diset di deploy-config.sh'}"
: "${USER:?'USER tidak diset di deploy-config.sh'}"
: "${PORT:?'PORT tidak diset di deploy-config.sh'}"
: "${REMOTE_DIR:?'REMOTE_DIR tidak diset di deploy-config.sh'}"

PUBLIC_HTML="/home/$USER/public_html"

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "🚀 DEPLOY KE PRODUCTION"
echo "   Host: $USER@$HOST:$PORT"
echo "   Dir : $REMOTE_DIR"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

# ==========================================
# STEP 1: Cek koneksi SSH ke server
# ==========================================
echo ""
echo "🔌 [1/6] Menguji koneksi SSH ke server..."
if ! ssh -p "$PORT" -o ConnectTimeout=10 -o BatchMode=yes "$USER@$HOST" "echo OK" &>/dev/null; then
    echo "❌ Gagal terhubung ke server! Cek:"
    echo "   - Koneksi internet"
    echo "   - SSH aktif di panel Hostinger"
    echo "   - IP Anda tidak diblokir firewall server"
    exit 1
fi
echo "✅ Koneksi SSH berhasil."

# ==========================================
# STEP 2: Pastikan branch main dan pull terbaru
# ==========================================
echo ""
echo "🌿 [2/6] Memastikan berada di branch main..."
git checkout main
git pull origin main

# ==========================================
# STEP 3: Build aset frontend
# ==========================================
echo ""
echo "🔨 [3/6] Membangun aset frontend..."
npm ci --silent && npm run build
echo "✅ Build frontend selesai."

# ==========================================
# STEP 4: Upload file aplikasi via rsync
# ==========================================
echo ""
echo "📦 [4/6] Mengunggah file aplikasi ke server..."
# Menggunakan --delete agar file yang sudah dihapus lokal
# juga terhapus di server (tidak ada file orphan).
rsync -avz --delete -e "ssh -p $PORT" \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.env' \
  --exclude='public/hot' \
  --exclude='public/build' \
  --exclude='public/images' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='storage/app/public' \
  --exclude='deploy-config.sh' \
  ./ "$USER@$HOST:$REMOTE_DIR"

echo ""
echo "🎨 Mengunggah aset build ke public_html..."
rsync -avz --delete -e "ssh -p $PORT" \
  ./public/build/ "$USER@$HOST:/home/$USER/public_html/build/"

echo ""
echo "🖼️  Mengunggah aset gambar ke public_html/images..."
rsync -avz -e "ssh -p $PORT" \
  ./public/images/ "$USER@$HOST:/home/$USER/public_html/images/"

echo ""
echo "🖼️  Mengunggah aset gambar ke direktori aplikasi (DomPDF)..."
rsync -avz -e "ssh -p $PORT" \
  ./public/images/ "$USER@$HOST:$REMOTE_DIR/public/images/"

echo ""
echo "📄 Menyinkronkan seluruh folder build ke direktori aplikasi..."
rsync -avz --delete -e "ssh -p $PORT" \
  ./public/build/ "$USER@$HOST:$REMOTE_DIR/public/build/"

# ==========================================
# STEP 5: Jalankan perintah Artisan di server
# ==========================================
echo ""
echo "⚙️  [5/6] Menjalankan perintah Artisan di server..."
ssh -p "$PORT" "$USER@$HOST" "cd $REMOTE_DIR && \
    composer install --no-dev --optimize-autoloader && \
    php artisan config:clear && \
    php artisan migrate --force && \
    php artisan db:seed --class=RolePermissionSeeder && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan event:cache"

# ==========================================
# STEP 5b: Perbaiki index.php dan symlink storage di public_html
# ==========================================
echo ""
echo "🔗 Memastikan public_html mengarah ke direktori yang benar..."
ssh -p "$PORT" "$USER@$HOST" "
# Update index.php agar mengarah ke REMOTE_DIR yang benar
cat > $PUBLIC_HTML/index.php << 'PHPEOF'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists(\$maintenance = __DIR__.'/../$(basename $REMOTE_DIR)/storage/framework/maintenance.php')) {
    require \$maintenance;
}

require __DIR__.'/../$(basename $REMOTE_DIR)/vendor/autoload.php';

/** @var Application \$app */
\$app = require_once __DIR__.'/../$(basename $REMOTE_DIR)/bootstrap/app.php';

\$app->handleRequest(Request::capture());
PHPEOF

# Update symlink storage agar mengarah ke REMOTE_DIR yang benar
rm -f $PUBLIC_HTML/storage
ln -s $REMOTE_DIR/storage/app/public $PUBLIC_HTML/storage
echo 'OK'"
echo "✅ public_html/index.php dan storage symlink sudah benar."

# ==========================================
# STEP 6: Selesai
# ==========================================
echo ""
echo "✅ [6/6] Deploy selesai!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "⚠️  PASTIKAN .env DI SERVER SUDAH PRODUCTION:"
echo "   APP_ENV=production"
echo "   APP_DEBUG=false"
echo "   SESSION_DRIVER=file"
echo "   SESSION_ENCRYPT=true"
echo "   LOG_LEVEL=error"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
