# Yandex Disk File Downloader WordPress Plugin
### English
WordPress plugin for download shered files in Yandex Disk
Features:
* Files are download to folder on your server (wp-content / uploads / ya-disk-files). Folder is creat when the plugin is activate.
* In case of an error, the Yandex API (if the API does not return a download link) tries to get the file 3 more times.
* It is possible to add a task to WP-Cron for regular daily file downloads.
* It is possible to download via public link once (without saving the link in the plugin settings).
* Display a list of all downloaded files in a folder.
* Provides manual removal of downloaded files one at a time or all at once.
* When removing the plugin, all plugin settings are delete from the database, the task in WP-Cron, previously downloaded files, and the plugin’s working folder in wp-content / uploads /
### Russian
Плагин для CMS WordPress позволяющий скачивать файлы по публичной ссылке с Яндекс.Диска.
Особенности:
* Файлы скачиваются в папку на вашем сервере (wp-content/uploads/ya-disc-files). Папка создаётся при активации плагина.
* В случае ошибки Яндекс API (если API не возвращает ссылку на загрузку) пытается получить файл ещё 3 раза.
* Есть возможность добавить задачу в WP-Cron для регулярного ежедневного скачивания файла.
* Возможно скачивание по публичной ссылке единоразово (без сохранения ссылки в настройках плагина).
* Отображает список всех скачанных файлов в папке.
* Обеспечивает ручное удаление скачанных файлов по одному или всех сразу.
* При удалении плагина удаляются все настройки плагина из базы данных, задача в WP-Cron, ранее скачанные файлы и рабочая папка плагина в wp-content/uploads/
