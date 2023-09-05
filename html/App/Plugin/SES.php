<?php

namespace App\Plugin;

class SES
{
    const REGION_SINGAPORE = 'ap-southeast-1';
    static $region = 'ap-southeast-1';

    /**
     * Set the region to use
     * @param   string  $region  The region to use
     * @return  void
     */
    static function set_region(string $region)
    {
        self::$region = $region;
    }

    /**
     * Create SES Policy
     * @param   ?string  $sid   The policy sid
     * @param   ?string  $email The email address to create
     * @param   ?int     $limit Sending limit per day
     * @return  string          The policy in json format
     */
    static function create_policy(?string $sid, ?string $email, ?int $limit)
    {
        $policy = [
            'Version' => '2012-10-17',
            'Statement' => [
                [
                    'Effect' => 'Allow',
                    'Action' => [
                        'ses:SendEmail',
                        'ses:SendRawEmail',
                    ],
                    'Resource' => '*',
                ],
            ],
        ];

        if ($sid) {
            $policy['Statement'][0]['Sid'] = $sid;
        }

        if ($limit) {
            $policy['Statement'][0]['Condition'] = [
                'NumericLessThanEquals' => [
                    'ses:DailyQuota' => $limit,
                ],
            ];
        }

        if ($email) {
            if (!isset($policy['Statement'][0]['Condition'])) {
                $policy['Statement'][0]['Condition'] = [];
            }

            $policy['Statement'][0]['Condition']['StringEquals'] = [
                'ses:FromAddress' => $email,
            ];
        }

        return json_encode($policy, JSON_PRETTY_PRINT);
    }

    /**
     * Create new smtp account
     * @param   string  $identifier The IAM user name and email address to create
     * @param   ?string  $email The email address to create
     * @param   ?int     $limit Sending limit per day
     * @return  array
     */
    static function create_smtp_account(string $identifier, ?string $email, ?int $limit)
    {
        $sid = 'AllowSendEmailFrom' . ucfirst($identifier);
        $sid = preg_replace_callback('/[^a-z0-9]+([a-z0-9])?/i', fn ($m) => strtoupper($m[1]), $sid);
        $sid = preg_replace('/[^a-z0-9]/i', '', $sid);

        $policy = self::create_policy($sid, $email, $limit);

        $iam = new \Aws\Iam\IamClient([
            'version' => 'latest',
            'region' => env('AWS_REGION', 'ap-southeast-1'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);

        // check if AutomatedSESUser group exists
        $groups = $iam->listGroups();
        $group = null;
        foreach ($groups['Groups'] as $g) {
            if ($g['GroupName'] === 'AutomatedSESUser') {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            $group = $iam->createGroup([
                'GroupName' => 'AutomatedSESUser',
            ]);
        }

        $iam->attachGroupPolicy([
            'GroupName' => 'AutomatedSESUser',
            'PolicyArn' => 'arn:aws:iam::aws:policy/AmazonSESFullAccess',
        ]);

        $user = $iam->createUser([
            'UserName' => $identifier,
        ]);

        $key = $iam->createAccessKey([
            'UserName' => $identifier,
        ]);

        $iam->putUserPolicy([
            'UserName' => $identifier,
            'PolicyName' => $identifier,
            'PolicyDocument' => $policy,
        ]);

        return [
            'username' => $key['AccessKey']['AccessKeyId'],
            'password' => $key['AccessKey']['SecretAccessKey'],
            'key' => json_decode(json_encode($key), true),
            'AccessKey' => $key['AccessKey']
        ];
    }
}
