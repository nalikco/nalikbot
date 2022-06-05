<?php
namespace Klassnoenazvanie\Helpers;

class Keyboards {
    public static function getMain(): string
    {
        return '{
            "one_time":false,
            "buttons":[
            [
                {
                    "action":{
                        "type":"text",
                        "label":"Меню"
                    },
                    "color":"secondary"
                }
            ]
            ]
        }';
    }

    public static function getMainMenu(): string
    {
        return '{
            "inline":true,
            "buttons":[
            [
                {
                    "action":{
                        "type":"callback",
                        "payload": "{\"action\": \"get_stats\"}",
                        "label":"Статистика"
                    },
                    "color":"secondary"
                }
            ], [
                {
                    "action":{
                        "type":"callback",
                        "payload": "{\"action\": \"get_courses\"}",
                        "label":"Курсы валют"
                    },
                    "color":"secondary"
                }
            ], [
                {
                    "action":{
                        "type":"callback",
                        "payload": "{\"action\": \"get_reminders_menu\"}",
                        "label":"Напоминания"
                    },
                    "color":"secondary"
                }
            ]
            ]
        }';
    }

    public static function getWithCancel(): string
    {
        return '{
            "one_time":false,
            "buttons":[
            [
                {
                    "action":{
                        "type":"text",
                        "label":"Отмена"
                    },
                    "color":"secondary"
                }
            ]
            ]
        }';
    }

    public static function getReminderMainMenu(): string
    {
        return '{
            "inline":true,
            "buttons":[
                [
                    {
                        "action":{
                            "type":"callback",
                            "payload": "{\"action\": \"reminder_create\"}",
                            "label":"Создать"
                        },
                        "color":"positive"
                    }
                ], [
                    {
                        "action":{
                            "type":"callback",
                            "payload": "{\"action\": \"reminder_list_active\"}",
                            "label":"Список активных"
                        },
                        "color":"secondary"
                    }
                ], [
                    {
                        "action":{
                            "type":"callback",
                            "payload": "{\"action\": \"reminder_delete\"}",
                            "label":"Удалить"
                        },
                        "color":"negative"
                    }
                ], [
                    {
                        "action":{
                            "type":"callback",
                            "payload": "{\"action\": \"return\"}",
                            "label":"Вернуться"
                        },
                        "color":"secondary"
                    }
                ]
            ]
        }';
    }
}