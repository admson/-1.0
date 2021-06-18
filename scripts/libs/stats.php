<?php
    // Bot statistics lib

    class Stats extends scriptController{

        //Скриптовые менюшки по которым просчитываем примерное время человека в боте
        //Среднее время проведенное в меню(диалоге, скрипте) в секундах
        public $action_times = [
            'login' => 60, // на и поиск фильма +-60 сек
            'show_film' => 120, // На просмотр карточки фильма +- 120 сек
            'cmd_help' => 30,
            'show_stats' => 60,
        ];

        public function __construct()
        {
            parent::__construct();
        }

        //  Добавляем статистику в Базу данных
        public function addStat($chat_id,$action){
            $this->db->insert("INSERT INTO stats(chat_id,action) VALUES('$chat_id','$action')");
        }

        public function getFilterByPeriod($period) {
            if ($period == "today") { // За сегодня
                $filter = "DATE(`created_at`) = DATE(CURDATE())";
            }elseif ($period == "curmonth") { // За текущий месяц
                $filter = "date_format(created_at, '%Y%m') = date_format(now(), '%Y%m')";
            }elseif ($period == "lastmonth") { // За прошлый месяц
                $filter = "MONTH(created_at) = MONTH(NOW()) - 1";
            }elseif ($period == "week") { // За неделю
                $filter = "`created_at` >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            }

            return $filter;
        }

        // Получаем статистику по действиям
        public function getStats($action,$period = "today") {
            $filter = self::getFilterByPeriod($period);

            $data = $this->db->select("SELECT * FROM stats WHERE action='$action' AND $filter");
            return count($data); // Возвращаем количество записей в статистике
        }

        // Получаем статистику по аккаунтам
        public function getAccountsStats($period = "today") {
            $filter = self::getFilterByPeriod($period);

            $data = $this->db->count("SELECT COUNT(1) FROM _accounts WHERE $filter");
            return $data; // Возвращаем количество записей в статистике
        }

        //Получаем статистику по времени в боте
        public function getUsedTime($period = "today") {
            $filter = self::getFilterByPeriod($period);

            $sec_time = 0;

            $data = $this->db->select("SELECT * FROM stats WHERE $filter");
            foreach ($data as $value) {
                if (isset($this->action_times[$value['action']])) $sec_time+= $this->action_times[$value['action']];
            }

            return round(($sec_time/60),1); // Возвращаем время в минутах
        }
    }