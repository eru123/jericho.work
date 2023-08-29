<?php

namespace App\Plugin;

use Aws\Iam\IamClient;
use Aws\Result;

class AWS
{
    const REGION_SINGAPORE = 'ap-southeast-1';
    static $region = 'ap-southeast-1';
    private $instance = null;

    static function set_region(string $region)
    {
        self::$region = $region;
    }

    function __construct(IamClient $am)
    {
        $this->instance = $am;
    }

    static function iam(): self
    {
        $iam = new IamClient([
            'region' => self::$region,
            'version' => 'latest',
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        return new self($iam);
    }

    function createUserGroup(string $name): ?array
    {
        $groups = $this->instance->listGroups();
        $group = null;
        foreach ($groups['Groups'] as $g) {
            if ($g['GroupName'] === $name) {
                $group = $g;
                break;
            }
        }

        if (!$group) {
            $group = $this->instance->createGroup([
                'GroupName' => $name,
            ]);
        }

        return $group instanceof Result ? $group->toArray() : $group;
    }

    function userGroups(string $name): ?array
    {
        if (!$name) {
            $groups = $this->instance->listGroupsForUser([
                'UserName' => $name,
            ]);
        } else {
            $groups = $this->instance->listGroups();
        }

        return $groups?->toArray();
    }

    function createGroupPolicy(string $group, string $policy, string $document): ?array
    {
        return $this->instance->putGroupPolicy([
            'GroupName' => $group,
            'PolicyName' => $policy,
            'PolicyDocument' => $document,
        ])?->toArray();
    }

    function attachGroupPolicy(string $group, string $policy): ?Result
    {
        return $this->instance->attachGroupPolicy([
            'GroupName' => $group,
            'PolicyArn' => $policy,
        ]);
    }

    function createUser(string $name): ?Result
    {
        $user = $this->instance->createUser([
            'UserName' => $name,
        ]);

        return $user;
    }

    function createAccessKey(string $name): ?Result
    {
        $key = $this->instance->createAccessKey([
            'UserName' => $name,
        ]);

        return $key;
    }

    function putUserPolicy(string $name, string $policy, string $document): ?Result
    {
        return $this->instance->putUserPolicy([
            'UserName' => $name,
            'PolicyName' => $policy,
            'PolicyDocument' => $document,
        ]);
    }

    function attachUserPolicy(string $name, string $policy): ?Result
    {
        return $this->instance->attachUserPolicy([
            'UserName' => $name,
            'PolicyArn' => $policy,
        ]);
    }

    function attachUserGroup(string $user, string $group): ?Result
    {
        return $this->instance->addUserToGroup([
            'UserName' => $user,
            'GroupName' => $group,
        ]);
    }

    function removeUserGroup(string $user, string $group): ?Result
    {
        return $this->instance->removeUserFromGroup([
            'UserName' => $user,
            'GroupName' => $group,
        ]);
    }
}
