Микросервис передачи данных из 1С в AutoCRM v2
==============================================

Задача, решаемая микросервисом - буферизация получаемых от 1С данных и
отправка их в асинхронном режиме в v2.

На данный момент реализована загрузка данных заказ-нарядов сервиса с созданием
в v2 рабочего листа, заказ-наряда и всех связанных с ними данных (подразделения, клиента
и его контактных лиц, автомобиля клиента, видов работ сервиса, типов ремонта, а также
пользователей, связанных с рабочим листом).

Конфигурация микросервиса
-------------------------

В директории `config` необходимо сконфигурировать два файла

Имя файла      | Описание
---------------|-------------------------
`config.php`   | Общие настройки микросервиса, такие как соединение с БД и уровень логгирования.
`clients.php`  | Настройки клиентов микросервиса. В файле `clients.php.dist` есть пример с пояснениями значений параметров.

Получение данных из 1С
----------------------

Есть одна единственная точка входа для 1С '/api/package/create', куда методом
POST передаются данные в формате JSON.

В запросе от 1С ожидаются следующие заголовки:

Заголовок     | Описание
--------------|-------------------------------------------------------------------
`X-API-Key`   | Ключ-идентификатор клиента для данного запроса, например `YYDExrEg8ptM24PS6ozROxfdo8LRWq7d`
`Content-Type`| Данные принимаются только в формате JSON, поэтому значение должно быть `application/json`

Тело запроса должно содержать JSON следующего вида.

```json
{
  "DataType": "Заказ-наряд",
  "Id": "А000000011",
  "Date": "23.12.2009",
  "ClientStatement": "",
  "Recommendations": "",
  "Department": {
    "DataType": "Цеха",
    "Id": "00000   ",
    "Name": "Основной цех"
  },
  "Employees": [
    {
      "DataType": "Сотрудники",
      "Id": "Администратор",
      "Name": "Беликова Мария Сергеевна",
      "Role": "Ответственный"
    }
  ],
  "RepairType": {
    "DataType": "Виды ремонта",
    "Id": "ЦБ000001",
    "Name": "ТО-0"
  },
  "Client": {
    "DataType": "Контрагенты и контакты",
    "Id": "00038",
    "LastName": "Соколов",
    "Name": "Олег",
    "MiddleName": "Степанович",
    "Gender": "Мужской",
    "Type": "Частное лицо",
    "Kpp": "",
    "Inn": "",
    "Ogrn": "",
    "Addresses": [
      {
        "Addr": "г.Тула. ул.Сойфера, д.5",
        "DataType": "Адреса",
        "Type": "Юридический адрес",
        "PostCode": "",
        "Region": "",
        "District": "",
        "City": "",
        "Street": "",
        "House": "",
        "Building": "",
        "Apartment": ""
      },
      {
        "Addr": "г.Тула, ул.Кутузова д.90,кв.47",
        "DataType": "Адреса",
        "Type": "Фактический адрес",
        "PostCode": "",
        "Region": "",
        "District": "",
        "City": "",
        "Street": "",
        "House": "",
        "Building": "",
        "Apartment": ""
      }
    ],
    "Contacts": [],
    "Email": "s@mail.ru",
    "Phones": [
      {
        "DataType": "Телефоны",
        "Type": "Мобильный телефон",
        "Number": "+7 (980) 6325354"
      },
      {
        "DataType": "Телефоны",
        "Type": "Домашний телефон",
        "Number": "+3 (3821) 2262640"
      }
    ],
    "Documents": [
      {
        "DataType": "Подтверждающие документы",
        "Type": "",
        "Serie": "",
        "Number": "",
        "IssuedAt": "",
        "IssuedBy": ""
      }
    ]
  },
  "Car": {
    "DataType": "Автомобили",
    "Id": "0000000006",
    "VIN": "672756H565",
    "Model": {
      "DataType": "Модели автомобилей",
      "Id": "0000000010",
      "Name": "MERCEDES BENZ S600 (140)"
    },
    "RegNum": "",
    "Mileage": "",
    "ProductionYear": "",
    "Engine": {
      "DataType": "Типы двигателей",
      "Id": "00000002",
      "Name": "Бензин 1,5"
    },
    "Transmission": {
      "DataType": "Типы КПП",
      "Id": "00000001",
      "Name": "Механика"
    },
    "Color": {
      "DataType": "Цвета",
      "Id": "00000003",
      "Name": "Серебристый металлик"
    }
  },
  "Works": [
    {
      "DataType": "Работы заказ-наряда",
      "Id": 1,
      "Type": {
        "DataType": "Автоработы",
        "Id": "0000000047",
        "Name": "Талон N1 (2000-3000 км пробега)",
        "StandardTime": "1,00",
        "Code": ""
      },
      "StandardTime": "1,00",
      "Price": "1 350,00",
      "Quantity": "1,00",
      "DiscountPercent": "",
      "Sum": "1 350,00"
    },
    {
      "DataType": "Работы заказ-наряда",
      "Id": 2,
      "Type": {
        "DataType": "Автоработы",
        "Id": "0000000065",
        "Name": "Замена масла в двигателе и масляного фильтра (на прогретом двигателе)",
        "StandardTime": "",
        "Code": ""
      },
      "StandardTime": "1,00",
      "Price": "350,00",
      "Quantity": "1,00",
      "DiscountPercent": "",
      "Sum": "350,00"
    }
  ],
  "Materials": [
    {
      "DataType": "Материалы заказ-наряда",
      "Id": 1,
      "Type": {
        "DataType": "Номенклатура",
        "Id": "00000138",
        "Name": "Formula RS 10W60 (1 lt)",
        "Code": "18330-0060"
      },
      "Price": "550,00",
      "Quantity": "4,00",
      "DiscountPercent": "",
      "Sum": "2 200,00"
    }
  ],
  "Stage": {
    "DataType": "Состояния заказ-нарядов",
    "Id": "0000000005",
    "Name": "Закрыт"
  }
}
```

Полученные от 1С данные складываются в хранилище и сразу отдается ответ со статусом `201 Created`.
Если положить в хранилище не удалось, то возвращается ответ со статусом `500 Internal Server Error`.


Отправка данных в CRM v2
------------------------

Сразу за отдачей ответа запускается цикл обработки отложенных записей. Из очереди выбираются все
ранее неотправленные данные (как новые, так и те, которые уже пытались отправить, но получили ошибку)
и производится их поочередная отправка.

Все операции логгируются в БД в таблицу `logs`.

