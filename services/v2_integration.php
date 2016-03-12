<?php
/** @var \Silex\Application $app */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ParseException;

/*
 * Сохранение рабочего листа и заказ-наряда
 *
 * Исходные данные
 * <code>
 * {
 *   "date": "18.01.2016",
 *   "repairsTypeCode": "ЦБ000002",
 *   "repairsTypeName": "ТО",
 *   "clientStatement": "ТО",
 *   "clientId": "ЦБ000096",
 *   "clientLastName": "",
 *   "clientName": "ООО Рога и копыта",
 *   "clientMiddleName": "",
 *   "clientType": 0,
 *   "gender": 0,
 *   "clientKpp": "682901001",
 *   "clientInn": "",
 *   "clientOKPO": "1234567890",
 *   "clientEmail": "",
 *   "clientAddrFact": "",
 *   "clientAddrPostCodeFact": "",
 *   "clientAddrRegionFact": "",
 *   "clientAddrDistrictFact": "",
 *   "clientAddrCityFact": "",
 *   "clientAddrStreetFact": "",
 *   "clientAddrHouseFact": "",
 *   "clientAddrBuildingFact": "",
 *   "clientAddrApartmentFact": "",
 *   "clientAddr": "",
 *   "clientAddrPostCode": "",
 *   "clientAddrRegion": "",
 *   "clientAddrDistrict": "",
 *   "clientAddrCity": "",
 *   "clientAddrStreet": "",
 *   "clientAddrHouse": "",
 *   "clientAddrBuilding": "",
 *   "clientAddrApartment": "",
 *   "clientPhones": [
 *     {
 *       "PhoneNumber": "+7(905)1234561"
 *     }
 *   ],
 *   "clientpassportSerie": "",
 *   "clientpassportNumber": "",
 *   "clientpassportIssuedAt": "",
 *   "clientpassportIssuedBy": "",
 *   "Contacts": [
 *     {
 *       "ContactsName": "Михайлов Иван Петрович",
 *       "ContactsPhone": "+7(905)1234561"
 *     },
 *     {
 *       "ContactsName": "Михайлова Юлия Геннадиевна",
 *       "ContactsPhone": "+7(980)6325354"
 *     }
 *   ],
 *   "carId": "ЦБ00000030",
 *   "carModelId": "ЦБ00000012",
 *   "carModelName": "C-Crosser",
 *   "carVIN": "",
 *   "carRegNum": "",
 *   "carMileage": "",
 *   "carProductionYear": 2008,
 *   "carEngineId": " ",
 *   "carEngine": "",
 *   "carTransmissionId": " ",
 *   "carTransmission": "",
 *   "car": "",
 *   "carColorId": " ",
 *   "carColor": "",
 *   "quantityWorkType": 2,
 *   "WorkType": [
 *     {
 *       "WorkTypeId": "ЦБ00000021",
 *       "WorkTypeName": "ТО-1",
 *       "WorkTypeStandrtTime": 1,
 *       "WorkTypePrice": 5000,
 *       "WorkTypeSum": 5000
 *     },
 *     {
 *       "WorkTypeId": "ЦБ00000015",
 *       "WorkTypeName": "ТО-2",
 *       "WorkTypeStandrtTime": 1,
 *       "WorkTypePrice": 7000,
 *       "WorkTypeSum": 7000
 *     }
 *   ],
 *   "stageCode": "0000000001",
 *   "stageName": "Заявка", // "Начать работу", "В работе", "Выполнен", "Закрыт"
 * }
 * <code>
 */

$serviceCaseSaver = function (array $data, $forClient) use ($app) {
    $clientData = [
        "id" => $data["clientId"],
        "statement" => $data["clientStatement"],
        "lastName" => $data["clientLastName"],
        "name" => $data["clientName"],
        "middleName" => $data["clientMiddleName"],
        "gender" => $data["gender"],
        "type" => $data["clientType"],
        "kpp" => $data["clientKpp"],
        "inn" => $data["clientInn"],
        "okpo" => $data["clientOKPO"],
        "email" => $data["clientEmail"],
        "addrFact" => $data["clientAddrFact"],
        "addrPostCodeFact" => $data["clientAddrPostCodeFact"],
        "addrRegionFact" => $data["clientAddrRegionFact"],
        "addrDistrictFact" => $data["clientAddrDistrictFact"],
        "addrCityFact" => $data["clientAddrCityFact"],
        "addrStreetFact" => $data["clientAddrStreetFact"],
        "addrHouseFact" => $data["clientAddrHouseFact"],
        "addrBuildingFact" => $data["clientAddrBuildingFact"],
        "addrApartmentFact" => $data["clientAddrApartmentFact"],
        "addr" => $data["clientAddr"],
        "addrPostCode" => $data["clientAddrPostCode"],
        "addrRegion" => $data["clientAddrRegion"],
        "addrDistrict" => $data["clientAddrDistrict"],
        "addrCity" => $data["clientAddrCity"],
        "addrStreet" => $data["clientAddrStreet"],
        "addrHouse" => $data["clientAddrHouse"],
        "addrBuilding" => $data["clientAddrBuilding"],
        "addrApartment" => $data["clientAddrApartment"],
        "phones" => $data["clientPhones"],
        "passportSerie" => $data["clientpassportSerie"],
        "passportNumber" => $data["clientpassportNumber"],
        "passportIssuedAt" => $data["clientpassportIssuedAt"],
        "passportIssuedBy" => $data["clientpassportIssuedBy"],
        "contacts" => $data["Contacts"],
    ];

    $vehicleData = [
        "id" => $data["carId"],
        "modelId" => $data["carModelId"],
        "modelName" => $data["carModelName"],
        "vin" => $data["carVIN"],
        "regNum" => $data["carRegNum"],
        "mileage" => $data["carMileage"],
        "productionYear" => $data["carProductionYear"],
        "engineId" => $data["carEngineId"],
        "engineName" => $data["carEngine"],
        "transmissionId" => $data["carTransmissionId"],
        "transmissionName" => $data["carTransmission"],
        "colorId" => $data["carColorId"],
        "colorName" => $data["carColor"],
        "client" => $clientData,
    ];

    $success = null !== $app['v2']['client_vehicle']($vehicleData, $forClient);

    $works = array_map(function ($srcData) use (&$success, $app, $forClient) {
        $workTypeData = [
            "id" => $srcData["WorkTypeId"],
            "name" => $srcData["WorkTypeName"],
            "standardTime" => $srcData["WorkTypeStandrtTime"],
        ];

        $success = null !== ($typeId = $app['v2']['service_work_type']($workTypeData, $forClient)) && $success;

        return [
            "workId" => $typeId,
            "standardTime" => $srcData["WorkTypeStandrtTime"],
            "price" => $srcData["WorkTypePrice"],
            "quantity" => 1,
            "sum" => $srcData["WorkTypeSum"],
        ];
    }, $data['WorkType']);

    $repairTypeData = [
        "id" => $data["repairsTypeCode"],
        "name" => $data["repairsTypeName"],
    ];

    $success = null !== ($repairTypeId = $app['v2']['service_repair_type']($repairTypeData, $forClient)) && $success;

    if (!$success) {
        $app['logger']->withName('v2_interaction')->info(
            'Рабочий лист не пытаемся сохранять, т.к. не сохранены связанные данные'
        );
        return null;
    }

    static $statusMap = [
        "Заявка" => 'opened',
        "Начать работу" => 'service.reminder',
        "В работе" => 'service.service',
        "Выполнен" => 'service.issuing',
        "Закрыт" => 'completed',
    ];

    $caseData = [
        'no' => $data['code'],
        'works' => $works,
        'activateWorkOrder' => true,
        'totalPaidForWorks' => 0,
        'totalPaidForSpares' => 0,
        'repairType' => $repairTypeId,
        'status' => $statusMap[$data['stageName']],
        'client' => ['id' => $app['association_fetcher']($forClient, 'clients', $clientData['id'])],
        'contact' => ['id' => $app['association_fetcher']($forClient, 'persons', $clientData['id'])],
        'vehicle' => ['id' => $app['association_fetcher']($forClient, 'vehicles', $vehicleData['id'])],
    ];

    return $app['v2_save']($forClient, 'serviceStation/cases', $data['id'], $caseData);
};

$clientSaver = function (array $data, $forClient) use ($app, &$normalizePhone) {
    $clientData = [
        'name' => $data['name'],
        'email' => $data['email'],
        'phones' => array_map(
            function ($item) use ($normalizePhone) {
                return $normalizePhone($item['PhoneNumber']);
            },
            $data['phones']
        ),
        'tags' => [],
        'autosalons' => [],
        'addrPostalCode' => $data["addrPostCode"],
        'addrRegionId' => $data["addrRegion"],
        'addrDistrict' => $data["addrDistrict"],
        'addrCity' => $data["addrCity"],
        'addrStreet' => $data["addrStreet"],
        'addrHouse' => $data["addrHouse"],
        'addrBuilding' => $data["addrBuilding"],
        'addrApartment' => $data["addrApartment"],
    ];

    $type = $data['type'] ? 'individual' : 'business';

    if ('individual' == $type) {
        $clientData = array_merge($clientData, [
            'passportSerie' => $data["passportSerie"],
            'passportNumber' => $data["passportNumber"],
            'passportIssuedAt' => $data["passportIssuedAt"],
            'passportIssuedBy' => $data["passportIssuedBy"],
            'gender' => $data["gender"],
        ]);
    } else {
        $clientData = array_merge($clientData, [
            "kpp" => $data["kpp"],
            "ogrn" => $data["inn"],
        ]);
    }

    if ($v2client = $app['v2_save']($forClient, 'clients', $data['id'], $clientData, ['type' => $type])) {
        foreach ($data['contacts'] as $item) {
            $nameParts = array_pad(preg_split('/\s+/', $item['ContactsName']), 3, '');
            $personData = [
                'id' => $data['id'],
                'clientId' => $data['id'],
                'middleName' => array_pop($nameParts),
                'firstName' => array_pop($nameParts),
                'lastName' => implode(' ', $nameParts),
                'phones' => [
                    $item['ContactsPhone'],
                ],
            ];

            $app['v2']['person']($personData, $forClient);
        }
    }

    return $v2client;
};

$personSaver = function (array $data, $forClient) use ($app, &$normalizePhone) {
    $personData = [
        'clientId' => $app['association_fetcher']($forClient, 'clients', $data['clientId']),
        'middleName' => $data['middleName'],
        'firstName' => $data['firstName'],
        'lastName' => $data['lastName'],
        'phones' => array_map($normalizePhone, $data['phones']),
    ];

    return $app['v2_save']($forClient, 'persons', $data["id"], $personData);
};

$clientVehicleSaver = function (array $data, $forClient) use ($app) {
    if (null === $ownerId = $app['v2']['client']($data['client'], $forClient)) {
        return null;
    }

    $vehicleData = [
        'modelId' => $app['v2']['vehicle_model'](['id' => $data["modelId"], 'name' => $data["modelName"]], $forClient),
        'vin' => $data["vin"],
        'regNumber' => $data["regNum"],
        'mileage' => $data["mileage"],
        'productionYear' => $data["productionYear"],
        'clientId' => $ownerId,
    ];

    return $app['v2_save']($forClient, 'vehicles', $data["id"], $vehicleData);
};

$serviceWorkTypeSaver = function (array $data, $forClient) use ($app) {
    return $app['v2_save']($forClient, 'serviceStation/workTypes', $data["id"], [
        'name' => $data["name"],
        "standardTime" => $data["standardTime"],
    ]);
};

$serviceRepairTypeSaver = function (array $data, $forClient) use ($app) {
    return $app['v2_save']($forClient, 'serviceStation/repairType', $data["id"], ['name' => $data["name"]]);
};

$vehicleModelGetter = function (array $data, $forClient) use ($app) {
    if (!$modelId = $app['association_fetcher']($forClient, 'models', $data['id'])) {
        $result = $app['v2_send']($forClient, 'get', '/models', ['query' => ['name' => $data["name"]]]);
        if ($result /* not null and not empty array */) {
            $modelId = $result[0]['id'];
            $app['association_saver']($forClient, 'models', $data['id'], $modelId);
        }
    }

    return $modelId;
};

$normalizePhone = function ($originalNumber) {
    $number = preg_replace('/\D/', '', $originalNumber);

    return [
        'number' => $originalNumber,
        'type' => strlen($number) == 11 && in_array(substr($number, 0, 2), ['79', '89'])
            ? 'M'
            : 'W'
    ];
};

// ------------------------------------------------------------

$app['v2'] = [
    'client' => $clientSaver,
    'person' => $personSaver,
    'service_case' => $serviceCaseSaver,
    'vehicle_model' => $vehicleModelGetter,
    'client_vehicle' => $clientVehicleSaver,
    'service_work_type' => $serviceWorkTypeSaver,
    'service_repair_type' => $serviceRepairTypeSaver,
];

// ============================================================

$app['v2_send'] = $app->protect(
    /**
     * @param string $clientId
     * @param string $method
     * @param string $url
     * @param array $options
     *
     * @return array|null
     */
    function ($clientId, $method, $url, $options = []) use ($app) {
        /** @var \Monolog\Logger $logger */
        $logger = $app['logger']->withName('v2_send');

        $clientConfig = $app['client_fetcher']($clientId);

        $http = new Client([
            'base_url' => $clientConfig['v2']['base_url'],
            'defaults' => [
                'debug' => $app['debug'],
                'exceptions' => false,
                'timeout' => 5, // sec
                'headers' => [
                    'X-AutoCRM-API' => '1',
                    'X-AutoCRM-Access-Token' => $clientConfig['v2']['auth_key']
                ],
            ],
        ]);

        $response = $http->send($http->createRequest($method, $url, $options));

        if (substr($response->getStatusCode(), 0, 1) !== '2') {
            $result = null;
            $logger->error(
                'Ошибка API запроса к v2. Получен ошибочный код ответа',
                [
                    'response' => [
                        'status' => $response->getStatusCode(),
                        'body' => $response->getBody()->getContents(),
                    ],
                ]
            );
        } else {
            try {
                $savedData = $response->json();
                if (!$savedData['success']) {
                    $result = null;
                    $logger->error(
                        'Ошибка API запроса к v2. API v2 вернул ошибку',
                        [
                            'response' => [
                                'status' => $response->getStatusCode(),
                                'body' => $response->json(),
                            ],
                        ]
                    );
                } else {
                    $result = $savedData['result'];
                }
            } catch (ParseException $e) {
                $result = null;
                $logger->error(
                    'Ошибка API запроса к v2. Полученный ответ не является валидным json-ом',
                    [
                        'response' => [
                            'status' => $response->getStatusCode(),
                            'body' => $response->getBody()->getContents(),
                        ],
                    ]
                );
            }
        }

        return $result;
    }
);

$app['v2_save'] = $app->protect(
    /**
     * @param string $clientId
     * @param string $type
     * @param string $id
     * @param array  $data
     * @param array  $query
     * @param string $idField
     *
     * @return null|integer
     */
    function ($clientId, $type, $id, $data, $query = [], $idField = 'id') use ($app) {
        /** @var \Monolog\Logger $logger */
        $logger = $app['logger']->withName('v2_saver');

        $crmId = $app['association_fetcher']($clientId, $type, $id);

        $url = implode('/', ['', $type, ($crmId ? 'save/' . $crmId : 'create')]);
        $options = ['query' => $query, 'json' => $data];
        $logger->debug('Отправка запроса "' . $url . '"', $options);

        $result = $app['v2_send']($clientId, 'post', $url, $options);

        if ($result !== null) {
            $app['association_saver']($clientId, $type, $id, $result[$idField]);
        }

        return $result[$idField];
    }
);

