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

	public function __construct() {
	}

	public function YDFD_get_file($count = 0){
		global $output_array;
		$option = get_option('option_name'); // Получает массив настроек
		$public_url = $option['input'];      // Получает публичный адрес с Я.Диска с файлом из массива настроек
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
							# Записываем файл в папку wp-content/uploads/ya-disc-files/
							$is_file_write = file_put_contents(WP_CONTENT_DIR . "/uploads/ya-disc-files/" . iconv("utf-8", "cp1251", $filename), $file);
						} else { // Папки для записи файла не существует на сервере
							return "<div class='update-nag'><p>Папки <strong>wp-content/uploads/ya-disc-files/</strong> не существует!</p></div>";
						}
						if ($is_file_write) { // Файл успешно получен и записан в папку на сервере
							$output = '<div class="updated"><p>Файл получен</p></div>
                                    <a class="button button-secondary" 
                                    href=' . WP_CONTENT_URL . '/uploads/ya-disc-files/'. $filename .'>Скачать полученный файл</a>';
							wp_mail($report_email, "YDFD", "Файл получен");
							return $output;
						} else { // Неудачная попытка записи файла в папку на сервере (проверить права доступа к папке на запись)
							$output = "<div class='update-nag'><p>Не удалось записать файл в папку Uploads/ya-disc-files/</p></div>";
							return $output;
						}
					} else { // Файл не удалось получить по переданной API Я.Диска ссылке, но ссылка получена!
						$output = "<div class='update-nag'><p>Не удалось скачать файл с Я.Диска</p></div>";
						wp_mail($report_email, "YDFD", "Не удалось скачать файл с Я.Диска");
						return $output;
					}
				} else { // Неуспешное получение ссылки для скачивания. От API Я.Диска вернулся ответ с сообщением об ошибке.
					if ($count >= 3){ // Устанавливаем кол-во повторных попыток скачать файл.
						$output_array[] = $json_result['message']; // Сообщение от API при последней попытке
						$output_array[] = $req_url; // URL запроса при последней попытке
						echo "<div class='update-nag'><p>Ссылка на скачивание файла не получена. API не вернул ссылку</p></div>";
						echo "<pre>"; print_r($output_array); echo "</pre>";
						wp_mail($report_email, "YDFD", $json_result['message']);
						#return $output;
					} else { // Если API не вернул ссылку на скачивание по запросу, повторяем запрос
						$count++;
						$output_array[] = $json_result['message'];
						$output_array[] = $req_url;
						$output = $this->YDFD_get_file($count);
						return $output;
					}
				}
			}
		}
		return null;
	}

	public function YDFD_add_to_cron() {
		// удалим на всякий случай все такие же задачи cron, чтобы добавить новые с "чистого листа"
		// это может понадобиться, если до этого подключалась такая же задача неправильно (без проверки что она уже есть)
		wp_clear_scheduled_hook( 'YDFD_daily_event' );

		// добавим новую cron задачу которая будет стартовать в 6 утра и повторяться через 24 часа
		wp_schedule_event( strtotime('06:00:00'), 'daily', 'YDFD_daily_event');
	}

	public function do_YDFD_daily_cron() {
		// делаем что-либо в CRON
		$this->YDFD_get_file();
	}

	public function YDFD_dell_cron() {
		wp_clear_scheduled_hook('YDFD_daily_event');
	}

	public function YDFD_add_file_folder(){
		if ( !file_exists($this->dirpath) ){
			mkdir($this->dirpath);
		}
	}

	public function list_dir(){
		$files = scandir($this->dirpath);
		echo '<h3>Files in your Server</h3>';
		echo '<table class="wp-list-table widefat fixed striped posts">';
		echo '<thead><tr><td>File Name</td><td>Actions</td></tr></thead>';
		foreach ($files as $file){
			if ($file === '.' || $file === '..'){
				continue;
			} else {
				echo '<tr>'; ?>
				<td>
					<a href="<?php echo $this->dir_url . '/' . urlencode( $file ) ?>" title="click to download"><?php echo $file ?></a>
				</td>
				<td>
					<a href="?page=YDFD&tab=actions&action=unset&file=<?php echo $file ?>">Delete</a>
				</td> <?php
				echo '</tr>';
			}
		}
		echo '</table>';
		echo '<a class="button button-primary" href="?page=YDFD&tab=actions&action=unset&file=all">Очистить рабочую паку</a>';
	}

	public function delete_file($filename){
		if ($filename === 'all'){
			$files = scandir($this->dirpath);
			foreach ($files as $file){
				if (is_file($this->dirpath . '/' . $file)) {
					unlink( $this->dirpath . '/' . $file );
				}
			}
			$message = "Work folder is clear!";
			echo '<div class="notice notice-success is-dismissible"> <p>' . $message . '</p></div>';
		} else {
			$file_path = $this->dirpath . '/' . $filename;
			if ( file_exists( $file_path ) ) {
				unlink( $file_path );
				$message = "File <strong>" . $filename . "</strong> is delete!";
				echo '<div class="notice notice-success is-dismissible"> <p>' . $message . '</p></div>';
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
	public function rus2translit($string) {
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

}