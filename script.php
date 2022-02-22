<?php

const NOTION_ENDPOINT = 'https://api.notion.com/v1/pages';
const TITLE_COLUMN_NAME = 'タイトル';
const MEMO_COLUMN_NAME = 'メモ';

$queries = explode(' ', $argv[1]);
if (count($queries) > 0) {
    $title = $queries[0];
    $memo = $queries[1];
} else {
    $title = $argv[1];
    $memo = null;
}

if (!$title) {
    echo 'Error: タイトルが入力されていません。';
}

// 日付
// if ($date) {
//     $dateTime = DateTime::createFromFormat('Y/m/d', $date);
//     $formattedDate = $dateTime->format('Y-m-d');
// }

addTaskToNotion($title, $memo);


/**
 * notionへAPIコールしてタスク追加
 *
 * @param string $title
 * @param string|null $formattedDate
 * @return void
 */
function addTaskToNotion(string $title, ?string $memo = null)
{
    $payload = [
        'parent' => [
            'database_id' => $_ENV['DATABASE_ID'],
        ],
        'properties' => [
            TITLE_COLUMN_NAME => [
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

    // 日付
    // if ($formattedDate) {
    //     $payload['properties'][DATE_COLUMN_NAME] = [
    //         'type' => 'date',
    //         'date' => [
    //             'start' => $formattedDate,
    //         ],
    //     ];
    // }

    // メモ
    if ($memo) {
        $payload['properties'][MEMO_COLUMN_NAME] = [
            'rich_text' => [
                [
                    'text' => [
                        'content' => $memo,
                    ],
                ]
            ]
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
        if (isset($title) && isset($memo)) {
            echo '「' . $title . '（' . $memo . '）」を追加しました';
        } elseif (isset($title)) {
            echo '「' . $title . '」を追加しました';
        }
    } else {
        echo 'Error: ' . $response;
    }
}
