<?php
namespace Klassnoenazvanie\Helpers;

class Keyboards {
    public static function getMain() {
        return '{
            "one_time":false,
            "buttons":[
            [
                {
                    "action":{
                        "type":"text",
                        "label":"Курсы валют"
                    },
                    "color":"secondary"
                },
                {
                    "action":{
                        "type":"text",
                        "label":"Напоминания"
                    },
                    "color":"primary"
                }
            ]
            ]
        }';
    }

    public static function getWithCancel() {
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

    public static function getReminderMainMenu() {
        return '{
            "one_time":false,
            "buttons":[
                [
                    {
                        "action":{
                            "type":"text",
                            "label":"Создать"
                        },
                        "color":"positive"
                    },
                    {
                        "action":{
                            "type":"text",
                            "label":"Список активных"
                        },
                        "color":"secondary"
                    }
                ], [
                    {
                        "action":{
                            "type":"text",
                            "label":"Вернуться"
                        },
                        "color":"negative"
                    }
                ]
            ]
        }';
    }

    public static function clear() {
        return '{"buttons":[],"one_time":true}';
    }
}