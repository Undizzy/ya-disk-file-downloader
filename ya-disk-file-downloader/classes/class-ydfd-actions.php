<?php


class YDFD_Actions {
	/**
	 * Path to working directory in wp-content/uploads
	 */
	public $dirpath =  WP_CONTENT_DIR . '/uploads/ya-disk-files';

	/**
	 * URL to working directory in wp-content/uploads
	 */
	public $dir_url = WP_CONTENT_URL . '/uploads/ya-disk-files';

	public $output_array = array();

	public function __construct() {
	}

	public function YDFD_get_file($count = 0){
		$option = get_option('option_name'); // Получает массив настроек
        if (isset($_POST['file']) and $_POST['file'] !== ''){
            $public_url = $_POST['file'];
        } else {
	        $public_url = $option['input'];      // Получает публичный адрес с Я.Диска с файлом из массива настроек
        }

		$report_email = $option['email'];    // Email для отправки отчёта о статусе выполнения

		if ($public_url){ //Публичный URL задан в настройках плагина

			$yadiskapiurl = "https://cloud-api.yandex.net/v1/disk/public/resources/download"; //URL API Я.Диска
			$req_url = $yadiskapiurl . "?public_key=" . $public_url; // Составляем URL для запроса ссылки на скачивание файла
			$result = wp_remote_get($req_url, array(
				'timeout'=> 15
			)); // Отправляет GET запрос к API Я.Диска для получения ссылки на скачивание

			if ($result){ // Ответ от API Я.Диска получен
				$json_result = json_decode($result['body'], true); // Декодируем json тело ответа
				if (isset($json_result['href']) ) { // Извлекаем ссылку для скачивания файла
					# Извлекаем название файла #
					$start_position = stripos($json_result['href'], '&filename=');
					$len = stripos($json_result['href'], '&disposition=')-$start_position;
					$file_URL = substr($json_result['href'], $start_position+10, $len-10);
					$filename = $this->rus2translit( urldecode( $file_URL ) );
					# ------------------------- #
					$file = file_get_contents($json_result['href']); // Получаем файл по извлечённой ссылке

					if ($file) { // Файл удалось получить
						if (file_exists($this->dirpath)) { // Папка для сохранения файла установлена (существует) в инсталяции WordPress
							# Записываем файл в папку wp-content/uploads/ya-disk-files/
							$is_file_write = file_put_contents($this->dirpath ."/" . iconv("utf-8", "cp1251", $filename), $file);
						} else { // Папки для записи файла не существует на сервере
							$message = __('Folder <strong>wp-content/uploads/ya-disk-files/</strong> does not exist', 'ydfd');
							$_SESSION['YDFD'] = "<div class='notice notice-error is-dismissible'><p>{$message}</p></div>";
							wp_mail($report_email, "YDFD", "$message");
							$this->redirect();
						}
						if ($is_file_write) { // Файл успешно получен и записан в папку на сервере
							$message = __('File successfully received ', 'ydfd') . "<a href=$this->dir_url/$filename download>" . __('Download File', 'ydfd') . "</a>";
							$_SESSION['YDFD'] = "<div class='notice notice-success  is-dismissible'><p>{$message}</p></div>";
							wp_mail($report_email, "YDFD", "$message");
							$this->redirect();
						} else { // Неудачная попытка записи файла в папку на сервере (проверить права доступа к папке на запись)
							$message = __('Failed to write file to folder Uploads/ya-disk-files/', 'ydfd');
							$_SESSION['YDFD'] = "<div class='notice notice-error is-dismissible'><p>{$message}</p></div>";
							wp_mail($report_email, "YDFD", "$message");
							$this->redirect();
						}
					} else { // Файл не удалось получить по переданной API Я.Диска ссылке, но ссылка получена!
						$message = __('Failed to download file from Yandex Disk', 'ydfd');
						$_SESSION['YDFD'] = "<div class='notice notice-error is-dismissible'><p>{$message}</p></div>";
						wp_mail($report_email, "YDFD", "$message");
						$this->redirect();
					}
				} else { // Неуспешное получение ссылки для скачивания. От API Я.Диска вернулся ответ с сообщением об ошибке.
					if ($count >= 3){ // Устанавливаем кол-во повторных попыток скачать файл.
						array_push($this->output_array, $json_result['message']); // Сообщение от API при последней попытке
						array_push($this->output_array, $req_url); // URL запроса при последней попытке
						$message = __('Link to download file not received. The API did not return the link.', 'ydfd') . '<pre>' . print_r($this->output_array, true) . '</pre>';
						$_SESSION['YDFD'] = "<div class='notice notice-error is-dismissible'><p>{$message}</p></div>";
						wp_mail($report_email, "YDFD", $json_result['message']);
						$this->redirect();
					} else { // Если API не вернул ссылку на скачивание по запросу, повторяем запрос
						$count++;
						array_push($this->output_array, $json_result['message']);
						array_push($this->output_array, $req_url);
						$this->YDFD_get_file($count);
					}
				}
			}
		}
		return null;
	}

	public function YDFD_add_to_cron() {
	    if (isset($_POST['cron-date']) and isset($_POST['cron-time'])){
	        $date = $_POST['cron-date'];
	        $time = $_POST['cron-time'];
        } else {
	        $date = date('Y-m-d H:i');
	        $time = '06:00';
        }
		// удалим на всякий случай все такие же задачи cron, чтобы добавить новые с "чистого листа"
		// это может понадобиться, если до этого подключалась такая же задача неправильно (без проверки что она уже есть)
		wp_clear_scheduled_hook( 'YDFD_daily_event' );

		// добавим новую cron задачу которая будет стартовать в 6 утра и повторяться через 24 часа
		wp_schedule_event( strtotime("$date $time"), 'daily', 'YDFD_daily_event');
		$message = __('Cron job successfully added in WP-Cron!', 'ydfd');
		$_SESSION['YDFD'] = "<div class='notice notice-success is-dismissible'><p>{$message}</p></div>";
		$this->redirect();
	}

	public function do_YDFD_daily_cron() {
		// делаем что-либо в CRON
		$this->YDFD_get_file();
	}

	public function YDFD_dell_cron() {
		wp_clear_scheduled_hook('YDFD_daily_event');
		$message = __('Cron job successfully deleted!', 'ydfd');
		$_SESSION['YDFD'] = "<div class='notice notice-success is-dismissible'><p>{$message}</p></div>";
		$this->redirect();
	}

	public function YDFD_add_file_folder(){
		if ( !file_exists($this->dirpath) ){
			mkdir($this->dirpath);
		}
	}

	public function list_dir(){
		$files = scandir($this->dirpath); ?>
		<h3><?php _e('Files in your Server', 'ydfd') ?></h3>
		<table class="wp-list-table widefat fixed striped posts">
		<thead><tr><td><?php _e('File Name','ydfd') ?></td><td><?php _e('Actions', 'ydfd') ?></td></tr></thead>
		<?php
        foreach ($files as $file){
			if ($file === '.' || $file === '..'){
				continue;
			} else { ?>
            <tr>
				<td>
					<a href="<?php echo $this->dir_url . '/' . urlencode( $file ) ?>" title="<?php _e('Click to download', 'ydfd')?>" download><?php echo $file ?></a>
				</td>
				<td>
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                        <input type="hidden" name="filename" value="<?php echo $file ?>" >
                        <input type="hidden" name="action" value="delete_file" >
                        <input type="submit" class="link delete" value="<?php _e('Delete', 'ydfd') ?>">
                    </form>
				</td>
            </tr>
            <?php }
		} ?>
		</table>
		<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <input type="hidden" name="filename" value="all" >
            <input type="hidden" name="action" value="delete_file" >
            <input type="submit" class="button button-primary" value="<?php _e('Clear working Folder', 'ydfd') ?>">
        </form>
		<?php
	}

	public function delete_file(){
	    $filename = $_POST['filename'];
		if ($filename === 'all'){
			$files = scandir($this->dirpath);
			foreach ($files as $file){
				if (is_file($this->dirpath . '/' . $file)) {
					unlink( $this->dirpath . '/' . $file );
				}
			}
			$message = __('Work folder is clear!', 'ydfd');
			$_SESSION['YDFD'] = "<div class='notice notice-success is-dismissible'><p>{$message}</p></div>";
			$this->redirect();
		} else {
			$file_path = $this->dirpath . '/' . $filename;
			if ( file_exists( $file_path ) ) {
				unlink( $file_path );
				$message = __('File ', 'ydfd') ."<strong>{$filename}</strong>" . __('is delete!', 'ydfd');
				$_SESSION['YDFD'] = "<div class='notice notice-success is-dismissible'><p>{$message}</p></div>";
				$this->redirect();
			} else {
				return null;
			}
		}
	}

	/**
	 * Функция транслитерации.
	 *
	 * @param $string
	 *
	 * @return string
	 */
	private function rus2translit($string) {
		$converter = array(
			'а' => 'a',   'б' => 'b',   'в' => 'v',
			'г' => 'g',   'д' => 'd',   'е' => 'e',
			'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
			'и' => 'i',   'й' => 'y',   'к' => 'k',
			'л' => 'l',   'м' => 'm',   'н' => 'n',
			'о' => 'o',   'п' => 'p',   'р' => 'r',
			'с' => 's',   'т' => 't',   'у' => 'u',
			'ф' => 'f',   'х' => 'h',   'ц' => 'c',
			'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
			'ь' => '\'',  'ы' => 'y',   'ъ' => '\'',
			'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

			'А' => 'A',   'Б' => 'B',   'В' => 'V',
			'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
			'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
			'И' => 'I',   'Й' => 'Y',   'К' => 'K',
			'Л' => 'L',   'М' => 'M',   'Н' => 'N',
			'О' => 'O',   'П' => 'P',   'Р' => 'R',
			'С' => 'S',   'Т' => 'T',   'У' => 'U',
			'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
			'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
			'Ь' => '\'',  'Ы' => 'Y',   'Ъ' => '\'',
			'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
			' ' => '_',
		);
		return strtr($string, $converter);
	}

	private function redirect(){
		header('Location: ' . $addr = $_SERVER['HTTP_REFERER']);
	}

}