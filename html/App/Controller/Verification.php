<?php

namespace App\Controller;

use App\Plugin\DB;
use App\Plugin\Vite;
use App\Plugin\Mailer;
use eru123\orm\Raw;
use eru123\helper\Format;
use PDOStatement;
use Throwable;
use Exception;
use App\Models\Verifications;
use App\Models\Users;
use App\Models\Mails;
use eru123\router\Context;

class Verification
{
    public static function generate_code(int $len = 6): string
    {
        return substr(str_shuffle(str_repeat('0123456789', $len)), 0, $len);
    }

    public static function verify_from_link(string $token): array
    {
        try {
            $date = date('Y-m-d H:i:s');
            $data = Verifications::find(Raw::build('`hash` = ? AND `expires_at` > ? AND `status` = 0 ORDER BY `id` DESC', [$token, $date]));
            if (!$data) {
                return [
                    'title' => 'Verification Failed',
                    'error' => 'The link might be already used or expired.',
                ];
            }

            $action = false;
            switch ($data['action']) {
                case 'add_email':
                    $action = static::action_add_mail($data);
            }

            if (!$action) {
                return [
                    'title' => 'Verification Error',
                    'error' => 'Link is verified but failed to execute action.',
                ];
            }

            return [
                'title' => 'Verification Success',
                'success' => 'Link has been verified successfully.',
            ];
        } catch (Throwable $th) {
            return [
                'title' => 'Verification Error',
                'error' => $th->getMessage(),
            ];
        }

        return [
            'title' => 'Verification Failed',
            'error' => 'The verification link is invalid',
        ];
    }

    public static function add_mail(Context $c)
    {
        $rdata = $c->json();
        $user_id = intval($c->jwt['id']);

        if (!isset($rdata['email'])) {
            throw new Exception('Missing email', 400);
        }

        if (!filter_var($rdata['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email', 400);
        }

        $email = strtolower($rdata['email']);

        if (!$user_id) {
            throw new Exception('Invalid user', 400);
        }

        if (Users::email_exists($email)) {
            throw new Exception('Email already in use', 400);
        }

        $user = Users::find($user_id);
        if (!$user) {
            throw new Exception('User not found', 404);
        }

        $code = static::generate_code(6);
        $expires_at = date('Y-m-d H:i:s', strtotime('+2 hours'));
        $hash = hash('sha256', json_encode([
            'user_id' => $user_id,
            'identifier' => $email,
            'code' => $code,
            'expires_at' => $expires_at,
        ]));

        if (Verifications::find(Raw::build('`hash` = ? AND `status` = 0 ', [$hash]))) {
            throw new Exception('Identical verification found, please try again.', 400);
        }

        $data = [
            'user_id' => $user_id,
            'type' => 'email',
            'identifier' => $email,
            'code' => $code,
            'hash' => $hash,
            'action' => 'add_email',
            'status' => false,
            'expires_at' => $expires_at,
        ];
        $insert = Verifications::insert($data);
        if (!$insert->rowCount()) {
            throw new Exception('Failed to create mail verification', 500);
        }
        $data['id'] = DB::instance()->last_insert_id();
        $link = env('BASE_URL') . '/verify/' . $hash;
        $template = '
            <p>Hi there, ${ name }!</p>
            <p>Please enter the following code within 2 hours to verify your email address:</p>
            <p><b>${ code }</b></p>
            <p>or click the link below:<br><a href="${ link }">${ link }</a></p>
            <p>Best regards,<br>SKIDD PH</p>
            <br>
            <br>
            <p><small>This is an automated message. Please do not reply to this email.</small></p>
            <p><small>If you did not register on our site using this email address, please ignore this email.</small></p>
        ';

        Mailer::queue([
            'to' => $email,
            'subject' => 'Verify your email address',
            'body' => Format::template($template, [
                'name' => $user['name'],
                'code' => $code,
                'link' => $link,
            ], FORMAT_TEMPLATE_DOLLAR_CURLY),
            'type' => Mails::TYPE_TRANSACTIONAL,
            'priority' => Mails::PRIORITY_HIGH
        ]);

        return [
            'success' => 'Success. Please check your email for the verification code.',
            'verification_id' => $data['id'],
        ];
    }

    public static function action_add_mail(array $data): bool
    {
        $db = DB::instance();
        try {
            $db->pdo()->beginTransaction();

            $user = Users::find($data['user_id']);
            if (!$user) {
                throw new Exception('User not found', 404);
            }

            $user_changes = ['emails'];

            $data['identifier'] = strtolower($data['identifier']);
            if (Users::email_exists($data['identifier'])) {
                throw new Exception('Email already in use', 400);
            }

            $emails = is_array($user['emails']) ? $user['emails'] : [];
            if (in_array($data['identifier'], $emails)) {
                throw new Exception('Email already verified', 400);
            }

            $emails[] = $data['identifier'];
            $user['emails'] = $emails;

            if (empty($user['email']) || empty($user['email_verified'])) {
                $user['email'] = $data['identifier'];
                $user['email_verified'] = true;
                $user_changes[] = 'email';
                $user_changes[] = 'email_verified';
            }

            $new_user = [];
            foreach ($user_changes as $key) {
                $new_user[$key] = $user[$key];
            }

            Vite::data([
                'new_user' => $new_user
            ]);

            Users::update($user['id'], $new_user);
            Verifications::update($data['id'], [
                'status' => 1,
                'hash' => Raw::build('NULL'),
                'code' => Raw::build('NULL'),
            ]);

            $db->pdo()->commit();
            return true;
        } catch (Throwable $th) {
            $db->pdo()->rollBack();
            throw $th;
        }

        return false;
    }
}
