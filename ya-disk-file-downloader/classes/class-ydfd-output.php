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

			?>

			<h2 class="nav-tab-wrapper">
				<a href="?page=YDFD&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>">Настройки</a>
				<a href="?page=YDFD&tab=actions" class="nav-tab <?php echo $active_tab == 'actions' ? 'nav-tab-active' : ''; ?>">Действия</a>
			</h2>
			<?php settings_errors(); ?>
			<?php if( $active_tab == 'display_options' ) { ?>
				<form action="<?php echo get_admin_url(); ?>options.php" method="POST">
					<?php
					settings_fields( 'option_group' );     // скрытые защитные поля
					do_settings_sections( 'YDFD_page' ); // секции с настройками (опциями). У нас она всего одна 'section_id'
					submit_button();
					?>
				</form>

			<?php } elseif ($active_tab == 'actions') {
				if (isset($_SESSION['YDFD'])){
					echo $_SESSION['YDFD'];
				}
				unset($_SESSION['YDFD']);
			    ?>

                <section style="padding: 10px 0">
                    <h3>Получение файла</h3>
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                        <input id="public-url" type="text" size="50" name="file">
                        <input type="hidden" name="action" value="get_file" />
                        <input class="button button-primary" type="submit" value="Получить файл">
                        <p><label for="public-url"><span class="dashicons dashicons-warning"></span>Оставьте это поле пустым, что бы получить файл по ссылки из настроек.</label></p>
                    </form>
                </section>
				<section  style="padding: 10px 0">
                    <h3>Задача в WP-CRON</h3>
                    <?php if ( wp_next_scheduled( 'YDFD_daily_event' ) ) { ?>
                        <p><?php echo 'Получение файла запланировано ' . date('d.m.Y H:i', wp_next_scheduled( 'YDFD_daily_event' )); ?></p>
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                        <input type="hidden" name="action" value="delete_cron_event" />
                        <input class="button button-primary" type="submit" value="Удалить CRON задачу">
                    </form>
                <?php } else { ?>
                        <h4>Задать время выполнения задачи:</h4>
                        <p><code><i>Задача будет выполнятся ежедневно в установленное ниже время начиная с установленной даты</i></code></p>
                    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                        <label for="cron-date">Дата</label>
                        <input id="cron-date" type="date" name="cron-date">
                        <label for="cron-time">Время</label>
                        <input id="cron-time" type="time" name="cron-time">
                        <input type="hidden" name="action" value="add_cron_event" />
                        <input class="button button-primary" type="submit" value="Создать CRON задачу">
                    </form>
                <?php } ?>
                </section>

                <?php
				$admin_actions->list_dir();
			}
			?>
		</div>
		<?php

	}
}