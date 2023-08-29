<?php

require_once __DIR__ . '/autoload.php';

use App\Plugin\AWS;

// $smtp = SES::create_smtp_account('test', 'test@skiddph.com', 14);
// // echo $smtp;
// echo json_encode($smtp, JSON_PRETTY_PRINT);

$iam = AWS::iam();

$group_name = 'smtps-skiddph.com';
$group = $iam->createUserGroup($group_name);
$domain = 'skiddph.com';
$group_policy = [
    'Version' => '2012-10-17',
    'Statement' => [
        [
            'Sid' => 'AllowSendEmailIfUsernameMatches',
            'Effect' => 'Allow',
            'Action' => [
                'ses:SendEmail',
                'ses:SendRawEmail',
            ],
            'Resource' => '*',
            'Condition' => [
                'StringEquals' => [
                    'ses:FromAddress' => '${aws:username}@' . $domain,
                ]
            ]
        ],
    ],
];
$group_policy = json_encode($group_policy, JSON_PRETTY_PRINT);
$policy = $iam->createGroupPolicy($group_name, 'smtps-skiddph.com', $group_policy);
echo print_r($policy, true);