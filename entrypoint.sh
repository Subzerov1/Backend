#!/bin/sh
# entrypoint.sh

# اعمل storage link جوه الحاوية
php artisan storage:link

# نفذ أي أوامر تانية لو محتاج
# مثلاً تشغيل السيرفر
php artisan serve --host=0.0.0.0 --port=8000

# لو انت بتستخدم supervisor أو أي process تاني ممكن تحطه هنا
