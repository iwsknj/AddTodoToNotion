<?php

const NOTION_ENDPOINT = 'https://api.notion.com/v1/pages';
const TITLE_COLUMN_NAME = 'タイトル';
const DATE_COLUMN_NAME = '行動予定日';

$title = $argv[1] ?? null;
$date = $argv[2] ?? null;
$formattedDate = null;

if (!$title) {
    echo 'Error: タイトルが入力されていません。';
}

if ($date) {
    $dateTime = DateTime::createFromFormat('Y/m/d', $date);
    $formattedDate = $dateTime->format('Y-m-d');
}

addTaskToNotion($title, $formattedDate);


/**
 * notionへAPIコールしてタスク追加
 *
 * @param string $title
 * @param string|null $formattedDate
 * @return void
 */
function addTaskToNotion(string $title, ?string $formattedDate = null)
{
    $payload = [
        'parent' => [
            'database_id' => $_ENV['DATABASE_ID'],
        ],
        'properties' => [
            'TITLE_COLUMN_NAME' => [
                'title' => [
                        [
                        'text' => [
                            'content' => $title,
                        ],
                    ],
                ],
            ],
        ],
    ];

    if ($formattedDate) {
        $payload['properties'][DATE_COLUMN_NAME] = [
            'type' => 'date',
            'date' => [
                'start' => $formattedDate,
            ],
        ];
    }

    $options = [
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-type: application/json',
            'Notion-Version: 2021-08-16',
            'Authorization: Bearer ' . $_ENV['NOTION_TOKEN'],
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POSTFIELDS => json_encode($payload)
    ];

    $ch = curl_init(NOTION_ENDPOINT);
    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($responseCode === 200) {
        if (isset($title) && isset($formattedDate)) {
            echo '「' . $title . '（' . $formattedDate . '）」を追加しました';
        } else if (isset($title)) {

            echo '「' . $title . '」を追加しました';
        }
    } else {
        echo 'Error: ' . $response;
    }
}
