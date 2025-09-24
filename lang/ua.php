<?php
// Защита от прямого доступа
if (!defined('SECURE_ACCESS')) {
    die('Direct access not permitted');
}

// Украинская локализация для StormHosting UA
$lang = [
    // Основные элементы
    'site_name' => 'StormHosting UA',
    'site_slogan' => 'Надійний хостинг для вашого бізнесу',
    'welcome' => 'Ласкаво просимо',
    'loading' => 'Завантаження...',
    'error' => 'Помилка',
    'success' => 'Успішно',
    'warning' => 'Попередження',
    'info' => 'Інформація',
    
    // Навігація
    'nav_home' => 'Головна',
    'nav_domains' => 'Домени',
    'nav_hosting' => 'Хостинг',
    'nav_vds' => 'VDS/VPS',
    'nav_tools' => 'Інструменти',
    'nav_info' => 'Інформація',
    'nav_contacts' => 'Контакти',
    'nav_login' => 'Вхід',
    'nav_register' => 'Реєстрація',
    'nav_client_area' => 'Кабінет клієнта',
    'nav_logout' => 'Вихід',
    
    // Підменю Домени
    'domains_register' => 'Реєстрація домену',
    'domains_whois' => 'Перевірка WHOIS',
    'domains_dns' => 'Перевірка DNS',
    'domains_transfer' => 'Перенесення домену',
    
    // Підменю Хостинг
    'hosting_shared' => 'Звичайний хостинг',
    'hosting_cloud' => 'Хмарне сховище',
    'hosting_reseller' => 'Реселінг хостингу',
    
    // Підменю VDS
    'vds_virtual' => 'Віртуальні сервери',
    'vds_dedicated' => 'Фізичні сервери',
    'vds_calculator' => 'Калькулятор цін',
    
    // Підменю Інструменти
    'tools_site_check' => 'Перевірка доступності сайту',
    'tools_http_headers' => 'Перевірка HTTP заголовків',
    'tools_ip_check' => 'Мій IP',
    'tools_site_info' => 'Інформація про сайт',
    
    // Підменю Інформація
    'info_about' => 'Про компанію',
    'info_quality' => 'Гарантія якості',
    'info_rules' => 'Правила надання послуг',
    'info_legal' => 'Юридична інформація',
    'info_complaints' => 'Книга скарг',
    'info_advertising' => 'Реклама',
    'info_faq' => 'FAQ',
    'info_ssl' => 'SSL сертифікати',
    
    // Головна сторінка
    'hero_title' => 'Професійний хостинг з підтримкою 24/7',
    'hero_subtitle' => 'Швидкі SSD сервери, безкоштовний SSL, миттєва активація',
    'hero_cta_hosting' => 'Обрати хостинг',
    'hero_cta_domain' => 'Зареєструвати домен',
    
    'features_title' => 'Чому обирають StormHosting UA?',
    'feature_speed_title' => '99.9% Аптайм',
    'feature_speed_desc' => 'Гарантована стабільність роботи ваших сайтів',
    'feature_support_title' => 'Підтримка 24/7',
    'feature_support_desc' => 'Швидка технічна підтримка в будь-який час',
    'feature_ssl_title' => 'Безкоштовний SSL',
    'feature_ssl_desc' => 'Let\'s Encrypt сертифікати для всіх сайтів',
    'feature_backup_title' => 'Автобекапи',
    'feature_backup_desc' => 'Щоденне резервне копіювання даних',
    
    'popular_domains_title' => 'Популярні домени',
    'domain_from' => 'від',
    'domain_per_year' => 'грн/рік',
    'register_now' => 'Зареєструвати зараз',
    
    'news_title' => 'Останні новини',
    'read_more' => 'Читати далі',
    'all_news' => 'Всі новини',
    
    // Форми
    'form_name' => 'Ім\'я',
    'form_email' => 'Email',
    'form_phone' => 'Телефон',
    'form_subject' => 'Тема',
    'form_message' => 'Повідомлення',
    'form_submit' => 'Відправити',
    'form_reset' => 'Очистити',
    'form_required' => 'Обов\'язкове поле',
    'form_invalid_email' => 'Невірний формат email',
    'form_invalid_phone' => 'Невірний формат телефону',
    'form_message_sent' => 'Повідомлення відправлено успішно',
    'form_message_error' => 'Помилка відправки повідомлення',
    
    // Авторизація
    'login_title' => 'Вхід в систему',
    'login_email' => 'Email',
    'login_password' => 'Пароль',
    'login_remember' => 'Запам\'ятати мене',
    'login_forgot' => 'Забули пароль?',
    'login_button' => 'Увійти',
    'login_no_account' => 'Немає аккаунту?',
    'login_register_link' => 'Зареєструватись',
    'login_invalid' => 'Невірний email або пароль',
    'login_blocked' => 'Аккаунт заблокований. Спробуйте пізніше',
    
    'register_title' => 'Реєстрація',
    'register_full_name' => 'Повне ім\'я',
    'register_email' => 'Email',
    'register_phone' => 'Телефон',
    'register_password' => 'Пароль',
    'register_confirm_password' => 'Підтвердіть пароль',
    'register_agree' => 'Я погоджуюсь з',
    'register_terms' => 'умовами використання',
    'register_button' => 'Зареєструватись',
    'register_have_account' => 'Вже є акаунт?',
    'register_login_link' => 'Увійти',
    'register_success' => 'Реєстрація пройшла успішно',
    'register_error' => 'Помилка реєстрації',
    'register_email_exists' => 'Email вже зареєстрований',
    'register_passwords_mismatch' => 'Паролі не співпадають',
    
    // Домени
    'domain_search_placeholder' => 'Введіть бажане ім\'я домену',
    'domain_search_button' => 'Перевірити',
    'domain_available' => 'Доступний',
    'domain_unavailable' => 'Зайнятий',
    'domain_price' => 'Ціна',
    'domain_registration' => 'Реєстрація',
    'domain_renewal' => 'Продовження',
    'domain_transfer' => 'Перенесення',
    'domain_whois_title' => 'WHOIS інформація',
    'domain_dns_title' => 'DNS записи',
    'domain_dns_check' => 'Перевірити DNS',
    
    // Хостинг
    'hosting_plans_title' => 'Тарифні плани хостингу',
    'hosting_disk_space' => 'Дисковий простір',
    'hosting_bandwidth' => 'Трафік',
    'hosting_databases' => 'Бази даних',
    'hosting_email_accounts' => 'Поштові скриньки',
    'hosting_domains' => 'Домени',
    'hosting_ssl' => 'SSL сертифікат',
    'hosting_backup' => 'Резервне копіювання',
    'hosting_support' => 'Технічна підтримка',
    'hosting_per_month' => 'грн/міс',
    'hosting_per_year' => 'грн/рік',
    'hosting_order_now' => 'Замовити зараз',
    'hosting_popular' => 'Популярний',
    'hosting_unlimited' => 'Безлімітно',
    
    // VDS/VPS
    'vds_plans_title' => 'Тарифні плани VDS',
    'vds_cpu' => 'CPU',
    'vds_ram' => 'ОЗП',
    'vds_disk' => 'Диск',
    'vds_bandwidth' => 'Канал',
    'vds_cores' => 'ядер',
    'vds_gb' => 'ГБ',
    'vds_tb' => 'ТБ',
    'vds_mbps' => 'Мбіт/с',
    'vds_root_access' => 'Root доступ',
    'vds_kvm' => 'KVM віртуалізація',
    'vds_ssd' => 'SSD диски',
    'vds_ddos_protection' => 'DDoS захист',
    
    // Калькулятор
    'calculator_title' => 'Калькулятор вартості',
    'calculator_configure' => 'Налаштувати конфігурацію',
    'calculator_total' => 'Загальна вартість',
    'calculator_monthly' => 'Щомісяця',
    'calculator_yearly' => 'Щорічно',
    'calculator_save' => 'Економія при оплаті на рік',
    
    // Інструменти
    'tools_site_check_title' => 'Перевірка доступності сайту',
    'tools_site_url_placeholder' => 'Введіть URL сайту',
    'tools_check_button' => 'Перевірити',
    'tools_site_online' => 'Сайт доступний',
    'tools_site_offline' => 'Сайт недоступний',
    'tools_response_time' => 'Час відгуку',
    'tools_status_code' => 'HTTP статус',
    'tools_ip_title' => 'Ваша IP адреса',
    'tools_ip_address' => 'IP адреса',
    'tools_location' => 'Розташування',
    'tools_provider' => 'Провайдер',
    
    // Контакти
    'contacts_title' => 'Контактна інформація',
    'contacts_address' => 'Адреса',
    'contacts_phone' => 'Телефон',
    'contacts_email' => 'Email',
    'contacts_work_hours' => 'Години роботи',
    'contacts_24_7' => '24/7',
    'contacts_form_title' => 'Зворотний зв\'язок',
    'contacts_telegram' => 'Telegram',
    
    // Про компанію
    'about_title' => 'Про StormHosting UA',
    'about_description' => 'Ми надаємо послуги хостингу та реєстрації доменів з 2015 року. Наша команда складається з досвідчених фахівців, які забезпечують стабільну роботу ваших проектів.',
    'about_mission' => 'Наша місія',
    'about_mission_text' => 'Забезпечити надійні та доступні послуги хостингу для українських підприємців та розробників.',
    'about_advantages' => 'Наші переваги',
    'about_advantage_1' => 'Власне обладнання в дата-центрах України',
    'about_advantage_2' => 'Технічна підтримка українською мовою',
    'about_advantage_3' => 'Швидка активація послуг',
    'about_advantage_4' => 'Конкурентні ціни',
    
    // FAQ
    'faq_title' => 'Часто задавані питання',
    'faq_hosting_q1' => 'Як швидко активується хостинг?',
    'faq_hosting_a1' => 'Хостинг активується автоматично протягом 5-10 хвилин після оплати.',
    'faq_hosting_q2' => 'Чи включений SSL сертифікат?',
    'faq_hosting_a2' => 'Так, для всіх тарифів ми надаємо безкоштовний Let\'s Encrypt SSL сертифікат.',
    'faq_domain_q1' => 'Скільки часу займає реєстрація домену?',
    'faq_domain_a1' => 'Реєстрація домену займає від кількох хвилин до кількох годин залежно від зони.',
    
    // Підвал
    'footer_about_title' => 'Про нас',
    'footer_about_text' => 'StormHosting UA - надійний партнер для вашого онлайн бізнесу. Ми надаємо якісні послуги хостингу та підтримки.',
    'footer_services_title' => 'Послуги',
    'footer_support_title' => 'Підтримка',
    'footer_contacts_title' => 'Контакти',
    'footer_copyright' => 'Всі права захищені',
    'footer_developed_by' => 'Розроблено',
    
    // Помилки
    'error_404_title' => 'Сторінка не знайдена',
    'error_404_message' => 'Запитувана сторінка не існує або була переміщена.',
    'error_500_title' => 'Внутрішня помилка сервера',
    'error_500_message' => 'На сервері сталася помилка. Спробуйте пізніше.',
    'error_csrf_token' => 'Помилка безпеки. Оновіть сторінку та спробуйте знову.',
    'error_access_denied' => 'Доступ заборонено',
    'error_maintenance' => 'Сайт на технічному обслуговуванні',
    
    // Повідомлення
    'message_data_saved' => 'Дані збережено успішно',
    'message_data_deleted' => 'Дані видалено успішно',
    'message_operation_completed' => 'Операція виконана успішно',
    'message_invalid_data' => 'Невірні дані',
    'message_access_denied' => 'Недостатньо прав доступу',
    
    // Кнопки та дії
    'btn_save' => 'Зберегти',
    'btn_cancel' => 'Скасувати',
    'btn_delete' => 'Видалити',
    'btn_edit' => 'Редагувати',
    'btn_view' => 'Переглянути',
    'btn_download' => 'Завантажити',
    'btn_upload' => 'Завантажити',
    'btn_search' => 'Пошук',
    'btn_filter' => 'Фільтр',
    'btn_reset' => 'Скинути',
    'btn_close' => 'Закрити',
    'btn_confirm' => 'Підтвердити',
    'btn_back' => 'Назад',
    'btn_next' => 'Далі',
    'btn_previous' => 'Попередня',
    'btn_first' => 'Перша',
    'btn_last' => 'Остання',
    
    // Таблиці
    'table_no_data' => 'Немає даних для відображення',
    'table_loading' => 'Завантаження даних...',
    'table_actions' => 'Дії',
    'table_date' => 'Дата',
    'table_status' => 'Статус',
    'table_total' => 'Всього',
    'table_per_page' => 'на сторінці',
    'table_showing' => 'Показано',
    'table_of' => 'з',
    'table_entries' => 'записів',
    
    // Статуси
    'status_active' => 'Активний',
    'status_inactive' => 'Неактивний',
    'status_pending' => 'Очікує',
    'status_processing' => 'Обробляється',
    'status_completed' => 'Завершено',
    'status_cancelled' => 'Скасовано',
    'status_expired' => 'Закінчився',
    'status_suspended' => 'Призупинено',
    
    // Дати та час
    'date_today' => 'Сьогодні',
    'date_yesterday' => 'Вчора',
    'date_tomorrow' => 'Завтра',
    'date_week_ago' => 'Тиждень тому',
    'date_month_ago' => 'Місяць тому',
    'time_ago' => 'тому',
    'time_minutes' => 'хвилин',
    'time_hours' => 'годин',
    'time_days' => 'днів',
    
    // Розміри
    'size_bytes' => 'байт',
    'size_kb' => 'КБ',
    'size_mb' => 'МБ',
    'size_gb' => 'ГБ',
    'size_tb' => 'ТБ',
    
    // Мова
    'language_ua' => 'Українська',
    'language_en' => 'English',
    'language_ru' => 'Русский',
    'change_language' => 'Змінити мову',
];
?>