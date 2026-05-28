#!/bin/bash

# ==========================================
# KONFIGURASI HOSTINGER (Silakan sesuaikan)
# ==========================================
HOST="45.143.81.228"
USER="u7686873"
PORT="65002" # Port SSH cPanel
REMOTE_DIR="/home/u7686873/siama-web"
# NOTE: Sesuaikan REMOTE_DIR dengan path folder project Laravel Anda di Hostinger

echo "Memastikan berada di branch main..."
git checkout main
git pull origin main

echo "Membangun aset frontend (opsional, jika belum dilakukan)..."
# Hapus komentar (#) di bawah ini jika ingin mem-build aset secara lokal sebelum deploy
# npm ci && npm run build

echo "Mengunggah file ke Hostinger menggunakan rsync..."
# Proses ini akan mengabaikan folder node_modules, vendor, dan file .env agar tidak menimpa pengaturan di server
rsync -avz -e "ssh -p $PORT" \
  --exclude='.git' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.env' \
  --exclude='public/hot' \
  --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='public/*' \
  ./ $USER@$HOST:$REMOTE_DIR

echo "Mengunggah aset publik ke public_html..."
rsync -avz -e "ssh -p $PORT" \
  ./public/build/ $USER@$HOST:/home/$USER/public_html/build/

echo "Mengunggah aset gambar ke public_html/images..."
rsync -avz -e "ssh -p $PORT" \
  ./public/images/ $USER@$HOST:/home/$USER/public_html/images/

echo "Menyinkronkan manifest.json ke direktori aplikasi..."
rsync -avz -e "ssh -p $PORT" \
  ./public/build/manifest.json $USER@$HOST:$REMOTE_DIR/public/build/manifest.json

echo "Menjalankan perintah Artisan di server Hostinger..."
ssh -p $PORT $USER@$HOST "cd $REMOTE_DIR && \
    composer install --no-dev --optimize-autoloader && \
    php artisan migrate --force && \
    php artisan optimize:clear && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan event:cache && \
    php artisan view:cache"

echo "Deploy selesai!"
