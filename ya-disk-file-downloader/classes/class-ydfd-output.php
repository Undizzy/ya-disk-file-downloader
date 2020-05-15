<?php


class YDFD_Output {
	public function __construct() {
	}

	// Вывод страницы с настройками
	public function YDFD_start(){
		$admin_actions = new YDFD_Actions();
		?>
		<div class="wrap">
			<h2><?php echo get_admin_page_title() ?></h2>
			<?php

			$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'display_options';
			$action = isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : '';
			$file = isset( $_GET[ 'file' ] ) ? $_GET[ 'file' ] : '';

			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=YDFD&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>">Настройки</a>
				<a href="?page=YDFD&tab=actions" class="nav-tab <?php echo $active_tab == 'actions' ? 'nav-tab-active' : ''; ?>">Действия</a>
			</h2>
			<?php settings_errors(); ?>
			<?php if( $active_tab == 'display_options' ) { ?>
				<form action="options.php" method="POST">
					<?php
					settings_fields( 'option_group' );     // скрытые защитные поля
					do_settings_sections( 'YDFD_page' ); // секции с настройками (опциями). У нас она всего одна 'section_id'
					submit_button();
					?>
				</form>

			<?php } elseif ($active_tab == 'actions') { ?>
				<div style="padding: 25px 0">
					<a class="button button-primary" href="?page=YDFD&tab=actions&action=get-file">Получить файл</a>
				</div>
				<?php
				if (isset($action) and $action == 'get-file'){
					echo $admin_actions->YDFD_get_file();
				}
				if (wp_next_scheduled('YDFD_daily_event')){ ?>
					<a class="button button-primary" href="?page=YDFD&tab=actions&action=delete-event">Удалить CRON задачу</a>
				<?php } else { ?>
					<a class="button button-primary" href="?page=YDFD&tab=actions&action=set-event">Создать задачу в CRON</a>
				<?php }
				if (isset($action) and $action == 'delete-event'){
					$admin_actions->YDFD_dell_cron();
					echo '<div class="updated"><p>Задача удалена</p></div>';
				} elseif (isset($action) and $action == 'set-event'){
					$admin_actions->YDFD_add_to_cron();
					echo '<div class="updated"><p>Задача создана</p></div>';
				} elseif (isset($action) and $action == 'unset' and isset($file) ){
					$admin_actions->delete_file($file);
				}
				$admin_actions->list_dir();
			}
			?>
		</div>
		<?php

	}
}