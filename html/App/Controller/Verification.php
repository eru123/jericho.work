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

    public static function do_verify_mail_action(array $data): bool
    {
        return match ($data['action']) {
            'add_email' => static::action_add_mail($data),
            'newsletter_add' => Newsletter::action_newsletter_add($data),
            default => false,
        };
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

            $action = static::do_verify_mail_action($data);

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

        $data = $data + [
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
            <p><b style="background-color: #eee; padding: 5px 10px; border-radius: 5px;">${ code }</b></p>
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
                'email' => $email,
            ], FORMAT_TEMPLATE_DOLLAR_CURLY),
            'type' => Mails::TYPE_TRANSACTIONAL,
            'priority' => Mails::PRIORITY_HIGH
        ]);

        return [
            'success' => 'Success. Please check your email for the verification code.',
            'verification_id' => $data['id'],
        ];
    }

    public static function make(array $data, string $template)
    {
        $user_id = intval(isset($data['user_id']) ? $data['user_id'] : 0);

        if (!isset($data['email'])) {
            throw new Exception('Missing email', 400);
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email', 400);
        }

        $email = strtolower($data['email']);
        
        if ($user_id) {
            $data['user_id'] = $user_id;
        }
        $user = Users::find($user_id);
        if (!$user) {
            $user = [
                'name' => isset($data['name']) ? $data['name'] : 'there',
            ];
        }

        $code = static::generate_code(6);
        $expires_at = isset($data['expires_at']) ? $data['expires_at'] : date('Y-m-d H:i:s', strtotime('+2 hours'));
        $hash = hash('sha256', json_encode([
            'user_id' => $user_id,
            'identifier' => $email,
            'code' => $code,
            'expires_at' => $expires_at,
        ]));

        if (Verifications::find(Raw::build('`hash` = ? AND `status` = 0 ', [$hash]))) {
            throw new Exception('Identical verification found, please try again.', 400);
        }

        $verf = Verifications::find(Raw::build('`identifier` = ? AND `type` = ? AND `action` = ? AND `status` = 0 AND `expires_at` > ?', [$email, 'email', $data['action'], date('Y-m-d H:i:s')]));
        if ($verf) {
            return $verf + $data;
        }

        $data['hash'] = $hash;
        $data['code'] = $code;
        $data['expires_at'] = $expires_at;
        $data['user_id'] = $user_id;
        $data['identifier'] = $email;
        $insert = Verifications::insert($data);
        if (!$insert->rowCount()) {
            throw new Exception('Failed to create mail verification', 500);
        }
        $data['id'] = DB::instance()->last_insert_id();
        $link = env('BASE_URL') . '/verify/' . $hash;
        
        $data['link'] = $link;

        Mailer::queue([
            'to' => $email,
            'subject' => isset($data['subject']) ? $data['subject'] : 'Verify your email address',
            'body' => Format::template($template, [
                'name' => $user['name'],
                'code' => $code,
                'link' => $link,
                'email' => $email,
            ], FORMAT_TEMPLATE_DOLLAR_CURLY),
            'type' => Mails::TYPE_TRANSACTIONAL,
            'priority' => Mails::PRIORITY_HIGH
        ]);

        return $data;
    }

    public static function verify_mail(Context $c)
    {
        $rdata = $c->json();
        $user_id = intval($c->jwt['id']);

        if (!isset($rdata['verification_id'])) {
            throw new Exception('Missing verification id', 400);
        }

        if (!isset($rdata['code'])) {
            throw new Exception('Missing verification code', 400);
        }

        $verification_id = intval($rdata['verification_id']);
        $code = $rdata['code'];

        if (!$user_id) {
            throw new Exception('Invalid user', 400);
        }

        $verification = Verifications::find(Raw::build('`id` = ? AND `status` = 0', [$verification_id]));
        if (!$verification) {
            throw new Exception('Verification not found', 404);
        }

        if ($verification['user_id'] !== $user_id) {
            throw new Exception('Invalid user', 400);
        }

        if (strval($verification['code']) !== strval($code)) {
            throw new Exception('Invalid verification code', 400);
        }

        $action = static::do_verify_mail_action($verification);

        if (!$action) {
            throw new Exception('Mail verified but failed to execute action.', 500);
        }

        return [
            'success' => 'Email verified successfully.',
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
                // Do I really need to remove these? or just leave them as is after verification?
                // 'hash' => Raw::build('NULL'),
                // 'code' => Raw::build('NULL'),
            ]);

            $db->pdo()->commit();
            return true;
        } catch (Throwable $th) {
            $db->pdo()->rollBack();
            throw $th;
        }
    }
}
